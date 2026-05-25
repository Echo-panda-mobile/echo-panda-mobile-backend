import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';

interface Props extends PageProps {
    genres: any[];
}

export default function Index({ genres }: Props) {
    const [name, setName] = useState('');

    const handleCreate = (e: any) => {
        e.preventDefault();
        router.post(route('admin.genres.store'), { name });
    };

    return (
        <AuthenticatedLayout header="Genres">
            <Head title="Genres" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Taxonomy control</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Genres</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Keep category names consistent and aligned with the Echo Panda music catalog.
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Create genre</div>
                        <form onSubmit={handleCreate} className="mt-5 space-y-4">
                            <input value={name} onChange={(e) => setName(e.target.value)} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" placeholder="Genre name" />
                            <button className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Add genre</button>
                        </form>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Existing genres</div>
                        <div className="mt-5 space-y-3">
                            {genres.length === 0 ? (
                                <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-400">No genres</div>
                            ) : (
                                genres.map((g: any) => (
                                    <div key={g.id} className="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3">
                                        <span className="font-medium text-white">{g.name}</span>
                                        <form method="post" action={route('admin.genres.destroy', g.id)} onSubmit={(e) => { if (!confirm('Delete genre?')) e.preventDefault(); }}>
                                            <input type="hidden" name="_method" value="DELETE" />
                                            <button className="text-rose-300 hover:text-rose-200">Delete</button>
                                        </form>
                                    </div>
                                ))
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
