import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    user: any;
}

export default function Show({ user }: Props) {
    return (
        <AuthenticatedLayout header={`User: ${user.name}`}>
            <Head title={`User ${user.name}`} />

            <div className="grid gap-6 lg:grid-cols-[0.9fr_1.1fr]">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">
                        User profile
                    </div>
                    <h2 className="mt-2 text-3xl font-black text-white">{user.name}</h2>
                    <p className="mt-3 text-sm leading-6 text-slate-300">Listener account and moderation profile.</p>
                    <div className="mt-6 rounded-3xl border border-white/10 bg-white/5 p-4">
                        <div className="text-xs uppercase tracking-[0.35em] text-slate-500">Email</div>
                        <div className="mt-2 text-sm font-semibold text-white">{user.email}</div>
                    </div>
                </section>

                <section className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">
                        Moderation status
                    </div>
                    <div className="mt-5 grid gap-4 sm:grid-cols-2">
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">
                            Ban / unban controls can be placed here.
                        </div>
                        <div className="rounded-2xl border border-white/10 bg-slate-950/40 p-4 text-sm text-slate-300">
                            Activity and listening history can be surfaced here.
                        </div>
                    </div>
                </section>
            </div>
        </AuthenticatedLayout>
    );
}
