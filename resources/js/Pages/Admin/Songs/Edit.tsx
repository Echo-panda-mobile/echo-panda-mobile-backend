import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';
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

    const controlClass =
        'mt-1 block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 shadow-sm focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20';

    return (
        <AuthenticatedLayout header="Edit Song">
            <Head title="Edit Song" />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Track editing</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Edit Song</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Update track metadata and lyrics while keeping the admin UI consistent.</p>
                </section>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="p-6 text-gray-900 dark:text-gray-100">
                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <InputLabel htmlFor="album_id" value="Album" />

                            <select
                                id="album_id"
                                name="album_id"
                                value={data.album_id}
                                className={controlClass}
                                onChange={(e) =>
                                    setData('album_id', e.target.value)
                                }
                                required
                            >
                                <option value="">Select an album</option>
                                {albums.map((album) => (
                                    <option key={album.id} value={album.id}>
                                        {album.title} - {album.artist}
                                    </option>
                                ))}
                            </select>

                            <InputError
                                message={errors.album_id}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel htmlFor="title" value="Song Title" />

                            <TextInput
                                id="title"
                                type="text"
                                name="title"
                                value={data.title}
                                className={controlClass}
                                autoComplete="title"
                                isFocused
                                onChange={(e) =>
                                    setData('title', e.target.value)
                                }
                                required
                            />

                            <InputError
                                message={errors.title}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel htmlFor="artist" value="Artist" />

                            <TextInput
                                id="artist"
                                type="text"
                                name="artist"
                                value={data.artist}
                                className={controlClass}
                                onChange={(e) =>
                                    setData('artist', e.target.value)
                                }
                            />

                            <InputError
                                message={errors.artist}
                                className="mt-2"
                            />
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel
                                    htmlFor="duration"
                                    value="Duration (seconds)"
                                />

                                <TextInput
                                    id="duration"
                                    type="number"
                                    name="duration"
                                    value={data.duration}
                                        className={controlClass}
                                    min="1"
                                    onChange={(e) =>
                                        setData('duration', e.target.value)
                                    }
                                    required
                                />

                                <InputError
                                    message={errors.duration}
                                    className="mt-2"
                                />
                            </div>

                            <div>
                                <InputLabel
                                    htmlFor="track_number"
                                    value="Track Number"
                                />

                                <TextInput
                                    id="track_number"
                                    type="number"
                                    name="track_number"
                                    value={data.track_number}
                                        className={controlClass}
                                    min="1"
                                    onChange={(e) =>
                                        setData('track_number', e.target.value)
                                    }
                                    required
                                />

                                <InputError
                                    message={errors.track_number}
                                    className="mt-2"
                                />
                            </div>
                        </div>

                        <div>
                            <InputLabel htmlFor="lyrics" value="Lyrics" />

                            <textarea
                                id="lyrics"
                                name="lyrics"
                                value={data.lyrics}
                                className={`${controlClass} min-h-40`}
                                onChange={(e) =>
                                    setData('lyrics', e.target.value)
                                }
                                rows={8}
                            />

                            <InputError
                                message={errors.lyrics}
                                className="mt-2"
                            />
                        </div>

                        <div className="flex items-center gap-4">
                            <PrimaryButton disabled={processing}>
                                Update Song
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

