import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    artist: {
        id: number;
        name: string;
        slug?: string | null;
        bio?: string | null;
        is_active?: boolean;
        verification_status?: string | null;
        verification_reason?: string | null;
        user?: {
            id: number;
            name: string;
            email: string;
            role: string;
        } | null;
    };
}

export default function Edit({ artist }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        name: artist.name ?? '',
        slug: artist.slug ?? '',
        bio: artist.bio ?? '',
        is_active: Boolean(artist.is_active ?? true),
        verification_status: artist.verification_status ?? 'pending',
        verification_reason: artist.verification_reason ?? '',
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        put(route('admin.artists.update', artist.id));
    };

    return (
        <AuthenticatedLayout header={`Edit Artist: ${artist.name}`}>
            <Head title={`Edit Artist ${artist.name}`} />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Artist maintenance</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Edit Artist</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Update the artist profile, moderation state, and verification metadata.
                    </p>
                </div>

                <form onSubmit={submit} className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="grid gap-4 md:grid-cols-2">
                        <label className="space-y-2 md:col-span-2">
                            <span className="text-sm text-slate-300">Name</span>
                            <input
                                value={data.name}
                                onChange={(event) => setData('name', event.target.value)}
                                className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                            />
                            {errors.name && <div className="text-sm text-rose-300">{errors.name}</div>}
                        </label>

                        <label className="space-y-2">
                            <span className="text-sm text-slate-300">Slug</span>
                            <input
                                value={data.slug}
                                onChange={(event) => setData('slug', event.target.value)}
                                className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
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
                            />
                            {errors.verification_reason && <div className="text-sm text-rose-300">{errors.verification_reason}</div>}
                        </label>

                        <div className="rounded-2xl border border-white/10 bg-white/5 p-4 md:col-span-2">
                            <div className="text-sm font-semibold text-white">Linked account</div>
                            <div className="mt-2 text-sm text-slate-300">
                                {artist.user ? (
                                    <>
                                        <div>{artist.user.name} ({artist.user.email})</div>
                                        <div className="text-xs text-slate-400">Role: {artist.user.role}</div>
                                    </>
                                ) : (
                                    <div>No linked user account</div>
                                )}
                            </div>
                        </div>

                        <label className="flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-300 md:col-span-2">
                            <input
                                type="checkbox"
                                checked={data.is_active}
                                onChange={(event) => setData('is_active', event.target.checked)}
                                className="h-4 w-4 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                            />
                            Artist is active
                        </label>
                    </div>

                    <div className="mt-6 flex flex-wrap gap-3">
                        <PrimaryButton disabled={processing}>Save Artist</PrimaryButton>
                        <Link href={route('admin.artists.show', artist.id)} className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-semibold text-slate-200 hover:bg-white/10">
                            Cancel
                        </Link>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
