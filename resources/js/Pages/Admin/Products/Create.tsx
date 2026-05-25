import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Create() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        description: '',
        price: '',
        quantity: '',
        sku: '',
        is_active: true,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('admin.products.store'), {
            onSuccess: () => reset(),
        });
    };

    const controlClass =
        'mt-1 block w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder:text-slate-500 shadow-sm focus:border-cyan-400/30 focus:outline-none focus:ring-2 focus:ring-cyan-400/20';

    return (
        <AuthenticatedLayout header="Create Product">
            <Head title="Create Product" />

            <div className="space-y-6">
                <section className="rounded-[1.75rem] border border-white/10 bg-[linear-gradient(135deg,rgba(8,15,30,0.95),rgba(18,28,50,0.92))] p-6 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="text-xs font-semibold uppercase tracking-[0.35em] text-cyan-300/70">Merchandise control</div>
                    <h2 className="mt-2 text-3xl font-black text-white">Create Product</h2>
                    <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-300">Publish a merch item or digital product inside the same Echo Panda admin shell.</p>
                </section>

                <div className="overflow-hidden rounded-[1.75rem] border border-white/10 bg-slate-950/50 shadow-2xl shadow-slate-950/20 backdrop-blur-sm">
                    <div className="p-6 text-gray-900 dark:text-gray-100">
                            <form onSubmit={submit} className="space-y-6">
                                <div>
                                    <InputLabel
                                        htmlFor="name"
                                        value="Product Name"
                                    />

                                    <TextInput
                                        id="name"
                                        type="text"
                                        name="name"
                                        value={data.name}
                                        className={controlClass}
                                        autoComplete="name"
                                        isFocused
                                        onChange={(e) =>
                                            setData('name', e.target.value)
                                        }
                                        required
                                    />

                                    <InputError
                                        message={errors.name}
                                        className="mt-2"
                                    />
                                </div>

                                <div>
                                    <InputLabel
                                        htmlFor="description"
                                        value="Description"
                                    />

                                    <textarea
                                        id="description"
                                        name="description"
                                        value={data.description}
                                        className={`${controlClass} min-h-32`}
                                        onChange={(e) =>
                                            setData(
                                                'description',
                                                e.target.value,
                                            )
                                        }
                                        rows={4}
                                    />

                                    <InputError
                                        message={errors.description}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel
                                            htmlFor="price"
                                            value="Price"
                                        />

                                        <TextInput
                                            id="price"
                                            type="number"
                                            name="price"
                                            value={data.price}
                                                className={controlClass}
                                            step="0.01"
                                            min="0"
                                            onChange={(e) =>
                                                setData('price', e.target.value)
                                            }
                                            required
                                        />

                                        <InputError
                                            message={errors.price}
                                            className="mt-2"
                                        />
                                    </div>

                                    <div>
                                        <InputLabel
                                            htmlFor="quantity"
                                            value="Quantity"
                                        />

                                        <TextInput
                                            id="quantity"
                                            type="number"
                                            name="quantity"
                                            value={data.quantity}
                                                className={controlClass}
                                            min="0"
                                            onChange={(e) =>
                                                setData(
                                                    'quantity',
                                                    e.target.value,
                                                )
                                            }
                                            required
                                        />

                                        <InputError
                                            message={errors.quantity}
                                            className="mt-2"
                                        />
                                    </div>
                                </div>

                                <div>
                                    <InputLabel htmlFor="sku" value="SKU" />

                                    <TextInput
                                        id="sku"
                                        type="text"
                                        name="sku"
                                        value={data.sku}
                                        className={controlClass}
                                        onChange={(e) =>
                                            setData('sku', e.target.value)
                                        }
                                    />

                                    <InputError
                                        message={errors.sku}
                                        className="mt-2"
                                    />
                                </div>

                                <div className="block">
                                    <label className="flex items-center">
                                        <Checkbox
                                            name="is_active"
                                            checked={data.is_active}
                                            onChange={(e) =>
                                                setData(
                                                    'is_active',
                                                    e.target.checked,
                                                )
                                            }
                                        />
                                        <span className="ms-2 text-sm text-slate-300">
                                            Active
                                        </span>
                                    </label>
                                </div>

                                <div className="flex items-center gap-4">
                                    <PrimaryButton disabled={processing}>
                                        Create Product
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

