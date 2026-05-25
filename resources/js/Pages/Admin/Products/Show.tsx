import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import PrimaryButton from '@/Components/PrimaryButton';
import { Head, Link } from '@inertiajs/react';
import { Product } from '@/types/product';

interface Props {
    product: Product;
}

export default function Show({ product }: Props) {
    return (
        <AuthenticatedLayout header="Product Details">
            <Head title="Product Details" />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Catalog item</div>
                            <h2 className="mt-2 text-3xl font-black text-white">{product.name}</h2>
                            <p className="mt-3 max-w-3xl text-sm leading-6 text-slate-300">{product.description || 'No description'}</p>
                        </div>
                        <Link href={route('admin.products.edit', product.id)}>
                            <PrimaryButton>Edit Product</PrimaryButton>
                        </Link>
                    </div>
                </section>

                <div className="grid gap-6 lg:grid-cols-[0.75fr_1.25fr]">
                    <div className="rounded-[1.75rem] border border-white/10 bg-white/5 p-6 shadow-lg shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Inventory</div>
                        <div className="mt-5 space-y-4 text-sm text-slate-300">
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Price</span><span className="font-semibold text-white">${product.price}</span></div>
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Quantity</span><span className="font-semibold text-white">{product.quantity}</span></div>
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>SKU</span><span className="font-semibold text-white">{product.sku || 'N/A'}</span></div>
                            <div className="flex items-center justify-between border-b border-white/10 pb-3"><span>Status</span><span className={`rounded-full px-2.5 py-1 text-xs font-semibold ${product.is_active ? 'bg-emerald-400/15 text-emerald-200 ring-1 ring-emerald-400/20' : 'bg-rose-400/15 text-rose-200 ring-1 ring-rose-400/20'}`}>{product.is_active ? 'Active' : 'Inactive'}</span></div>
                        </div>
                    </div>

                    <div className="rounded-[1.75rem] border border-white/10 bg-slate-950/50 p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                        <div className="text-xs font-semibold uppercase tracking-[0.35em] text-slate-500">Audit trail</div>
                        <div className="mt-5 space-y-3 text-sm text-slate-300">
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">Created: {new Date(product.created_at).toLocaleString()}</div>
                            <div className="rounded-2xl border border-white/10 bg-white/5 p-4">Updated: {new Date(product.updated_at).toLocaleString()}</div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

