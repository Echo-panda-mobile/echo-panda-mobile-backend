# Quick API Reference - Web & Mobile

## Base Configuration

```javascript
// For React/Web
const API_BASE_URL = process.env.REACT_APP_API_URL || 'http://localhost:8000/api';

// For Mobile (React Native/Flutter)
const API_BASE_URL = 'http://localhost:8000/api'; // or your server IP
const TIMEOUT = 30000; // 30 seconds
```

## Common Headers

```javascript
const headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': `Bearer ${token}` // For protected routes
};
```

---

## Essential Endpoints for Web & Mobile

### 1. Authentication

#### Register
```
POST /register
{
  "name": "User Name",
  "email": "user@example.com",
  "password": "password123"
}
Returns: { user, token, token_type }
```

#### Login
```
POST /login
{
  "email": "user@example.com",
  "password": "password123"
}
Returns: { user, token, token_type }
```

#### Logout
```
POST /logout
Headers: Authorization Bearer token
Returns: { message: "Logged out successfully" }
```

---

### 2. Browse Music

#### Get All Albums
```
GET /albums?search={query}&sort_by=latest&per_page=15&page=1
Returns: Paginated albums
```

#### Get Album with Songs
```
GET /albums/{albumId}
Returns: { id, title, artist, cover_image, songs: [...] }
```

#### Get All Songs
```
GET /songs?search={query}&album_id={albumId}&sort_by=track_number&per_page=20&page=1
Returns: Paginated songs with album info
```

#### Get Songs by Album
```
GET /albums/{albumId}/songs
Returns: Paginated songs for specific album
```

---

### 3. User Profile

#### Get Profile
```
GET /profile
Headers: Authorization Bearer token
Returns: { user: { id, name, email, role, created_at } }
```

#### Update Profile
```
PUT /profile
Headers: Authorization Bearer token
Body: { "name": "New Name", "email": "newemail@example.com" }
Returns: Updated user object
```

#### Get Favorite Songs
```
GET /profile/favorite-songs?per_page=20&page=1
Headers: Authorization Bearer token
Returns: Paginated favorite songs
```

#### Get Favorite Albums
```
GET /profile/favorite-albums?per_page=20&page=1
Headers: Authorization Bearer token
Returns: Paginated favorite albums
```

---

### 4. Favorites Management

#### Get All Favorites
```
GET /favorites?type=song|album&per_page=20&page=1
Headers: Authorization Bearer token
Returns: Paginated favorites
```

#### Add Song to Favorites
```
POST /favorites/songs
Headers: Authorization Bearer token
Body: { "song_id": 1 }
Returns: { message, data: { id, favoritable: { title, artist } } }
Status: 201 (Created) or 409 (Already favorited)
```

#### Add Album to Favorites
```
POST /favorites/albums
Headers: Authorization Bearer token
Body: { "album_id": 1 }
Returns: { message, data: { id, favoritable: { title, artist } } }
Status: 201 (Created) or 409 (Already favorited)
```

#### Check if Song is Favorited
```
POST /favorites/songs/check
Headers: Authorization Bearer token
Body: { "song_id": 1 }
Returns: { "is_favorited": true|false }
```

#### Check if Album is Favorited
```
POST /favorites/albums/check
Headers: Authorization Bearer token
Body: { "album_id": 1 }
Returns: { "is_favorited": true|false }
```

#### Remove Song from Favorites
```
POST /favorites/songs/remove
Headers: Authorization Bearer token
Body: { "song_id": 1 }
Returns: { message: "Song removed from favorites" }
```

#### Remove Album from Favorites
```
POST /favorites/albums/remove
Headers: Authorization Bearer token
Body: { "album_id": 1 }
Returns: { message: "Album removed from favorites" }
```

---

## Example: React Implementation

```javascript
// API Client Service
class MusicAPI {
  constructor(baseURL) {
    this.baseURL = baseURL;
    this.token = localStorage.getItem('auth_token');
  }

  async request(endpoint, options = {}) {
    const headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      ...options.headers
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    const response = await fetch(`${this.baseURL}${endpoint}`, {
      ...options,
      headers
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    return await response.json();
  }

  // Auth
  async register(name, email, password) {
    const data = await this.request('/register', {
      method: 'POST',
      body: JSON.stringify({ name, email, password })
    });
    this.token = data.token;
    localStorage.setItem('auth_token', this.token);
    return data;
  }

  async login(email, password) {
    const data = await this.request('/login', {
      method: 'POST',
      body: JSON.stringify({ email, password })
    });
    this.token = data.token;
    localStorage.setItem('auth_token', this.token);
    return data;
  }

  // Albums
  async getAlbums(search = '', page = 1) {
    return this.request(`/albums?search=${search}&page=${page}`);
  }

  async getAlbum(id) {
    return this.request(`/albums/${id}`);
  }

  // Songs
  async getSongs(search = '', albumId = null, page = 1) {
    let url = `/songs?search=${search}&page=${page}`;
    if (albumId) url += `&album_id=${albumId}`;
    return this.request(url);
  }

  // Favorites
  async addSongToFavorites(songId) {
    return this.request('/favorites/songs', {
      method: 'POST',
      body: JSON.stringify({ song_id: songId })
    });
  }

  async isSongFavorited(songId) {
    const data = await this.request('/favorites/songs/check', {
      method: 'POST',
      body: JSON.stringify({ song_id: songId })
    });
    return data.is_favorited;
  }

  async removeSongFromFavorites(songId) {
    return this.request('/favorites/songs/remove', {
      method: 'POST',
      body: JSON.stringify({ song_id: songId })
    });
  }

  // Profile
  async getProfile() {
    return this.request('/profile');
  }

  async updateProfile(name, email) {
    return this.request('/profile', {
      method: 'PUT',
      body: JSON.stringify({ name, email })
    });
  }
}

// Usage
const api = new MusicAPI('http://localhost:8000/api');

// Login
const { token, user } = await api.login('user@example.com', 'password123');

// Get albums
const albums = await api.getAlbums('love');

// Favorite a song
await api.addSongToFavorites(1);

// Check if favorited
const isFav = await api.isSongFavorited(1);
```

---

## Example: React Native/Flutter Implementation

```javascript
// React Native / Expo
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://YOUR_SERVER_IP:8000/api',
  timeout: 30000
});

// Add token to requests
api.interceptors.request.use(config => {
  const token = AsyncStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Usage
async function loginUser(email, password) {
  try {
    const response = await api.post('/login', { email, password });
    await AsyncStorage.setItem('auth_token', response.data.token);
    return response.data.user;
  } catch (error) {
    console.error('Login failed:', error.response.data);
  }
}

async function getAlbums() {
  const response = await api.get('/albums');
  return response.data.data;
}

async function favoriteSong(songId) {
  const response = await api.post('/favorites/songs', { song_id: songId });
  return response.data;
}
```

---

## Response Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request successful |
| 201 | Created - Resource created |
| 400 | Bad Request - Invalid data |
| 401 | Unauthorized - Missing/invalid token |
| 403 | Forbidden - Access denied |
| 404 | Not Found - Resource doesn't exist |
| 409 | Conflict - Already exists (e.g., already favorited) |
| 422 | Validation Error - Invalid input |
| 500 | Server Error |

---

## Common Errors & Solutions

### 401 Unauthorized
```
Solution: Token missing or expired. Re-login required.
```

### 422 Validation Error
```
Response: { message, errors: { field: ["error message"] } }
Solution: Check required fields and data types.
```

### 409 Conflict
```
Response: { message: "Song already in favorites" }
Solution: Item already exists. Check before adding.
```

### Network Timeout
```
Solution: Increase timeout or check server connection.
Default timeout: 30 seconds
```

---

## Pagination

Most list endpoints return paginated results:

```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": "...",
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75,
    "from": 1,
    "to": 15
  }
}
```

**Query Parameters:**
- `page` - Page number (default: 1)
- `per_page` - Items per page (default: 15-20)

---

## Rate Limiting

Currently: No rate limiting (implement in production!)

Recommended:
- 1000 requests per hour per IP
- 100 requests per minute for authenticated users

---

## Development Checklist

- [ ] Set up API base URL for your environment
- [ ] Implement token storage (localStorage for web, AsyncStorage for mobile)
- [ ] Create API client/service class
- [ ] Test all authentication endpoints
- [ ] Test album/song browsing
- [ ] Test favorites functionality
- [ ] Handle error responses properly
- [ ] Add loading states
- [ ] Cache responses where appropriate
- [ ] Test on actual device/server

---

For complete documentation, see [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)
