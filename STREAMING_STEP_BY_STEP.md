# Echo Panda Streaming Step-by-Step (Do This In Order)

This guide maps to the code already added in this repository.

## Step 0: What Is Already Implemented

The following are now present:
- Database migrations for artists, lyrics, play_history, stream_logs, and streaming song fields.
- New models: Artist, Lyric, PlayHistory, StreamLog.
- New streaming services: StreamTokenService, S3RangeStreamService, PlaybackTrackingService.
- New controllers: StreamTicketController, AudioStreamController, PlaybackController, LyricsController.
- New middleware: EnforceRangeRequests.
- New API routes for stream ticket, secure stream, playback progress, completion, recent plays, and lyrics.

## Step 1: Configure Environment

Update your `.env` in backend with S3 values:

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=YOUR_KEY
AWS_SECRET_ACCESS_KEY=YOUR_SECRET
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=echo-panda-audio-private
AWS_URL=
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Important:
- Use a **private** bucket.
- Keep Block Public Access enabled.

## Step 2: Ensure Song Rows Have Streaming Fields

For each song row, fill at least these fields:
- `file_size_bytes`
- `mime_type` (usually `audio/mpeg`)
 - `audio_url` (public URL for audio if needed)
 - `variant_key_128` and/or `variant_key_320` (internal S3 object keys for quality variants)
- `default_quality` (`128` or `320`)
- `is_active` (`true`)

Example object keys (stored in `variant_key_128` / `variant_key_320`):
- `audio/128/night-drive.mp3`
- `audio/320/night-drive.mp3`

## Step 3: Run Migrations

Use a DB host reachable from your current runtime.

```bash
php artisan migrate
```

If you run local (not docker), ensure `DB_HOST` is not `postgres` unless that hostname resolves.

## Step 4: Verify Routes

```bash
php artisan route:list --path=api/stream
php artisan route:list --path=playback
php artisan route:list --path=lyrics
```

Expected key route:
- `GET api/stream/{song}/{quality}`

## Step 5: Request Stream Ticket (Authenticated)

Call:
- `GET /api/songs/{songId}/stream-ticket?quality=320`
- Header: `Authorization: Bearer <sanctum_or_jwt_token>`

Response:

```json
{
  "song_id": 321,
  "quality": "320",
  "expires_in_seconds": 300,
  "stream_url": "https://api.example.com/api/stream/321/320?expires=...&signature=...&uid=1"
}
```

## Step 6: Play Audio Using Range Requests

The player must send a Range header to stream endpoint.

Example first chunk:

```http
GET /api/stream/321/320?...signed...
Range: bytes=0-262143
```

Expected:
- Status `206 Partial Content`
- `Accept-Ranges: bytes`
- `Content-Range: bytes .../...`

## Step 7: Track Progress Every 10-15 Seconds

Call authenticated endpoint:
- `POST /api/playback/progress`

Body:

```json
{
  "song_id": 321,
  "progress_seconds": 37,
  "duration_seconds": 248,
  "source": "web"
}
```

Play count increments when:
- progress >= 30 seconds, or
- progress >= 50% of duration.

## Step 8: Mark Completion At Song End

Call:
- `POST /api/playback/complete`

Body:

```json
{
  "song_id": 321,
  "duration_seconds": 248,
  "source": "web"
}
```

## Step 9: Load Recently Played

Call:
- `GET /api/playback/recent`

Use this for user home feed.

## Step 10: Load Synced Lyrics

Call:
- `GET /api/songs/{songId}/lyrics`

Response shape:

```json
{
  "song_id": 321,
  "format": "lrc",
  "language": "en",
  "lines": [
    { "time_ms": 12200, "text": "I can hear the city breathing" },
    { "time_ms": 15600, "text": "Neon lights and midnight dreaming" }
  ]
}
```

Frontend sync logic:
- every 200ms read `currentTime * 1000`
- highlight latest line whose `time_ms <= currentTimeMs`

## Step 11: Web Frontend Integration

1. User clicks Play.
2. Request stream ticket.
3. Assign returned `stream_url` to HTMLAudioElement source.
4. Start playback.
5. Timer sends `/api/playback/progress`.
6. On ended, call `/api/playback/complete`.
7. Sync lyrics using `currentTime`.

## Step 12: Android Integration (ExoPlayer)

1. Request stream ticket from backend.
2. Build ExoPlayer media item with signed URL.
3. ExoPlayer handles range/chunk requests.
4. Send periodic playback progress to backend.
5. On completion event, call complete endpoint.

## Step 13: Security Checklist

- Private S3 bucket only.
- Short ticket expiration (5 minutes).
- Require Range header on stream endpoint.
- Keep stream endpoint throttled.
- Log stream attempts in stream_logs.
- Do not expose S3 object URLs directly to clients.

## Step 14: Performance Checklist

- Start mobile default at 128 kbps.
- Use 320 kbps on Wi-Fi or better network quality.
- Cache metadata and lyrics in Redis.
- Move heavy stream log processing to queues later.
- Put CDN in front once stable.

## Step 15: Next Upgrade Path (After Baseline Works)

- Adaptive bitrate using HLS/DASH.
- Waveform precompute and JSON peaks endpoint.
- Offline encrypted download with license checks.
- Recommendation event pipeline.
- Podcast and live radio models/endpoints.
