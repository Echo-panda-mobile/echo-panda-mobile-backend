<?php

namespace App\Jobs;

use App\Models\Song;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Exception;

class ProcessUploadedSong implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $songId;

    public function __construct(int $songId)
    {
        $this->songId = $songId;
    }

    public function handle(): void
    {
        $song = Song::find($this->songId);
        if (! $song) {
            return;
        }

        $song->processing_status = 'processing';
        $song->save();

        $originalKey = $song->original_key;
        try {
            // Download original to local temp
            $tmpIn = sys_get_temp_dir().'/'.Str::random(10).'-in';
            $contents = Storage::disk('s3')->get($originalKey);
            file_put_contents($tmpIn, $contents);

            // Transcode to 128k and 320k
            $tmp128 = sys_get_temp_dir().'/'.Str::random(10).'-128.mp3';
            $tmp320 = sys_get_temp_dir().'/'.Str::random(10).'-320.mp3';

            // Use ffmpeg to transcode (assumes ffmpeg is installed)
            $cmd128 = "ffmpeg -y -i " . escapeshellarg($tmpIn) . " -b:a 128k -vn " . escapeshellarg($tmp128);
            exec($cmd128, $out128, $rc1);

            $cmd320 = "ffmpeg -y -i " . escapeshellarg($tmpIn) . " -b:a 320k -vn " . escapeshellarg($tmp320);
            exec($cmd320, $out320, $rc2);

            if (! file_exists($tmp128) || ! file_exists($tmp320)) {
                throw new Exception('Transcoding failed');
            }

            // Upload derivatives
            $artist = $song->artistModel;
            $artistSlug = Str::slug($artist->name ?: 'artist-'.$artist->id);
            $key128 = "audio/128/{$artistSlug}/{$song->id}-128.mp3";
            $key320 = "audio/320/{$artistSlug}/{$song->id}-320.mp3";

            Storage::disk('s3')->put($key128, fopen($tmp128, 'r'));
            Storage::disk('s3')->put($key320, fopen($tmp320, 'r'));

            // Get duration and bitrate via ffprobe
            $duration = 0;
            $bitrate = 0;
            $probeCmd = "ffprobe -v error -show_entries format=duration,bit_rate -of default=noprint_wrappers=1:nokey=1 " . escapeshellarg($tmpIn);
            $probeOutput = array_values(array_filter(array_map('trim', explode("\n", (string) shell_exec($probeCmd)))));
            if (isset($probeOutput[0]) && is_numeric($probeOutput[0])) {
                $duration = (int) round((float) $probeOutput[0]);
            }
            if (isset($probeOutput[1]) && is_numeric($probeOutput[1])) {
                $bitrate = (int) round(((float) $probeOutput[1]) / 1000);
            }

            if ($duration <= 0) {
                $duration = max(1, (int) $song->duration);
            }

            // Generate a Spotify-style preview clip (30 seconds max)
            $tmpPreview = sys_get_temp_dir().'/'.Str::random(10).'-preview.mp3';
            $cmdPreview = "ffmpeg -y -i " . escapeshellarg($tmpIn) . " -t 30 -vn -b:a 128k " . escapeshellarg($tmpPreview);
            exec($cmdPreview, $outPreview, $rcPreview);

            // Generate downsampled raw PCM for waveform (1kHz mono)
            $tmpRaw = sys_get_temp_dir().'/'.Str::random(10).'-raw.s16le';
            $cmdRaw = "ffmpeg -y -i " . escapeshellarg($tmpIn) . " -ac 1 -ar 1000 -f s16le " . escapeshellarg($tmpRaw);
            exec($cmdRaw, $outRaw, $rcRaw);

            $waveformJson = null;
            if (file_exists($tmpRaw)) {
                $rawData = file_get_contents($tmpRaw);
                if ($rawData !== false && strlen($rawData) > 0) {
                    $samples = unpack('s*', $rawData);
                    if (is_array($samples) && count($samples) > 0) {
                        $samples = array_values($samples);
                        $buckets = 200;
                        $totalSamples = count($samples);
                        $bucketSize = max(1, (int) floor($totalSamples / $buckets));
                        $peaks = [];
                        for ($i = 0; $i < $buckets; $i++) {
                            $start = $i * $bucketSize;
                            $chunk = array_slice($samples, $start, $bucketSize);
                            if (count($chunk) === 0) {
                                $peaks[] = 0;
                                continue;
                            }
                            $sum = 0;
                            foreach ($chunk as $s) {
                                $sum += abs($s);
                            }
                            $avg = $sum / count($chunk);
                            $peaks[] = $avg / 32768.0;
                        }
                        $waveformJson = json_encode($peaks);
                    }
                }
            }

            // Update song metadata
            $song->variant_key_128 = $key128;
            $song->variant_key_320 = $key320;
            $song->file_size_bytes = filesize($tmp320);
            $song->mime_type = 'audio/mpeg';
            $song->duration = $duration;
            $song->bitrate = $bitrate ?: null;
            if (file_exists($tmpPreview)) {
                $previewKey = "audio/preview/{$artistSlug}/{$song->id}-preview.mp3";
                Storage::disk('s3')->put($previewKey, fopen($tmpPreview, 'r'));
                $song->preview_path = $previewKey;
            }
            $song->waveform_json = $waveformJson;
            $song->processing_status = 'ready';
            $song->save();

            // Cleanup
            @unlink($tmpIn);
            @unlink($tmp128);
            @unlink($tmp320);
            @unlink($tmpPreview);
            @unlink($tmpRaw);
        } catch (Exception $e) {
            $song->processing_status = 'failed';
            $song->processing_error = $e->getMessage();
            $song->save();
        }
    }
}
