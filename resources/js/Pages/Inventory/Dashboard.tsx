import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { PageProps } from '@/types';
import { Truck, CheckCircle, PackagePlus, ArrowUpRight, ArrowDownLeft } from 'lucide-react';

// --- Types Definition ---

// ข้อมูลสินค้าในตารางหลัก
interface InventoryItemData {
    id: number;
    sku: string;
    name: string;
    category: string;
    uom: string;
    price: number;
    on_hand: number;
}

// ข้อมูลรายการรอรับของ (Incoming)
interface IncomingMove {
    id: number;
    item_name: string;
    qty: number;
    uom: string;
    source: string;
    date: string;
}

// รวม Props ทั้งหมด
type DashboardProps = PageProps<{
    items: InventoryItemData[];
    incomingMoves: IncomingMove[]; // รับค่า Incoming Moves มาจาก Controller
}>;

export default function InventoryDashboard({ auth, items, incomingMoves }: DashboardProps) {

    // ใช้ useForm สำหรับการยิง Request กดรับของ (Validate)
    const { post, processing } = useForm();

    // ฟังก์ชันกดปุ่ม Receive ในกล่องแจ้งเตือน
    const handleReceive = (id: number) => {
        if (window.confirm('Confirm receipt of these goods into stock?')) {
            post(route('inventory.moves.validate', id));
        }
    };

    // ฟังก์ชันจัดรูปแบบเงิน (THB)
    const formatCurrency = (amount: number) => {
        return new Intl.NumberFormat('th-TH', { style: 'currency', currency: 'THB' }).format(amount);
    };

    return (
        <AuthenticatedLayout
            user={auth.user}
            header={
                <div className="flex justify-between items-center">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                        Inventory Dashboard
                    </h2>
                </div>
            }
        >
            <Head title="Inventory" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* ---------------------------------------------------------
                        SECTION 1: Action Buttons (ปุ่มทางลัด)
                       --------------------------------------------------------- */}
                    <div className="flex flex-wrap gap-3 justify-end">
                        <Link
                            href={route('inventory.operations.receive')}
                            className="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            <ArrowDownLeft className="w-4 h-4 mr-2 text-green-600" />
                            Direct Receive
                        </Link>

                        <Link
                            href={route('inventory.operations.delivery')}
                            className="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150"
                        >
                            <ArrowUpRight className="w-4 h-4 mr-2 text-orange-600" />
                            Direct Delivery
                        </Link>

                        <Link
                            href={route('inventory.items.create')}
                            className="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
                        >
                            <PackagePlus className="w-4 h-4 mr-2" />
                            New Product
                        </Link>
                    </div>

                    {/* ---------------------------------------------------------
                        SECTION 2: Pending Receipts (งานรอรับของ) - แสดงเฉพาะเมื่อมีข้อมูล
                       --------------------------------------------------------- */}
                    {incomingMoves && incomingMoves.length > 0 && (
                        <div className="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-6 animate-fade-in-up">
                            <h3 className="text-lg font-bold text-orange-800 dark:text-orange-300 flex items-center mb-4">
                                <Truck className="w-5 h-5 mr-2" /> Pending Receipts (Incoming)
                            </h3>
                            <div className="bg-white dark:bg-gray-800 rounded shadow overflow-hidden border border-orange-100 dark:border-gray-700">
                                <table className="min-w-full text-sm divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead className="bg-gray-50 dark:bg-gray-700">
                                        <tr>
                                            <th className="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Expected Date</th>
                                            <th className="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Source / Vendor</th>
                                            <th className="px-4 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Product</th>
                                            <th className="px-4 py-3 text-right font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                            <th className="px-4 py-3 text-center font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100 dark:divide-gray-700">
                                        {incomingMoves.map(move => (
                                            <tr key={move.id} className="hover:bg-orange-50/50 dark:hover:bg-gray-700/50 transition">
                                                <td className="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-gray-300">{move.date}</td>
                                                <td className="px-4 py-3 whitespace-nowrap text-gray-500 dark:text-gray-400">{move.source}</td>
                                                <td className="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-gray-100">{move.item_name}</td>
                                                <td className="px-4 py-3 whitespace-nowrap text-right font-bold text-orange-600 dark:text-orange-400">
                                                    {move.qty} <span className="text-xs font-normal text-gray-500">{move.uom}</span>
                                                </td>
                                                <td className="px-4 py-3 text-center">
                                                    <button
                                                        onClick={() => handleReceive(move.id)}
                                                        disabled={processing}
                                                        className="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-medium rounded shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-green-500"
                                                    >
                                                        <CheckCircle className="w-3 h-3 mr-1" />
                                                        Receive
                                                    </button>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {/* ---------------------------------------------------------
                        SECTION 3: Stock Overview (ตารางสินค้าคงเหลือ)
                       --------------------------------------------------------- */}
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3 className="text-lg font-medium mb-4">Stock Overview</h3>

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
                                            <tr key={item.id} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600 dark:text-indigo-400">
                                                    {item.sku}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm">{item.name}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">{item.category}</td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right font-bold">
                                                    {item.on_hand} <span className="text-xs text-gray-500 font-normal">{item.uom}</span>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-right">
                                                    {formatCurrency(item.price)}
                                                </td>
                                            </tr>
                                        ))}
                                        {items.length === 0 && (
                                            <tr>
                                                <td colSpan={5} className="px-6 py-8 text-center text-gray-500">
                                                    No items found. Click "+ New Product" to start.
                                                </td>
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
