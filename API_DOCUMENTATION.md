# Echo Panda Music Streaming API Documentation

This API is designed for both web and mobile applications to consume music streaming services.

## Base URL
```
http://localhost:8000/api
```

## Authentication
Most endpoints require Bearer token authentication via Laravel Sanctum.

**Header:**
```
Authorization: Bearer {token}
```

---

## Authentication Endpoints

### Register
- **POST** `/register`
- **Auth:** No
- **Request:**
  ```json
  {
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123"
  }
  ```
- **Response:** (201)
  ```json
  {
    "message": "User registered successfully",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|token_string",
    "token_type": "Bearer"
  }
  ```

### Login
- **POST** `/login`
- **Auth:** No
- **Request:**
  ```json
  {
    "email": "john@example.com",
    "password": "password123"
  }
  ```
- **Response:** (200)
  ```json
  {
    "message": "Login successful",
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    },
    "token": "1|token_string",
    "token_type": "Bearer"
  }
  ```

### Logout
- **POST** `/logout`
- **Auth:** Yes (Bearer Token)
- **Response:** (200)
  ```json
  {
    "message": "Logged out successfully"
  }
  ```

### Get Current User
- **GET** `/me`
- **Auth:** Yes (Bearer Token)
- **Response:** (200)
  ```json
  {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user"
    }
  }
  ```

---

## Album Endpoints

### List All Albums
- **GET** `/albums`
- **Auth:** No
- **Query Parameters:**
  - `search` (string) - Search by title, artist, or description
  - `sort_by` (string) - `latest` (default) or `oldest`
  - `per_page` (int) - Items per page (default: 15)
  - `page` (int) - Page number (default: 1)
- **Response:** (200) - Paginated list
  ```json
  {
    "data": [
      {
        "id": 1,
        "title": "Album Name",
        "artist": "Artist Name",
        "release_date": "2024-01-15",
        "description": "Album description",
        "cover_image": "https://s3.example.com/cover.jpg",
        "created_at": "2024-01-15T10:00:00Z",
        "updated_at": "2024-01-15T10:00:00Z"
      }
    ],
    "links": {},
    "meta": {}
  }
  ```

### Get Album with Songs
- **GET** `/albums/{id}`
- **Auth:** No
- **Response:** (200)
  ```json
  {
    "id": 1,
    "title": "Album Name",
    "artist": "Artist Name",
    "release_date": "2024-01-15",
    "description": "Album description",
    "cover_image": "https://s3.example.com/cover.jpg",
    "songs": [
      {
        "id": 1,
        "album_id": 1,
        "title": "Song Title",
        "artist": "Song Artist",
        "duration": 240,
        "track_number": 1,
        "lyrics": "Song lyrics...",
        "created_at": "2024-01-15T10:00:00Z"
      }
    ],
    "created_at": "2024-01-15T10:00:00Z",
    "updated_at": "2024-01-15T10:00:00Z"
  }
  ```

### Create Album
- **POST** `/albums`
- **Auth:** Yes (Artist, Publisher, Admin)
- **Request:**
  ```json
  {
    "title": "New Album",
    "artist": "Artist Name",
    "release_date": "2024-01-15",
    "description": "Album description",
    "cover_image": "https://s3.example.com/cover.jpg"
  }
  ```
- **Response:** (201)

### Update Album
- **PUT** `/albums/{id}`
- **Auth:** Yes (Artist, Publisher, Admin)
- **Request:** Same as Create
- **Response:** (200)

### Delete Album
- **DELETE** `/albums/{id}`
- **Auth:** Yes (Artist, Publisher, Admin)
- **Response:** (200)
  ```json
  {
    "message": "Album deleted successfully"
  }
  ```

---

## Song Endpoints

### List All Songs
- **GET** `/songs`
- **Auth:** No
- **Query Parameters:**
  - `search` (string) - Search by title, artist, or lyrics
  - `album_id` (int) - Filter by album
  - `sort_by` (string) - `track_number` (default) or `latest`
  - `per_page` (int) - Items per page (default: 20)
  - `page` (int) - Page number
- **Response:** (200) - Paginated list

### Get Song Details
- **GET** `/songs/{id}`
- **Auth:** No
- **Response:** (200)
  ```json
  {
    "id": 1,
    "album_id": 1,
    "title": "Song Title",
    "artist": "Song Artist",
    "duration": 240,
    "track_number": 1,
    "lyrics": "Song lyrics...",
    "album": {
      "id": 1,
      "title": "Album Name",
      "artist": "Artist Name",
      "cover_image": "https://s3.example.com/cover.jpg"
    },
    "created_at": "2024-01-15T10:00:00Z"
  }
  ```

### Get Songs by Album
- **GET** `/albums/{albumId}/songs`
- **Auth:** No
- **Query Parameters:**
  - `per_page` (int) - Items per page (default: 20)
  - `page` (int) - Page number
- **Response:** (200) - Paginated list of songs

### Create Song
- **POST** `/songs`
- **Auth:** Yes (Artist, Publisher, Admin)
- **Request:**
  ```json
  {
    "album_id": 1,
    "title": "Song Title",
    "artist": "Song Artist",
    "duration": 240,
    "track_number": 1,
    "lyrics": "Optional song lyrics"
  }
  ```
- **Response:** (201)

### Update Song
- **PUT** `/songs/{id}`
- **Auth:** Yes (Artist, Publisher, Admin)
- **Request:** Same as Create
- **Response:** (200)

### Delete Song
- **DELETE** `/songs/{id}`
- **Auth:** Yes (Artist, Publisher, Admin)
- **Response:** (200)
  ```json
  {
    "message": "Song deleted successfully"
  }
  ```

---

## Profile Endpoints

### Get User Profile
- **GET** `/profile`
- **Auth:** Yes (Bearer Token)
- **Response:** (200)
  ```json
  {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "user",
      "created_at": "2024-01-15T10:00:00Z",
      "updated_at": "2024-01-15T10:00:00Z"
    }
  }
  ```

### Update User Profile
- **PUT** `/profile`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "name": "New Name",
    "email": "newemail@example.com"
  }
  ```
- **Response:** (200)

### Get Favorite Songs
- **GET** `/profile/favorite-songs`
- **Auth:** Yes (Bearer Token)
- **Query Parameters:**
  - `per_page` (int) - Items per page (default: 20)
  - `page` (int) - Page number
- **Response:** (200) - Paginated list of favorite songs

### Get Favorite Albums
- **GET** `/profile/favorite-albums`
- **Auth:** Yes (Bearer Token)
- **Query Parameters:**
  - `per_page` (int) - Items per page (default: 20)
  - `page` (int) - Page number
- **Response:** (200) - Paginated list of favorite albums

---

## Favorites Endpoints

### Get All Favorites
- **GET** `/favorites`
- **Auth:** Yes (Bearer Token)
- **Query Parameters:**
  - `type` (string) - Filter by `song` or `album` (optional)
  - `per_page` (int) - Items per page (default: 20)
  - `page` (int) - Page number
- **Response:** (200) - Paginated list
  ```json
  {
    "data": [
      {
        "id": 1,
        "user_id": 1,
        "favoritable_id": 1,
        "favoritable_type": "App\\Models\\Song",
        "created_at": "2024-01-15T10:00:00Z",
        "updated_at": "2024-01-15T10:00:00Z",
        "favoritable": {
          "id": 1,
          "title": "Song Title",
          "artist": "Song Artist"
        }
      }
    ],
    "links": {},
    "meta": {}
  }
  ```

### Add Song to Favorites
- **POST** `/favorites/songs`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "song_id": 1
  }
  ```
- **Response:** (201)
  ```json
  {
    "message": "Song added to favorites",
    "data": {
      "id": 1,
      "user_id": 1,
      "favoritable_id": 1,
      "favoritable_type": "App\\Models\\Song",
      "created_at": "2024-01-15T10:00:00Z",
      "favoritable": {
        "id": 1,
        "title": "Song Title",
        "artist": "Song Artist"
      }
    }
  }
  ```

### Add Album to Favorites
- **POST** `/favorites/albums`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "album_id": 1
  }
  ```
- **Response:** (201) - Same structure as song

### Check if Song is Favorited
- **POST** `/favorites/songs/check`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "song_id": 1
  }
  ```
- **Response:** (200)
  ```json
  {
    "is_favorited": true
  }
  ```

### Check if Album is Favorited
- **POST** `/favorites/albums/check`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "album_id": 1
  }
  ```
- **Response:** (200)
  ```json
  {
    "is_favorited": true
  }
  ```

### Remove Song from Favorites
- **POST** `/favorites/songs/remove`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "song_id": 1
  }
  ```
- **Response:** (200)
  ```json
  {
    "message": "Song removed from favorites"
  }
  ```

### Remove Album from Favorites
- **POST** `/favorites/albums/remove`
- **Auth:** Yes (Bearer Token)
- **Request:**
  ```json
  {
    "album_id": 1
  }
  ```
- **Response:** (200)
  ```json
  {
    "message": "Album removed from favorites"
  }
  ```

### Delete Favorite by ID
- **DELETE** `/favorites/{id}`
- **Auth:** Yes (Bearer Token)
- **Response:** (200)
  ```json
  {
    "message": "Favorite removed successfully"
  }
  ```

---

## Product Endpoints

### List Products
- **GET** `/products`
- **Auth:** No
- **Query Parameters:**
  - `search` (string) - Search by name, description, or SKU
  - `is_active` (boolean) - Filter active/inactive
  - `per_page` (int) - Items per page (default: 15)
  - `page` (int) - Page number
- **Response:** (200) - Paginated list

### Get Product Details
- **GET** `/products/{id}`
- **Auth:** No

### Create Product
- **POST** `/products`
- **Auth:** Yes (Admin only)
- **Request:**
  ```json
  {
    "name": "Product Name",
    "description": "Product description",
    "price": "99.99",
    "quantity": 100,
    "sku": "PROD-001",
    "is_active": true
  }
  ```
- **Response:** (201)

### Update Product
- **PUT** `/products/{id}`
- **Auth:** Yes (Admin only)

### Delete Product
- **DELETE** `/products/{id}`
- **Auth:** Yes (Admin only)

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The given data was invalid",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated"
}
```

### Forbidden (403)
```json
{
  "message": "This action is unauthorized"
}
```

### Not Found (404)
```json
{
  "message": "Not Found"
}
```

### Conflict (409)
```json
{
  "message": "Song already in favorites"
}
```

---

## Integration Notes for S3 Streaming

When your friend creates the S3 streaming service, songs will include:
- Streaming URL from S3
- Audio file status
- Duration in seconds
- Format/codec information

Songs can be updated with S3 file references through the song update endpoint.

---

## Rate Limiting

Currently no rate limiting is implemented. Consider adding for production use.

---

## Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `409` - Conflict
- `422` - Validation Error
- `500` - Server Error
