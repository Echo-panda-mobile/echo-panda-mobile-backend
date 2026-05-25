import { InertiaLinkProps, Link } from '@inertiajs/react';

export default function SidebarNavLink({
    active = false,
    className = '',
    children,
    ...props
}: InertiaLinkProps & { active?: boolean }) {
    return (
        <Link
            {...props}
            className={
                'flex items-center rounded-2xl px-3 py-2.5 text-sm font-medium transition-all duration-200 ' +
                (active
                    ? 'bg-gradient-to-r from-cyan-400/20 to-fuchsia-500/20 text-cyan-50 ring-1 ring-cyan-300/20 shadow-[0_0_24px_rgba(34,211,238,0.12)]'
                    : 'text-slate-300 hover:bg-white/5 hover:text-white') +
                ' ' +
                className
            }
        >
            {children}
        </Link>
    );
}

