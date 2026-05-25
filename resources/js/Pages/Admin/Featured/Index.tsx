import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';

interface Props extends PageProps {
    items: { data: any[] };
}

export default function Index({ items }: Props) {
    const [type, setType] = useState('song');
    const [itemId, setItemId] = useState('');

    const handleAdd = (e: any) => {
        e.preventDefault();
        router.post(route('admin.featured.store'), { item_type: type, item_id: itemId });
    };

    return (
        <AuthenticatedLayout header="Featured">
            <Head title="Featured" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Promotion system</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Featured Content</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Promote songs, albums, and artists across Echo Panda discovery surfaces.
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Add featured item</div>
                        <form onSubmit={handleAdd} className="mt-5 space-y-4">
                            <select value={type} onChange={(e) => setType(e.target.value)} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20">
                                <option value="song">Song</option>
                                <option value="album">Album</option>
                                <option value="artist">Artist</option>
                            </select>
                            <input value={itemId} onChange={(e) => setItemId(e.target.value)} placeholder="Item ID" className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" />
                            <button className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Feature</button>
                        </form>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Active featured items</div>
                        <div className="mt-5 space-y-3">
                            {items.data.length === 0 ? (
                                <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-400">No featured items</div>
                            ) : (
                                items.data.map((it) => (
                                    <div key={it.id} className="flex items-center justify-between rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3">
                                        <span className="font-medium text-white">{it.item_type} #{it.item_id}</span>
                                        <form method="post" action={route('admin.featured.destroy', it.id)} onSubmit={(e) => { if (!confirm('Remove item?')) e.preventDefault(); }}>
                                            <input type="hidden" name="_method" value="DELETE" />
                                            <button className="text-rose-300 hover:text-rose-200">Remove</button>
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
