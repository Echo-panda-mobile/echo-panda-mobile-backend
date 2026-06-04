import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState, useEffect } from 'react';
import debounce from 'lodash/debounce';

interface Props extends PageProps {
    users: { data: any[], links: any[], meta: any };
    filters: { search?: string };
}

export default function Index({ users, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');

    const debouncedSearch = debounce((value: string) => {
        router.get(route('admin.users.index'), { search: value }, {
            preserveState: true,
            replace: true
        });
    }, 500);

    const handleSearchChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const value = e.target.value;
        setSearch(value);
        debouncedSearch(value);
    };

    const handleBan = (user: any) => {
        const action = user.is_banned ? 'unban' : 'ban';
        if (confirm(`Are you sure you want to ${action} ${user.name}?`)) {
            router.post(route(`admin.users.${action}`, user.id));
        }
    };

    return (
        <AuthenticatedLayout header="Users">
            <Head title="Users" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">
                                Listener governance
                            </div>
                            <h2 className="mt-2 text-3xl font-black text-white">User Management</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                View all users, search for specific accounts, and manage moderation status.
                            </p>
                        </div>
                        <div className="w-full lg:w-72">
                            <label className="text-xs font-bold uppercase tracking-widest text-slate-500 mb-2 block ml-1">Search Users</label>
                            <input
                                type="text"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Name or email..."
                                className="w-full rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                            />
                        </div>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 px-6 py-4 flex items-center justify-between">
                        <span className="text-sm text-slate-300">{users.data.length} users in view</span>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Identity</th>
                                    <th className="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Contact</th>
                                    <th className="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Role</th>
                                    <th className="px-6 py-4 text-left text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Status</th>
                                    <th className="px-6 py-4 text-right text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">Management</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {users.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-20 text-center text-sm text-slate-500 italic">No users found matching your search.</td>
                                    </tr>
                                ) : (
                                    users.data.map((u) => (
                                        <tr key={u.id} className="group transition-colors hover:bg-white/[0.02]">
                                            <td className="px-6 py-4">
                                                <div className="text-sm font-bold text-white group-hover:text-cyan-300 transition-colors">{u.name}</div>
                                                <div className="text-[10px] text-slate-500 font-mono mt-0.5 uppercase tracking-tighter">ID: {u.id}</div>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-300 font-medium">{u.email}</td>
                                            <td className="px-6 py-4">
                                                <span className="px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-[10px] font-black uppercase tracking-widest text-slate-400">
                                                    {u.role}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-sm text-slate-300">
                                                <span className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[10px] font-black uppercase tracking-widest ${u.is_banned ? 'bg-rose-500/10 text-rose-400 border border-rose-400/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-400/20'}`}>
                                                    <span className={`w-1.5 h-1.5 rounded-full ${u.is_banned ? 'bg-rose-400' : 'bg-cyan-400'}`}></span>
                                                    {u.is_banned ? 'Banned' : 'Active'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-right">
                                                <div className="flex items-center justify-end gap-2">
                                                    <Link
                                                        href={route('admin.users.show', u.id)}
                                                        className="h-9 px-4 rounded-xl border border-white/10 bg-white/5 flex items-center justify-center text-[10px] font-black uppercase tracking-widest text-white hover:bg-white/10 transition-colors"
                                                    >
                                                        Profile
                                                    </Link>
                                                    <button
                                                        onClick={() => handleBan(u)}
                                                        className={`h-9 px-4 rounded-xl border flex items-center justify-center text-[10px] font-black uppercase tracking-widest transition-all ${u.is_banned ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20'}`}
                                                    >
                                                        {u.is_banned ? 'Unban' : 'Ban'}
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
