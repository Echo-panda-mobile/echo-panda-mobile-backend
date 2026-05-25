import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    artist: any;
}

export default function Show({ artist }: Props) {
    return (
        <AuthenticatedLayout header={`Artist: ${artist.name}`}>
            <Head title={`Artist ${artist.name}`} />
            <div className="space-y-6">
                <section className="rounded-2xl border border-white/10 bg-gradient-to-br from-[#24123a] to-[#2a0e4a] p-6 shadow-2xl backdrop-blur-sm">
                    <div className="flex items-start justify-between gap-6">
                        <div>
                            <h1 className="text-3xl font-extrabold text-white">{artist.name}</h1>
                            <p className="mt-1 text-sm text-slate-300">{artist.bio || 'Artist profile and recent activity'}</p>
                        </div>
                        <div className="flex items-center gap-3">
                            <Link href={route('admin.artists.edit', artist.id)} className="rounded-lg bg-gradient-to-r from-pink-500 to-cyan-400 px-4 py-2 text-sm font-semibold text-white shadow-md">
                                Edit Artist
                            </Link>
                        </div>
                    </div>
                </section>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-4">
                    <div className="col-span-1 rounded-xl bg-white/3 p-4">
                        <div className="text-xs text-slate-300">Total Songs</div>
                        <div className="mt-2 text-2xl font-bold text-white">{(artist.songs || []).length}</div>
                    </div>
                    <div className="col-span-1 rounded-xl bg-white/3 p-4">
                        <div className="text-xs text-slate-300">Total Plays</div>
                        <div className="mt-2 text-2xl font-bold text-white">{artist.stats?.plays ?? 0}</div>
                    </div>
                    <div className="col-span-1 rounded-xl bg-white/3 p-4">
                        <div className="text-xs text-slate-300">Favorites</div>
                        <div className="mt-2 text-2xl font-bold text-white">{artist.stats?.favorites ?? 0}</div>
                    </div>
                    <div className="col-span-1 rounded-xl bg-white/3 p-4">
                        <div className="text-xs text-slate-300">Followers</div>
                        <div className="mt-2 text-2xl font-bold text-white">{artist.stats?.followers ?? 0}</div>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1fr_420px]">
                    <div className="rounded-2xl border border-white/8 bg-gradient-to-br from-[#1d1630]/60 to-[#241634]/50 p-5 shadow-lg">
                        <div className="flex items-center justify-between">
                            <h3 className="text-lg font-semibold text-white">Top Songs</h3>
                            <div className="text-sm text-slate-300">All time</div>
                        </div>

                        <div className="mt-4 space-y-3">
                            {(artist.songs || []).slice(0, 8).map((song: any, idx: number) => (
                                <div key={song.id || idx} className="flex items-center justify-between rounded-xl bg-white/2 p-3">
                                    <div className="flex items-center gap-3">
                                        <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-pink-500 to-violet-500 text-sm font-bold text-white">{idx + 1}</div>
                                        <div>
                                            <div className="text-sm font-semibold text-white">{song.title}</div>
                                            <div className="text-xs text-slate-300">{song.artist_name || artist.name}</div>
                                        </div>
                                    </div>

                                    <div className="flex items-center gap-4 text-sm text-slate-300">
                                        <div className="flex items-center gap-2"><svg className="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 10a8 8 0 1016 0A8 8 0 002 10z"/></svg>{song.plays ?? 0}</div>
                                        <div className="text-xs text-amber-300">{song.favorites ?? 0}</div>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>

                    <aside className="space-y-4">
                        <div className="rounded-2xl border border-white/8 bg-gradient-to-br from-[#211230]/50 to-[#1a1230]/40 p-4">
                            <div className="text-xs text-slate-300">Top Song</div>
                            <div className="mt-2 text-lg font-bold text-white">{(artist.songs || [])[0]?.title ?? '—'}</div>
                            <div className="text-xs text-slate-400">{(artist.songs || [])[0]?.plays ?? 0} plays</div>
                        </div>

                        <div className="rounded-2xl border border-white/8 bg-gradient-to-br from-[#211230]/50 to-[#1a1230]/40 p-4">
                            <div className="text-xs text-slate-300">Profile</div>
                            <div className="mt-2 text-lg font-bold text-white">{artist.name}</div>
                            <div className="text-xs text-slate-400">{artist.verified ? 'Verified' : 'Not verified'}</div>
                        </div>
                    </aside>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
