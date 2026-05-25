import { Album } from './album';

export interface Song {
    id: number;
    album_id: number;
    title: string;
    artist: string | null;
    duration: number;
    track_number: number;
    lyrics: string | null;
    created_at: string;
    updated_at: string;
    album?: Album;
}

