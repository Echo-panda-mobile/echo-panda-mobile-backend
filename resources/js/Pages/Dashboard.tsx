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
        total_categories: number;
        total_tags: number;
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
        total_streams: number;
        new_users_this_month: number;
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
    most_played_song: {
        id: number;
        title: string;
        artist: string | null;
        album: string | null;
        play_count: number;
    } | null;
    most_favorite_artist: {
        id: number | null;
        name: string;
        favorites_count: number;
        songs_count: number;
    } | null;
    latest_users: Array<{
        id: number;
        name: string;
        email: string;
        role: string;
        created_at: string;
    }>;
    latest_artists: Array<{
        id: number;
        name: string;
        slug: string;
        is_active: boolean;
        created_at: string;
    }>;
    latest_songs: Array<{
        id: number;
        title: string;
        artist: string | null;
        album: string | null;
        play_count: number;
        created_at: string;
    }>;
    latest_albums: Array<{
        id: number;
        title: string;
        artist: string | null;
        release_status: string;
        created_at: string;
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

type ChartSeries = {
    label: string;
    values: number[];
    color: string;
};

const chartWidth = 720;
const chartHeight = 240;
const chartPadding = 28;

function buildLinePath(values: number[], width: number, height: number, padding: number): string {
    if (values.length === 0) {
        return '';
    }

    const maxValue = Math.max(1, ...values);
    const stepX = values.length > 1 ? (width - padding * 2) / (values.length - 1) : 0;

    return values
        .map((value, index) => {
            const x = padding + index * stepX;
            const y = height - padding - ((value / maxValue) * (height - padding * 2));
            return `${index === 0 ? 'M' : 'L'} ${x} ${y}`;
        })
        .join(' ');
}

function buildAreaPath(values: number[], width: number, height: number, padding: number): string {
    if (values.length === 0) {
        return '';
    }

    const maxValue = Math.max(1, ...values);
    const stepX = values.length > 1 ? (width - padding * 2) / (values.length - 1) : 0;
    const points = values.map((value, index) => {
        const x = padding + index * stepX;
        const y = height - padding - ((value / maxValue) * (height - padding * 2));
        return { x, y };
    });

    const lastPoint = points[points.length - 1];
    const firstPoint = points[0];

    return `M ${firstPoint.x} ${height - padding} ${points.map((point) => `L ${point.x} ${point.y}`).join(' ')} L ${lastPoint.x} ${height - padding} Z`;
}

function ChartCard({ title, subtitle, children }: { title: string; subtitle: string; children: React.ReactNode }) {
    return (
        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">{title}</div>
            <div className="mt-2 text-sm text-slate-400">{subtitle}</div>
            <div className="mt-5">{children}</div>
        </div>
    );
}

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
        title: 'Moderation Queue',
        description: 'Review reports, take enforcement actions, and handle copyright or abuse issues.',
        href: route('admin.moderation.index'),
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
        title: 'Tag Management',
        description: 'Create and maintain tags that power search, recommendations, and moderation workflows.',
        href: route('admin.tags.index'),
        tone: 'from-teal-500/20 to-cyan-500/10',
        bullet: 'Search and discovery labels',
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
    { label: 'Moderation Queue', href: route('admin.moderation.index') },
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
        { label: 'Categories', value: metrics.totals.total_categories.toLocaleString(), tone: 'from-violet-400 to-indigo-500', note: 'Genre taxonomy' },
        { label: 'Tags', value: metrics.totals.total_tags.toLocaleString(), tone: 'from-teal-400 to-cyan-500', note: 'Search labels' },
        { label: 'Reports Open', value: metrics.moderation.reports_open.toLocaleString(), tone: 'from-rose-400 to-red-500', note: 'Needs moderation' },
        { label: 'Featured Items', value: metrics.moderation.featured_items.toLocaleString(), tone: 'from-violet-400 to-indigo-500', note: 'Promoted content' },
        { label: 'Total Streams', value: metrics.listening.total_streams.toLocaleString(), tone: 'from-sky-400 to-cyan-500', note: 'Playback volume' },
        { label: 'New Users This Month', value: metrics.listening.new_users_this_month.toLocaleString(), tone: 'from-emerald-400 to-lime-500', note: 'Signup growth' },
    ];

    const growthSeries: ChartSeries[] = [
        { label: 'Users', values: metrics.recent_growth.map((entry) => entry.users), color: '#22d3ee' },
        { label: 'Artists', values: metrics.recent_growth.map((entry) => entry.artists), color: '#d946ef' },
        { label: 'Songs', values: metrics.recent_growth.map((entry) => entry.songs), color: '#f59e0b' },
    ];
    const chartLabels = metrics.recent_growth.map((entry) => entry.label);
    const catalogMix = [
        { label: 'Users', value: metrics.totals.total_users, color: 'bg-cyan-400' },
        { label: 'Artists', value: metrics.totals.total_artists, color: 'bg-fuchsia-400' },
        { label: 'Songs', value: metrics.totals.total_songs, color: 'bg-amber-400' },
        { label: 'Albums', value: metrics.totals.total_albums, color: 'bg-emerald-400' },
    ];
    const catalogMax = Math.max(1, ...catalogMix.map((item) => item.value));

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
                                <div className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3">Categories • Tags • Reports • Featured • Analytics</div>
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
                    <div className="space-y-6">
                        <ChartCard title="Growth trend" subtitle="Daily signups and content uploads across the last seven days.">
                            <svg viewBox={`0 0 ${chartWidth} ${chartHeight}`} className="h-72 w-full overflow-visible">
                                <defs>
                                    <linearGradient id="growthAreaUsers" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stopColor="#22d3ee" stopOpacity="0.28" />
                                        <stop offset="100%" stopColor="#22d3ee" stopOpacity="0.02" />
                                    </linearGradient>
                                    <linearGradient id="growthAreaArtists" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stopColor="#d946ef" stopOpacity="0.25" />
                                        <stop offset="100%" stopColor="#d946ef" stopOpacity="0.02" />
                                    </linearGradient>
                                    <linearGradient id="growthAreaSongs" x1="0" x2="0" y1="0" y2="1">
                                        <stop offset="0%" stopColor="#f59e0b" stopOpacity="0.24" />
                                        <stop offset="100%" stopColor="#f59e0b" stopOpacity="0.02" />
                                    </linearGradient>
                                </defs>

                                <line x1={chartPadding} y1={chartHeight - chartPadding} x2={chartWidth - chartPadding} y2={chartHeight - chartPadding} stroke="rgba(148,163,184,0.22)" />
                                <line x1={chartPadding} y1={chartPadding} x2={chartPadding} y2={chartHeight - chartPadding} stroke="rgba(148,163,184,0.22)" />

                                {growthSeries.map((series, index) => (
                                    <g key={series.label}>
                                        <path
                                            d={buildAreaPath(series.values, chartWidth, chartHeight, chartPadding)}
                                            fill={index === 0 ? 'url(#growthAreaUsers)' : index === 1 ? 'url(#growthAreaArtists)' : 'url(#growthAreaSongs)'}
                                        />
                                        <path
                                            d={buildLinePath(series.values, chartWidth, chartHeight, chartPadding)}
                                            fill="none"
                                            stroke={series.color}
                                            strokeWidth="3"
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                        />
                                    </g>
                                ))}

                                {metrics.recent_growth.map((entry, index) => {
                                    const x = chartPadding + (index * ((chartWidth - chartPadding * 2) / Math.max(1, metrics.recent_growth.length - 1)));
                                    const maxUsers = Math.max(1, ...metrics.recent_growth.map((item) => item.users));
                                    const maxArtists = Math.max(1, ...metrics.recent_growth.map((item) => item.artists));
                                    const maxSongs = Math.max(1, ...metrics.recent_growth.map((item) => item.songs));

                                    return (
                                        <g key={entry.label}>
                                            <circle cx={x} cy={chartHeight - chartPadding - ((entry.users / maxUsers) * (chartHeight - chartPadding * 2))} r="4" fill="#22d3ee" />
                                            <circle cx={x} cy={chartHeight - chartPadding - ((entry.artists / maxArtists) * (chartHeight - chartPadding * 2))} r="4" fill="#d946ef" />
                                            <circle cx={x} cy={chartHeight - chartPadding - ((entry.songs / maxSongs) * (chartHeight - chartPadding * 2))} r="4" fill="#f59e0b" />
                                            <text x={x} y={chartHeight - 8} textAnchor="middle" className="fill-slate-400 text-[11px]">{entry.label}</text>
                                        </g>
                                    );
                                })}
                            </svg>

                            <div className="mt-4 flex flex-wrap gap-3 text-xs text-slate-300">
                                {growthSeries.map((series) => (
                                    <div key={series.label} className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1.5">
                                        <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: series.color }} />
                                        <span>{series.label}</span>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 flex flex-wrap gap-2 text-xs text-slate-400">
                                {chartLabels.map((label) => (
                                    <span key={label} className="rounded-full border border-white/10 px-3 py-1">{label}</span>
                                ))}
                            </div>
                        </ChartCard>

                        <ChartCard title="Catalog mix" subtitle="Current platform volume by core object type.">
                            <div className="space-y-4">
                                {catalogMix.map((item) => (
                                    <div key={item.label} className="space-y-2">
                                        <div className="flex items-center justify-between text-sm text-slate-300">
                                            <span>{item.label}</span>
                                            <span>{item.value.toLocaleString()}</span>
                                        </div>
                                        <div className="h-3 overflow-hidden rounded-full bg-white/5">
                                            <div className={`h-full rounded-full ${item.color}`} style={{ width: `${Math.max(4, (item.value / catalogMax) * 100)}%` }} />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </ChartCard>

                        <div className="grid gap-4 sm:grid-cols-2">
                            <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                                <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Playback totals</div>
                                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Listen events</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.listening.listen_events.toLocaleString()}</div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Completed listens</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.listening.completed_listens.toLocaleString()}</div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Today</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.listening.today_listens.toLocaleString()}</div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Minutes listened</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.listening.minutes_listened.toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>

                            <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                                <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Moderation totals</div>
                                <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Open reports</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.moderation.reports_open.toLocaleString()}</div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Featured items</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.moderation.featured_items.toLocaleString()}</div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">Total favorites</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.moderation.favorites_total.toLocaleString()}</div>
                                    </div>
                                    <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="text-sm text-slate-400">New users this month</div>
                                        <div className="mt-1 text-2xl font-black text-white">{metrics.listening.new_users_this_month.toLocaleString()}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Quick headline metrics</div>
                            <div className="mt-4 grid gap-4 sm:grid-cols-2">
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div className="text-sm text-slate-400">Most played song</div>
                                    <div className="mt-1 text-lg font-bold text-white">{metrics.most_played_song?.title || 'No song yet'}</div>
                                    <div className="text-sm text-slate-300">{metrics.most_played_song?.artist || 'Unknown artist'}</div>
                                </div>
                                <div className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div className="text-sm text-slate-400">Most favorite artist</div>
                                    <div className="mt-1 text-lg font-bold text-white">{metrics.most_favorite_artist?.name || 'No artist yet'}</div>
                                    <div className="text-sm text-slate-300">{metrics.most_favorite_artist ? `${metrics.most_favorite_artist.favorites_count} favorites` : 'Waiting for data'}</div>
                                </div>
                            </div>
                        </div>

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

                <section className="grid gap-6 xl:grid-cols-2">
                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Recent uploads</div>
                        <div className="mt-5 grid gap-4 md:grid-cols-2">
                            <div className="space-y-3">
                                <div className="text-sm font-semibold text-white">Songs</div>
                                {metrics.latest_songs.map((song) => (
                                    <div key={song.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="font-medium text-white">{song.title}</div>
                                        <div className="text-sm text-slate-400">{song.artist || 'Unknown artist'}{song.album ? ` · ${song.album}` : ''}</div>
                                    </div>
                                ))}
                            </div>
                            <div className="space-y-3">
                                <div className="text-sm font-semibold text-white">Albums</div>
                                {metrics.latest_albums.map((album) => (
                                    <div key={album.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="font-medium text-white">{album.title}</div>
                                        <div className="text-sm text-slate-400">{album.artist || 'Unknown artist'} · {album.release_status}</div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Latest signups</div>
                        <div className="mt-5 grid gap-4 md:grid-cols-2">
                            <div className="space-y-3">
                                <div className="text-sm font-semibold text-white">Users</div>
                                {metrics.latest_users.map((user) => (
                                    <div key={user.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="font-medium text-white">{user.name}</div>
                                        <div className="text-sm text-slate-400">{user.email} · {user.role}</div>
                                    </div>
                                ))}
                            </div>
                            <div className="space-y-3">
                                <div className="text-sm font-semibold text-white">Artists</div>
                                {metrics.latest_artists.map((artist) => (
                                    <div key={artist.id} className="rounded-2xl border border-white/10 bg-white/5 p-4">
                                        <div className="font-medium text-white">{artist.name}</div>
                                        <div className="text-sm text-slate-400">{artist.slug} · {artist.is_active ? 'Active' : 'Inactive'}</div>
                                    </div>
                                ))}
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
