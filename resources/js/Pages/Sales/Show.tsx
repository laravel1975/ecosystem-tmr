import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, CheckCircle, Truck, User, Calendar } from 'lucide-react';

export default function SalesShow({ order }: { order: any }) {
    const { post, processing } = useForm();

    const confirmOrder = () => {
        if (confirm('Confirm this Sales Order? Stock will be reserved and a delivery order will be created.')) {
            post(route('sales.orders.confirm', order.id));
        }
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Sales Order: {order.code}</h2>}>
            <Head title={`SO ${order.code}`} />
            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">

                    {/* Top Action Bar */}
                    <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                        <Link href={route('sales.orders.index')} className="flex items-center text-gray-500 hover:text-gray-700 text-sm">
                            <ArrowLeft className="w-4 h-4 mr-1" /> Back to Orders
                        </Link>

                        <div className="flex items-center gap-3">
                            <span className={`px-3 py-1 text-sm font-bold rounded-full border ${
                                order.status === 'confirmed' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                order.status === 'draft' ? 'bg-gray-100 text-gray-600 border-gray-200' :
                                'bg-green-50 text-green-700 border-green-200'
                            }`}>
                                Status: {order.status.toUpperCase()}
                            </span>

                            {order.status === 'draft' && (
                                <Button onClick={confirmOrder} disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-md">
                                    <CheckCircle className="w-4 h-4 mr-2" /> Confirm Sales Order
                                </Button>
                            )}
                            {order.status === 'confirmed' && (
                                <div className="flex items-center text-orange-600 bg-orange-50 px-3 py-1.5 rounded-md border border-orange-100 text-sm font-medium">
                                    <Truck className="w-4 h-4 mr-2" /> Waiting for Delivery
                                </div>
                            )}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        {/* Customer Card */}
                        <Card className="md:col-span-2 border-t-4 border-t-indigo-500 shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <User className="w-4 h-4" /> Customer Information
                                </CardTitle>
                            </CardHeader>
                            <CardContent>
                                <p className="text-xl font-bold text-gray-800">{order.customer.name}</p>
                                <p className="text-gray-600 text-sm mt-1">{order.customer.address || 'No address provided'}</p>
                                <div className="mt-2 text-sm text-gray-500">
                                    {order.customer.email && <span>{order.customer.email} • </span>}
                                    {order.customer.phone}
                                </div>
                            </CardContent>
                        </Card>

                        {/* Order Meta Card */}
                        <Card className="shadow-sm">
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm font-medium text-gray-500 flex items-center gap-2">
                                    <Calendar className="w-4 h-4" /> Order Details
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-3">
                                <div className="flex justify-between">
                                    <span className="text-gray-500 text-sm">Order Date</span>
                                    <span className="font-medium">{new Date(order.date_order).toLocaleDateString()}</span>
                                </div>
                                <div className="flex justify-between">
                                    <span className="text-gray-500 text-sm">Delivery Expected</span>
                                    <span className="font-medium">
                                        {order.date_delivery_expected ? new Date(order.date_delivery_expected).toLocaleDateString() : '-'}
                                    </span>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Order Lines Table */}
                    <Card className="shadow-sm overflow-hidden border border-gray-200">
                        <div className="bg-gray-50 px-6 py-4 border-b border-gray-200">
                            <h3 className="font-semibold text-gray-700">Order Lines</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm text-left">
                                <thead className="text-gray-500 bg-white border-b uppercase text-xs">
                                    <tr>
                                        <th className="px-6 py-3 font-medium">Product</th>
                                        <th className="px-6 py-3 font-medium text-right">Quantity</th>
                                        <th className="px-6 py-3 font-medium text-right">Unit Price</th>
                                        <th className="px-6 py-3 font-medium text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100 bg-white">
                                    {order.lines.map((line: any) => (
                                        <tr key={line.id} className="hover:bg-gray-50/50">
                                            <td className="px-6 py-4 font-medium text-gray-900">{line.item.name} <span className="text-gray-400 font-normal text-xs ml-1">({line.item.sku})</span></td>
                                            <td className="px-6 py-4 text-right">{Number(line.quantity).toLocaleString()} <span className="text-gray-400 text-xs">{line.item.uom.symbol}</span></td>
                                            <td className="px-6 py-4 text-right">{Number(line.price_unit).toLocaleString()}</td>
                                            <td className="px-6 py-4 text-right font-medium text-gray-800">{Number(line.subtotal).toLocaleString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot className="bg-gray-50 border-t border-gray-200">
                                    <tr>
                                        <td colSpan={3} className="text-right font-bold px-6 py-4 text-gray-600">Grand Total</td>
                                        <td className="text-right font-bold px-6 py-4 text-xl text-indigo-700">
                                            {Number(order.total_amount).toLocaleString('th-TH', { style: 'currency', currency: 'THB' })}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
