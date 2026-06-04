import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { Song } from '@/types/song';

interface Props {
    song: Song & { is_active?: boolean };
}

export default function Show({ song }: Props) {
    const formatDuration = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    const handleHide = () => {
        router.post(route('admin.songs.hide', song.id));
    };

    const handleApprove = () => {
        router.post(route('admin.songs.approve', song.id));
    };

    const handleDelete = () => {
        if (confirm('Permanently delete this song?')) {
            router.delete(route('admin.songs.destroy', song.id));
        }
    };

    return (
        <AuthenticatedLayout header="Inspect Track">
            <Head title={`Track: ${song.title}`} />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-8 shadow-2xl shadow-slate-950/20 backdrop-blur-sm relative overflow-hidden">
                    <div className="absolute top-0 right-0 p-8">
                         <div className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest ${song.is_active ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-400/20' : 'bg-rose-500/10 text-rose-400 border border-rose-400/20'}`}>
                            {song.is_active ? 'Publicly visible' : 'Currently Hidden'}
                         </div>
                    </div>

                    <div className="relative">
                        <div className="text-[10px] font-black uppercase tracking-[0.35em] text-cyan-300/70">Audio Distribution</div>
                        <h2 className="mt-3 text-5xl font-black text-white tracking-tight">{song.title}</h2>
                        <p className="mt-4 text-xl font-medium text-slate-300">
                            {song.artist || song.album?.artist || 'Unknown Artist'}
                        </p>

                        <div className="mt-8 flex flex-wrap gap-3">
                            <button
                                onClick={song.is_active ? handleHide : handleApprove}
                                className={`px-8 py-3 rounded-xl font-black text-[10px] uppercase tracking-widest transition-all ${song.is_active ? 'bg-white text-black hover:bg-slate-200' : 'bg-cyan-400 text-black hover:bg-cyan-300'}`}
                            >
                                {song.is_active ? 'Hide from Platform' : 'Approve Track'}
                            </button>
                            <button
                                onClick={handleDelete}
                                className="px-8 py-3 rounded-xl border border-rose-500/20 bg-rose-500/10 text-rose-400 font-black text-[10px] uppercase tracking-widest hover:bg-rose-500/20 transition-all"
                            >
                                Delete Song
                            </button>
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-[0.8fr_1.2fr]">
                    <div className="space-y-6">
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                            <div className="text-[10px] font-black uppercase tracking-[0.35em] text-slate-500 mb-6">Catalog Metadata</div>
                            <div className="space-y-4 text-sm text-slate-300">
                                <div className="flex items-center justify-between border-b border-white/5 pb-4">
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-slate-600">Parent Album</span>
                                    <span className="font-semibold text-white">{song.album?.title || 'N/A (Single)'}</span>
                                </div>
                                <div className="flex items-center justify-between border-b border-white/5 pb-4">
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-slate-600">Track Position</span>
                                    <span className="font-semibold text-white">#{song.track_number}</span>
                                </div>
                                <div className="flex items-center justify-between border-b border-white/5 pb-4">
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-slate-600">Length</span>
                                    <span className="font-semibold text-white">{formatDuration(song.duration)}</span>
                                </div>
                                <div className="flex items-center justify-between pt-2">
                                    <span className="text-[10px] font-bold uppercase tracking-widest text-slate-600">Platform ID</span>
                                    <span className="font-mono text-xs text-slate-500">#{song.id}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-[10px] font-black uppercase tracking-[0.35em] text-slate-500 mb-6 text-center">Lyrics Buffer</div>
                        {song.lyrics ? (
                            <div className="whitespace-pre-wrap rounded-3xl border border-white/5 bg-white/[0.02] p-8 text-center text-lg leading-relaxed text-slate-200 italic font-medium">
                                {song.lyrics}
                            </div>
                        ) : (
                            <div className="py-20 text-center rounded-3xl border border-dashed border-white/10">
                                <div className="text-[10px] font-black uppercase tracking-widest text-slate-600 italic">No lyrics data indexed for this track</div>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
