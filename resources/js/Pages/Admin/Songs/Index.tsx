import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Song } from '@/types/song';
import { Album } from '@/types/album';
import { useState } from 'react';

interface ModeratedSong extends Song {
    deleted_at?: string | null;
    artist_model?: {
        name: string;
        image_url?: string;
    } | null;
    cover_url?: string;
}

interface Stats {
    total: number;
    active: number;
    reported: number;
    deleted: number;
}

interface Props extends PageProps {
    songs: {
        data: ModeratedSong[];
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
    albums: Album[];
    stats: Stats;
    filters: {
        search?: string;
        album_id?: string;
        status?: string;
    };
}

export default function Index({ songs, albums, stats, filters }: Props) {
    const [selectedSong, setSelectedSong] = useState<ModeratedSong | null>(null);

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this song? It will be moved to the trash.')) {
            router.delete(route('admin.songs.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const formatDuration = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    const getStatusBadge = (song: ModeratedSong) => {
        if (song.deleted_at) {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-rose-500/10 text-rose-400 border border-rose-400/20 shadow-[0_0_15px_rgba(244,63,94,0.1)]">
                    <span className="w-1.5 h-1.5 rounded-full bg-rose-400 shadow-[0_0_8px_rgba(244,63,94,0.6)]"></span>
                    Deleted
                </span>
            );
        }
        if (song.open_report_count && song.open_report_count > 0) {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-amber-500/10 text-amber-400 border border-amber-400/20 shadow-[0_0_15px_rgba(245,158,11,0.1)] animate-pulse">
                    <span className="w-1.5 h-1.5 rounded-full bg-amber-400 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                    Reported
                </span>
            );
        }
        if (song.is_active) {
            return (
                <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-emerald-500/10 text-emerald-400 border border-emerald-400/20 shadow-[0_0_15px_rgba(16,185,129,0.1)]">
                    <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                    Active
                </span>
            );
        }
        return (
            <span className="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest bg-slate-500/10 text-slate-400 border border-slate-400/20">
                <span className="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                Inactive
            </span>
        );
    };

    return (
        <AuthenticatedLayout header="Song Management">
            <Head title="Song Management" />

            <div className="space-y-8">
                {/* Header & Description */}
                <div className="rounded-[2rem] border border-white/10 bg-gradient-to-br from-slate-900 to-slate-950 p-8 shadow-2xl">
                    <div className="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.4em] text-cyan-400/80 mb-2">Moderation Dashboard</div>
                            <h2 className="text-4xl font-black text-white tracking-tight">Song Management</h2>
                            <p className="mt-3 max-w-2xl text-base text-slate-400 leading-relaxed">
                                Manage songs uploaded by artists, review metadata, monitor reports, and remove inappropriate content.
                            </p>
                        </div>
                    </div>

                    {/* Statistics Cards */}
                    <div className="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {[
                            { label: 'Total Songs', value: stats.total, color: 'text-white', bg: 'bg-white/5' },
                            { label: 'Active Tracks', value: stats.active, color: 'text-emerald-400', bg: 'bg-emerald-500/5' },
                            { label: 'Reported', value: stats.reported, color: 'text-amber-400', bg: 'bg-amber-500/5' },
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
                    <form method="get" action={route('admin.songs.index')} className="flex flex-wrap gap-4 items-center">
                        <div className="relative flex-1 min-w-[300px]">
                            <input
                                type="text"
                                name="search"
                                placeholder="Search by title, artist, or album..."
                                defaultValue={filters.search}
                                className="h-12 w-full rounded-2xl border border-white/10 bg-slate-950/50 px-5 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20 shadow-inner"
                            />
                        </div>
                        <select
                            name="status"
                            defaultValue={filters.status}
                            className="h-12 rounded-2xl border border-white/10 bg-slate-950/50 px-5 text-sm text-white focus:border-cyan-400/30 focus:outline-none"
                        >
                            <option value="">All Statuses</option>
                            <option value="active">Active</option>
                            <option value="reported">Reported</option>
                            <option value="deleted">Deleted</option>
                        </select>
                        <select
                            name="album_id"
                            defaultValue={filters.album_id}
                            className="h-12 rounded-2xl border border-white/10 bg-slate-950/50 px-5 text-sm text-white focus:border-cyan-400/30 focus:outline-none"
                        >
                            <option value="">All Albums</option>
                            {albums.map((album) => <option key={album.id} value={album.id}>{album.title}</option>)}
                        </select>
                        <button type="submit" className="h-12 px-8 rounded-2xl bg-white text-black text-[11px] font-black uppercase tracking-widest hover:bg-cyan-400 transition-all shadow-xl hover:shadow-cyan-400/20">
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
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Song Title</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Artist & Album</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Duration</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Status</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Reports</th>
                                    <th className="px-8 py-5 text-left text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Created</th>
                                    <th className="px-8 py-5 text-right text-[10px] font-black uppercase tracking-[0.25em] text-slate-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/5">
                                {songs.data.length === 0 ? (
                                    <tr><td colSpan={8} className="px-8 py-32 text-center text-slate-500 italic">No songs found matching your criteria.</td></tr>
                                ) : songs.data.map((song) => (
                                    <tr key={song.id} className="group transition-colors hover:bg-white/[0.03]">
                                        <td className="px-8 py-4">
                                            <div className="h-12 w-12 overflow-hidden rounded-xl border border-white/10 bg-white/5">
                                                {song.cover_url ? (
                                                    <img src={song.cover_url} alt={song.title} className="h-full w-full object-cover" />
                                                ) : (
                                                    <div className="flex h-full w-full items-center justify-center text-slate-700">
                                                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/></svg>
                                                    </div>
                                                )}
                                            </div>
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className="text-sm font-bold text-white group-hover:text-cyan-400 transition-colors">{song.title}</div>
                                            <div className="text-[10px] text-slate-500 mt-1 uppercase tracking-tighter font-mono">ID: {song.id.toString().padStart(6, '0')}</div>
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className="text-sm text-slate-300 font-medium">{song.artist || song.artist_model?.name || 'Unknown Artist'}</div>
                                            <div className="text-[10px] font-black text-slate-600 uppercase tracking-widest mt-0.5">{song.album?.title || 'Single'}</div>
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className="text-xs font-mono text-slate-400">{formatDuration(song.duration)}</div>
                                        </td>
                                        <td className="px-8 py-4">
                                            {getStatusBadge(song)}
                                        </td>
                                        <td className="px-8 py-4">
                                            <div className={`text-xs font-black ${song.report_count && song.report_count > 0 ? 'text-rose-400' : 'text-slate-700'}`}>
                                                {song.report_count || 0} Reports
                                            </div>
                                        </td>
                                        <td className="px-8 py-4 text-xs text-slate-500">
                                            {new Date(song.created_at).toLocaleDateString()}
                                        </td>
                                        <td className="px-8 py-4 text-right">
                                            <div className="flex items-center justify-end gap-3">
                                                <button
                                                    onClick={() => setSelectedSong(song)}
                                                    className="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-white hover:bg-white/10 transition-all"
                                                    title="View Details"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" strokeWidth="2" /><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" strokeWidth="2" /></svg>
                                                </button>
                                                <Link
                                                    href={route('admin.songs.edit', song.id)}
                                                    className="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-cyan-400 hover:bg-cyan-400/10 transition-all"
                                                    title="Edit Metadata"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/></svg>
                                                </Link>
                                                <button
                                                    onClick={() => handleDelete(song.id)}
                                                    className="p-2 rounded-lg bg-white/5 border border-white/10 text-slate-400 hover:text-rose-400 hover:bg-rose-400/10 transition-all"
                                                    title="Delete Song"
                                                    disabled={!!song.deleted_at}
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"/></svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {/* Pagination */}
                    {songs.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/5 px-8 py-6 bg-white/[0.01]">
                            <div className="text-[10px] font-black uppercase tracking-widest text-slate-600">
                                Page {songs.current_page} of {songs.last_page} ┬╖ {songs.total} Records
                            </div>
                            <div className="flex gap-2">
                                {songs.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`h-10 px-4 rounded-xl flex items-center justify-center text-[10px] font-black uppercase tracking-widest transition-all ${link.active ? 'bg-white text-black shadow-xl' : 'border border-white/10 text-slate-500 hover:bg-white/5 hover:text-white'} ${!link.url ? 'cursor-not-allowed opacity-20' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {/* Song Details Modal */}
            {selectedSong && (
                <div className="fixed inset-0 z-50 flex items-center justify-end bg-slate-950/80 backdrop-blur-md">
                    <button type="button" className="absolute inset-0 cursor-default" onClick={() => setSelectedSong(null)} />
                    <aside className="relative h-full w-full max-w-2xl overflow-y-auto border-l border-white/10 bg-slate-950 shadow-2xl animate-slide-in-right">
                        <div className="sticky top-0 z-10 bg-slate-950/80 backdrop-blur-md p-8 border-b border-white/10 flex items-center justify-between">
                            <div>
                                <div className="text-[10px] font-black uppercase tracking-[0.4em] text-cyan-400">Moderation Audit</div>
                                <h3 className="mt-2 text-3xl font-black text-white">{selectedSong.title}</h3>
                            </div>
                            <button
                                onClick={() => setSelectedSong(null)}
                                className="h-12 w-12 rounded-2xl bg-white/5 border border-white/10 flex items-center justify-center text-slate-400 hover:text-white transition-all"
                            >
                                <svg className="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
                            </button>
                        </div>

                        <div className="p-8 space-y-10">
                            {/* Summary Cards */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-inner">
                                    <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Platform Status</div>
                                    <div className="flex items-center gap-2">
                                        {getStatusBadge(selectedSong)}
                                    </div>
                                </div>
                                <div className="rounded-3xl border border-white/10 bg-white/5 p-6 shadow-inner">
                                    <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-2">Upload Date</div>
                                    <div className="text-lg font-black text-white">
                                        {new Date(selectedSong.created_at).toLocaleDateString(undefined, { dateStyle: 'long' })}
                                    </div>
                                </div>
                            </div>

                            {/* Entity Information */}
                            <div className="space-y-6">
                                <div className="flex items-center justify-between">
                                    <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">Entity Metadata</h4>
                                    <div className="h-px flex-1 bg-white/5 ml-4"></div>
                                </div>

                                <div className="grid gap-6 sm:grid-cols-2">
                                    <div className="flex items-start gap-4">
                                        <div className="h-16 w-16 shrink-0 rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
                                            {selectedSong.cover_url && <img src={selectedSong.cover_url} className="h-full w-full object-cover" />}
                                        </div>
                                        <div>
                                            <div className="text-[10px] font-black uppercase text-slate-500">Track</div>
                                            <div className="text-sm font-bold text-white">{selectedSong.title}</div>
                                            <div className="text-xs text-slate-400 mt-1">{formatDuration(selectedSong.duration)} ┬╖ Track #{selectedSong.track_number}</div>
                                        </div>
                                    </div>

                                    <div className="flex items-start gap-4">
                                        <div className="h-16 w-16 shrink-0 rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
                                            {selectedSong.artist_model?.image_url && <img src={selectedSong.artist_model.image_url} className="h-full w-full object-cover" />}
                                        </div>
                                        <div>
                                            <div className="text-[10px] font-black uppercase text-slate-500">Artist</div>
                                            <div className="text-sm font-bold text-white">{selectedSong.artist || selectedSong.artist_model?.name || 'Unknown Artist'}</div>
                                            <div className="text-xs text-slate-400 mt-1">Managed Creator</div>
                                        </div>
                                    </div>
                                </div>

                                <div className="rounded-3xl border border-white/5 bg-white/[0.02] p-6">
                                    <div className="text-[10px] font-black uppercase text-slate-500 mb-1">Album Project</div>
                                    <div className="text-sm font-bold text-white">{selectedSong.album?.title || 'Standalone Single'}</div>
                                </div>
                            </div>

                            {/* Reports History */}
                            <div className="space-y-6">
                                <div className="flex items-center justify-between">
                                    <h4 className="text-[10px] font-black uppercase tracking-[0.3em] text-slate-600">Report History ({selectedSong.report_count || 0})</h4>
                                    <div className="h-px flex-1 bg-white/5 ml-4"></div>
                                </div>

                                <div className="space-y-4">
                                    {(selectedSong.recent_reports || []).length === 0 ? (
                                        <div className="rounded-3xl border border-dashed border-white/10 py-12 text-center">
                                            <div className="text-xs font-bold text-slate-600 uppercase tracking-widest italic">No incident reports recorded</div>
                                        </div>
                                    ) : (
                                        selectedSong.recent_reports?.map((report) => (
                                            <div key={report.id} className="rounded-[2rem] border border-white/10 bg-white/5 p-6 group transition-all hover:bg-white/[0.07]">
                                                <div className="flex items-start justify-between mb-4">
                                                    <div>
                                                        <div className="text-[10px] font-black text-cyan-400 uppercase tracking-widest">{report.reason}</div>
                                                        <div className="text-[10px] font-bold text-slate-500 mt-1">
                                                            {report.reporter || 'Anonymous'} ┬╖ {new Date(report.created_at).toLocaleDateString()}
                                                        </div>
                                                    </div>
                                                    <span className={`rounded-full px-2.5 py-0.5 text-[8px] font-black uppercase tracking-widest ${report.status === 'open' ? 'bg-rose-500 text-white shadow-[0_0_10px_rgba(244,63,94,0.4)]' : 'bg-emerald-500/20 text-emerald-400'}`}>
                                                        {report.status}
                                                    </span>
                                                </div>
                                                {report.details && (
                                                    <div className="text-sm leading-6 text-slate-400 bg-slate-950/50 p-4 rounded-2xl border border-white/5 italic">
                                                        "{report.details}"
                                                    </div>
                                                )}
                                            </div>
                                        ))
                                    )}
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="sticky bottom-0 bg-slate-950 pt-6 pb-8 border-t border-white/10">
                                <div className="grid grid-cols-2 gap-4">
                                    <Link
                                        href={route('admin.songs.edit', selectedSong.id)}
                                        className="h-14 rounded-2xl bg-white text-black font-black text-[11px] uppercase tracking-widest flex items-center justify-center hover:bg-cyan-400 transition-all shadow-xl"
                                    >
                                        Edit Metadata
                                    </Link>
                                    <button
                                        onClick={() => handleDelete(selectedSong.id)}
                                        disabled={!!selectedSong.deleted_at}
                                        className={`h-14 rounded-2xl font-black text-[11px] uppercase tracking-widest border transition-all ${selectedSong.deleted_at ? 'border-white/5 bg-white/5 text-slate-600 cursor-not-allowed' : 'border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20'}`}
                                    >
                                        {selectedSong.deleted_at ? 'Already Deleted' : 'Remove Song'}
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

