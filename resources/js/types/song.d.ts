import { Album } from './album';

export interface Song {
    id: number;
    album_id: number;
    title: string;
    artist: string | null;
    duration: number;
    track_number: number;
    cover_url?: string;
    lyrics: string | null;
    is_active?: boolean;
    report_count?: number;
    open_report_count?: number;
    recent_reports?: Array<{
        id: number;
        reason: string;
        details?: string | null;
        status: string;
        reporter?: string | null;
        created_at: string;
    }>;
    created_at: string;
    updated_at: string;
    album?: Album;
}

