import ApplicationLogo from '@/Components/ApplicationLogo';
import SidebarNavLink from '@/Components/SidebarNavLink';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useState } from 'react';

type NavLink = {
    href: string;
    pattern: string;
    label: string;
    icon: string;
};

export default function Authenticated({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const user = usePage().props.auth.user as { name: string; email: string };
    const [sidebarOpen, setSidebarOpen] = useState(false);

    const navGroups: Array<{ label: string; links: NavLink[] }> = [
        {
            label: 'Overview',
            links: [
                {
                    href: route('dashboard'),
                    pattern: 'dashboard',
                    label: 'Dashboard',
                    icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z',
                },
            ],
        },
        {
            label: 'Platform',
            links: [
                {
                    href: route('admin.artists.index'),
                    pattern: 'admin.artists.*',
                    label: 'Artists',
                    icon: 'M17 20h5v-2a4 4 0 00-5-3.87M9 20H4v-2a4 4 0 015-3.87m8-4a4 4 0 10-8 0 4 4 0 008 0z',
                },
                {
                    href: route('admin.users.index'),
                    pattern: 'admin.users.*',
                    label: 'Users',
                    icon: 'M17 20h5v-2a4 4 0 00-5-3.87m-4-5a4 4 0 11-8 0 4 4 0 018 0zm-8 8H4v-2a4 4 0 015-3.87',
                },
                {
                    href: route('admin.albums.index'),
                    pattern: 'admin.albums.*',
                    label: 'Albums',
                    icon: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z',
                },
                {
                    href: route('admin.songs.index'),
                    pattern: 'admin.songs.*',
                    label: 'Songs',
                    icon: 'M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z',
                },
                {
                    href: route('admin.products.index'),
                    pattern: 'admin.products.*',
                    label: 'Products',
                    icon: 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
                },
            ],
        },
        {
            label: 'Operations',
            links: [
                {
                    href: route('admin.reports.index'),
                    pattern: 'admin.reports.*',
                    label: 'Reports',
                    icon: 'M9 12h6m-6 4h6M4 6h16v12H4z',
                },
                {
                    href: route('admin.genres.index'),
                    pattern: 'admin.genres.*',
                    label: 'Genres',
                    icon: 'M12 2l3 7h7l-5.5 4.1L19 21l-7-4.3L5 21l2.5-7.9L2 9h7z',
                },
                {
                    href: route('admin.featured.index'),
                    pattern: 'admin.featured.*',
                    label: 'Featured',
                    icon: 'M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z',
                },
                {
                    href: route('admin.analytics.index'),
                    pattern: 'admin.analytics.*',
                    label: 'Analytics',
                    icon: 'M4 19V5m4 14v-8m4 8V9m4 10V3m4 16v-5',
                },
            ],
        },
    ];

    return (
        <div className="min-h-screen overflow-hidden bg-[#07111f] text-slate-100">
            <div className="pointer-events-none fixed inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(34,211,238,0.14),_transparent_28%),radial-gradient(circle_at_top_right,_rgba(236,72,153,0.12),_transparent_24%),linear-gradient(180deg,_rgba(10,17,32,0.92),_rgba(7,17,31,1))]" />
            <div className="pointer-events-none fixed left-1/2 top-0 h-72 w-[42rem] -translate-x-1/2 rounded-full bg-cyan-400/10 blur-3xl" />

            {sidebarOpen && (
                <div
                    className="fixed inset-0 z-40 bg-slate-950/70 backdrop-blur-sm lg:hidden"
                    onClick={() => setSidebarOpen(false)}
                />
            )}

            <aside
                className={`fixed inset-y-0 left-0 z-50 w-72 transform border-r border-white/10 bg-slate-950/80 shadow-2xl shadow-cyan-950/20 backdrop-blur-xl transition-transform duration-300 ease-in-out lg:translate-x-0 ${
                    sidebarOpen ? 'translate-x-0' : '-translate-x-full'
                }`}
            >
                <div className="flex h-full flex-col">
                    <div className="flex h-20 items-center justify-between border-b border-white/10 px-5">
                        <Link href="/" className="flex items-center gap-3">
                            <span className="flex h-11 w-11 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 via-sky-500 to-fuchsia-500 shadow-lg shadow-cyan-500/20">
                                <ApplicationLogo className="block h-6 w-6 fill-current text-white" />
                            </span>
                            <span>
                                <span className="block text-[0.65rem] font-semibold uppercase tracking-[0.35em] text-cyan-200/70">
                                    Echo Panda
                                </span>
                                <span className="block text-lg font-bold text-white">
                                    Admin Control
                                </span>
                            </span>
                        </Link>
                        <button
                            onClick={() => setSidebarOpen(false)}
                            className="rounded-full border border-white/10 p-2 text-slate-300 hover:bg-white/5 lg:hidden"
                        >
                            <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <nav className="flex-1 space-y-6 overflow-y-auto px-4 py-5">
                        {navGroups.map((group) => (
                            <div key={group.label}>
                                <div className="mb-3 px-3 text-[0.7rem] font-semibold uppercase tracking-[0.35em] text-slate-500">
                                    {group.label}
                                </div>
                                <div className="space-y-1">
                                    {group.links.map((link) => (
                                        <SidebarNavLink
                                            key={link.label}
                                            href={link.href}
                                            active={route().current(link.pattern)}
                                        >
                                            <svg className="me-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d={link.icon} />
                                            </svg>
                                            <span className="truncate">{link.label}</span>
                                        </SidebarNavLink>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </nav>

                    <div className="border-t border-white/10 p-5">
                        <div className="mb-4 rounded-3xl border border-white/10 bg-white/5 p-4">
                            <div className="mb-2 flex items-center gap-3">
                                <div className="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-cyan-400 to-fuchsia-500 text-sm font-bold text-white shadow-lg shadow-cyan-500/20">
                                    {user.name?.slice(0, 1).toUpperCase()}
                                </div>
                                <div>
                                    <div className="text-sm font-semibold text-white">{user.name}</div>
                                    <div className="text-xs text-slate-400">{user.email}</div>
                                </div>
                            </div>
                            <div className="flex items-center justify-between text-xs text-slate-400">
                                <span>Command access</span>
                                <span className="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-2 py-1 text-cyan-200">
                                    Online
                                </span>
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Link
                                href={route('profile.edit')}
                                className="block rounded-2xl border border-white/10 bg-white/5 px-4 py-2.5 text-sm font-medium text-slate-200 transition hover:border-cyan-400/25 hover:bg-white/10"
                            >
                                Profile Settings
                            </Link>
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="block w-full rounded-2xl border border-rose-400/20 bg-rose-500/10 px-4 py-2.5 text-left text-sm font-medium text-rose-200 transition hover:bg-rose-500/20"
                            >
                                Log Out
                            </Link>
                        </div>
                    </div>
                </div>
            </aside>

            <div className="relative lg:pl-72">
                <div className="sticky top-0 z-30 flex h-20 items-center justify-between border-b border-white/10 bg-slate-950/70 px-4 shadow-[0_10px_40px_rgba(2,6,23,0.35)] backdrop-blur-xl lg:px-8">
                    <button
                        onClick={() => setSidebarOpen(true)}
                        className="rounded-2xl border border-white/10 p-2 text-slate-300 hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-cyan-400 lg:hidden"
                    >
                        <svg className="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>

                    {header && (
                        <div>
                            <div className="text-[0.7rem] font-semibold uppercase tracking-[0.35em] text-cyan-300/70">
                                Echo Panda Admin
                            </div>
                            <h1 className="text-lg font-semibold text-white lg:text-xl">
                                {header}
                            </h1>
                        </div>
                    )}

                    <div className="hidden rounded-full border border-white/10 bg-white/5 px-3 py-1.5 text-xs text-slate-300 lg:flex">
                        {user.email}
                    </div>
                </div>

                <main className="relative p-4 lg:p-8">
                    {children}
                </main>
            </div>
        </div>
    );
}
