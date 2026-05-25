import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

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
    };
    moderation: {
        reports_open: number;
        featured_items: number;
        favorites_total: number;
    };
    listening: {
        listen_events: number;
        completed_listens: number;
        today_listens: number;
        minutes_listened: number;
    };
    recent_growth: Array<{
        label: string;
        users: number;
        artists: number;
        songs: number;
    }>;
    most_favorited_songs: Array<{
        id: number;
        title: string;
        artist: string | null;
        album: string | null;
        favorites_count: number;
        play_count: number;
    }>;
    trending_artists: Array<{
        id: number;
        name: string;
        songs_count: number;
        albums_count: number;
        play_count: number;
    }>;
};

type AdminMetric = {
    label: string;
    value: string;
    tone: string;
    note: string;
};

type AdminCard = {
    title: string;
    description: string;
    href: string;
    tone: string;
    bullet: string;
};

const responsibilityCards: AdminCard[] = [
    {
        title: 'Artist Management',
        description: 'Create accounts, approve verification, activate or suspend artists, and review activity.',
        href: route('admin.artists.index'),
        tone: 'from-cyan-500/20 to-sky-500/10',
        bullet: 'Verification + lifecycle control',
    },
    {
        title: 'User Management',
        description: 'Monitor listeners, ban or unban accounts, and reset user status when needed.',
        href: route('admin.users.index'),
        tone: 'from-fuchsia-500/20 to-pink-500/10',
        bullet: 'Audience safety and trust',
    },
    {
        title: 'Song & Album Control',
        description: 'Approve uploads, edit metadata, hide tracks, or remove problematic content.',
        href: route('admin.songs.index'),
        tone: 'from-amber-500/20 to-orange-500/10',
        bullet: 'Catalog moderation',
    },
    {
        title: 'Content Moderation',
        description: 'Review reports, take enforcement actions, and handle copyright or abuse issues.',
        href: route('admin.reports.index'),
        tone: 'from-rose-500/20 to-red-500/10',
        bullet: 'Reports and enforcement',
    },
    {
        title: 'Feature & Promotion',
        description: 'Pin songs, artists, albums, and curated content across discovery surfaces.',
        href: route('admin.featured.index'),
        tone: 'from-violet-500/20 to-indigo-500/10',
        bullet: 'Homepage amplification',
    },
    {
        title: 'Genres & Categories',
        description: 'Keep the music taxonomy clean with genre creation, edits, and tagging consistency.',
        href: route('admin.genres.index'),
        tone: 'from-emerald-500/20 to-teal-500/10',
        bullet: 'Taxonomy governance',
    },
    {
        title: 'Analytics Dashboard',
        description: 'Track growth, playback trends, storage pressure, and platform performance.',
        href: route('admin.analytics.index'),
        tone: 'from-slate-500/20 to-slate-700/10',
        bullet: 'Data and insight',
    },
    {
        title: 'Deactivation System',
        description: 'Disable songs, albums, or artists without deleting data when disputes arise.',
        href: route('admin.albums.index'),
        tone: 'from-cyan-500/15 to-fuchsia-500/10',
        bullet: 'Safety rail operations',
    },
];

const quickActions = [
    { label: 'Review Artists', href: route('admin.artists.index') },
    { label: 'Inspect Reports', href: route('admin.reports.index') },
    { label: 'Feature Content', href: route('admin.featured.index') },
    { label: 'View Analytics', href: route('admin.analytics.index') },
];

export default function Dashboard() {
    const page = usePage<PageProps<{ metrics: DashboardMetrics }>>();
    const userRole = (page.props.auth.user as PageProps['auth']['user'] & { role?: string }).role;
    const metrics = page.props.metrics;

    const metricCards: AdminMetric[] = [
        { label: 'Total Users', value: metrics.totals.total_users.toLocaleString(), tone: 'from-cyan-400 to-sky-500', note: 'Listener base' },
        { label: 'Total Artists', value: metrics.totals.total_artists.toLocaleString(), tone: 'from-fuchsia-500 to-pink-500', note: 'Creator accounts' },
        { label: 'Songs Uploaded', value: metrics.totals.total_songs.toLocaleString(), tone: 'from-amber-400 to-orange-500', note: `${metrics.totals.active_songs.toLocaleString()} active` },
        { label: 'Albums Published', value: metrics.totals.total_albums.toLocaleString(), tone: 'from-emerald-400 to-teal-500', note: `${metrics.totals.published_albums.toLocaleString()} published` },
        { label: 'Reports Open', value: metrics.moderation.reports_open.toLocaleString(), tone: 'from-rose-400 to-red-500', note: 'Needs moderation' },
        { label: 'Featured Items', value: metrics.moderation.featured_items.toLocaleString(), tone: 'from-violet-400 to-indigo-500', note: 'Promoted content' },
    ];

    const maxGrowth = Math.max(
        1,
        ...metrics.recent_growth.flatMap((entry) => [entry.users, entry.artists, entry.songs]),
    );

    return (
        <AuthenticatedLayout header="Command Center">
            <Head title="Echo Panda Admin" />

            <div className="space-y-8">
                <section className="relative overflow-hidden rounded-[2rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(13,23,44,0.92),rgba(20,36,63,0.9))] p-6 shadow-2xl shadow-cyan-950/20 lg:p-8">
                    <div className="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(34,211,238,0.14),_transparent_26%),radial-gradient(circle_at_bottom_left,_rgba(236,72,153,0.1),_transparent_30%)]" />
                    <div className="relative grid gap-6 lg:grid-cols-[1.3fr_0.7fr] lg:items-end">
                        <div className="space-y-4">
                            <div className="inline-flex items-center gap-2 rounded-full border border-cyan-400/20 bg-cyan-400/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.35em] text-cyan-200">
                                Echo Panda Control Deck
                            </div>
                            <div className="max-w-3xl space-y-4">
                                <h2 className="text-3xl font-black tracking-tight text-white lg:text-5xl">
                                    Moderate, feature, and grow the platform from one dashboard.
                                </h2>
                                <p className="max-w-2xl text-sm leading-7 text-slate-300 lg:text-base">
                                    This admin surface is built around the Echo Panda theme: deep night tones, neon accent rails, and clear command cards for artist operations, moderation, promotion, and analytics.
                                </p>
                            </div>
                            <div className="flex flex-wrap gap-3">
                                {quickActions.map((action) => (
                                    <Link
                                        key={action.label}
                                        href={action.href}
                                        className="rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-semibold text-slate-100 transition hover:border-cyan-400/30 hover:bg-white/10"
                                    >
                                        {action.label}
                                    </Link>
                                ))}
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">
                                Architecture
                            </div>
                            <div className="mt-4 space-y-3 text-sm text-slate-200">
                                <div className="rounded-2xl border border-cyan-400/15 bg-cyan-400/10 px-4 py-3">Admin</div>
                                <div className="ml-6 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Users</div>
                                <div className="ml-12 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Artists</div>
                                <div className="ml-6 rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Songs / Albums</div>
                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Reports • Genres • Featured • Analytics</div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    {metricCards.map((metric) => (
                        <div key={metric.label} className="rounded-3xl border border-white/10 bg-white/5 p-5 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className={`mb-4 h-1.5 w-24 rounded-full bg-gradient-to-r ${metric.tone}`} />
                            <div className="text-sm text-slate-400">{metric.label}</div>
                            <div className="mt-1 text-3xl font-black text-white">{metric.value}</div>
                            <div className="mt-2 text-sm text-slate-400">{metric.note}</div>
                        </div>
                    ))}
                </section>

                <section className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {responsibilityCards.map((card) => (
                        <Link
                            key={card.title}
                            href={card.href}
                            className="group rounded-[1.75rem] border border-white/10 bg-white/5 p-5 transition duration-200 hover:-translate-y-1 hover:border-cyan-400/25 hover:bg-white/10"
                        >
                            <div className={`inline-flex rounded-full bg-gradient-to-r ${card.tone} px-3 py-1 text-xs font-semibold text-white/90`}>
                                {card.bullet}
                            </div>
                            <h3 className="mt-4 text-xl font-bold text-white group-hover:text-cyan-100">{card.title}</h3>
                            <p className="mt-3 text-sm leading-6 text-slate-300">{card.description}</p>
                            <div className="mt-5 text-sm font-semibold text-cyan-200">Open section →</div>
                        </Link>
                    ))}
                </section>

                <section className="grid gap-6 xl:grid-cols-[1.05fr_0.95fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">
                            Listening Analytics
                        </div>
                        <div className="mt-5 grid gap-4 sm:grid-cols-2">
                            <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <div className="text-sm text-slate-400">Listen events</div>
                                <div className="mt-1 text-2xl font-black text-white">{metrics.listening.listen_events.toLocaleString()}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <div className="text-sm text-slate-400">Completed listens</div>
                                <div className="mt-1 text-2xl font-black text-white">{metrics.listening.completed_listens.toLocaleString()}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <div className="text-sm text-slate-400">Today</div>
                                <div className="mt-1 text-2xl font-black text-white">{metrics.listening.today_listens.toLocaleString()}</div>
                            </div>
                            <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <div className="text-sm text-slate-400">Minutes listened</div>
                                <div className="mt-1 text-2xl font-black text-white">{metrics.listening.minutes_listened.toLocaleString()}</div>
                            </div>
                        </div>

                        <div className="mt-6 space-y-3">
                            {metrics.recent_growth.map((entry) => (
                                <div key={entry.label} className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                    <div className="flex items-center justify-between text-sm text-slate-300">
                                        <span>{entry.label}</span>
                                        <span>{entry.users + entry.artists + entry.songs} new items</span>
                                    </div>
                                    <div className="mt-3 space-y-2">
                                        {[
                                            { label: 'Users', value: entry.users, tone: 'bg-cyan-400' },
                                            { label: 'Artists', value: entry.artists, tone: 'bg-fuchsia-400' },
                                            { label: 'Songs', value: entry.songs, tone: 'bg-amber-400' },
                                        ].map((bar) => (
                                            <div key={bar.label} className="flex items-center gap-3 text-xs text-slate-400">
                                                <span className="w-14">{bar.label}</span>
                                                <div className="h-2 flex-1 overflow-hidden rounded-full bg-white/5">
                                                    <div className={`h-full rounded-full ${bar.tone}`} style={{ width: `${Math.max(4, (bar.value / maxGrowth) * 100)}%` }} />
                                                </div>
                                                <span className="w-8 text-right text-slate-300">{bar.value}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="rounded-[1.75rem] border border-white/10 bg-gradient-to-br from-cyan-400/10 via-slate-950/40 to-fuchsia-500/10 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">
                                Moderation + Promotion
                            </div>
                            <div className="mt-4 grid gap-4 sm:grid-cols-3">
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div className="text-sm text-slate-400">Open reports</div>
                                    <div className="mt-1 text-2xl font-black text-white">{metrics.moderation.reports_open.toLocaleString()}</div>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div className="text-sm text-slate-400">Favorites</div>
                                    <div className="mt-1 text-2xl font-black text-white">{metrics.moderation.favorites_total.toLocaleString()}</div>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div className="text-sm text-slate-400">Featured</div>
                                    <div className="mt-1 text-2xl font-black text-white">{metrics.moderation.featured_items.toLocaleString()}</div>
                                </div>
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Most favorited songs</div>
                            <div className="mt-5 space-y-3">
                                {metrics.most_favorited_songs.map((song) => (
                                    <div key={song.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="flex items-start justify-between gap-4">
                                            <div>
                                                <div className="font-semibold text-white">{song.title}</div>
                                                <div className="text-sm text-slate-400">{song.artist || 'Unknown Artist'}{song.album ? ` · ${song.album}` : ''}</div>
                                            </div>
                                            <div className="text-right text-sm text-slate-300">
                                                <div>{song.favorites_count} favorites</div>
                                                <div>{song.play_count} plays</div>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                                {metrics.most_favorited_songs.length === 0 && <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-400">No favorites recorded yet.</div>}
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Trending artists</div>
                            <div className="mt-5 space-y-3">
                                {metrics.trending_artists.map((artist) => (
                                    <div key={artist.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="flex items-start justify-between gap-4">
                                            <div>
                                                <div className="font-semibold text-white">{artist.name}</div>
                                                <div className="text-sm text-slate-400">{artist.songs_count} songs · {artist.albums_count} albums</div>
                                            </div>
                                            <div className="text-sm text-cyan-200">{artist.play_count} plays</div>
                                        </div>
                                    </div>
                                ))}
                                {metrics.trending_artists.length === 0 && <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-400">No trending artists yet.</div>}
                            </div>
                        </div>
                    </div>
                </section>

                <section className="rounded-[1.75rem] border border-white/10 bg-gradient-to-br from-cyan-400/10 via-slate-950/40 to-fuchsia-500/10 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">
                        Session
                    </div>
                    <div className="mt-4 text-2xl font-black text-white">
                        {userRole === 'admin' ? 'Administrator' : 'Platform user'}
                    </div>
                    <p className="mt-3 text-sm leading-6 text-slate-300">
                        This portal is scoped for {userRole === 'admin' ? 'admin operations' : 'general access'} and uses the shared Echo Panda visual language across every section.
                    </p>
                    <div className="mt-6 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200">
                        Theme: night navy + cyan + fuchsia with high-contrast command cards.
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
