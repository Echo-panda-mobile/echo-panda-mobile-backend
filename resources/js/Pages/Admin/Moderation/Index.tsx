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
    reports: QueueReport[];
}

interface AlbumReportGroup {
    id: number;
    title: string;
    artist?: string | null;
    release_status?: string | null;
    reports: QueueReport[];
}

interface ArtistReportGroup {
    id: number;
    name: string;
    is_active: boolean;
    reports: QueueReport[];
}

interface Props extends PageProps {
    songReports: SongReportGroup[];
    albumReports: AlbumReportGroup[];
    artistReports: ArtistReportGroup[];
    openReportsCount: number;
}

export default function Index({ songReports, albumReports, artistReports, openReportsCount }: Props) {
    const handleAction = (reportId: number, action: 'review' | 'remove' | 'ignore') => {
        router.post(route('admin.reports.action', reportId), { action }, {
            preserveScroll: true,
        });
    };

    const formatDate = (value: string) => new Date(value).toLocaleString();

    return (
        <AuthenticatedLayout header="Moderation Queue">
            <Head title="Moderation Queue" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Safety Protocol</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Moderation Queue</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Review user reports for copyright, inappropriate content, or policy violations. Take enforcement actions to maintain platform integrity.
                            </p>
                        </div>
                        <div className="rounded-3xl border border-white/10 bg-slate-950/40 px-5 py-4 text-right">
                            <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Pending items</div>
                            <div className="mt-1 text-3xl font-black text-white">{openReportsCount}</div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-8 xl:grid-cols-3">
                    {/* Song Reports */}
                    <section className="space-y-4">
                        <div className="flex items-center justify-between px-2">
                            <h3 className="text-xl font-black text-white uppercase tracking-tight">Song Reports</h3>
                            <span className="text-xs font-bold text-slate-500">{songReports.length} flagged</span>
                        </div>
                        <div className="space-y-4">
                            {songReports.length === 0 ? (
                                <div className="rounded-2xl border border-white/5 bg-white/5 p-6 text-center text-sm text-slate-500">Clear queue</div>
                            ) : (
                                songReports.map((song) => (
                                    <div key={song.id} className="rounded-3xl border border-white/10 bg-slate-950/50 p-5 backdrop-blur-sm">
                                        <div className="mb-4">
                                            <div className="flex items-start justify-between gap-2">
                                                <h4 className="font-bold text-white line-clamp-1">{song.title}</h4>
                                                <span className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-widest ${song.is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'}`}>
                                                    {song.is_active ? 'Active' : 'Hidden'}
                                                </span>
                                            </div>
                                            <p className="text-xs text-slate-400 mt-1">{song.artist || 'Unknown'}</p>
                                        </div>
                                        <div className="space-y-3">
                                            {song.reports.map((report) => (
                                                <ReportItem key={report.id} report={report} onAction={handleAction} formatDate={formatDate} />
                                            ))}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </section>

                    {/* Artist Reports */}
                    <section className="space-y-4">
                        <div className="flex items-center justify-between px-2">
                            <h3 className="text-xl font-black text-white uppercase tracking-tight">Artist Reports</h3>
                            <span className="text-xs font-bold text-slate-500">{artistReports.length} flagged</span>
                        </div>
                        <div className="space-y-4">
                            {artistReports.length === 0 ? (
                                <div className="rounded-2xl border border-white/5 bg-white/5 p-6 text-center text-sm text-slate-500">Clear queue</div>
                            ) : (
                                artistReports.map((artist) => (
                                    <div key={artist.id} className="rounded-3xl border border-white/10 bg-slate-950/50 p-5 backdrop-blur-sm">
                                        <div className="mb-4">
                                            <div className="flex items-start justify-between gap-2">
                                                <h4 className="font-bold text-white line-clamp-1">{artist.name}</h4>
                                                <span className={`shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-widest ${artist.is_active ? 'bg-emerald-500/10 text-emerald-400' : 'bg-rose-500/10 text-rose-400'}`}>
                                                    {artist.is_active ? 'Active' : 'Suspended'}
                                                </span>
                                            </div>
                                        </div>
                                        <div className="space-y-3">
                                            {artist.reports.map((report) => (
                                                <ReportItem key={report.id} report={report} onAction={handleAction} formatDate={formatDate} />
                                            ))}
                                        </div>
                                    </div>
                                ))
                            )}
                        </div>
                    </section>

                    {/* Album Reports */}
                    <section className="space-y-4">
                        <div className="flex items-center justify-between px-2">
                            <h3 className="text-xl font-black text-white uppercase tracking-tight">Album Reports</h3>
                            <span className="text-xs font-bold text-slate-500">{albumReports.length} flagged</span>
                        </div>
                        <div className="space-y-4">
                            {albumReports.length === 0 ? (
                                <div className="rounded-2xl border border-white/5 bg-white/5 p-6 text-center text-sm text-slate-500">Clear queue</div>
                            ) : (
                                albumReports.map((album) => (
                                    <div key={album.id} className="rounded-3xl border border-white/10 bg-slate-950/50 p-5 backdrop-blur-sm">
                                        <div className="mb-4">
                                            <div className="flex items-start justify-between gap-2">
                                                <h4 className="font-bold text-white line-clamp-1">{album.title}</h4>
                                                <span className="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-black uppercase tracking-widest bg-white/5 text-slate-400">
                                                    {album.release_status}
                                                </span>
                                            </div>
                                            <p className="text-xs text-slate-400 mt-1">{album.artist || 'Unknown'}</p>
                                        </div>
                                        <div className="space-y-3">
                                            {album.reports.map((report) => (
                                                <ReportItem key={report.id} report={report} onAction={handleAction} formatDate={formatDate} />
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

function ReportItem({ report, onAction, formatDate }: { report: QueueReport, onAction: any, formatDate: any }) {
    const statusColors: Record<string, string> = {
        'open': 'bg-amber-400/10 text-amber-400 border-amber-400/20',
        'under_review': 'bg-cyan-400/10 text-cyan-400 border-cyan-400/20',
        'resolved': 'bg-emerald-400/10 text-emerald-400 border-emerald-400/20',
        'ignored': 'bg-slate-400/10 text-slate-400 border-slate-400/20',
    };

    return (
        <div className="rounded-2xl border border-white/5 bg-white/5 p-4 transition-all hover:bg-white/[0.08]">
            <div className="flex items-start justify-between gap-3">
                <div>
                    <div className="text-sm font-bold text-white">{report.reason}</div>
                    <div className="mt-0.5 text-[10px] text-slate-500 font-medium">
                        {report.reporter || 'Anonymous'} · {formatDate(report.created_at)}
                    </div>
                </div>
                <span className={`rounded-full border px-2 py-0.5 text-[9px] font-black uppercase tracking-widest ${statusColors[report.status] || statusColors.open}`}>
                    {report.status.replace('_', ' ')}
                </span>
            </div>
            {report.details && (
                <div className="mt-3 text-xs leading-5 text-slate-400 line-clamp-2">
                    {report.details}
                </div>
            )}

            {report.status !== 'resolved' && report.status !== 'ignored' && (
                <div className="mt-4 flex flex-wrap gap-2 border-t border-white/5 pt-3">
                    <button
                        onClick={() => onAction(report.id, 'review')}
                        className="rounded-lg bg-cyan-400/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-cyan-200 transition hover:bg-cyan-400/20"
                    >
                        Review
                    </button>
                    <button
                        onClick={() => onAction(report.id, 'remove')}
                        className="rounded-lg bg-rose-500/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-rose-200 transition hover:bg-rose-500/20"
                    >
                        Remove
                    </button>
                    <button
                        onClick={() => onAction(report.id, 'ignore')}
                        className="rounded-lg bg-white/5 px-3 py-1.5 text-[10px] font-black uppercase tracking-widest text-slate-300 transition hover:bg-white/10"
                    >
                        Ignore
                    </button>
                </div>
            )}
        </div>
    );
}
