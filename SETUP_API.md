# API Setup & Quick Start Guide

## 1. Run Database Migrations

First, run the migration to create the `favorites` table:

```bash
php artisan migrate
```

This will create the necessary database tables including the new `favorites` table for storing user preferences.

## 2. Verify Controllers and Routes

All API controllers are now in place:
- `App\Http\Controllers\Api\AlbumController`
- `App\Http\Controllers\Api\SongController`
- `App\Http\Controllers\Api\FavoriteController`
- `App\Http\Controllers\Api\ProfileController`

Routes are configured in `routes/api.php` with proper authentication and authorization middleware.

## 3. Testing the API

### Via Postman/Insomnia

1. **Register a user**
   ```
   POST http://localhost:8000/api/register
   Content-Type: application/json
   
   {
     "name": "Test User",
     "email": "test@example.com",
     "password": "password123"
   }
   ```

2. **Copy the token** from the response

3. **Use the token** for authenticated requests:
   ```
   Authorization: Bearer {token_from_response}
   ```

### Via cURL

```bash
# Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test User",
    "email": "test@example.com",
    "password": "password123"
  }'

# Get profile (replace TOKEN with actual token)
curl -X GET http://localhost:8000/api/profile \
  -H "Authorization: Bearer TOKEN"

# Get albums
curl -X GET http://localhost:8000/api/albums

# Add song to favorites
curl -X POST http://localhost:8000/api/favorites/songs \
  -H "Authorization: Bearer TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "song_id": 1
  }'
```

## 4. API Endpoints Summary

### Public Endpoints (No Auth)
- `GET /albums` - List albums
- `GET /albums/{id}` - Album details
- `GET /songs` - List songs
- `GET /songs/{id}` - Song details
- `GET /albums/{id}/songs` - Songs in album
- `POST /register` - Register user
- `POST /login` - Login user

### Protected Endpoints (Requires Auth)
- `GET /profile` - User profile
- `PUT /profile` - Update profile
- `GET /favorites` - User's favorites
- `POST /favorites/songs` - Add song to favorites
- `POST /favorites/albums` - Add album to favorites
- `POST /favorites/songs/check` - Check if song is favorited
- `POST /favorites/albums/check` - Check if album is favorited
- `POST /favorites/songs/remove` - Remove song from favorites
- `POST /favorites/albums/remove` - Remove album from favorites

### Admin/Creator Endpoints (Admin, Artist, or Publisher only)
- `POST /albums` - Create album
- `PUT /albums/{id}` - Update album
- `DELETE /albums/{id}` - Delete album
- `POST /songs` - Create song
- `PUT /songs/{id}` - Update song
- `DELETE /songs/{id}` - Delete song

## 5. Integration with S3 Streaming

When your friend creates the S3 streaming service:

1. Add the S3 URL to songs when needed
2. Update the Song model if additional fields are needed
3. The API will return the streaming URL in song responses

Example response with S3:
```json
{
  "id": 1,
  "title": "Song Title",
  "artist": "Artist Name",
  "duration": 240,
  "track_number": 1,
  "stream_url": "https://bucket.s3.amazonaws.com/songs/123.mp3",
  "lyrics": "..."
}
```

## 6. Database Schema

### Users Table
- id, name, email, password, role, email_verified_at, created_at, updated_at

### Albums Table
- id, title, artist, release_date, description, cover_image, created_at, updated_at

### Songs Table
- id, album_id, title, artist, duration, track_number, lyrics, created_at, updated_at

### Favorites Table (NEW)
- id, user_id, favoritable_id, favoritable_type, created_at, updated_at
- Supports polymorphic relationships for both songs and albums

### Products Table
- id, name, description, price, quantity, sku, is_active, created_at, updated_at

## 7. User Roles

- `user` - Regular user (can favorite songs/albums)
- `artist` - Can create and manage albums/songs
- `publicer` - Can create and manage albums/songs (alternative name)
- `admin` - Full access to all resources

## 8. Error Handling

All endpoints return appropriate HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict (e.g., already favorited)
- `422` - Validation Error
- `500` - Server Error

## 9. Testing with Laravel Sail (if using Docker)

```bash
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker
```

## 10. Next Steps

1. ✅ Run migrations (`php artisan migrate`)
2. ✅ Test endpoints with Postman/Insomnia
3. ⏳ Wait for S3 streaming service from friend
4. ✅ Integrate S3 URLs into Song responses
5. ✅ Test with frontend (React)
6. ✅ Test with mobile app (TBD framework)

---

For complete API documentation, see [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)
