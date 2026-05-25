import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    reports: { data: any[] };
}

export default function Index({ reports }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('Remove report?')) {
            router.delete(route('admin.reports.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout header="Reports">
            <Head title="Reports" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Moderation queue</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Reports</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Review song, album, and artist reports. Keep moderation actions aligned with the Echo Panda safety workflow.
                    </p>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Type</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Reason</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {reports.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={3} className="px-6 py-10 text-center text-sm text-slate-400">No reports</td>
                                    </tr>
                                ) : (
                                    reports.data.map((r) => (
                                        <tr key={r.id} className="transition hover:bg-white/5">
                                            <td className="px-6 py-4 text-sm font-semibold text-white">{r.reportable_type}</td>
                                            <td className="px-6 py-4 text-sm text-slate-300">{r.reason}</td>
                                            <td className="px-6 py-4 text-right text-sm font-medium">
                                                <Link href={route('admin.reports.show', r.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                                <button onClick={() => handleDelete(r.id)} className="text-rose-300 hover:text-rose-200">Remove</button>
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
