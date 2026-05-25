import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Song } from '@/types/song';
import { Album } from '@/types/album';

interface Props extends PageProps {
    songs: {
        data: Song[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    albums: Album[];
    filters: {
        search?: string;
        album_id?: string;
    };
}

export default function Index({ songs, albums, filters }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this song?')) {
            router.delete(route('admin.songs.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const formatDuration = (seconds: number) => {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    };

    return (
        <AuthenticatedLayout header="Songs">
            <Head title="Songs" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Track control</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Songs</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Moderate individual tracks, browse by album, and keep the catalog clean and searchable.
                            </p>
                        </div>
                        <Link href={route('admin.songs.create')}>
                            <PrimaryButton>Create Song</PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 p-6">
                        <form method="get" action={route('admin.songs.index')} className="grid gap-3 lg:grid-cols-[1fr_220px_auto] lg:items-center">
                            <input type="text" name="search" placeholder="Search songs..." defaultValue={filters.search} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" />
                            <select name="album_id" defaultValue={filters.album_id} className="rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20">
                                <option value="">All Albums</option>
                                {albums.map((album) => <option key={album.id} value={album.id}>{album.title}</option>)}
                            </select>
                            <button type="submit" className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Filter</button>
                        </form>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Track</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Title</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Artist</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Album</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Duration</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {songs.data.length === 0 ? (
                                    <tr><td colSpan={6} className="px-6 py-10 text-center text-sm text-slate-400">No songs found.</td></tr>
                                ) : songs.data.map((song) => (
                                    <tr key={song.id} className="transition hover:bg-white/5">
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{song.track_number}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-white">{song.title}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{song.artist || song.album?.artist || 'N/A'}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{song.album?.title || 'N/A'}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{formatDuration(song.duration)}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link href={route('admin.songs.show', song.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                            <Link href={route('admin.songs.edit', song.id)} className="mr-3 text-fuchsia-200 hover:text-fuchsia-100">Edit</Link>
                                            <button onClick={() => handleDelete(song.id)} className="text-rose-300 hover:text-rose-200">Delete</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {songs.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/10 px-6 py-4 text-sm text-slate-300">
                            <div>Showing {songs.per_page * (songs.current_page - 1) + 1} to {Math.min(songs.per_page * songs.current_page, songs.total)} of {songs.total} results</div>
                            <div className="flex gap-2">
                                {songs.links.map((link, index) => (
                                    <Link key={index} href={link.url || '#'} className={`rounded-2xl px-3 py-2 text-sm ${link.active ? 'bg-cyan-400/15 text-cyan-100 ring-1 ring-cyan-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`} dangerouslySetInnerHTML={{ __html: link.label }} />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

