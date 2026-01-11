import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, Save } from 'lucide-react';
import InventoryNavigation from '../Presentation/Partials/InventoryNavigation';

export default function OperationsEdit({ transfer }: { transfer: any }) {

    // ตั้งค่า Form State
    const { data, setData, put, processing, errors } = useForm({
        scheduled_date: transfer.scheduled_date || '',
        note: transfer.note || '',
        moves: transfer.moves.map((m: any) => ({
            id: m.id,
            item_name: m.item.name,
            uom: m.item.uom.symbol,
            quantity_demand: m.quantity_demand,
            quantity_done: m.quantity_done // เอาไว้กรอกยอดรับจริง
        })),
    });

    const handleSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        put(route('inventory.ops.update', transfer.id));
    };

    // ฟังก์ชันเปลี่ยนค่าในตาราง (Moves)
    const handleMoveChange = (index: number, field: string, value: any) => {
        const newMoves = [...data.moves];
        newMoves[index][field] = value;
        setData('moves', newMoves);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800">Edit: {transfer.reference}</h2>}
            navigation={<InventoryNavigation />}
        >
            <Head title={`Edit ${transfer.reference}`} />

            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={handleSubmit}>

                        {/* Action Bar */}
                        <div className="flex justify-between items-center mb-6">
                            <Link href={route('inventory.ops.show', transfer.id)} className="flex items-center text-gray-500 hover:text-gray-700">
                                <ArrowLeft className="w-4 h-4 mr-1" /> Cancel
                            </Link>
                            <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 text-white">
                                <Save className="w-4 h-4 mr-2" /> Save Changes
                            </Button>
                        </div>

                        {/* General Info */}
                        <Card className="mb-6">
                            <CardHeader><CardTitle>General Information</CardTitle></CardHeader>
                            <CardContent className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <Label>Reference</Label>
                                    <Input value={transfer.reference} disabled className="bg-gray-100" />
                                </div>
                                <div>
                                    <Label>Contact</Label>
                                    <Input value={transfer.contact?.name || '-'} disabled className="bg-gray-100" />
                                </div>
                                <div>
                                    <Label>Scheduled Date</Label>
                                    <Input
                                        type="date"
                                        value={data.scheduled_date}
                                        onChange={e => setData('scheduled_date', e.target.value)}
                                    />
                                    {errors.scheduled_date && <div className="text-red-500 text-xs mt-1">{errors.scheduled_date}</div>}
                                </div>
                                <div>
                                    <Label>Note</Label>
                                    <Input
                                        value={data.note}
                                        onChange={e => setData('note', e.target.value)}
                                        placeholder="Add notes..."
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        {/* Lines Table */}
                        <Card>
                            <CardHeader><CardTitle>Operations Lines</CardTitle></CardHeader>
                            <CardContent>
                                <table className="w-full text-sm text-left">
                                    <thead className="text-gray-500 bg-gray-50 uppercase">
                                        <tr>
                                            <th className="px-4 py-2">Product</th>
                                            <th className="px-4 py-2 text-right">Demand (Plan)</th>
                                            <th className="px-4 py-2 text-right w-32">Done (Actual)</th>
                                            <th className="px-4 py-2">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-100">
                                        {data.moves.map((move: any, index: number) => (
                                            <tr key={move.id}>
                                                <td className="px-4 py-3 font-medium">{move.item_name}</td>
                                                <td className="px-4 py-3 text-right">
                                                    <Input
                                                        type="number"
                                                        className="text-right h-8"
                                                        value={move.quantity_demand}
                                                        onChange={e => handleMoveChange(index, 'quantity_demand', e.target.value)}
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-right">
                                                    <Input
                                                        type="number"
                                                        className={`text-right h-8 ${move.quantity_done < move.quantity_demand ? 'border-orange-400 bg-orange-50' : 'border-green-400 bg-green-50'}`}
                                                        value={move.quantity_done}
                                                        onChange={e => handleMoveChange(index, 'quantity_done', e.target.value)}
                                                    />
                                                </td>
                                                <td className="px-4 py-3 text-gray-500">{move.uom}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
