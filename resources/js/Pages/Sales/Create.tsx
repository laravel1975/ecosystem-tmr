import React, { FormEventHandler } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { Plus, Trash2, Save, ShoppingCart } from 'lucide-react';

// Types
interface Customer { id: number; name: string; }
interface Item { id: number; sku: string; name: string; uom_name: string; price: number; } // Note: price คือราคาขาย
interface OrderItem { item_id: string; quantity: number; price_unit: number; }

export default function CreateSalesOrder({ customers, items }: { customers: Customer[], items: Item[] }) {
    const { data, setData, post, processing, errors } = useForm({
        customer_id: '',
        date_order: new Date().toISOString().split('T')[0],
        items: [{ item_id: '', quantity: 1, price_unit: 0 }] as OrderItem[],
    });

    // คำนวณยอดรวม Real-time
    const totalAmount = data.items.reduce((sum, line) => sum + (line.quantity * line.price_unit), 0);

    const addItem = () => {
        setData('items', [...data.items, { item_id: '', quantity: 1, price_unit: 0 }]);
    };

    const removeItem = (index: number) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    const updateItem = (index: number, field: keyof OrderItem, value: any) => {
        const newItems = [...data.items];
        // @ts-ignore
        newItems[index][field] = value;

        // Auto-fill Price เมื่อเลือกสินค้า
        if (field === 'item_id') {
            const selectedItem = items.find(i => i.id.toString() === value);
            if (selectedItem) {
                newItems[index].price_unit = Number(selectedItem.price);
            }
        }
        setData('items', newItems);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('sales.orders.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200">New Sales Order</h2>}>
            <Head title="New SO" />
            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit}>
                        {/* Header Info */}
                        <Card className="mb-6 border-l-4 border-l-indigo-500">
                            <CardHeader><CardTitle className="flex items-center gap-2"><ShoppingCart className="w-5 h-5"/> Customer & Date</CardTitle></CardHeader>
                            <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <Label>Customer <span className="text-red-500">*</span></Label>
                                    <Select onValueChange={v => setData('customer_id', v)}>
                                        <SelectTrigger><SelectValue placeholder="Select Customer" /></SelectTrigger>
                                        <SelectContent>
                                            {customers.map(c => <SelectItem key={c.id} value={c.id.toString()}>{c.name}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.customer_id} />
                                </div>
                                <div>
                                    <Label>Order Date</Label>
                                    <Input type="date" value={data.date_order} onChange={e => setData('date_order', e.target.value)} />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Order Lines */}
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between bg-gray-50 dark:bg-gray-800/50">
                                <CardTitle className="text-base">Order Lines</CardTitle>
                                <Button type="button" size="sm" onClick={addItem} variant="outline" className="text-indigo-600 border-indigo-200 hover:bg-indigo-50">
                                    <Plus className="w-4 h-4 mr-1"/> Add Product
                                </Button>
                            </CardHeader>
                            <CardContent className="p-0">
                                <table className="w-full text-sm text-left">
                                    <thead className="text-gray-500 bg-gray-100 dark:bg-gray-700 uppercase text-xs">
                                        <tr>
                                            <th className="px-4 py-3">Product</th>
                                            <th className="px-4 py-3 w-24 text-right">Qty</th>
                                            <th className="px-4 py-3 w-32 text-right">Price</th>
                                            <th className="px-4 py-3 w-32 text-right">Subtotal</th>
                                            <th className="px-4 py-3 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {data.items.map((line, index) => (
                                            <tr key={index}>
                                                <td className="p-3">
                                                    <Select value={line.item_id} onValueChange={v => updateItem(index, 'item_id', v)}>
                                                        <SelectTrigger className="border-0 shadow-none focus:ring-0 pl-0 font-medium">
                                                            <SelectValue placeholder="Select Product..." />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {items.map(i => <SelectItem key={i.id} value={i.id.toString()}>{i.sku} - {i.name}</SelectItem>)}
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError message={errors[`items.${index}.item_id` as any]} />
                                                </td>
                                                <td className="p-3">
                                                    <Input
                                                        type="number"
                                                        className="text-right h-8"
                                                        value={line.quantity}
                                                        onChange={e => updateItem(index, 'quantity', Number(e.target.value))}
                                                    />
                                                </td>
                                                <td className="p-3">
                                                    <Input
                                                        type="number"
                                                        className="text-right h-8"
                                                        value={line.price_unit}
                                                        onChange={e => updateItem(index, 'price_unit', Number(e.target.value))}
                                                    />
                                                </td>
                                                <td className="p-3 text-right font-mono text-gray-700">
                                                    {(line.quantity * line.price_unit).toLocaleString(undefined, {minimumFractionDigits: 2})}
                                                </td>
                                                <td className="p-3 text-center">
                                                    {data.items.length > 1 && (
                                                        <button type="button" onClick={() => removeItem(index)} className="text-gray-400 hover:text-red-500 transition">
                                                            <Trash2 className="w-4 h-4" />
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr className="bg-indigo-50 dark:bg-indigo-900/20">
                                            <td colSpan={3} className="text-right font-bold p-4 text-gray-600 dark:text-gray-300">Total Amount:</td>
                                            <td className="text-right font-bold p-4 text-xl text-indigo-700 dark:text-indigo-400">
                                                {totalAmount.toLocaleString('th-TH', { style: 'currency', currency: 'THB' })}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end mt-6 gap-3">
                            <Link href={route('sales.orders.index')} className="px-4 py-2 bg-white border border-gray-300 rounded-md text-gray-700 shadow-sm hover:bg-gray-50">Cancel</Link>
                            <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-md">
                                <Save className="w-4 h-4 mr-2"/> Confirm & Save
                            </Button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
