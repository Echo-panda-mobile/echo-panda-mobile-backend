import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
        artist: '',
        release_date: '',
        description: '',
        cover_image: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('admin.albums.store'), {
            onSuccess: () => reset(),
        });
    };

    const controlClass =
        'mt-1 block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 shadow-sm focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20';

    return (
        <AuthenticatedLayout header="Create Album">
            <Head title="Create Album" />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Catalog creation</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Create Album</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Add a new release to the Echo Panda catalog with the same dark control-deck aesthetic.</p>
                </section>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="p-6 text-gray-900 dark:text-gray-100">
                    <form onSubmit={submit} className="space-y-6">
                        <div>
                            <InputLabel htmlFor="title" value="Album Title" />

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
                                required
                            />

                            <InputError
                                message={errors.artist}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="release_date"
                                value="Release Date"
                            />

                            <TextInput
                                id="release_date"
                                type="date"
                                name="release_date"
                                value={data.release_date}
                                className={controlClass}
                                onChange={(e) =>
                                    setData('release_date', e.target.value)
                                }
                            />

                            <InputError
                                message={errors.release_date}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="description"
                                value="Description"
                            />

                            <textarea
                                id="description"
                                name="description"
                                value={data.description}
                                className={`${controlClass} min-h-32`}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                rows={4}
                            />

                            <InputError
                                message={errors.description}
                                className="mt-2"
                            />
                        </div>

                        <div>
                            <InputLabel
                                htmlFor="cover_image"
                                value="Cover Image URL"
                            />

                            <TextInput
                                id="cover_image"
                                type="text"
                                name="cover_image"
                                value={data.cover_image}
                                className={controlClass}
                                onChange={(e) =>
                                    setData('cover_image', e.target.value)
                                }
                            />

                            <InputError
                                message={errors.cover_image}
                                className="mt-2"
                            />
                        </div>

                        <div className="flex items-center gap-4">
                            <PrimaryButton disabled={processing}>
                                Create Album
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

