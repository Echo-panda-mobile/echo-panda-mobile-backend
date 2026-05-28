import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';

interface AdminUser {
    id: number;
    name: string;
    email: string;
    role: string;
}

interface Props extends PageProps {
    users: AdminUser[];
}

export default function Create({ users }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        user_id: '',
        email: '',
        user_role: 'artist',
        name: '',
        slug: '',
        bio: '',
        is_active: true,
        verification_status: 'pending',
        verification_reason: '',
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        post(route('admin.artists.store'));
    };

    return (
        <AuthenticatedLayout header="Create Artist">
            <Head title="Create Artist" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Artist lifecycle</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Create Artist</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Create a new artist profile, link it to an existing user account if needed, or enter an email to create the linked user on the spot.
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
                    <form onSubmit={submit} className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="grid gap-4 md:grid-cols-2">
                            <label className="space-y-2 md:col-span-2">
                                <span className="text-sm text-slate-300">Name</span>
                                <input
                                    value={data.name}
                                    onChange={(event) => setData('name', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Artist name"
                                />
                                {errors.name && <div className="text-sm text-rose-300">{errors.name}</div>}
                            </label>

                            <label className="space-y-2 md:col-span-2">
                                <span className="text-sm text-slate-300">User account</span>
                                <select
                                    value={data.user_id}
                                    onChange={(event) => setData('user_id', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                >
                                    <option value="">No linked user</option>
                                    {users.map((user) => (
                                        <option key={user.id} value={user.id}>
                                            {user.name} ({user.email}) - {user.role}
                                        </option>
                                    ))}
                                </select>
                                {errors.user_id && <div className="text-sm text-rose-300">{errors.user_id}</div>}
                            </label>

                            <label className="space-y-2 md:col-span-2">
                                <span className="text-sm text-slate-300">Gmail / email</span>
                                <input
                                    type="email"
                                    value={data.email}
                                    onChange={(event) => setData('email', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="artist@gmail.com"
                                />
                                <div className="text-xs text-slate-400">Leave blank if you selected an existing linked user.</div>
                                {errors.email && <div className="text-sm text-rose-300">{errors.email}</div>}
                            </label>

                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Linked account role</span>
                                <select
                                    value={data.user_role}
                                    onChange={(event) => setData('user_role', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                >
                                    <option value="artist">Artist</option>
                                    <option value="user">User</option>
                                    <option value="publicer">Publicer</option>
                                    <option value="admin">Admin</option>
                                </select>
                                <div className="text-xs text-slate-400">Role applied only when creating a new linked user via email.</div>
                                {errors.user_role && <div className="text-sm text-rose-300">{errors.user_role}</div>}
                            </label>

                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Slug</span>
                                <input
                                    value={data.slug}
                                    onChange={(event) => setData('slug', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Optional custom slug"
                                />
                                {errors.slug && <div className="text-sm text-rose-300">{errors.slug}</div>}
                            </label>

                            <label className="space-y-2">
                                <span className="text-sm text-slate-300">Verification status</span>
                                <select
                                    value={data.verification_status}
                                    onChange={(event) => setData('verification_status', event.target.value)}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                >
                                    <option value="pending">Pending</option>
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                {errors.verification_status && <div className="text-sm text-rose-300">{errors.verification_status}</div>}
                            </label>

                            <label className="space-y-2 md:col-span-2">
                                <span className="text-sm text-slate-300">Bio</span>
                                <textarea
                                    value={data.bio}
                                    onChange={(event) => setData('bio', event.target.value)}
                                    rows={5}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Optional biography"
                                />
                                {errors.bio && <div className="text-sm text-rose-300">{errors.bio}</div>}
                            </label>

                            <label className="space-y-2 md:col-span-2">
                                <span className="text-sm text-slate-300">Verification reason</span>
                                <textarea
                                    value={data.verification_reason}
                                    onChange={(event) => setData('verification_reason', event.target.value)}
                                    rows={4}
                                    className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                    placeholder="Optional moderation note"
                                />
                                {errors.verification_reason && <div className="text-sm text-rose-300">{errors.verification_reason}</div>}
                            </label>

                            <label className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-300 md:col-span-2">
                                <input
                                    type="checkbox"
                                    checked={data.is_active}
                                    onChange={(event) => setData('is_active', event.target.checked)}
                                    className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                />
                                Start artist as active
                            </label>
                        </div>

                        <div className="mt-6 flex flex-wrap gap-3">
                            <PrimaryButton disabled={processing}>Create Artist</PrimaryButton>
                            <Link href={route('admin.artists.index')} className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <aside className="space-y-4">
                        <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-5 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">What gets saved</div>
                            <div className="mt-4 space-y-3 text-sm text-slate-300">
                                <p>Name and slug create the public artist record.</p>
                                <p>Linked user ownership powers artist moderation and profile sync.</p>
                                <p>If you do not pick a user, the email field creates a new linked user account and sends a Firebase password-reset invite.</p>
                                <p>Verification settings control the initial moderation state.</p>
                            </div>
                        </div>
                    </aside>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
