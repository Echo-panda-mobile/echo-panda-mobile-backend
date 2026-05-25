# Database Relationships & Models Documentation

## Entity Relationships

```
User
├── hasMany Favorites
│   ├── morphTo Favoritable (Album or Song)
└── hasMany ApiTokens (Sanctum)

Album
├── hasMany Songs
└── morphMany Favorites

Song
├── belongsTo Album
└── morphMany Favorites

Favorite (Polymorphic)
├── belongsTo User
└── morphTo Favoritable
```

## Model Definitions

### User Model
```php
// app/Models/User.php
namespace App\Models;

class User extends Authenticatable {
    // Attributes
    string $name
    string $email
    string $password
    string $role (user, artist, publicer, admin)
    timestamp $email_verified_at
    timestamp $created_at
    timestamp $updated_at

    // Relationships
    public function favorites() -> hasMany(Favorite)
}
```

### Album Model
```php
// app/Models/Album.php
namespace App\Models;

class Album extends Model {
    // Attributes
    int $id
    string $title
    string $artist
    date $release_date (nullable)
    string $description (nullable)
    string $cover_image (nullable)
    timestamp $created_at
    timestamp $updated_at

    // Relationships
    public function songs() -> hasMany(Song)
    public function favorites() -> morphMany(Favorite)
}
```

### Song Model
```php
// app/Models/Song.php
namespace App\Models;

class Song extends Model {
    // Attributes
    int $id
    int $album_id (foreign key)
    string $title
    string $artist (nullable)
    int $duration (in seconds)
    int $track_number
    string $lyrics (nullable)
    timestamp $created_at
    timestamp $updated_at

    // Relationships
    public function album() -> belongsTo(Album)
    public function favorites() -> morphMany(Favorite)
}
```

### Favorite Model (NEW)
```php
// app/Models/Favorite.php
namespace App\Models;

class Favorite extends Model {
    // Attributes
    int $id
    int $user_id (foreign key)
    int $favoritable_id
    string $favoritable_type (App\Models\Song or App\Models\Album)
    timestamp $created_at
    timestamp $updated_at

    // Relationships
    public function user() -> belongsTo(User)
    public function favoritable() -> morphTo()

    // Constraints
    unique: (user_id, favoritable_id, favoritable_type)
}
```

### Product Model
```php
// app/Models/Product.php
namespace App\Models;

class Product extends Model {
    // Attributes
    int $id
    string $name
    string $description
    decimal $price
    int $quantity
    string $sku
    bool $is_active
    timestamp $created_at
    timestamp $updated_at
}
```

## Database Tables

### users
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    email VARCHAR(255) UNIQUE,
    password VARCHAR(255),
    role VARCHAR(50) DEFAULT 'user',
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### albums
```sql
CREATE TABLE albums (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255),
    artist VARCHAR(255),
    release_date DATE NULL,
    description TEXT NULL,
    cover_image VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### songs
```sql
CREATE TABLE songs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    album_id BIGINT NOT NULL,
    title VARCHAR(255),
    artist VARCHAR(255) NULL,
    duration INT NOT NULL,
    track_number INT NOT NULL,
    lyrics LONGTEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (album_id) REFERENCES albums(id) ON DELETE CASCADE
);
```

### favorites
```sql
CREATE TABLE favorites (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL,
    favoritable_id BIGINT NOT NULL,
    favoritable_type VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, favoritable_id, favoritable_type)
);
```

### products
```sql
CREATE TABLE products (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    description TEXT NULL,
    price DECIMAL(10,2),
    quantity INT,
    sku VARCHAR(100),
    is_active TINYINT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Polymorphic Relationships Explained

The `Favorite` model uses Laravel's polymorphic relationships to support both Song and Album favorites.

### How it works:
```php
// User adds song to favorites
$user->favorites()->create([
    'favoritable_id' => 1,
    'favoritable_type' => 'App\Models\Song'
]);

// Database row:
// id: 1, user_id: 1, favoritable_id: 1, favoritable_type: 'App\Models\Song'

// User adds album to favorites
$user->favorites()->create([
    'favoritable_id' => 5,
    'favoritable_type' => 'App\Models\Album'
]);

// Database row:
// id: 2, user_id: 1, favoritable_id: 5, favoritable_type: 'App\Models\Album'

// Retrieve the actual favorite object
$favorite = Favorite::find(1);
$song = $favorite->favoritable; // Returns Song model
```

## Foreign Key Relationships

### Albums → Songs
- One Album has many Songs
- When Album is deleted, all related Songs are deleted (CASCADE)

### Users → Favorites
- One User has many Favorites
- When User is deleted, all Favorites are deleted (CASCADE)

### Favorites → polymorphic Favoritable
- One Favorite belongs to either Song or Album
- Supports flexible many-to-many-like relationships

## Query Examples

```php
// Get album with all its songs
$album = Album::with('songs')->find(1);

// Get user with their favorite songs
$user = User::with('favorites')->find(1);
$favoriteSongs = $user->favorites()
    ->where('favoritable_type', 'App\Models\Song')
    ->with('favoritable')
    ->get();

// Get all users who favorited a specific song
$song = Song::find(1);
$users = Favorite::where('favoritable_id', 1)
    ->where('favoritable_type', 'App\Models\Song')
    ->with('user')
    ->get()
    ->pluck('user');

// Check if user favorited a song
$isFavorited = Favorite::where('user_id', $userId)
    ->where('favoritable_id', $songId)
    ->where('favoritable_type', 'App\Models\Song')
    ->exists();

// Get favorites count for a song
$favoriteCount = Favorite::where('favoritable_id', 1)
    ->where('favoritable_type', 'App\Models\Song')
    ->count();
```

## Mass Assignment (Fillable Attributes)

```php
// Album
protected $fillable = [
    'title',
    'artist',
    'release_date',
    'description',
    'cover_image',
];

// Song
protected $fillable = [
    'album_id',
    'title',
    'artist',
    'duration',
    'track_number',
    'lyrics',
];

// Favorite
protected $fillable = [
    'user_id',
    'favoritable_id',
    'favoritable_type',
];

// Product
protected $fillable = [
    'name',
    'description',
    'price',
    'quantity',
    'sku',
    'is_active',
];

// User
protected $fillable = [
    'name',
    'email',
    'password',
    'role',
];
```

## Casting (Type Conversions)

```php
// Album
protected function casts(): array {
    return [
        'release_date' => 'date',
    ];
}

// Song
// No special casts

// Favorite
// No special casts

// Product
protected function casts(): array {
    return [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}

// User
protected function casts(): array {
    return [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

## User Roles

```php
const ROLE_USER = 'user';        // Regular user
const ROLE_ARTIST = 'artist';    // Can create content
const ROLE_PUBLICER = 'publicer'; // Can publish content
const ROLE_ADMIN = 'admin';      // Full access

// Helper methods
public function isAdmin(): bool
public function isArtistOrPublicer(): bool
```

## Middleware Used

- `auth:sanctum` - Requires valid Bearer token
- `role:admin,artist,publicer` - Checks user role

## Relationships Summary

| Model | Relationship | Target | Type |
|-------|-------------|--------|------|
| User | favorites | Favorite | hasMany |
| Album | songs | Song | hasMany |
| Album | favorites | Favorite | morphMany |
| Song | album | Album | belongsTo |
| Song | favorites | Favorite | morphMany |
| Favorite | user | User | belongsTo |
| Favorite | favoritable | Song/Album | morphTo |

---

This structure supports efficient music streaming with user preferences, content management, and flexible favoriting system suitable for both web and mobile apps.
