import React, { FormEvent, useState, useMemo } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    artist: any;
}

export default function Show({ artist }: Props) {
    const { data, setData, put, processing, errors } = router as any; // We'll use manual state and router.put for simplicity or useForm if preferred.

    // Using local state for the form to match the "Show" page pattern of edit-in-place
    const [formData, setFormData] = useState({
        name: artist.name || '',
        email: artist.user?.email || '',
        bio: artist.bio || '',
        facebook_url: artist.facebook_url || '',
        instagram_url: artist.instagram_url || '',
        tiktok_url: artist.tiktok_url || '',
        youtube_url: artist.youtube_url || '',
        is_active: Boolean(artist.is_active),
    });

    const [profilePreview, setProfilePreview] = useState<string | null>(artist.image_url || null);
    const [coverPreview, setCoverPreview] = useState<string | null>(artist.cover_image_url || null);
    const [profileFile, setProfileFile] = useState<File | null>(null);
    const [coverFile, setCoverFile] = useState<File | null>(null);

    const stats = useMemo(() => [
        { label: 'Songs', value: artist.songs_count ?? (artist.songs || []).length, color: 'text-cyan-400', bg: 'bg-cyan-400/10' },
        { label: 'Albums', value: artist.albums_count ?? (artist.albums || []).length, color: 'text-fuchsia-400', bg: 'bg-fuchsia-400/10' },
        { label: 'Total Plays', value: Number(artist.songs_sum_play_count || 0).toLocaleString(), color: 'text-amber-400', bg: 'bg-amber-400/10' },
    ], [artist]);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, field: 'profile_image' | 'cover_image') => {
        const file = e.target.files?.[0] || null;
        if (field === 'profile_image') setProfileFile(file);
        else setCoverFile(file);

        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                if (field === 'profile_image') setProfilePreview(reader.result as string);
                else setCoverPreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        }
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        // Manual form data construction for file support in PUT
        const payload = new FormData();
        payload.append('_method', 'PUT'); // Laravel spoofing
        payload.append('name', formData.name);
        payload.append('bio', formData.bio || '');
        payload.append('facebook_url', formData.facebook_url || '');
        payload.append('instagram_url', formData.instagram_url || '');
        payload.append('tiktok_url', formData.tiktok_url || '');
        payload.append('youtube_url', formData.youtube_url || '');
        payload.append('is_active', formData.is_active ? '1' : '0');

        if (profileFile) payload.append('profile_image', profileFile);
        if (coverFile) payload.append('cover_image', coverFile);

        router.post(route('admin.artists.update', artist.id), payload);
    };

    const inputClasses = "w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20 transition-all";
    const labelClasses = "text-sm font-semibold text-slate-300 block mb-2 ml-1";

    return (
        <AuthenticatedLayout header={`Inspect: ${artist.name}`}>
            <Head title={`Artist: ${artist.name}`} />

            <div className="space-y-6">
                {/* Header Section */}
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-8 shadow-lg shadow-slate-950/20 backdrop-blur-sm relative overflow-hidden">
                    <div className="absolute top-0 right-0 p-8">
                         <div className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest ${formData.is_active ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-400/20' : 'bg-rose-500/10 text-rose-400 border border-rose-400/20'}`}>
                            {formData.is_active ? 'Account Live' : 'Account Suspended'}
                         </div>
                    </div>

                    <div className="flex flex-col md:flex-row gap-8 items-center md:items-start">
                        <div className="w-32 h-32 rounded-[2rem] border-4 border-white/10 overflow-hidden shadow-2xl shrink-0">
                            <img src={profilePreview || '/placeholder-artist.png'} className="w-full h-full object-cover" alt={artist.name} />
                        </div>
                        <div className="flex-1 text-center md:text-left">
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Artist Identity</div>
                            <h2 className="mt-2 text-4xl font-black text-white">{artist.name}</h2>
                            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-300">
                                {artist.bio || 'No biography provided for this artist.'}
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-8 pt-8 border-t border-white/5">
                        {stats.map((stat) => (
                            <div key={stat.label} className="p-4 rounded-2xl bg-white/5 border border-white/5">
                                <div className="text-[10px] font-black uppercase tracking-widest text-slate-500">{stat.label}</div>
                                <div className={`text-2xl font-black mt-1 ${stat.color}`}>{stat.value}</div>
                            </div>
                        ))}
                    </div>
                </div>

                <form onSubmit={submit} className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    <div className="space-y-6">
                        {/* Core Info */}
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm">
                            <h3 className="text-lg font-bold text-white mb-6 flex items-center gap-3">
                                <span className="w-8 h-8 rounded-full bg-cyan-400/10 flex items-center justify-center text-cyan-400 text-xs">01</span>
                                Essential Information
                            </h3>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <label className={labelClasses}>Stage Name</label>
                                    <input
                                        value={formData.name}
                                        onChange={(e) => setFormData({...formData, name: e.target.value})}
                                        className={inputClasses}
                                    />
                                </div>

                                <div className="space-y-2 opacity-60 cursor-not-allowed">
                                    <label className={labelClasses}>Contact Email (Read-only)</label>
                                    <input
                                        type="email"
                                        value={formData.email}
                                        disabled
                                        className={`${inputClasses} cursor-not-allowed`}
                                    />
                                </div>

                                <div className="md:col-span-2 space-y-2">
                                    <label className={labelClasses}>Artist Biography</label>
                                    <textarea
                                        value={formData.bio}
                                        onChange={(e) => setFormData({...formData, bio: e.target.value})}
                                        rows={4}
                                        className={inputClasses}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Social Links */}
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm">
                            <h3 className="text-lg font-bold text-white mb-6 flex items-center gap-3">
                                <span className="w-8 h-8 rounded-full bg-fuchsia-400/10 flex items-center justify-center text-fuchsia-400 text-xs">02</span>
                                Social Integration
                            </h3>
                            <div className="grid gap-6 md:grid-cols-2">
                                <div className="space-y-2">
                                    <label className={labelClasses}>Facebook URL</label>
                                    <input
                                        value={formData.facebook_url}
                                        onChange={(e) => setFormData({...formData, facebook_url: e.target.value})}
                                        className={inputClasses}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClasses}>Instagram URL</label>
                                    <input
                                        value={formData.instagram_url}
                                        onChange={(e) => setFormData({...formData, instagram_url: e.target.value})}
                                        className={inputClasses}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClasses}>TikTok URL</label>
                                    <input
                                        value={formData.tiktok_url}
                                        onChange={(e) => setFormData({...formData, tiktok_url: e.target.value})}
                                        className={inputClasses}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClasses}>YouTube URL</label>
                                    <input
                                        value={formData.youtube_url}
                                        onChange={(e) => setFormData({...formData, youtube_url: e.target.value})}
                                        className={inputClasses}
                                    />
                                </div>
                            </div>
                        </div>

                        {/* Recent Activity / Top Songs */}
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm">
                            <h3 className="text-lg font-bold text-white mb-6">Popular Discography</h3>
                            <div className="space-y-3">
                                {(artist.songs || []).slice(0, 5).map((song: any, idx: number) => (
                                    <div key={song.id} className="flex items-center justify-between p-4 rounded-2xl bg-white/5 border border-white/5 hover:bg-white/10 transition-colors">
                                        <div className="flex items-center gap-4">
                                            <span className="text-xs font-black text-slate-600 w-4">{idx + 1}</span>
                                            <div>
                                                <div className="text-sm font-bold text-white">{song.title}</div>
                                                <div className="text-[10px] uppercase font-black tracking-widest text-slate-500 mt-0.5">{song.album?.title || 'Single'}</div>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-6">
                                            <div className="text-right">
                                                <div className="text-xs font-bold text-cyan-400">{Number(song.play_count || 0).toLocaleString()}</div>
                                                <div className="text-[8px] font-black uppercase text-slate-600 tracking-tighter">Streams</div>
                                            </div>
                                            <Link href={route('admin.songs.show', song.id)} className="w-8 h-8 rounded-xl bg-white/5 flex items-center justify-center text-white hover:bg-cyan-500 transition-colors">
                                                <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"/></svg>
                                            </Link>
                                        </div>
                                    </div>
                                ))}
                                {(!artist.songs || artist.songs.length === 0) && (
                                    <div className="text-center py-12 text-slate-500 text-sm italic font-medium">No songs uploaded yet.</div>
                                )}
                            </div>
                        </div>
                    </div>

                    <div className="space-y-6">
                        {/* Asset Management */}
                        <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm">
                            <h3 className="text-lg font-bold text-white mb-6 flex items-center gap-3">
                                <span className="w-8 h-8 rounded-full bg-amber-400/10 flex items-center justify-center text-amber-400 text-xs">03</span>
                                Branding Assets
                            </h3>

                            <div className="space-y-6">
                                <div>
                                    <label className={labelClasses}>Profile Image</label>
                                    <div className="relative group overflow-hidden rounded-2xl border border-white/10 bg-white/5 aspect-square flex items-center justify-center">
                                        {profilePreview ? (
                                            <img src={profilePreview} className="absolute inset-0 w-full h-full object-cover" alt="Profile" />
                                        ) : (
                                            <div className="text-center p-4">
                                                <div className="text-slate-600 mb-2 font-black uppercase text-[10px] tracking-widest">No Profile Image</div>
                                            </div>
                                        )}
                                        <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <label className="cursor-pointer bg-white text-black px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                                                Update Photo
                                                <input type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, 'profile_image')} />
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label className={labelClasses}>Cover Image</label>
                                    <div className="relative group overflow-hidden rounded-2xl border border-white/10 bg-white/5 aspect-[21/9] flex items-center justify-center">
                                        {coverPreview ? (
                                            <img src={coverPreview} className="absolute inset-0 w-full h-full object-cover" alt="Cover" />
                                        ) : (
                                            <div className="text-center p-4">
                                                <div className="text-slate-600 mb-2 font-black uppercase text-[10px] tracking-widest">No Cover Image</div>
                                            </div>
                                        )}
                                        <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <label className="cursor-pointer bg-white text-black px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                                                Update Cover
                                                <input type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, 'cover_image')} />
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex items-center gap-3 p-4 rounded-2xl bg-cyan-400/5 border border-cyan-400/10">
                                    <input
                                        type="checkbox"
                                        id="is_active_check"
                                        checked={formData.is_active}
                                        onChange={(e) => setFormData({...formData, is_active: e.target.checked})}
                                        className="h-5 w-5 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                    />
                                    <label htmlFor="is_active_check" className="text-sm text-slate-300 font-medium cursor-pointer">
                                        Active Profile
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div className="flex flex-col gap-3">
                            <PrimaryButton type="submit" disabled={processing} className="w-full justify-center py-4 rounded-2xl bg-white text-black hover:bg-slate-100 font-black text-sm uppercase tracking-widest shadow-xl">
                                {processing ? 'Updating...' : 'Update Artist Data'}
                            </PrimaryButton>
                            <Link href={route('admin.artists.index')} className="w-full text-center py-4 rounded-2xl border border-white/10 bg-white/5 text-sm font-bold text-slate-300 hover:bg-white/10 transition-all">
                                Return to Gallery
                            </Link>
                            <button
                                type="button"
                                onClick={() => { if(confirm('Permanently delete this artist?')) router.delete(route('admin.artists.destroy', artist.id)) }}
                                className="w-full text-center py-4 rounded-2xl border border-rose-500/20 bg-rose-500/10 text-sm font-bold text-rose-400 hover:bg-rose-500/20 transition-all mt-4"
                            >
                                Delete Artist Profile
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
