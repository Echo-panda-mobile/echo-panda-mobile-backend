import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    artists: { data: any[] };
}

export default function Index({ artists }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('Delete this artist?')) {
            router.delete(route('admin.artists.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout header="Artists">
            <Head title="Artists" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">
                                Artist lifecycle
                            </div>
                            <h2 className="mt-2 text-3xl font-black text-white">Artist Management</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Create accounts, review verification, activate or suspend artists, and inspect upload activity from one place.
                            </p>
                        </div>
                        <Link href={route('admin.artists.create')}>
                            <PrimaryButton>Create Artist</PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 px-6 py-4 text-sm text-slate-300">
                        {artists.data.length} artists in the system
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Verified</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {artists.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={3} className="px-6 py-10 text-center text-sm text-slate-400">No artists</td>
                                    </tr>
                                ) : (
                                    artists.data.map((a) => (
                                        <tr key={a.id} className="bg-transparent transition hover:bg-white/5">
                                            <td className="px-6 py-4 text-sm font-semibold text-white">{a.name}</td>
                                            <td className="px-6 py-4 text-sm text-slate-300">
                                                <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${a.verified ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20' : 'bg-amber-400/15 text-amber-200 ring-1 ring-amber-400/20'}`}>
                                                    {a.verified ? 'Verified' : 'Pending'}
                                                </span>
                                            </td>
                                            <td className="px-6 py-4 text-right text-sm font-medium">
                                                <Link href={route('admin.artists.show', a.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                                <Link href={route('admin.artists.edit', a.id)} className="mr-3 text-fuchsia-200 hover:text-fuchsia-100">Edit</Link>
                                                <button onClick={() => handleDelete(a.id)} className="text-rose-300 hover:text-rose-200">Delete</button>
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
