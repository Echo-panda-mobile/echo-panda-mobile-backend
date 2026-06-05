export interface Album {
    id: number;
    title: string;
    artist: string;
    artist_id?: number | null;
    release_date: string | null;
    description: string | null;
    cover_image: string | null;
    cover_url?: string;
    release_status?: string;
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
    songs_count?: number;
}

