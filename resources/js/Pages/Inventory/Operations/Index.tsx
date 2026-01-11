import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Badge } from '@/Components/ui/badge'; // สมมติว่ามี หรือใช้ span class ปกติ
import { Button } from '@/Components/ui/button';
import { FileText, ArrowRight } from 'lucide-react';
import InventoryNavigation from '../Presentation/Partials/InventoryNavigation';

export default function OperationsIndex({ type, transfers }: { type: string, transfers: any }) {

    // Mapping ชื่อหัวข้อให้ตรงกับ 3 เมนูที่คุณต้องการ
    const titles: Record<string, string> = {
        picking: 'Pick Operations',
        packing: 'Pack Operations',
        outgoing: 'Delivery Orders',
        incoming: 'Receipts',
        internal: 'Internal Transfers'
    };

    const title = titles[type] || 'Operations';

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800">{title}</h2>}
            navigation={<InventoryNavigation />}
        >
            <Head title={title} />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white shadow-sm sm:rounded-lg overflow-hidden border border-gray-200">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Reference</th>
                                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Contact</th>
                                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Source</th>
                                    <th className="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Destination</th>
                                    <th className="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase">Status</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {transfers.data.map((doc: any) => (
                                    <tr key={doc.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 font-bold text-indigo-600 flex items-center gap-2">
                                            <FileText className="w-4 h-4" /> {doc.reference}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-700">
                                            {doc.contact ? doc.contact.name : '-'}
                                        </td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{doc.source_location?.name}</td>
                                        <td className="px-6 py-4 text-sm text-gray-500">{doc.destination_location?.name}</td>
                                        <td className="px-6 py-4 text-center">
                                            <span className={`px-2 py-1 rounded-full text-xs font-semibold ${doc.status === 'done'
                                                    ? 'bg-green-100 text-green-800'
                                                    : 'bg-blue-100 text-blue-800'
                                                }`}>
                                                {doc.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <Link href={route('inventory.ops.show', doc.id)}>
                                                <Button size="sm" variant="outline" className="text-indigo-600 border-indigo-200 hover:bg-indigo-50">
                                                    Process <ArrowRight className="w-3 h-3 ml-1" />
                                                </Button>
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {transfers.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-8 text-center text-gray-400">
                                            No operations found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
