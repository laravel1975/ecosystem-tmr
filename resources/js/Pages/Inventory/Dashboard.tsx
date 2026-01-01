import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { PageProps } from '@/types';

// กำหนด Type ของข้อมูลที่ส่งมาจาก Controller
interface InventoryItemData {
    id: number;
    sku: string;
    name: string;
    category: string;
    uom: string;
    price: number;
    on_hand: number;
}

export default function InventoryDashboard({ auth, items }: PageProps<{ items: InventoryItemData[] }>) {
    return (
        <AuthenticatedLayout
            user={auth.user}
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Inventory Dashboard</h2>}
        >
            <Head title="Inventory" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">

                            <div className="flex justify-between items-center mb-6">
                                <h3 className="text-lg font-medium">Stock Overview</h3>
                                <button className="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-500">
                                    + New Product
                                </button>
                            </div>

                            {/* ตารางสินค้า */}
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead className="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">SKU</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Category</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">On Hand</th>
                                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        {items.map((item) => (
                                            <tr key={item.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">{item.sku}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">{item.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{item.category}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-bold">
                                                    {item.on_hand} <span className="text-xs text-gray-500 font-normal">{item.uom}</span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right">{item.price}</td>
                                            </tr>
                                        ))}
                                        {items.length === 0 && (
                                            <tr>
                                                <td colSpan={5} className="px-6 py-4 text-center text-gray-500">No items found.</td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
