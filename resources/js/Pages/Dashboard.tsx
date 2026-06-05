import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { ReactNode } from 'react';
import {
    FaUsers,
    FaMusic,
    FaLayerGroup,
    FaShieldHalved,
    FaStar,
    FaCompactDisc
} from "react-icons/fa6";

type DashboardMetrics = {
    totals: {
        total_users: number;
        total_admins: number;
        total_artists: number;
        active_artists: number;
        total_songs: number;
        active_songs: number;
        total_albums: number;
        published_albums: number;
        total_categories: number;
        total_tags: number;
    };
    moderation: {
        reports_open: number;
        favorites_total: number;
    };
};

type AdminMetric = {
    label: string;
    value: string;
    tone: string;
    note: string;
    icon: ReactNode;
    colorRail: string;
};

// Global UI Style Dictionary
const STYLES = {
    dashboardWrapper: "mx-auto max-w-[1400px] space-y-6 pb-12 px-4 text-slate-100 font-sans",
    glassCard: "rounded-2xl border border-white/[0.08] bg-slate-900/40 backdrop-blur-xl shadow-xl shadow-black/20 p-6",
    subHeaderLabel: "text-[10px] font-black text-cyan-400 uppercase tracking-[0.2em] block mb-1"
};

export default function Dashboard() {
    const page = usePage<PageProps<{ metrics: DashboardMetrics }>>();
    const user = page.props.auth.user as PageProps['auth']['user'] & { role?: string };
    const metrics = page.props.metrics;

    const metricCards: AdminMetric[] = [
        { label: 'Total Users', value: metrics.totals.total_users.toLocaleString(), tone: 'from-cyan-500/20 to-cyan-500/5 text-cyan-400 border-cyan-500/30', note: 'Listener base', icon: <FaUsers size={16} />, colorRail: 'bg-cyan-500' },
        { label: 'Total Artists', value: metrics.totals.total_artists.toLocaleString(), tone: 'from-fuchsia-500/20 to-fuchsia-500/5 text-fuchsia-400 border-fuchsia-500/30', note: 'Creator accounts', icon: <FaStar size={16} />, colorRail: 'bg-fuchsia-500' },
        { label: 'Songs Uploaded', value: metrics.totals.total_songs.toLocaleString(), tone: 'from-amber-500/20 to-amber-500/5 text-amber-400 border-amber-500/30', note: `${metrics.totals.active_songs.toLocaleString()} active`, icon: <FaMusic size={16} />, colorRail: 'bg-amber-500' },
        { label: 'Albums Published', value: metrics.totals.total_albums.toLocaleString(), tone: 'from-emerald-500/20 to-emerald-500/5 text-emerald-400 border-emerald-500/30', note: `${metrics.totals.published_albums.toLocaleString()} published`, icon: <FaCompactDisc size={16} />, colorRail: 'bg-emerald-500' },
        { label: 'Categories', value: metrics.totals.total_categories.toLocaleString(), tone: 'from-indigo-500/20 to-indigo-500/5 text-indigo-400 border-indigo-500/30', note: 'Genre taxonomy', icon: <FaLayerGroup size={16} />, colorRail: 'bg-indigo-500' },
        { label: 'Open Reports', value: metrics.moderation.reports_open.toLocaleString(), tone: 'from-rose-500/20 to-rose-500/5 text-rose-400 border-rose-500/30', note: 'Needs moderation', icon: <FaShieldHalved size={16} />, colorRail: 'bg-rose-500' },
    ];

    return (
        <AuthenticatedLayout header="Command Center">
            <Head title="Echo Panda Admin" />

            <div className={STYLES.dashboardWrapper}>

                {/* Hero Feature Block */}
                <section className="relative overflow-hidden rounded-2xl border border-white/[0.08] bg-[#0d1021]/80 p-8 shadow-2xl lg:p-10">
                    <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(34,211,238,0.12),_transparent_45%)]" />

                    <div className="relative z-10 flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-end">
                        <div className="max-w-2xl space-y-4">
                            <div className="inline-flex items-center gap-2 rounded-full border border-cyan-500/20 bg-cyan-500/10 px-4 py-1.5 text-[10px] font-bold uppercase tracking-widest text-cyan-300">
                                <span className="h-2 w-2 animate-pulse rounded-full bg-cyan-400 shadow-[0_0_8px_#22d3ee]" />
                                Echo Panda Control Deck
                            </div>
                            <h2 className="text-3xl font-black tracking-tight text-white lg:text-5xl leading-tight">
                                Moderate, feature, and grow the<br />
                                <span className="bg-gradient-to-r from-cyan-400 to-indigo-400 bg-clip-text text-transparent">platform from one dashboard.</span>
                            </h2>
                            <p className="text-sm leading-relaxed text-slate-400">
                                This admin surface is built around the Echo Panda theme: deep night tones, neon accent rails, and clear command cards for artist operations, moderation, promotion, and analytics.
                            </p>
                            <div className="flex flex-wrap gap-3 pt-2">
                                <Link href={route('admin.artists.index')} className="rounded-xl bg-white text-slate-950 font-bold px-5 py-3 text-xs uppercase tracking-wider transition hover:bg-cyan-400 active:scale-98">
                                    Review Artists
                                </Link>
                                <Link href={route('admin.moderation.index')} className="rounded-xl border border-white/[0.1] bg-white/[0.02] hover:bg-white/[0.06] text-white font-bold px-5 py-3 text-xs uppercase tracking-wider transition active:scale-98">
                                    Moderation Queue
                                </Link>
                            </div>
                        </div>

                        {/* Top Summary Frame */}
                        <div className="w-full lg:max-w-xs">
                            <div className="rounded-xl border border-white/[0.08] bg-black/30 p-5">
                                <span className={STYLES.subHeaderLabel}>Architecture Layout</span>
                                <div className="mt-4 space-y-3">
                                    <div className="flex items-center justify-between border-b border-white/[0.04] pb-2">
                                        <span className="text-xs font-semibold text-slate-400">Total Genres</span>
                                        <span className="text-sm font-black text-cyan-400">{metrics.totals.total_categories}</span>
                                    </div>
                                    <div className="flex items-center justify-between border-b border-white/[0.04] pb-2">
                                        <span className="text-xs font-semibold text-slate-400">Active Tags</span>
                                        <span className="text-sm font-black text-fuchsia-400">{metrics.totals.total_tags}</span>
                                    </div>
                                    <div className="flex items-center justify-between">
                                        <span className="text-xs font-semibold text-slate-400">Open Incidents</span>
                                        <span className="text-sm font-black text-rose-500">{metrics.moderation.reports_open}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                {/* Primary Core Grid Metrics */}
                <section className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6">
                    {metricCards.map((metric) => (
                        <div key={metric.label} className="group relative rounded-2xl border border-white/[0.08] bg-slate-900/30 p-5 transition-all hover:bg-slate-900/60">
                            {/* Accent Neon Colored Top Bar */}
                            <div className={`absolute top-0 left-4 right-4 h-[2px] ${metric.colorRail} opacity-60`} />

                            <div className={`mb-3 flex h-8 w-8 items-center justify-center rounded-xl border bg-gradient-to-b ${metric.tone}`}>
                                {metric.icon}
                            </div>
                            <div className="text-[10px] font-bold uppercase tracking-widest text-slate-500">{metric.label}</div>
                            <div className="mt-1 text-2xl font-black text-white tracking-tight">{metric.value}</div>
                            <div className="mt-2 text-[10px] font-medium text-slate-400 group-hover:text-cyan-400 transition-colors">{metric.note}</div>
                        </div>
                    ))}
                </section>

                {/* Session Guard Footer */}
                <section className="flex flex-col items-center justify-between gap-6 rounded-2xl border border-white/[0.08] bg-slate-950/40 p-8 lg:flex-row shadow-xl">
                    <div>
                        <span className="text-[9px] font-bold text-slate-500 uppercase tracking-widest block">Security Token Scoping</span>
                        <h4 className="mt-1 text-xl font-black text-white tracking-tight">
                            Authenticated as {user.role === 'admin' ? 'Platform Administrator' : 'System Terminal User'}
                        </h4>
                        <p className="mt-1 text-xs text-slate-500 font-medium">
                            Every single database transaction and lifecycle modification is cryptographically audited.
                        </p>
                    </div>
                    <div className="flex h-12 items-center gap-3 rounded-xl bg-black/40 px-5 border border-white/[0.06]">
                        <span className="h-2 w-2 animate-pulse rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399]" />
                        <span className="text-[10px] font-bold uppercase tracking-widest text-slate-400 font-mono">Core Gateway Sync Lock</span>
                    </div>
                </section>

            </div>
        </AuthenticatedLayout>
    );
}
