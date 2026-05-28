import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useMemo, useState } from 'react';

interface Props extends PageProps {
    user: any;
}

export default function Show({ user }: Props) {
    const [name, setName] = useState(user.name || '');
    const [email, setEmail] = useState(user.email || '');
    const [role, setRole] = useState(user.role || 'user');
    const [isBanned, setIsBanned] = useState(Boolean(user.is_banned));

    const profileStats = useMemo(() => [
        { label: 'Favorites', value: user.favorites_count ?? 0 },
        { label: 'Listen history', value: user.listen_history_count ?? 0 },
        { label: 'Following', value: user.following_count ?? 0 },
        { label: 'Followers', value: user.followers_count ?? 0 },
    ], [user]);

    const saveUser = () => {
        router.put(route('admin.users.update', user.id), {
            name,
            email,
            role,
            is_banned: isBanned,
        });
    };

    const toggleBan = () => {
        setIsBanned((current) => !current);
        router.put(route('admin.users.update', user.id), {
            name,
            email,
            role,
            is_banned: !isBanned,
        });
    };

    return (
        <AuthenticatedLayout header={`User: ${user.name}`}>
            <Head title={`User ${user.name}`} />

            <div className="grid gap-6 lg:grid-cols-[0.92fr_1.08fr]">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">
                        User profile
                    </div>
                    <h2 className="mt-2 text-3xl font-black text-white">{user.name}</h2>
                    <p className="mt-3 text-sm leading-6 text-slate-300">Listener account and moderation profile.</p>
                    <div className="mt-6 grid gap-4 sm:grid-cols-2">
                        <div className="rounded-3xl border border-white/10 bg-white/5 p-4">
                            <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Email</div>
                            <div className="mt-2 text-sm font-semibold text-white">{user.email}</div>
                        </div>
                        <div className="rounded-3xl border border-white/10 bg-white/5 p-4">
                            <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Role</div>
                            <div className="mt-2 text-sm font-semibold text-white capitalize">{user.role}</div>
                        </div>
                    </div>
                    <div className="mt-4 rounded-3xl border border-white/10 bg-white/5 p-4">
                        <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Account state</div>
                        <div className="mt-2 inline-flex rounded-full border border-white/10 px-3 py-1 text-sm font-semibold text-white">
                            {user.is_banned ? 'Banned' : 'Active'}
                        </div>
                    </div>
                </section>

                <section className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">
                        Admin controls
                    </div>
                    <div className="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        {profileStats.map((stat) => (
                            <div key={stat.label} className="rounded-2xl border border-white/10 bg-slate-950/40 p-4">
                                <div className="text-xs uppercase tracking-[0.35em] text-slate-500">{stat.label}</div>
                                <div className="mt-2 text-2xl font-black text-white">{stat.value}</div>
                            </div>
                        ))}
                    </div>

                    <div className="mt-6 space-y-4">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Display name</span>
                                <input value={name} onChange={(event) => setName(event.target.value)} className="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/30 focus:ring-2 focus:ring-cyan-400/20" />
                            </label>
                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Email</span>
                                <input value={email} onChange={(event) => setEmail(event.target.value)} className="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/30 focus:ring-2 focus:ring-cyan-400/20" />
                            </label>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-[1fr_auto]">
                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Role</span>
                                <select value={role} onChange={(event) => setRole(event.target.value)} className="w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/30 focus:ring-2 focus:ring-cyan-400/20">
                                    <option value="user">User</option>
                                    <option value="artist">Artist</option>
                                    <option value="publicer">Publicer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </label>

                            <div className="flex items-end gap-3">
                                <button type="button" onClick={toggleBan} className={`rounded-2xl px-4 py-3 text-sm font-semibold ${isBanned ? 'border border-emerald-400/20 bg-emerald-400/10 text-emerald-100' : 'border border-rose-400/20 bg-rose-500/10 text-rose-100'}`}>
                                    {isBanned ? 'Unban user' : 'Ban user'}
                                </button>
                                <button type="button" onClick={saveUser} className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100">
                                    Save changes
                                </button>
                            </div>
                        </div>

                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">
                            Use the role selector to promote or demote this account, and ban/unban toggles to suspend platform access.
                        </div>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
