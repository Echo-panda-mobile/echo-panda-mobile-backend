import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { useMemo, useState } from 'react';

interface Props extends PageProps {
    user: any;
}

export default function Show({ user }: Props) {
    const [name, setName] = useState(user.name || '');
    const [email, setEmail] = useState(user.email || '');
    const [role, setRole] = useState(user.role || 'user');

    const profileStats = useMemo(() => [
        { label: 'Favorites', value: user.favorites_count ?? 0, color: 'text-cyan-400' },
        { label: 'Listen history', value: user.listen_history_count ?? 0, color: 'text-fuchsia-400' },
        { label: 'Following', value: user.following_count ?? 0, color: 'text-amber-400' },
        { label: 'Followers', value: user.followers_count ?? 0, color: 'text-emerald-400' },
    ], [user]);

    const saveUser = (e: React.FormEvent) => {
        e.preventDefault();
        router.put(route('admin.users.update', user.id), {
            name,
            email,
            role,
        });
    };

    const toggleBan = () => {
        const action = user.is_banned ? 'unban' : 'ban';
        if (confirm(`Are you sure you want to ${action} this user?`)) {
            router.post(route(`admin.users.${action}`, user.id));
        }
    };

    const inputClasses = "w-full rounded-2xl border border-white/10 bg-slate-950/60 px-4 py-3 text-sm text-white outline-none focus:border-cyan-400/30 focus:ring-2 focus:ring-cyan-400/20 transition-all";
    const labelClasses = "text-xs font-black uppercase tracking-[0.2em] text-slate-500 mb-2 block ml-1";

    return (
        <AuthenticatedLayout header={`Inspect: ${user.name}`}>
            <Head title={`User Profile - ${user.name}`} />

            <div className="space-y-6">
                {/* Profile Header */}
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-8 shadow-lg shadow-slate-950/20 backdrop-blur-sm relative overflow-hidden">
                    <div className="absolute top-0 right-0 p-8">
                         <div className={`px-4 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest ${!user.is_banned ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-400/20' : 'bg-rose-500/10 text-rose-400 border border-rose-400/20'}`}>
                            {!user.is_banned ? 'Account Active' : 'Account Suspended'}
                         </div>
                    </div>

                    <div className="flex flex-col md:flex-row gap-8 items-center md:items-start">
                        <div className="w-24 h-24 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center text-3xl font-black text-white shadow-2xl shrink-0">
                            {user.name.charAt(0).toUpperCase()}
                        </div>
                        <div className="flex-1 text-center md:text-left pt-2">
                            <div className="text-[10px] font-black uppercase tracking-[0.35em] text-cyan-300/70">Listener Identity</div>
                            <h2 className="mt-2 text-4xl font-black text-white">{user.name}</h2>
                            <div className="mt-2 text-slate-400 text-sm font-medium">{user.email}</div>
                        </div>
                    </div>

                    <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-8 pt-8 border-t border-white/5">
                        {profileStats.map((stat) => (
                            <div key={stat.label} className="p-4 rounded-2xl bg-white/5 border border-white/5">
                                <div className="text-[10px] font-black uppercase tracking-widest text-slate-500">{stat.label}</div>
                                <div className={`text-2xl font-black mt-1 ${stat.color}`}>{stat.value}</div>
                            </div>
                        ))}
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                    {/* Settings Form */}
                    <form onSubmit={saveUser} className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm space-y-6">
                        <h3 className="text-lg font-bold text-white mb-6">Account Settings</h3>

                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="space-y-1">
                                <label className={labelClasses}>Full Name</label>
                                <input value={name} onChange={(e) => setName(e.target.value)} className={inputClasses} />
                            </div>
                            <div className="space-y-1">
                                <label className={labelClasses}>Email Address</label>
                                <input value={email} onChange={(e) => setEmail(e.target.value)} className={inputClasses} />
                            </div>
                        </div>

                        <div className="space-y-1">
                            <label className={labelClasses}>Platform Role</label>
                            <select value={role} onChange={(e) => setRole(e.target.value)} className={inputClasses}>
                                <option value="user">Standard Listener</option>
                                <option value="artist">Artist Account</option>
                                <option value="publicer">Content Publisher</option>
                                <option value="admin">Platform Administrator</option>
                            </select>
                        </div>

                        <div className="pt-4 flex flex-wrap gap-3">
                            <button type="submit" className="px-8 py-3 rounded-xl bg-white text-black font-black text-[10px] uppercase tracking-widest hover:bg-slate-100 transition-colors">
                                Save Profile Changes
                            </button>
                            <button type="button" onClick={() => router.get(route('admin.users.index'))} className="px-8 py-3 rounded-xl bg-white/5 border border-white/10 text-white font-black text-[10px] uppercase tracking-widest hover:bg-white/10 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>

                    {/* Moderation Actions */}
                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-8 shadow-2xl backdrop-blur-sm">
                        <h3 className="text-lg font-bold text-white mb-6">Moderation Actions</h3>

                        <div className="space-y-4">
                            <div className={`p-4 rounded-2xl border ${user.is_banned ? 'bg-rose-500/10 border-rose-500/20' : 'bg-white/5 border-white/10'}`}>
                                <div className="text-[10px] font-black uppercase tracking-widest text-slate-500 mb-1">Status</div>
                                <div className={`text-sm font-bold ${user.is_banned ? 'text-rose-400' : 'text-cyan-400'}`}>
                                    {user.is_banned ? 'Account is currently Suspended' : 'Account is active and healthy'}
                                </div>
                            </div>

                            <button
                                type="button"
                                onClick={toggleBan}
                                className={`w-full py-4 rounded-2xl font-black text-[10px] uppercase tracking-[0.2em] transition-all border ${user.is_banned ? 'border-emerald-500/20 bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20' : 'border-rose-500/20 bg-rose-500/10 text-rose-400 hover:bg-rose-500/20'}`}
                            >
                                {user.is_banned ? 'Restore Account Access' : 'Suspend Account Access'}
                            </button>

                            <p className="text-[10px] text-slate-500 leading-relaxed px-1">
                                {user.is_banned
                                    ? "Restoring access will allow the user to log in, stream music, and participate in the community again."
                                    : "Suspending an account will immediately invalidate the user's sessions and prevent them from accessing any platform features."}
                            </p>

                            <div className="pt-8 border-t border-white/5">
                                <button
                                    type="button"
                                    onClick={() => { if(confirm('Permanently delete this user? This cannot be undone.')) router.delete(route('admin.users.destroy', user.id)) }}
                                    className="w-full py-4 rounded-2xl border border-white/5 bg-white/[0.02] text-slate-500 font-black text-[10px] uppercase tracking-widest hover:bg-rose-500/10 hover:text-rose-400 hover:border-rose-400/20 transition-all"
                                >
                                    Delete User Permanently
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
