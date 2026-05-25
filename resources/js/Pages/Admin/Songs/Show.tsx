import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link } from '@inertiajs/react';
import { Song } from '@/types/song';

interface Props {
    song: Song;
}

export default function Show({ song }: Props) {
    const formatDuration = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    return (
        <AuthenticatedLayout header="Song Details">
            <Head title="Song Details" />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Track profile</div>
                            <h2 className="mt-2 text-3xl font-black text-white">{song.title}</h2>
                            <p className="mt-2 text-sm leading-6 text-slate-300">{song.artist || song.album?.artist || 'Unknown Artist'}</p>
                        </div>
                        <Link href={route('admin.songs.edit', song.id)}>
                            <PrimaryButton>Edit Song</PrimaryButton>
                        </Link>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-[0.75fr_1.25fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Metadata</div>
                        <div className="mt-5 space-y-4 text-sm text-slate-300">
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Album</span><span className="font-semibold text-white">{song.album?.title || 'N/A'}</span></div>
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Track Number</span><span className="font-semibold text-white">#{song.track_number}</span></div>
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Duration</span><span className="font-semibold text-white">{formatDuration(song.duration)}</span></div>
                        </div>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Lyrics</div>
                        {song.lyrics ? (
                            <div className="mt-4 whitespace-pre-wrap rounded-2xl border border-white/10 bg-white/5 p-5 text-sm leading-7 text-slate-200">{song.lyrics}</div>
                        ) : (
                            <div className="mt-4 rounded-2xl border border-white/10 bg-white/5 p-5 text-sm text-slate-400">No lyrics provided.</div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

