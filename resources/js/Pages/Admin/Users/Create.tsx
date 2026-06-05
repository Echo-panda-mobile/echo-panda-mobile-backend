import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Create(_: PageProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        role: 'user',
        is_banned: false,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('admin.users.store'));
    };

    return (
        <AuthenticatedLayout header="Create User">
            <Head title="Create User" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Account creation</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Create User</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Create a real user account for listeners, artists, publishers, or admins.
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <form onSubmit={submit} className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Name</span>
                                <input
                                    value={data.name}
                                    onChange={(event) => setData('name', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Full name"
                                />
                                {errors.name && <div className="text-sm text-rose-300">{errors.name}</div>}
                            </label>

                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Email</span>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(event) => setData('email', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="name@example.com"
                                />
                                {errors.email && <div className="text-sm text-rose-300">{errors.email}</div>}
                            </label>

                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Password</span>
                                <input
                                    type="password"
                                    value={data.password}
                                    onChange={(event) => setData('password', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Minimum 8 characters"
                                />
                                {errors.password && <div className="text-sm text-rose-300">{errors.password}</div>}
                            </label>

                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Role</span>
                                <select
                                    value={data.role}
                                    onChange={(event) => setData('role', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                >
                                    <option value="user">User</option>
                                    <option value="artist">Artist</option>
                                    <option value="publicer">Publicer</option>
                                    <option value="admin">Admin</option>
                                </select>
                                {errors.role && <div className="text-sm text-rose-300">{errors.role}</div>}
                            </label>

                            <label className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-300 md:col-span-2">
                                <input
                                    type="checkbox"
                                    checked={data.is_banned}
                                    onChange={(event) => setData('is_banned', event.target.checked)}
                                    className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                />
                                Start user as banned
                            </label>
                        </div>

                        <div className="mt-6 flex flex-wrap gap-3">
                            <Link href={route('admin.users.index')} className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">
                                Back to User List
                            </Link>
                        </div>
                    </form>

                    <aside className="space-y-4">
                        <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Account notes</div>
                            <div className="mt-4 space-y-3 text-sm text-slate-300">
                                <p>The user is created immediately and can sign in with the password you set.</p>
                                <p>Role controls access across the admin and artist surfaces.</p>
                                <p>You can still ban or edit the account later from the users index.</p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
