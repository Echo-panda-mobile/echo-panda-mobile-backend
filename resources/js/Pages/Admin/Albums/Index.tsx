import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Album } from '@/types/album';

interface Props extends PageProps {
    albums: {
        data: Album[];
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
    filters: {
        search?: string;
    };
}

export default function Index({ albums, filters }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this album?')) {
            router.delete(route('admin.albums.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    const formatDate = (date: string | null) => {
        if (!date) return 'N/A';
        return new Date(date).toLocaleDateString();
    };

    return (
        <AuthenticatedLayout header="Albums">
            <Head title="Albums" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Release control</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Albums</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Review album releases, trace song counts, and open album detail views for moderation or editing.
                            </p>
                        </div>
                        <Link href={route('admin.albums.create')}>
                            <PrimaryButton>Create Album</PrimaryButton>
                        </Link>
                    </div>
                    <div className="mt-6 grid gap-4 sm:grid-cols-3">
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">Search and filter by title or artist.</div>
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">Flag albums for deactivation or reactivation.</div>
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">Pin releases to trending or featured spots.</div>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 p-6">
                        <form method="get" action={route('admin.albums.index')} className="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                            <input type="text" name="search" placeholder="Search albums..." defaultValue={filters.search} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" />
                            <button type="submit" className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Search</button>
                        </form>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Title</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Artist</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Release</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Songs</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {albums.data.length === 0 ? (
                                    <tr>
                                        <td colSpan={5} className="px-6 py-10 text-center text-sm text-slate-400">No albums found.</td>
                                    </tr>
                                ) : (
                                    albums.data.map((album) => (
                                        <tr key={album.id} className="transition hover:bg-white/5">
                                            <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-white">{album.title}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{album.artist}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{formatDate(album.release_date)}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{album.songs_count || 0}</td>
                                            <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                                <Link href={route('admin.albums.show', album.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                                <Link href={route('admin.albums.edit', album.id)} className="mr-3 text-fuchsia-200 hover:text-fuchsia-100">Edit</Link>
                                                <button onClick={() => handleDelete(album.id)} className="text-rose-300 hover:text-rose-200">Delete</button>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                    {albums.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/10 px-6 py-4 text-sm text-slate-300">
                            <div>
                                Showing {albums.per_page * (albums.current_page - 1) + 1} to {Math.min(albums.per_page * albums.current_page, albums.total)} of {albums.total} results
                            </div>
                            <div className="flex gap-2">
                                {albums.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url || '#'}
                                        className={`rounded-2xl px-3 py-2 text-sm ${link.active ? 'bg-cyan-400/15 text-cyan-100 ring-1 ring-cyan-400/20' : 'bg-white/5 text-slate-300 hover:bg-white/10'} ${!link.url ? 'cursor-not-allowed opacity-50' : ''}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

