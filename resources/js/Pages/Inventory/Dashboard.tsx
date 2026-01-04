import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Package,
    Truck,
    Layers,
    ArrowDownToLine,
    MapPin,
    Box
} from 'lucide-react';

export default function InventoryDashboard({ stats, stocks }: { stats: any, stocks: any }) {

    // Helper สร้าง Card เมนู
    const OperationCard = ({ title, count, type, icon: Icon, color }: any) => (
        <Link href={route('inventory.ops.index', type)}>
            <Card className="hover:shadow-lg transition-all cursor-pointer border-l-4" style={{ borderLeftColor: color }}>
                <CardContent className="p-6 flex items-center justify-between">
                    <div>
                        <div className="text-gray-500 text-sm font-medium uppercase tracking-wider">{title}</div>
                        <div className="text-3xl font-bold mt-2 text-gray-800">{count}</div>
                        <div className="text-xs text-gray-400 mt-1">To Process</div>
                    </div>
                    <div className={`p-4 rounded-full bg-opacity-10`} style={{ backgroundColor: color }}>
                        <Icon className="w-8 h-8" style={{ color: color }} />
                    </div>
                </CardContent>
            </Card>
        </Link>
    );

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Inventory Overview</h2>}
        >
            <Head title="Inventory Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                    {/* 1. Operation Overview Cards */}
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <OperationCard
                            title="Receipts"
                            count={stats.incoming}
                            type="incoming"
                            icon={ArrowDownToLine}
                            color="#10B981" // Green
                        />
                        <OperationCard
                            title="Internal Picking"
                            count={stats.picking}
                            type="picking"
                            icon={Layers}
                            color="#3B82F6" // Blue
                        />
                        <OperationCard
                            title="Packing"
                            count={stats.packing}
                            type="packing"
                            icon={Box}
                            color="#F59E0B" // Amber
                        />
                        <OperationCard
                            title="Delivery Orders"
                            count={stats.outgoing}
                            type="outgoing"
                            icon={Truck}
                            color="#6366F1" // Indigo
                        />
                    </div>

                    {/* 2. Current Stock Balance Table */}
                    <Card>
                        <CardHeader className="border-b bg-gray-50">
                            <div className="flex items-center gap-2">
                                <MapPin className="w-5 h-5 text-gray-500" />
                                <CardTitle>Current Stock Balance</CardTitle>
                            </div>
                        </CardHeader>
                        <CardContent className="p-0">
                            <div className="overflow-x-auto">
                                <table className="w-full text-sm text-left">
                                    <thead className="text-gray-500 bg-gray-50 uppercase border-b">
                                        <tr>
                                            <th className="px-6 py-3">Location</th>
                                            <th className="px-6 py-3">Product</th>
                                            <th className="px-6 py-3 text-right">On Hand</th>
                                            <th className="px-6 py-3 text-center">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {stocks.data.length > 0 ? (
                                            stocks.data.map((stock: any) => (
                                                <tr key={stock.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 font-medium text-indigo-600">
                                                        {stock.location?.name}
                                                    </td>
                                                    <td className="px-6 py-4">
                                                        <div className="font-bold text-gray-700">{stock.item?.name}</div>
                                                        <div className="text-xs text-gray-400">Code: {stock.item?.id}</div>
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-bold text-gray-800 text-lg">
                                                        {Number(stock.quantity).toLocaleString()}
                                                    </td>
                                                    <td className="px-6 py-4 text-center text-gray-500">
                                                        {stock.item?.uom?.symbol}
                                                    </td>
                                                </tr>
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan={4} className="px-6 py-10 text-center text-gray-400">
                                                    No stock available currently.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {/* Pagination (Simple) */}
                            {stocks.links && (
                                <div className="p-4 border-t flex justify-end gap-2">
                                    {stocks.links.map((link: any, key: number) => (
                                        link.url ? (
                                            <Link
                                                key={key}
                                                href={link.url}
                                                className={`px-3 py-1 rounded border text-xs ${link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 hover:bg-gray-50'}`}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : null
                                    ))}
                                </div>
                            )}
                        </CardContent>
                    </Card>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
