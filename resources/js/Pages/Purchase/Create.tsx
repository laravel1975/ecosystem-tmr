import React, { FormEventHandler, useState, useEffect } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/select';
import { ChevronLeft, Plus, Trash2, Save } from 'lucide-react';

// Types
interface Vendor { id: number; name: string; }
interface Item { id: number; sku: string; name: string; uom_name: string; cost: number; }
interface OrderItem { item_id: string; quantity: number; price_unit: number; }

export default function CreatePurchaseOrder({ vendors, items }: { vendors: Vendor[], items: Item[] }) {
    const { data, setData, post, processing, errors } = useForm({
        vendor_id: '',
        date_order: new Date().toISOString().split('T')[0], // Today
        items: [{ item_id: '', quantity: 1, price_unit: 0 }] as OrderItem[], // เริ่มต้น 1 แถว
    });

    // คำนวณยอดรวม
    const totalAmount = data.items.reduce((sum, line) => sum + (line.quantity * line.price_unit), 0);

    // ฟังก์ชันเพิ่มแถว
    const addItem = () => {
        setData('items', [...data.items, { item_id: '', quantity: 1, price_unit: 0 }]);
    };

    // ฟังก์ชันลบแถว
    const removeItem = (index: number) => {
        const newItems = [...data.items];
        newItems.splice(index, 1);
        setData('items', newItems);
    };

    // ฟังก์ชันอัปเดตข้อมูลในแถว
    const updateItem = (index: number, field: keyof OrderItem, value: any) => {
        const newItems = [...data.items];
        // @ts-ignore
        newItems[index][field] = value;

        // ถ้าเปลี่ยน Item ให้ดึงราคา Cost มาใส่ auto
        if (field === 'item_id') {
            const selectedItem = items.find(i => i.id.toString() === value);
            if (selectedItem) {
                newItems[index].price_unit = Number(selectedItem.cost);
            }
        }
        setData('items', newItems);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('purchase.orders.store'));
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200">Create Purchase Order</h2>}>
            <Head title="New PO" />
            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit}>
                        <Card className="mb-6">
                            <CardHeader><CardTitle>Order Details</CardTitle></CardHeader>
                            <CardContent className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label>Vendor <span className="text-red-500">*</span></Label>
                                    <Select onValueChange={v => setData('vendor_id', v)}>
                                        <SelectTrigger><SelectValue placeholder="Select Vendor" /></SelectTrigger>
                                        <SelectContent>
                                            {vendors.map(v => <SelectItem key={v.id} value={v.id.toString()}>{v.name}</SelectItem>)}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={errors.vendor_id} />
                                </div>
                                <div>
                                    <Label>Order Date</Label>
                                    <Input type="date" value={data.date_order} onChange={e => setData('date_order', e.target.value)} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between">
                                <CardTitle>Order Lines</CardTitle>
                                <Button type="button" size="sm" onClick={addItem} variant="outline"><Plus className="w-4 h-4 mr-1"/> Add Item</Button>
                            </CardHeader>
                            <CardContent>
                                <table className="w-full text-sm text-left">
                                    <thead className="text-gray-500 bg-gray-50 dark:bg-gray-700 uppercase">
                                        <tr>
                                            <th className="px-4 py-2">Product</th>
                                            <th className="px-4 py-2 w-24">Qty</th>
                                            <th className="px-4 py-2 w-32">Unit Price</th>
                                            <th className="px-4 py-2 w-32 text-right">Subtotal</th>
                                            <th className="px-4 py-2 w-10"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {data.items.map((line, index) => (
                                            <tr key={index} className="border-b">
                                                <td className="p-2">
                                                    <Select value={line.item_id} onValueChange={v => updateItem(index, 'item_id', v)}>
                                                        <SelectTrigger><SelectValue placeholder="Select Product" /></SelectTrigger>
                                                        <SelectContent>
                                                            {items.map(i => <SelectItem key={i.id} value={i.id.toString()}>{i.sku} - {i.name}</SelectItem>)}
                                                        </SelectContent>
                                                    </Select>
                                                    <InputError message={errors[`items.${index}.item_id` as any]} />
                                                </td>
                                                <td className="p-2">
                                                    <Input type="number" value={line.quantity} onChange={e => updateItem(index, 'quantity', Number(e.target.value))} />
                                                </td>
                                                <td className="p-2">
                                                    <Input type="number" value={line.price_unit} onChange={e => updateItem(index, 'price_unit', Number(e.target.value))} />
                                                </td>
                                                <td className="p-2 text-right font-mono">
                                                    {(line.quantity * line.price_unit).toLocaleString()}
                                                </td>
                                                <td className="p-2 text-center">
                                                    {data.items.length > 1 && (
                                                        <button type="button" onClick={() => removeItem(index)} className="text-red-500 hover:text-red-700">
                                                            <Trash2 className="w-4 h-4" />
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colSpan={3} className="text-right font-bold p-4">Total Amount:</td>
                                            <td className="text-right font-bold p-4 text-lg text-indigo-600">
                                                {totalAmount.toLocaleString('th-TH', { style: 'currency', currency: 'THB' })}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end mt-6 gap-2">
                            <Link href={route('purchase.orders.index')} className="px-4 py-2 bg-gray-200 rounded-md">Cancel</Link>
                            <Button type="submit" disabled={processing}><Save className="w-4 h-4 mr-2"/> Save Order</Button>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
