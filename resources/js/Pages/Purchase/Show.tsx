import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, CheckCircle } from 'lucide-react';

export default function PurchaseShow({ order }: { order: any }) {
    const { post, processing } = useForm();

    const confirmOrder = () => {
        if (confirm('Are you sure you want to confirm this order? This will generate a stock receipt.')) {
            post(route('purchase.orders.confirm', order.id));
        }
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl">Purchase Order: {order.code}</h2>}>
            <Head title={`PO ${order.code}`} />
            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">
                    {/* Header Actions */}
                    <div className="flex justify-between items-center mb-6">
                        <Link href={route('purchase.orders.index')} className="flex items-center text-gray-500 hover:text-gray-700">
                            <ArrowLeft className="w-4 h-4 mr-1" /> Back to List
                        </Link>
                        <div className="space-x-2">
                            {order.status === 'draft' && (
                                <Button onClick={confirmOrder} disabled={processing} className="bg-green-600 hover:bg-green-700">
                                    <CheckCircle className="w-4 h-4 mr-2" /> Confirm Order
                                </Button>
                            )}
                            {order.status === 'confirmed' && (
                                <span className="text-green-600 font-bold border border-green-200 bg-green-50 px-4 py-2 rounded-md">
                                    ✅ Order Confirmed (Waiting for Receipt)
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Order Info */}
                    <Card className="mb-6">
                        <CardHeader><CardTitle>Vendor Information</CardTitle></CardHeader>
                        <CardContent className="grid grid-cols-2 gap-4">
                            <div>
                                <p className="text-sm text-gray-500">Vendor</p>
                                <p className="font-semibold text-lg">{order.vendor.name}</p>
                            </div>
                            <div>
                                <p className="text-sm text-gray-500">Order Date</p>
                                <p className="font-semibold">{order.date_order}</p>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Order Lines */}
                    <Card>
                        <CardHeader><CardTitle>Products</CardTitle></CardHeader>
                        <CardContent>
                            <table className="w-full text-sm text-left">
                                <thead className="text-gray-500 bg-gray-50 uppercase">
                                    <tr>
                                        <th className="px-4 py-2">Product</th>
                                        <th className="px-4 py-2 text-right">Qty</th>
                                        <th className="px-4 py-2 text-right">Unit Price</th>
                                        <th className="px-4 py-2 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {order.lines.map((line: any) => (
                                        <tr key={line.id} className="border-b">
                                            <td className="px-4 py-2">{line.item.name}</td>
                                            <td className="px-4 py-2 text-right">{Number(line.quantity).toLocaleString()} {line.item.uom.symbol}</td>
                                            <td className="px-4 py-2 text-right">{Number(line.price_unit).toLocaleString()}</td>
                                            <td className="px-4 py-2 text-right font-medium">{Number(line.subtotal).toLocaleString()}</td>
                                        </tr>
                                    ))}
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colSpan={3} className="text-right font-bold p-4">Grand Total</td>
                                        <td className="text-right font-bold p-4 text-lg text-indigo-600">
                                            {Number(order.total_amount).toLocaleString('th-TH', { style: 'currency', currency: 'THB' })}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
