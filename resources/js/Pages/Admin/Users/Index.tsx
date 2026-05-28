import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import PrimaryButton from '@/Components/PrimaryButton';
import { PageProps } from '@/types';

interface Props extends PageProps {
    users: { data: any[] };
}

export default function Index({ users }: Props) {
    const handleBan = (id: number) => {
        if (confirm('Ban/unban this user?')) {
            const current = users.data.find((user) => user.id === id);
            router.put(route('admin.users.update', id), { is_banned: !current?.is_banned });
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
                                View all users, inspect behavior, and apply ban or unban actions when moderation requires it.
                            </p>
                        </div>
                        <Link href={route('admin.users.create')}>
                            <PrimaryButton>Create User</PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 px-6 py-4 text-sm text-slate-300">
                        {users.data.length} users monitored
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Email</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Role</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Status</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {users.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-10 text-center text-sm text-slate-400">No users</td>
                                    </tr>
                                ) : (
                                    users.data.map((u) => (
                                        <tr key={u.id} className="transition hover:bg-white/5">
                                            <td className="px-6 py-4 text-sm font-semibold text-white">{u.name}</td>
                                            <td className="px-6 py-4 text-sm text-slate-300">{u.email}</td>
                                            <td className="px-6 py-4 text-sm text-slate-300 capitalize">{u.role}</td>
                                            <td className="px-6 py-4 text-sm text-slate-300">
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${u.is_banned ? 'bg-rose-500/15 text-rose-200 ring-1 ring-rose-400/20' : 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20'}`}>
                                                    {u.is_banned ? 'Banned' : 'Active'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm font-medium">
                                                <Link href={route('admin.users.show', u.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                                <button onClick={() => handleBan(u.id)} className="text-rose-300 hover:text-rose-200">Ban / Unban</button>
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
