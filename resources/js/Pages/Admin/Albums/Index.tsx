import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Album } from '@/types/album';
import { useState } from 'react';

interface ModeratedAlbum extends Album {
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
    filters: {
        search?: string;
    };
}

export default function Index({ albums, filters }: Props) {
    const [selectedAlbumIds, setSelectedAlbumIds] = useState<number[]>([]);
    const [selectedAlbum, setSelectedAlbum] = useState<ModeratedAlbum | null>(null);

    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this album?')) {
            router.delete(route('admin.albums.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const handleApprove = (id: number) => {
        router.post(route('admin.albums.approve', id), {}, { preserveScroll: true });
    };

    const handleHide = (id: number) => {
        router.post(route('admin.albums.hide', id), {}, { preserveScroll: true });
    };

    const handleReport = (id: number) => {
        const reason = window.prompt('Report reason', 'Needs moderation review');
        if (!reason) {
            return;
        }

        router.post(route('admin.albums.report', id), { reason }, { preserveScroll: true });
    };

    const handleBulkModerate = (action: 'approve' | 'hide') => {
        if (selectedAlbumIds.length === 0) {
            return;
        }

        router.post(
            route('admin.albums.bulk-moderate'),
            { action, ids: selectedAlbumIds },
            { preserveScroll: true, onSuccess: () => setSelectedAlbumIds([]) }
        );
    };

    const currentPageIds = albums.data.map((album) => album.id);
    const allSelected = currentPageIds.length > 0 && currentPageIds.every((id) => selectedAlbumIds.includes(id));

    const toggleSelected = (id: number) => {
        setSelectedAlbumIds((current) => (
            current.includes(id) ? current.filter((item) => item !== id) : [...current, id]
        ));
    };

    const toggleSelectAll = () => {
        setSelectedAlbumIds((current) => (
            allSelected ? current.filter((id) => !currentPageIds.includes(id)) : Array.from(new Set([...current, ...currentPageIds]))
        ));
    };

    const formatDate = (date: string | null) => {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString();
    };

    return (
        <AuthenticatedLayout header="Albums">
            <Head title="Albums" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Release control</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Albums</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Review album releases, trace song counts, and open album detail views for moderation or editing.
                            </p>
                        </div>
                        <Link href={route('admin.albums.create')}>
                            <PrimaryButton>Create Album</PrimaryButton>
                        </Link>
                    </div>
                    <div className="mt-6 flex flex-wrap items-center gap-3 text-sm text-slate-300">
                        <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1">Selected {selectedAlbumIds.length}</span>
                        <button onClick={() => handleBulkModerate('approve')} disabled={selectedAlbumIds.length === 0} className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 font-semibold text-emerald-100 disabled:cursor-not-allowed disabled:opacity-50">Approve selected</button>
                        <button onClick={() => handleBulkModerate('hide')} disabled={selectedAlbumIds.length === 0} className="rounded-full border border-amber-400/20 bg-amber-400/10 px-3 py-1 font-semibold text-amber-100 disabled:cursor-not-allowed disabled:opacity-50">Hide selected</button>
                    </div>
                    <div className="mt-6 grid gap-4 sm:grid-cols-3">
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">Search and filter by title or artist.</div>
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">Flag albums for deactivation or reactivation.</div>
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">Pin releases to trending or featured spots.</div>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 p-6">
                        <form method="get" action={route('admin.albums.index')} className="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                            <input type="text" name="search" placeholder="Search albums..." defaultValue={filters.search} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" />
                            <button type="submit" className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Search</button>
                        </form>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">
                                        <input type="checkbox" checked={allSelected} onChange={toggleSelectAll} className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400" />
                                    </th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Title</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Artist</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Status</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Release</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Songs</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {albums.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={7} className="px-6 py-10 text-center text-sm text-slate-400">No albums found.</td>
                                    </tr>
                                ) : (
                                    albums.data.map((album) => (
                                        <tr key={album.id} className={`transition hover:bg-white/5 ${selectedAlbum?.id === album.id ? 'bg-white/5' : ''}`}>
                                            <td className="whitespace-nowrap px-4 py-4 text-sm text-slate-300">
                                                <input type="checkbox" checked={selectedAlbumIds.includes(album.id)} onChange={() => toggleSelected(album.id)} className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400" />
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-white">{album.title}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{album.artist}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${album.release_status === 'published' ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20' : album.release_status === 'rejected' ? 'bg-rose-400/15 text-rose-200 ring-1 ring-rose-400/20' : 'bg-amber-400/15 text-amber-200 ring-1 ring-amber-400/20'}`}>
                                                    {album.release_status || 'draft'}
                                                </span>
                                            </td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{formatDate(album.release_date)}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{album.songs_count || 0}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <Link href={route('admin.albums.show', album.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                                <Link href={route('admin.albums.edit', album.id)} className="mr-3 text-fuchsia-200 hover:text-fuchsia-100">Edit</Link>
                                                <button type="button" onClick={() => setSelectedAlbum(album)} className="mr-3 text-sky-200 hover:text-sky-100">Details</button>
                                                <button onClick={() => handleApprove(album.id)} className="mr-3 text-emerald-300 hover:text-emerald-200">Approve</button>
                                                <button onClick={() => handleHide(album.id)} className="mr-3 text-amber-300 hover:text-amber-200">Hide</button>
                                                <button onClick={() => handleReport(album.id)} className="mr-3 text-violet-300 hover:text-violet-200">Report</button>
                                                <button onClick={() => handleDelete(album.id)} className="text-rose-300 hover:text-rose-200">Delete</button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                    {albums.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/10 px-6 py-4 text-sm text-slate-300">
                            <div>
                                Showing {albums.per_page * (albums.current_page - 1) + 1} to {Math.min(albums.per_page * albums.current_page, albums.total)} of {albums.total} results
                            </div>
                            <div className="flex gap-2">
                                {albums.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`rounded-2xl px-3 py-2 text-sm ${link.active ? 'bg-cyan-400/15 text-cyan-100 ring-1 ring-cyan-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>

            {selectedAlbum && (
                <div className="fixed inset-0 z-50 bg-slate-950/70 backdrop-blur-sm">
                    <button type="button" className="absolute inset-0 cursor-default" aria-label="Close details" onClick={() => setSelectedAlbum(null)} />
                    <aside className="absolute right-0 top-0 h-full w-full max-w-xl overflow-y-auto border-l border-white/10 bg-slate-950 p-6 shadow-2xl shadow-black/40">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Album details</div>
                                <h3 className="mt-2 text-3xl font-black text-white">{selectedAlbum.title}</h3>
                                <p className="mt-2 text-sm text-slate-300">{selectedAlbum.artist || 'Unknown artist'} · {selectedAlbum.songs_count || 0} songs</p>
                            </div>
                            <button type="button" onClick={() => setSelectedAlbum(null)} className="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm text-slate-200">Close</button>
                        </div>

                        <div className="mt-6 grid gap-4 sm:grid-cols-2">
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Publish state</div>
                                <div className="mt-2 text-lg font-bold text-white capitalize">{selectedAlbum.release_status || 'draft'}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Reports</div>
                                <div className="mt-2 text-lg font-bold text-white">{selectedAlbum.report_count ?? 0} total</div>
                                <div className="text-sm text-slate-400">{selectedAlbum.open_report_count ?? 0} open</div>
                            </div>
                        </div>

                        <div className="mt-6 flex flex-wrap gap-3">
                            <button type="button" onClick={() => handleApprove(selectedAlbum.id)} className="rounded-full border border-emerald-400/20 bg-emerald-400/10 px-4 py-2 text-sm font-semibold text-emerald-100">Approve</button>
                            <button type="button" onClick={() => handleHide(selectedAlbum.id)} className="rounded-full border border-amber-400/20 bg-amber-400/10 px-4 py-2 text-sm font-semibold text-amber-100">Hide</button>
                            <button type="button" onClick={() => handleReport(selectedAlbum.id)} className="rounded-full border border-violet-400/20 bg-violet-400/10 px-4 py-2 text-sm font-semibold text-violet-100">Report</button>
                        </div>

                        <div className="mt-6 rounded-3xl border border-white/10 bg-slate-950/40 p-5">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Report history</div>
                            <div className="mt-4 space-y-3">
                                {(selectedAlbum.recent_reports || []).length === 0 ? (
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4 text-sm text-slate-400">No reports yet.</div>
                                ) : (
                                    selectedAlbum.recent_reports?.map((report) => (
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

