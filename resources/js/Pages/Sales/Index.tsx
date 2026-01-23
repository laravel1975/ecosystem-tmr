import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Plus, Search, FileText } from 'lucide-react';
import SaleNavigation from './Partials/SaleNavigation';

export default function SalesIndex({ orders }: { orders: any }) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200">Sales Orders</h2>}
            navigation={<SaleNavigation />}
        >
            <Head title="Sales Orders" />
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Toolbar */}
                    <div className="flex justify-between items-center mb-6">
                        <div className="relative w-64">
                            {/* Placeholder สำหรับ Search ในอนาคต */}
                            <Search className="absolute left-2 top-2.5 h-4 w-4 text-gray-400" />
                            <input type="text" placeholder="Search orders..." className="pl-8 h-9 w-full rounded-md border border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" disabled />
                        </div>
                        <Link href={route('sales.orders.create')}>
                            <Button className="bg-indigo-600 hover:bg-indigo-700">
                                <Plus className="w-4 h-4 mr-2" /> Create Quotation
                            </Button>
                        </Link>
                    </div>

                    {/* Table */}
                    <div className="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                        <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead className="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order No.</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                                    <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th className="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th className="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                {orders.data.map((order: any) => (
                                    <tr key={order.id} className="hover:bg-indigo-50/30 transition">
                                        <td className="px-6 py-4 whitespace-nowrap font-medium text-indigo-600 dark:text-indigo-400">
                                            {order.code}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                                            {order.customer.name}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {new Date(order.date_order).toLocaleDateString()}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                            {Number(order.total_amount).toLocaleString('th-TH', { style: 'currency', currency: 'THB' })}
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center">
                                            <span className={`px-2.5 py-0.5 text-xs font-medium rounded-full border ${order.status === 'confirmed'
                                                ? 'bg-blue-100 text-blue-800 border-blue-200'
                                                : order.status === 'draft'
                                                    ? 'bg-gray-100 text-gray-800 border-gray-200'
                                                    : 'bg-green-100 text-green-800 border-green-200'
                                                }`}>
                                                {order.status.toUpperCase()}
                                            </span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <Link href={route('sales.orders.show', order.id)} className="text-indigo-600 hover:text-indigo-900 inline-flex items-center">
                                                View <FileText className="w-3 h-3 ml-1" />
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                                {orders.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-6 py-10 text-center text-gray-500">No orders found.</td>
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
