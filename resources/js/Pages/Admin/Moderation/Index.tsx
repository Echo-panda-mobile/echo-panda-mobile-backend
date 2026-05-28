import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';

interface QueueReport {
    id: number;
    reason: string;
    details?: string | null;
    status: string;
    reporter?: string | null;
    created_at: string;
}

interface SongReportGroup {
    id: number;
    title: string;
    artist?: string | null;
    album?: string | null;
    is_active: boolean;
    play_count: number;
    reports: QueueReport[];
}

interface AlbumReportGroup {
    id: number;
    title: string;
    artist?: string | null;
    release_status?: string | null;
    songs_count: number;
    reports: QueueReport[];
}

interface Props extends PageProps {
    songReports: SongReportGroup[];
    albumReports: AlbumReportGroup[];
    openReportsCount: number;
}

export default function Index({ songReports, albumReports, openReportsCount }: Props) {
    const dismissReport = (reportId: number) => {
        if (confirm('Dismiss this report?')) {
            router.delete(route('admin.reports.destroy', reportId), {
                preserveScroll: true,
            });
        }
    };

    const formatDate = (value: string) => new Date(value).toLocaleString();

    return (
        <AuthenticatedLayout header="Moderation Queue">
            <Head title="Moderation Queue" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Open reports</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Moderation Queue</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Review open song and album reports in one place, then jump to the underlying item or dismiss completed reports.
                            </p>
                        </div>
                        <div className="rounded-3xl border border-white/10 bg-slate-950/40 px-5 py-4 text-right">
                            <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Open reports</div>
                            <div className="mt-1 text-3xl font-black text-white">{openReportsCount}</div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 xl:grid-cols-2">
                    <section className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <div className="text-xs font-semibold uppercase tracking-[0.35em] text-emerald-300/70">Song reports</div>
                                <h3 className="mt-2 text-2xl font-black text-white">Tracks under review</h3>
                            </div>
                            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-300">{songReports.length} songs</span>
                        </div>

                        <div className="mt-6 space-y-4">
                            {songReports.length === 0 ? (
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-5 text-sm text-slate-400">No open song reports.</div>
                            ) : (
                                songReports.map((song) => (
                                    <div key={song.id} className="rounded-3xl border border-white/10 bg-white/5 p-5">
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h4 className="text-lg font-bold text-white">{song.title}</h4>
                                                    <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${song.is_active ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20' : 'bg-rose-400/15 text-rose-200 ring-1 ring-rose-400/20'}`}>
                                                        {song.is_active ? 'Approved' : 'Hidden'}
                                                    </span>
                                                </div>
                                                <p className="mt-1 text-sm text-slate-400">{song.artist || 'Unknown artist'} · {song.album || 'No album'}</p>
                                                <p className="mt-1 text-sm text-slate-500">{song.play_count} plays</p>
                                            </div>
                                            <div className="flex flex-wrap gap-2">
                                                <Link href={route('admin.songs.show', song.id)} className="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-2 text-sm font-semibold text-cyan-100">Open song</Link>
                                                <Link href={route('admin.songs.edit', song.id)} className="rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-2 text-sm font-semibold text-fuchsia-100">Edit</Link>
                                            </div>
                                        </div>

                                        <div className="mt-5 space-y-3">
                                            {song.reports.map((report) => (
                                                <div key={report.id} className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                        <div>
                                                            <div className="font-semibold text-white">{report.reason}</div>
                                                            <div className="mt-1 text-sm text-slate-400">{report.reporter || 'Anonymous'} · {formatDate(report.created_at)}</div>
                                                        </div>
                                                        <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${report.status === 'open' ? 'bg-amber-400/15 text-amber-200 ring-1 ring-amber-400/20' : 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20'}`}>
                                                            {report.status}
                                                        </span>
                                                    </div>
                                                    {report.details && <div className="mt-3 text-sm leading-6 text-slate-300">{report.details}</div>}
                                                    <div className="mt-4 flex justify-end">
                                                        <button type="button" onClick={() => dismissReport(report.id)} className="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-200">Dismiss report</button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </section>

                    <section className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="flex items-center justify-between gap-4">
                            <div>
                                <div className="text-xs font-semibold uppercase tracking-[0.35em] text-amber-300/70">Album reports</div>
                                <h3 className="mt-2 text-2xl font-black text-white">Releases under review</h3>
                            </div>
                            <span className="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-sm text-slate-300">{albumReports.length} albums</span>
                        </div>

                        <div className="mt-6 space-y-4">
                            {albumReports.length === 0 ? (
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-5 text-sm text-slate-400">No open album reports.</div>
                            ) : (
                                albumReports.map((album) => (
                                    <div key={album.id} className="rounded-3xl border border-white/10 bg-white/5 p-5">
                                        <div className="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                            <div>
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <h4 className="text-lg font-bold text-white">{album.title}</h4>
                                                    <span className="rounded-full border border-white/10 bg-slate-950/40 px-2.5 py-1 text-xs font-semibold text-slate-200 capitalize">
                                                        {album.release_status || 'draft'}
                                                    </span>
                                                </div>
                                                <p className="mt-1 text-sm text-slate-400">{album.artist || 'Unknown artist'} · {album.songs_count} songs</p>
                                            </div>
                                            <div className="flex flex-wrap gap-2">
                                                <Link href={route('admin.albums.show', album.id)} className="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-2 text-sm font-semibold text-cyan-100">Open album</Link>
                                                <Link href={route('admin.albums.edit', album.id)} className="rounded-full border border-fuchsia-400/20 bg-fuchsia-400/10 px-3 py-2 text-sm font-semibold text-fuchsia-100">Edit</Link>
                                            </div>
                                        </div>

                                        <div className="mt-5 space-y-3">
                                            {album.reports.map((report) => (
                                                <div key={report.id} className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                                    <div className="flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                                                        <div>
                                                            <div className="font-semibold text-white">{report.reason}</div>
                                                            <div className="mt-1 text-sm text-slate-400">{report.reporter || 'Anonymous'} · {formatDate(report.created_at)}</div>
                                                        </div>
                                                        <span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${report.status === 'open' ? 'bg-amber-400/15 text-amber-200 ring-1 ring-amber-400/20' : 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20'}`}>
                                                            {report.status}
                                                        </span>
                                                    </div>
                                                    {report.details && <div className="mt-3 text-sm leading-6 text-slate-300">{report.details}</div>}
                                                    <div className="mt-4 flex justify-end">
                                                        <button type="button" onClick={() => dismissReport(report.id)} className="rounded-full border border-white/10 bg-white/5 px-3 py-2 text-sm font-semibold text-slate-200">Dismiss report</button>
                                                    </div>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </section>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
