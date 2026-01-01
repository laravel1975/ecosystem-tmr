import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';

export default function OperationsIndex({ type, transfers }) {
    // Mapping ชื่อหัวข้อให้สวยงาม
    const titles: Record<string, string> = {
        incoming: 'Receipts (Inbound)',
        outgoing: 'Delivery Orders (Outbound)',
        picking:  'Pick Operations',
        packing:  'Pack Operations',
        internal: 'Internal Transfers'
    };

    const title = titles[type] || 'Operations';

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl">{title}</h2>}>
            <Head title={title} />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ref</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">To</th>
                                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {transfers.data.map((doc: any) => (
                                    <tr key={doc.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-4 font-bold text-indigo-600">{doc.reference}</td>
                                        <td className="px-6 py-4 text-sm">{doc.source_location?.name}</td>
                                        <td className="px-6 py-4 text-sm">{doc.destination_location?.name}</td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${
                                                doc.status === 'done' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'
                                            }`}>
                                                {doc.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link href={route('inventory.ops.show', doc.id)} className="text-indigo-600 hover:underline font-medium">
                                                Manage
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
