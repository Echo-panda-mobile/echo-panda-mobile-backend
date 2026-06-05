import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Album } from '@/types/album';
import { Song } from '@/types/song';
import { useState } from 'react';

interface ModeratedAlbum extends Album {
    deleted_at?: string | null;
    songs?: Song[];
    artist_model?: {
        name: string;
        image_url?: string;
    } | null;
}

interface Stats {
    total: number;
    active: number;
    deleted: number;
}

interface Props extends PageProps {
    albums: {
        data: ModeratedAlbum[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    stats: Stats;
    filters: {
        search?: string;
        status?: string;
    };
}

export default function Index({ albums, stats, filters }: Props) {
    const [selectedAlbum, setSelectedAlbum] = useState<ModeratedAlbum | null>(null);

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this album? It will be moved to the trash.')) {
            router.delete(route('admin.albums.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const getStatusBadge = (album: ModeratedAlbum) => {
        if (album.deleted_at) {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-400 border border-rose-400/20">
                    <span className="w-1.5 h-1.5 rounded-full bg-rose-400 shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span>
                    Deleted
                </span>
            );
        }
        if (album.release_status === 'published') {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-400 border border-emerald-400/20">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                    Active
                </span>
            );
        }
        return (
            <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-slate-500/10 text-slate-400 border border-slate-400/20">
                <span className="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                {album.release_status || 'Draft'}
            </span>
        );
    };

    return (
        <AuthenticatedLayout header="Album Management">
            <Head title="Album Management" />

            <div className="space-y-8">
                {/* Header & Statistics */}
                <div className="rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-900 to-slate-950 p-8 shadow-2xl">
                    <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.4em] text-cyan-400/80 mb-2">Platform Moderation</div>
                            <h2 className="text-4xl font-black text-white tracking-tight">Album Management</h2>
                            <p className="mt-3 max-w-2xl text-base text-slate-400 leading-relaxed">
                                Manage artist albums, review album information, and remove inappropriate content.
                            </p>
                        </div>
                    </div>

                    <div className="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {[
                            { label: 'Total Albums', value: stats.total, color: 'text-white', bg: 'bg-white/5' },
                            { label: 'Active Albums', value: stats.active, color: 'text-emerald-400', bg: 'bg-emerald-500/5' },
                            { label: 'Deleted', value: stats.deleted, color: 'text-rose-400', bg: 'bg-rose-500/5' },
                        ].map((stat, i) => (
                            <div key={i} className={`rounded-3xl border border-white/5 ${stat.bg} p-6 backdrop-blur-sm transition-transform hover:scale-[1.02]`}>
                                <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">{stat.label}</div>
                                <div className={`text-3xl font-black ${stat.color}`}>{stat.value.toLocaleString()}</div>
                            </div>
                        ))}
                    </div>
                </div>

                {/* Filters */}
                <div className="rounded-[2rem] border border-white/10 bg-white/5 p-6 backdrop-blur-sm">
                    <form method="get" action={route('admin.albums.index')} className="flex flex-wrap gap-4 items-center">
                        <div className="relative flex-1 min-w-[300px]">
                            <input
                                type="text"
                                name="search"
                                placeholder="Search by title or artist..."
                                defaultValue={filters.search}
                                className="h-12 w-full rounded-2xl border border-white/10 bg-slate-950/50 px-5 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20 shadow-inner"
                            />
                        </div>
                        <select
                            name="status"
                            defaultValue={filters.status}
                            className="h-12 rounded-2xl border border-white/10 bg-slate-950/50 px-5 text-sm text-white focus:border-cyan-400/30 focus:outline-none"
                        >
                            <option value="">All Albums</option>
                            <option value="active">Active</option>
                            <option value="deleted">Deleted</option>
                        </select>
                        <button type="submit" className="h-12 px-8 rounded-2xl bg-white text-black text-[11px] font-black uppercase tracking-widest hover:bg-cyan-400 transition-all shadow-xl">
                            Apply Filters
                        </button>
                    </form>
                </div>

                {/* Table */}
                <div className="overflow-hidden rounded-[2.5rem] border border-white/10 bg-slate-950/50 shadow-2xl backdrop-blur-md">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/5">
                            <thead className="bg-white/[0.02]">
                                <tr>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Cover</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Album Title</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Artist</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Release Date</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Songs</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Status</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Created</th>
                                    <th className="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {albums.data.length === 0 ? (
                                    <tr><td colSpan={9} className="px-8 py-32 text-center text-slate-500 italic">No albums found matching your criteria.</td></tr>
                                ) : albums.data.map((album) => (
                                    <tr key={album.id} className="group transition-colors hover:bg-white/[0.03]">
                                        <td className="px-8 py-4">
                                            <div className="h-12 w-12 overflow-hidden rounded-xl border border-white/10 bg-white/5">
                                                {album.cover_url ? (
                                                    <img src={album.cover_url} alt={album.title} className="h-full w-full object-cover" />
                                                ) : (
                                                    <div className="flex h-full w-full items-center justify-center text-slate-700">
                                                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/></svg>
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">{album.title}</div>
                                            <div className="text-[10px] text-slate-500 mt-1 uppercase tracking-tighter font-mono">ID: {album.id.toString().padStart(6, '0')}</div>
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className="text-sm text-slate-300 font-medium">{album.artist || album.artist_model?.name || 'Unknown Artist'}</div>
                                        </td>
                                        <td className="px-8 py-4 text-xs text-slate-400">
                                            {album.release_date ? new Date(album.release_date).toLocaleDateString() : 'N/A'}
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className="text-xs font-black text-slate-500">{album.songs_count || 0} Tracks</div>
                                        </td>
                                        <td className="px-8 py-4">
                                            {getStatusBadge(album)}
                                        </td>
                                        <td className="px-8 py-4 text-xs text-slate-500">
                                            {new Date(album.created_at).toLocaleDateString()}
                                        </td>
                                        <td className="px-8 py-4 text-right">
                                            <div className="flex items-center justify-end gap-3">
                                                <button
                                                    onClick={() => setSelectedAlbum(album)}
                                                    className="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-white transition-all"
                                                    title="View Details"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" strokeWidth="2" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" strokeWidth="2" /></svg>
                                                </button>
                                                <Link
                                                    href={route('admin.albums.edit', album.id)}
                                                    className="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-cyan-400 transition-all"
                                                    title="Edit Metadata"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" strokeWidth="2" /></svg>
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(album.id)}
                                                    className="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-rose-400 transition-all"
                                                    title="Delete Album"
                                                    disabled={!!album.deleted_at}
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" strokeWidth="2" /></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {albums.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/5 px-8 py-6 bg-white/[0.01]">
                            <div className="text-[10px] font-black uppercase tracking-widest text-slate-600">
                                Page {albums.current_page} of {albums.last_page} ┬╖ {albums.total} Records
                            </div>
                            <div className="flex gap-2">
                                {albums.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`h-10 px-4 rounded-xl flex items-center justify-center text-[10px] font-black uppercase tracking-widest transition-all ${link.active ? 'bg-white text-black' : 'border border-white/10 text-slate-500 hover:text-white'} ${!link.url ? 'cursor-not-allowed opacity-20' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Album Details Modal */}
            {selectedAlbum && (
                <div className="fixed inset-0 z-50 flex items-center justify-end bg-slate-950/80 backdrop-blur-md">
                    <button type="button" className="absolute inset-0 cursor-default" onClick={() => setSelectedAlbum(null)} />
                    <aside className="relative h-full w-full max-w-2xl overflow-y-auto border-l border-white/10 bg-slate-950 shadow-2xl animate-slide-in-right">
                        <div className="sticky top-0 z-10 bg-slate-950/80 backdrop-blur-md p-8 border-b border-white/10 flex items-center justify-between">
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.4em] text-cyan-400">Album Audit</div>
                                <h3 className="mt-2 text-3xl font-black text-white">{selectedAlbum.title}</h3>
                            </div>
                            <button onClick={() => setSelectedAlbum(null)} className="h-12 w-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white">
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
                            </button>
                        </div>

                        <div className="p-8 space-y-10">
                            <div className="grid grid-cols-2 gap-4">
                                <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Platform Status</div>
                                    <div className="flex items-center gap-2">{getStatusBadge(selectedAlbum)}</div>
                                </div>
                                <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                                    <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Release Date</div>
                                    <div className="text-lg font-black text-white">
                                        {selectedAlbum.release_date ? new Date(selectedAlbum.release_date).toLocaleDateString(undefined, { dateStyle: 'long' }) : 'N/A'}
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-6">
                                <div className="flex items-center justify-between">
                                    <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">Entity Information</h4>
                                    <div className="h-px flex-1 bg-white/5 ml-4"></div>
                                </div>

                                <div className="flex items-center gap-6">
                                    <div className="h-32 w-32 shrink-0 rounded-3xl border border-white/10 overflow-hidden bg-white/5">
                                        {selectedAlbum.cover_url && <img src={selectedAlbum.cover_url} className="h-full w-full object-cover" />}
                                    </div>
                                    <div className="space-y-4">
                                        <div>
                                            <div className="text-[10px] font-black uppercase text-slate-500">Artist Profile</div>
                                            <div className="text-xl font-bold text-white">{selectedAlbum.artist || selectedAlbum.artist_model?.name || 'Unknown Artist'}</div>
                                        </div>
                                        <div>
                                            <div className="text-[10px] font-black uppercase text-slate-500">Album Title</div>
                                            <div className="text-xl font-bold text-cyan-400">{selectedAlbum.title}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="space-y-6">
                                <div className="flex items-center justify-between">
                                    <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">Tracklist ({selectedAlbum.songs_count || 0})</h4>
                                    <div className="h-px flex-1 bg-white/5 ml-4"></div>
                                </div>
                                <div className="rounded-[2.5rem] border border-white/10 bg-white/5 overflow-hidden">
                                    <div className="p-4 space-y-1">
                                        {(selectedAlbum.songs || []).length === 0 ? (
                                            <div className="py-8 text-center text-slate-500 italic text-xs uppercase tracking-widest">No songs uploaded to this album</div>
                                        ) : (
                                            selectedAlbum.songs?.map((song, idx) => (
                                                <div key={song.id} className="flex items-center justify-between p-4 rounded-2xl hover:bg-white/5 transition-colors">
                                                    <div className="flex items-center gap-4">
                                                        <span className="text-[10px] font-black text-slate-700 w-4">{idx + 1}</span>
                                                        <span className="text-sm font-bold text-slate-300">{song.title}</span>
                                                    </div>
                                                    <span className="text-xs font-mono text-slate-500">
                                                        {Math.floor(song.duration / 60)}:{(song.duration % 60).toString().padStart(2, '0')}
                                                    </span>
                                                </div>
                                            ))
                                        )}
                                    </div>
                                </div>
                            </div>

                            <div className="sticky bottom-0 bg-slate-950 pt-6 pb-8 border-t border-white/10">
                                <div className="grid grid-cols-2 gap-4">
                                    <Link
                                        href={route('admin.albums.edit', selectedAlbum.id)}
                                        className="h-14 rounded-2xl bg-white text-black font-black text-[11px] uppercase tracking-widest flex items-center justify-center hover:bg-cyan-400 transition-all shadow-xl"
                                    >
                                        Edit Metadata
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(selectedAlbum.id)}
                                        disabled={!!selectedAlbum.deleted_at}
                                        className={`h-14 rounded-2xl font-black text-[11px] uppercase tracking-widest border transition-all ${selectedAlbum.deleted_at ? 'border-white/5 bg-white/5 text-slate-600 cursor-not-allowed' : 'border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20'}`}
                                    >
                                        {selectedAlbum.deleted_at ? 'Already Deleted' : 'Remove Album'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

