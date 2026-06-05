<?php

namespace App\Services;

use App\Models\Song;
use App\Models\Album;
use App\Models\Artist;
use App\Models\Playlist;

class ShareMetadataService
{
    public function getMetadata(string $type, string $id): array
    {
        return match ($type) {
            'song' => $this->getSongMetadata($id),
            'album' => $this->getAlbumMetadata($id),
            'artist' => $this->getArtistMetadata($id),
            'playlist' => $this->getPlaylistMetadata($id),
            default => $this->getDefaultMetadata(),
        };
    }

    private function getSongMetadata(string $id): array
    {
        $song = Song::with(['album', 'artists'])->find($id);
        if (!$song) return $this->getDefaultMetadata();

        $artistName = $song->artists->pluck('name')->join(', ');

        return [
            'title' => "{$song->title} by {$artistName}",
            'description' => "Listen to {$song->title} on Echo Panda. Experience high-quality music streaming.",
            'image' => $song->songCover_url ?: ($song->album?->cover_url ?: asset('logo.webp')),
            'type' => 'music.song',
            'url' => url("/songs/{$id}"),
        ];
    }

    private function getAlbumMetadata(string $id): array
    {
        $album = Album::with(['artistModel'])->find($id);
        if (!$album) return $this->getDefaultMetadata();

        return [
            'title' => "{$album->title} by {$album->artist_name}",
            'description' => "Stream the album {$album->title} on Echo Panda. Check out all the tracks now.",
            'image' => $album->cover_url ?: asset('logo.webp'),
            'type' => 'music.album',
            'url' => url("/albums/{$id}"),
        ];
    }

    private function getArtistMetadata(string $id): array
    {
        $artist = Artist::find($id);
        if (!$artist) return $this->getDefaultMetadata();

        return [
            'title' => "{$artist->name} on Echo Panda",
            'description' => "Discover music and top tracks from {$artist->name} on Echo Panda.",
            'image' => $artist->image_url ?: asset('logo.webp'),
            'type' => 'profile',
            'url' => url("/artists/{$id}"),
        ];
    }

    private function getPlaylistMetadata(string $id): array
    {
        $playlist = Playlist::find($id);
        if (!$playlist) return $this->getDefaultMetadata();

        return [
            'title' => "{$playlist->name} - Playlist by Echo Panda User",
            'description' => $playlist->description ?: "Check out this amazing playlist on Echo Panda.",
            'image' => $playlist->image_url ?: asset('logo.webp'),
            'type' => 'music.playlist',
            'url' => url("/playlists/{$id}"),
        ];
    }

    private function getDefaultMetadata(): array
    {
        return [
            'title' => 'Echo Panda - Best Songs in One Place',
            'description' => 'Stream high-quality music, enjoy without interruptions. Whatever your taste, we have it ready for you.',
            'image' => asset('logo.webp'),
            'type' => 'website',
            'url' => url('/'),
        ];
    }
}
