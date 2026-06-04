import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useState } from 'react';

interface Props extends PageProps {
    tags: any[];
}

export default function Index({ tags }: Props) {
    const [name, setName] = useState('');
    const [editingId, setEditingId] = useState<number | null>(null);
    const [editingName, setEditingName] = useState('');

    const createTag = (event: React.FormEvent) => {
        event.preventDefault();
        router.post(route('admin.tags.store'), { name }, {
            onSuccess: () => setName('')
        });
    };

    const saveTag = (id: number) => {
        router.put(route('admin.tags.update', id), { name: editingName }, {
            onSuccess: () => {
                setEditingId(null);
                setEditingName('');
            }
        });
    };

    const deleteTag = (id: number) => {
        if (confirm('Delete tag? This will remove the label from all songs.')) {
            router.delete(route('admin.tags.destroy', id));
        }
    };

    return (
        <AuthenticatedLayout header="Tags">
            <Head title="Tags" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Mood & Activity labels</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Tag Management</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Create tags like Workout, Chill, Party, Sad, Romantic, and Focus. These power moods and song discovery.
                    </p>
                </div>

                <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm h-fit">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Create label</div>
                        <form onSubmit={createTag} className="mt-5 space-y-4">
                            <input
                                value={name}
                                onChange={(e) => setName(e.target.value)}
                                className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20"
                                placeholder="Label name (e.g. Chill)"
                                required
                            />
                            <button className="w-full rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">
                                Add Tag
                            </button>
                        </form>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Existing tags</div>
                        <div className="mt-5 space-y-3">
                            {tags.length === 0 ? (
                                <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-400">No tags found</div>
                            ) : (
                                tags.map((tag: any) => (
                                    <div key={tag.id} className="rounded-2xl border border-white/10 bg-slate-950/40 px-4 py-3 transition hover:bg-white/5">
                                        {editingId === tag.id ? (
                                            <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                                                <input
                                                    autoFocus
                                                    value={editingName}
                                                    onChange={(e) => setEditingName(e.target.value)}
                                                    className="flex-1 rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-sm text-white focus:border-cyan-400/30 focus:outline-none"
                                                />
                                                <div className="flex gap-2">
                                                    <button type="button" onClick={() => saveTag(tag.id)} className="rounded-xl bg-cyan-400/10 px-3 py-2 text-xs font-bold text-cyan-200 hover:bg-cyan-400/20">Save</button>
                                                    <button type="button" onClick={() => { setEditingId(null); setEditingName(''); }} className="rounded-xl bg-white/5 px-3 py-2 text-xs font-bold text-slate-400 hover:bg-white/10">Cancel</button>
                                                </div>
                                            </div>
                                        ) : (
                                            <div className="flex items-center justify-between gap-4">
                                                <span className="font-semibold text-white">{tag.name}</span>
                                                <div className="flex items-center gap-4">
                                                    <button type="button" onClick={() => { setEditingId(tag.id); setEditingName(tag.name); }} className="text-xs font-bold uppercase tracking-widest text-cyan-300/70 hover:text-cyan-200">Edit</button>
                                                    <button type="button" onClick={() => deleteTag(tag.id)} className="text-xs font-bold uppercase tracking-widest text-rose-400/70 hover:text-rose-300">Delete</button>
                                                </div>
                                            </div>
                                        )}
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
