import React, { FormEvent, useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';

export default function Create({ auth }: PageProps) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        bio: '',
        profile_image: null as File | null,
        cover_image: null as File | null,
        facebook_url: '',
        instagram_url: '',
        tiktok_url: '',
        youtube_url: '',
        is_active: true,
    });

    const [profilePreview, setProfilePreview] = useState<string | null>(null);
    const [coverPreview, setCoverPreview] = useState<string | null>(null);

    const handleFileChange = (e: React.ChangeEvent<HTMLInputElement>, field: 'profile_image' | 'cover_image') => {
        const file = e.target.files?.[0] || null;
        setData(field, file);

        if (file) {
            const reader = new FileReader();
            reader.onloadend = () => {
                if (field === 'profile_image') setProfilePreview(reader.result as string);
                else setCoverPreview(reader.result as string);
            };
            reader.readAsDataURL(file);
        } else {
            if (field === 'profile_image') setProfilePreview(null);
            else setCoverPreview(null);
        }
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        post(route('admin.artists.store'));
    };

    const inputClasses = "w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20 transition-all";
    const labelClasses = "text-sm font-semibold text-slate-300 block mb-2 ml-1";

    return (
        <AuthenticatedLayout header="Redesign: Create Artist">
            <Head title="Create Artist" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Artist Onboarding</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Initialize New Artist</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                        Create a professional artist profile with social integration and high-quality branding assets.
                    </p>
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
                                        value={data.name}
                                        onChange={(e) => setData('name', e.target.value)}
                                        className={inputClasses}
                                        placeholder="e.g. Neon Horizon"
                                    />
                                    {errors.name && <div className="text-sm text-rose-400 font-medium">{errors.name}</div>}
                                </div>

                                <div className="space-y-2">
                                    <label className={labelClasses}>Contact Email</label>
                                    <input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) => setData('email', e.target.value)}
                                        className={inputClasses}
                                        placeholder="artist@echopanda.com"
                                    />
                                    {errors.email && <div className="text-sm text-rose-400 font-medium">{errors.email}</div>}
                                </div>

                                <div className="md:col-span-2 space-y-2">
                                    <label className={labelClasses}>Artist Biography</label>
                                    <textarea
                                        value={data.bio}
                                        onChange={(e) => setData('bio', e.target.value)}
                                        rows={4}
                                        className={inputClasses}
                                        placeholder="Describe the artist's sound and journey..."
                                    />
                                    {errors.bio && <div className="text-sm text-rose-400 font-medium">{errors.bio}</div>}
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
                                        value={data.facebook_url}
                                        onChange={(e) => setData('facebook_url', e.target.value)}
                                        className={inputClasses}
                                        placeholder="https://facebook.com/..."
                                    />
                                    {errors.facebook_url && <div className="text-sm text-rose-400 font-medium">{errors.facebook_url}</div>}
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClasses}>Instagram URL</label>
                                    <input
                                        value={data.instagram_url}
                                        onChange={(e) => setData('instagram_url', e.target.value)}
                                        className={inputClasses}
                                        placeholder="https://instagram.com/..."
                                    />
                                    {errors.instagram_url && <div className="text-sm text-rose-400 font-medium">{errors.instagram_url}</div>}
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClasses}>TikTok URL</label>
                                    <input
                                        value={data.tiktok_url}
                                        onChange={(e) => setData('tiktok_url', e.target.value)}
                                        className={inputClasses}
                                        placeholder="https://tiktok.com/@..."
                                    />
                                    {errors.tiktok_url && <div className="text-sm text-rose-400 font-medium">{errors.tiktok_url}</div>}
                                </div>
                                <div className="space-y-2">
                                    <label className={labelClasses}>YouTube URL</label>
                                    <input
                                        value={data.youtube_url}
                                        onChange={(e) => setData('youtube_url', e.target.value)}
                                        className={inputClasses}
                                        placeholder="https://youtube.com/c/..."
                                    />
                                    {errors.youtube_url && <div className="text-sm text-rose-400 font-medium">{errors.youtube_url}</div>}
                                </div>
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
                                            <img src={profilePreview} className="absolute inset-0 w-full h-full object-cover" />
                                        ) : (
                                            <div className="text-center p-4">
                                                <div className="text-slate-600 mb-2 font-black uppercase text-[10px] tracking-widest">No Profile Selected</div>
                                            </div>
                                        )}
                                        <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <label className="cursor-pointer bg-white text-black px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                                                Upload Photo
                                                <input type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, 'profile_image')} />
                                            </label>
                                        </div>
                                    </div>
                                    {errors.profile_image && <div className="text-sm text-rose-400 mt-2">{errors.profile_image}</div>}
                                </div>

                                <div>
                                    <label className={labelClasses}>Cover Image</label>
                                    <div className="relative group overflow-hidden rounded-2xl border border-white/10 bg-white/5 aspect-[21/9] flex items-center justify-center">
                                        {coverPreview ? (
                                            <img src={coverPreview} className="absolute inset-0 w-full h-full object-cover" />
                                        ) : (
                                            <div className="text-center p-4">
                                                <div className="text-slate-600 mb-2 font-black uppercase text-[10px] tracking-widest">No Cover Selected</div>
                                            </div>
                                        )}
                                        <div className="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <label className="cursor-pointer bg-white text-black px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider">
                                                Upload Cover
                                                <input type="file" className="hidden" accept="image/*" onChange={(e) => handleFileChange(e, 'cover_image')} />
                                            </label>
                                        </div>
                                    </div>
                                    {errors.cover_image && <div className="text-sm text-rose-400 mt-2">{errors.cover_image}</div>}
                                </div>

                                <div className="flex items-center gap-3 p-4 rounded-2xl bg-cyan-400/5 border border-cyan-400/10">
                                    <input
                                        type="checkbox"
                                        id="is_active_check"
                                        checked={data.is_active}
                                        onChange={(e) => setData('is_active', e.target.checked)}
                                        className="h-5 w-5 rounded border-white/20 bg-slate-900 text-cyan-400 focus:ring-cyan-400"
                                    />
                                    <label htmlFor="is_active_check" className="text-sm text-slate-300 font-medium cursor-pointer">
                                        Activate Profile on save
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div className="flex flex-col gap-3">
                            <PrimaryButton type="submit" disabled={processing} className="w-full justify-center py-4 rounded-2xl bg-cyan-500 hover:bg-cyan-400 text-white font-black text-sm uppercase tracking-widest shadow-xl shadow-cyan-500/20">
                                {processing ? 'Initializing...' : 'Create Artist Profile'}
                            </PrimaryButton>
                            <Link href={route('admin.artists.index')} className="w-full text-center py-4 rounded-2xl border border-white/10 bg-white/5 text-sm font-bold text-slate-300 hover:bg-white/10 transition-all">
                                Discard & Go Back
                            </Link>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
