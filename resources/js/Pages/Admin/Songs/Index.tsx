import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Song } from '@/types/song';
import { Album } from '@/types/album';
import { useState } from 'react';

interface ModeratedSong extends Song {
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
    filters: {
        search?: string;
        album_id?: string;
    };
}

export default function Index({ songs, albums, filters }: Props) {
    const [selectedSongIds, setSelectedSongIds] = useState<number[]>([]);
    const [selectedSong, setSelectedSong] = useState<ModeratedSong | null>(null);

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this song?')) {
            router.delete(route('admin.songs.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const handleApprove = (id: number) => {
        router.post(route('admin.songs.approve', id), {}, { preserveScroll: true });
    };

    const handleHide = (id: number) => {
        router.post(route('admin.songs.hide', id), {}, { preserveScroll: true });
    };

    const handleReport = (id: number) => {
        const reason = window.prompt('Report reason', 'Needs moderation review');
        if (!reason) {
            return;
        }

        router.post(route('admin.songs.report', id), { reason }, { preserveScroll: true });
    };

    const handleBulkModerate = (action: 'approve' | 'hide') => {
        if (selectedSongIds.length === 0) {
            return;
        }

        router.post(
            route('admin.songs.bulk-moderate'),
            { action, ids: selectedSongIds },
            { preserveScroll: true, onSuccess: () => setSelectedSongIds([]) }
        );
    };

    const currentPageIds = songs.data.map((song) => song.id);
    const allSelected = currentPageIds.length > 0 && currentPageIds.every((id) => selectedSongIds.includes(id));

    const toggleSelected = (id: number) => {
        setSelectedSongIds((current) => (
            current.includes(id) ? current.filter((item) => item !== id) : [...current, id]
        ));
    };

    const toggleSelectAll = () => {
        setSelectedSongIds((current) => (
            allSelected ? current.filter((id) => !currentPageIds.includes(id)) : Array.from(new Set([...current, ...currentPageIds]))
        ));
    };

    const formatDuration = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    return (
        <AuthenticatedLayout header="Songs">
            <Head title="Songs" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Track control</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Songs</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Moderate individual tracks, browse by album, and keep the catalog clean and searchable.
                            </p>
                        </div>
                        <Link href={route('admin.songs.create')}>
                            <PrimaryButton>Create Song</PrimaryButton>
                        </Link>
                    </div>
                    <div className="mt-6 flex flex-wrap items-center gap-3 text-sm text-slate-300">
                        <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1">Selected {selectedSongIds.length}</span>
                        <button onClick={() => handleBulkModerate('approve')} disabled={selectedSongIds.length === 0} className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 font-semibold text-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">Approve selected</button>
                        <button onClick={() => handleBulkModerate('hide')} disabled={selectedSongIds.length === 0} className="rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 font-semibold text-amber-100 disabled:cursor-not-allowed disabled:opacity-50">Hide selected</button>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 p-6">
                        <form method="get" action={route('admin.songs.index')} className="grid gap-3 lg:grid-cols-[1fr_220px_auto] lg:items-center">
                            <input type="text" name="search" placeholder="Search songs..." defaultValue={filters.search} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" />
                            <select name="album_id" defaultValue={filters.album_id} className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20">
                                <option value="">All Albums</option>
                                {albums.map((album) => <option key={album.id} value={album.id}>{album.title}</option>)}
                            </select>
                            <button type="submit" className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Filter</button>
                        </form>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">
                                        <input type="checkbox" checked={allSelected} onChange={toggleSelectAll} className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400" />
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Track</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Title</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Artist</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Album</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Duration</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {songs.data.length === 0 ? (
                                    <tr><td colSpan={8} className="px-6 py-10 text-center text-sm text-slate-400">No songs found.</td></tr>
                                ) : songs.data.map((song) => (
                                    <tr key={song.id} className={`transition hover:bg-white/5 ${selectedSong?.id === song.id ? 'bg-white/5' : ''}`}>
                                        <td className="whitespace-nowrap px-4 py-4 text-sm text-slate-300">
                                            <input type="checkbox" checked={selectedSongIds.includes(song.id)} onChange={() => toggleSelected(song.id)} className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400" />
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{song.track_number}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-white">{song.title}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{song.artist || song.album?.artist || 'N/A'}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{song.album?.title || 'N/A'}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">
                                            <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${song.is_active ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20' : 'bg-rose-400/15 text-rose-200 ring-1 ring-rose-400/20'}`}>
                                                {song.is_active ? 'Approved' : 'Hidden'}
                                            </span>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{formatDuration(song.duration)}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link href={route('admin.songs.show', song.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                            <Link href={route('admin.songs.edit', song.id)} className="mr-3 text-fuchsia-200 hover:text-fuchsia-100">Edit</Link>
                                            <button type="button" onClick={() => setSelectedSong(song)} className="mr-3 text-sky-200 hover:text-sky-100">Details</button>
                                            <button onClick={() => handleApprove(song.id)} className="mr-3 text-emerald-300 hover:text-emerald-200">Approve</button>
                                            <button onClick={() => handleHide(song.id)} className="mr-3 text-amber-300 hover:text-amber-200">Hide</button>
                                            <button onClick={() => handleReport(song.id)} className="mr-3 text-violet-300 hover:text-violet-200">Report</button>
                                            <button onClick={() => handleDelete(song.id)} className="text-rose-300 hover:text-rose-200">Delete</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {songs.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/10 px-6 py-4 text-sm text-slate-300">
                            <div>Showing {songs.per_page * (songs.current_page - 1) + 1} to {Math.min(songs.per_page * songs.current_page, songs.total)} of {songs.total} results</div>
                            <div className="flex gap-2">
                                {songs.links.map((link, index) => (
                                    <Link key={index} href={link.url || '#'} className={`rounded-2xl px-3 py-2 text-sm ${link.active ? 'bg-cyan-400/15 text-cyan-100 ring-1 ring-cyan-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {selectedSong && (
                <div className="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm">
                    <button type="button" className="absolute inset-0 cursor-default" aria-label="Close details" onClick={() => setSelectedSong(null)} />
                    <aside className="absolute right-0 top-0 h-full w-full max-w-xl overflow-y-auto border-l border-white/10 bg-slate-950 p-6 shadow-2xl shadow-black/40">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Song details</div>
                                <h3 className="mt-2 text-3xl font-black text-white">{selectedSong.title}</h3>
                                <p className="mt-2 text-sm text-slate-300">{selectedSong.artist || selectedSong.album?.artist || 'Unknown artist'} · {selectedSong.album?.title || 'No album'}</p>
                            </div>
                            <button type="button" onClick={() => setSelectedSong(null)} className="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200">Close</button>
                        </div>

                        <div className="mt-6 grid gap-4 sm:grid-cols-2">
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Publish state</div>
                                <div className="mt-2 text-lg font-bold text-white">{selectedSong.is_active ? 'Approved' : 'Hidden'}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Reports</div>
                                <div className="mt-2 text-lg font-bold text-white">{selectedSong.report_count ?? 0} total</div>
                                <div className="text-sm text-slate-400">{selectedSong.open_report_count ?? 0} open</div>
                            </div>
                        </div>

                        <div className="mt-6 flex flex-wrap gap-3">
                            <button type="button" onClick={() => handleApprove(selectedSong.id)} className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-100">Approve</button>
                            <button type="button" onClick={() => handleHide(selectedSong.id)} className="rounded-full border border-amber-400/20 bg-amber-400/10 px-4 py-2 text-sm font-semibold text-amber-100">Hide</button>
                            <button type="button" onClick={() => handleReport(selectedSong.id)} className="rounded-full border border-violet-400/20 bg-violet-400/10 px-4 py-2 text-sm font-semibold text-violet-100">Report</button>
                        </div>

                        <div className="mt-6 rounded-3xl border border-white/10 bg-slate-950/40 p-5">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Report history</div>
                            <div className="mt-4 space-y-3">
                                {(selectedSong.recent_reports || []).length === 0 ? (
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-400">No reports yet.</div>
                                ) : (
                                    selectedSong.recent_reports?.map((report) => (
                                        <div key={report.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                            <div className="flex items-start justify-between gap-3">
                                                <div>
                                                    <div className="font-semibold text-white">{report.reason}</div>
                                                    <div className="text-sm text-slate-400">{report.reporter || 'Anonymous'} · {new Date(report.created_at).toLocaleString()}</div>
                                                </div>
                                                <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${report.status === 'open' ? 'bg-amber-400/15 text-amber-200 ring-1 ring-amber-400/20' : 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20'}`}>
                                                    {report.status}
                                                </span>
                                            </div>
                                            {report.details && <div className="mt-3 text-sm leading-6 text-slate-300">{report.details}</div>}
                                        </div>
                                    ))
                                )}
                            </div>
                        </div>
                    </aside>
                </div>
            )}
        </AuthenticatedLayout>
    );
}

