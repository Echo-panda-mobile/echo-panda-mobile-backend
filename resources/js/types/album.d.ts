export interface Album {
    id: number;
    title: string;
    artist: string;
    release_date: string | null;
    description: string | null;
    cover_image: string | null;
    created_at: string;
    updated_at: string;
    songs_count?: number;
}

