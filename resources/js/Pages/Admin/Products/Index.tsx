import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import DangerButton from '@/Components/DangerButton';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link, router } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Product } from '@/types/product';

interface Props extends PageProps {
    products: {
        data: Product[];
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
        is_active?: boolean;
    };
}

export default function Index({ products, filters }: Props) {
    const handleDelete = (id: number) => {
        if (confirm('Are you sure you want to delete this product?')) {
            router.delete(route('admin.products.destroy', id), {
                preserveScroll: true,
            });
        }
    };

    return (
        <AuthenticatedLayout header="Products">
            <Head title="Products" />

            <div className="space-y-6">
                <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Catalog admin</div>
                            <h2 className="mt-2 text-3xl font-black text-white">Products</h2>
                            <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">
                                Keep secondary platform items clean, active, and searchable without breaking the Echo Panda visual tone.
                            </p>
                        </div>
                        <Link href={route('admin.products.create')}>
                            <PrimaryButton>Create Product</PrimaryButton>
                        </Link>
                    </div>
                </div>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="border-b border-white/10 p-6">
                        <form method="get" action={route('admin.products.index')} className="grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
                            <input type="text" name="search" placeholder="Search products..." defaultValue={filters.search} className="w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20" />
                            <button type="submit" className="rounded-2xl border border-cyan-400/20 bg-cyan-400/10 px-4 py-3 text-sm font-semibold text-cyan-100 transition hover:bg-cyan-400/15">Search</button>
                        </form>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="min-w-full divide-y divide-white/10">
                            <thead className="bg-white/5">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Name</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">SKU</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Price</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Quantity</th>
                                    <th className="px-6 py-3 text-left text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Status</th>
                                    <th className="px-6 py-3 text-right text-xs font-semibold uppercase tracking-[0.35em] text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-white/10">
                                {products.data.length === 0 ? (
                                    <tr><td colSpan={6} className="px-6 py-10 text-center text-sm text-slate-400">No products found.</td></tr>
                                ) : products.data.map((product) => (
                                    <tr key={product.id} className="transition hover:bg-white/5">
                                        <td className="whitespace-nowrap px-6 py-4 text-sm font-semibold text-white">{product.name}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{product.sku || 'N/A'}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">${product.price}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">{product.quantity}</td>
                                        <td className="whitespace-nowrap px-6 py-4 text-sm text-slate-300">
                                            <span className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${product.is_active ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20' : 'bg-rose-400/15 text-rose-200 ring-1 ring-rose-400/20'}`}>
                                                {product.is_active ? 'Active' : 'Inactive'}
                                            </span>
                                        </td>
                                        <td className="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                                            <Link href={route('admin.products.show', product.id)} className="mr-3 text-cyan-200 hover:text-cyan-100">View</Link>
                                            <Link href={route('admin.products.edit', product.id)} className="mr-3 text-fuchsia-200 hover:text-fuchsia-100">Edit</Link>
                                            <button onClick={() => handleDelete(product.id)} className="text-rose-300 hover:text-rose-200">Delete</button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    {products.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-white/10 px-6 py-4 text-sm text-slate-300">
                            <div>Showing {products.per_page * (products.current_page - 1) + 1} to {Math.min(products.per_page * products.current_page, products.total)} of {products.total} results</div>
                            <div className="flex gap-2">
                                {products.links.map((link, index) => (
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

