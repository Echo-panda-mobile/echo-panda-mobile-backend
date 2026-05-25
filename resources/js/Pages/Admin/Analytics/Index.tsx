import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';

interface Props extends PageProps {
    metrics: any;
}

export default function Index({ metrics }: Props) {
    return (
        <AuthenticatedLayout header="Analytics">
            <Head title="Analytics" />

            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="p-4 bg-white shadow rounded dark:bg-gray-800">
                    <h4 className="text-sm text-gray-500">Total Users</h4>
                    <div className="text-2xl font-bold">{metrics.total_users}</div>
                </div>
                <div className="p-4 bg-white shadow rounded dark:bg-gray-800">
                    <h4 className="text-sm text-gray-500">Total Artists</h4>
                    <div className="text-2xl font-bold">{metrics.total_artists}</div>
                </div>
                <div className="p-4 bg-white shadow rounded dark:bg-gray-800">
                    <h4 className="text-sm text-gray-500">Total Songs</h4>
                    <div className="text-2xl font-bold">{metrics.total_songs}</div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
