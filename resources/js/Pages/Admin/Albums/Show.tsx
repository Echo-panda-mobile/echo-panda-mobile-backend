import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link } from '@inertiajs/react';
import { Album } from '@/types/album';
import { Song } from '@/types/song';

interface Props {
    album: Album & {
        songs: Song[];
    };
}

export default function Show({ album }: Props) {
    const formatDate = (date: string | null) => {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString();
    };

    const formatDuration = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    return (
        <AuthenticatedLayout header="Album Details">
            <Head title="Album Details" />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">
                                Album profile
                            </div>
                            <h2 className="mt-2 text-3xl font-black text-white">{album.title}</h2>
                            <p className="mt-2 text-sm leading-6 text-slate-300">by {album.artist}</p>
                            {album.description && <p className="mt-3 max-w-3xl text-sm leading-6 text-slate-300">{album.description}</p>}
                        </div>
                        <div className="flex gap-3">
                            <Link href={route('admin.songs.create', { album_id: album.id })}>
                                <PrimaryButton>Add Song</PrimaryButton>
                            </Link>
                            <Link href={route('admin.albums.edit', album.id)} className="inline-flex rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-2.5 text-sm font-semibold text-cyan-100 hover:bg-cyan-400/15">
                                Edit Album
                            </Link>
                        </div>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-[0.75fr_1.25fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Release metrics</div>
                        <div className="mt-5 space-y-4 text-sm text-slate-300">
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Release Date</span><span className="font-semibold text-white">{formatDate(album.release_date)}</span></div>
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Total Songs</span><span className="font-semibold text-white">{album.songs?.length || 0}</span></div>
                        </div>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Songs on album</div>
                        {album.songs && album.songs.length > 0 ? (
                            <div className="mt-5 space-y-3">
                                {album.songs.sort((a, b) => a.track_number - b.track_number).map((song) => (
                                    <div key={song.id} className="flex items-center justify-between rounded-2xl border border-white/10 bg-white/5 p-4 transition hover:bg-white/10">
                                        <div className="flex items-center gap-4">
                                            <span className="rounded-full border border-cyan-400/20 bg-cyan-400/10 px-2.5 py-1 text-xs font-semibold text-cyan-100">#{song.track_number}</span>
                                            <div>
                                                <p className="font-semibold text-white">{song.title}</p>
                                                {song.artist && <p className="text-sm text-slate-400">{song.artist}</p>}
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-4">
                                            <span className="text-sm text-slate-400">{formatDuration(song.duration)}</span>
                                            <Link href={route('admin.songs.edit', song.id)} className="text-cyan-200 hover:text-cyan-100">Edit</Link>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="mt-5 rounded-2xl border border-white/10 bg-white/5 p-5 text-sm text-slate-400">No songs added yet.</div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

