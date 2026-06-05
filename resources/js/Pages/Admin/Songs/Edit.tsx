import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Song } from '@/types/song';
import { Album } from '@/types/album';

interface Props {
    song: Song;
    albums: Album[];
}

export default function Edit({ song, albums }: Props) {
    const { data, setData, put, processing, errors } = useForm({
        album_id: song.album_id?.toString() || '',
        title: song.title || '',
        artist: song.artist || '',
        duration: song.duration?.toString() || '',
        track_number: song.track_number?.toString() || '',
        lyrics: song.lyrics || '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('admin.songs.update', song.id));
    };

    const inputClasses = "w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20 transition-all";
    const labelClasses = "text-xs font-black uppercase tracking-[0.2em] text-slate-500 mb-2 block ml-1";

    return (
        <AuthenticatedLayout header={`Edit: ${song.title}`}>
            <Head title={`Edit Song - ${song.title}`} />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-white/5 p-8 shadow-lg shadow-slate-950/20 backdrop-blur-sm relative overflow-hidden">
                    <div className="relative">
                        <div className="text-[10px] font-black uppercase tracking-[0.35em] text-cyan-300/70">Metadata Override</div>
                        <h2 className="mt-3 text-4xl font-black text-white tracking-tight">Edit Track Data</h2>
                        <p className="mt-2 text-sm leading-6 text-slate-400 max-w-2xl">
                            Update individual track information, re-assign albums, or correct lyric transcription errors while keeping the Echo Panda catalog high-quality.
                        </p>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <form onSubmit={submit} className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm space-y-6">
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-1">
                                <label className={labelClasses}>Song Title</label>
                                <input
                                    value={data.title}
                                    onChange={(e) => setData('title', e.target.value)}
                                    className={inputClasses}
                                    placeholder="Enter track title"
                                />
                                {errors.title && <div className="text-[10px] text-rose-400 font-bold uppercase mt-1 ml-1">{errors.title}</div>}
                            </div>
                            <div className="space-y-1">
                                <label className={labelClasses}>Artist / Credits</label>
                                <input
                                    value={data.artist}
                                    onChange={(e) => setData('artist', e.target.value)}
                                    className={inputClasses}
                                    placeholder="Stage name or contributors"
                                />
                                {errors.artist && <div className="text-[10px] text-rose-400 font-bold uppercase mt-1 ml-1">{errors.artist}</div>}
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className={labelClasses}>Parent Album</label>
                            <select
                                value={data.album_id}
                                onChange={(e) => setData('album_id', e.target.value)}
                                className={inputClasses}
                            >
                                <option value="">Single (No Album)</option>
                                {albums.map((album) => (
                                    <option key={album.id} value={album.id}>
                                        {album.title} ┬╖ {album.artist}
                                    </option>
                                ))}
                            </select>
                            {errors.album_id && <div className="text-[10px] text-rose-400 font-bold uppercase mt-1 ml-1">{errors.album_id}</div>}
                        </div>

                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-1">
                                <label className={labelClasses}>Duration (Seconds)</label>
                                <input
                                    type="number"
                                    value={data.duration}
                                    onChange={(e) => setData('duration', e.target.value)}
                                    className={inputClasses}
                                />
                                {errors.duration && <div className="text-[10px] text-rose-400 font-bold uppercase mt-1 ml-1">{errors.duration}</div>}
                            </div>
                            <div className="space-y-1">
                                <label className={labelClasses}>Track Number</label>
                                <input
                                    type="number"
                                    value={data.track_number}
                                    onChange={(e) => setData('track_number', e.target.value)}
                                    className={inputClasses}
                                />
                                {errors.track_number && <div className="text-[10px] text-rose-400 font-bold uppercase mt-1 ml-1">{errors.track_number}</div>}
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className={labelClasses}>Lyrics Buffer</label>
                            <textarea
                                value={data.lyrics}
                                onChange={(e) => setData('lyrics', e.target.value)}
                                rows={8}
                                className={`${inputClasses} resize-none min-h-[200px] leading-relaxed`}
                                placeholder="Paste lyrics here..."
                            />
                            {errors.lyrics && <div className="text-[10px] text-rose-400 font-bold uppercase mt-1 ml-1">{errors.lyrics}</div>}
                        </div>

                        <div className="pt-4 flex flex-wrap gap-3">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-8 py-3 rounded-xl bg-white text-black font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-colors disabled:opacity-50"
                            >
                                {processing ? 'Synchronizing...' : 'Save Track Changes'}
                            </button>
                            <Link
                                href={route('admin.songs.index')}
                                className="px-8 py-3 rounded-xl bg-white/5 border border-white/10 text-white font-black text-[10px] uppercase tracking-widest hover:bg-white/10 transition-colors"
                            >
                                Cancel
                            </Link>
                        </div>
                    </form>

                    <div className="space-y-6">
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm">
                            <h3 className="text-lg font-bold text-white mb-6">Moderation Guidelines</h3>
                            <div className="space-y-4">
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/5 text-sm leading-relaxed text-slate-300">
                                    <p className="font-bold text-cyan-400 mb-1 uppercase text-[10px] tracking-widest">Metadata Quality</p>
                                    Ensure stage names match the official artist profile. Titles should not contain version tags like (Explicit) or (HD).
                                </div>
                                <div className="p-4 rounded-2xl bg-white/5 border border-white/5 text-sm leading-relaxed text-slate-300">
                                    <p className="font-bold text-fuchsia-400 mb-1 uppercase text-[10px] tracking-widest">Lyric Formatting</p>
                                    Prefer standard line breaks. Do not include timestamp tags [00:00] in this buffer as they are managed by the Sync Engine.
                                </div>
                            </div>
                        </div>

                        <div className="rounded-[1.75rem] border border-rose-500/20 bg-rose-500/5 p-8 shadow-2xl backdrop-blur-sm">
                            <h3 className="text-lg font-bold text-rose-400 mb-4 tracking-tight">Danger Zone</h3>
                            <p className="text-xs text-slate-500 mb-6 leading-relaxed">
                                Deleting this song will permanently remove it from the platform, including its streaming history, user favorites, and all associated S3 storage keys.
                            </p>
                            <button
                                type="button"
                                onClick={() => { if(confirm('Permanently delete this song?')) put(route('admin.songs.destroy', song.id)) }}
                                className="w-full py-4 rounded-2xl border border-rose-500/30 bg-rose-500/10 text-rose-400 font-black text-[10px] uppercase tracking-widest hover:bg-rose-500/20 transition-all"
                            >
                                Destroy Track Permanently
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
