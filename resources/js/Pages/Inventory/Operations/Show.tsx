import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { ArrowLeft, CheckCircle, MapPin, User, Calendar } from 'lucide-react';

export default function OperationsShow({ transfer }: { transfer: any }) {
    const { post, processing } = useForm();

    const handleValidate = () => {
        if (confirm('Confirm validation? This will process stock moves and may create the next operation document.')) {
            post(route('inventory.ops.validate', transfer.id));
        }
    };

    return (
        <AuthenticatedLayout header={<h2 className="font-semibold text-xl text-gray-800">Operation: {transfer.reference}</h2>}>
            <Head title={transfer.reference} />
            <div className="py-12">
                <div className="max-w-5xl mx-auto sm:px-6 lg:px-8">

                    {/* Top Action Bar */}
                    <div className="flex justify-between items-center mb-6">
                        <Link href={route('inventory.ops.index', transfer.type)} className="flex items-center text-gray-500 hover:text-gray-700">
                            <ArrowLeft className="w-4 h-4 mr-1" /> Back to List
                        </Link>
                        <div className="flex gap-2">
                            {/* ปุ่ม Edit */}
                            {transfer.status !== 'done' && (
                                <Link href={route('inventory.ops.edit', transfer.id)}>
                                    <Button variant="outline" className="border-gray-300">
                                        Edit
                                    </Button>
                                </Link>
                            )}
                            
                            {transfer.status !== 'done' ? (
                                <Button onClick={handleValidate} disabled={processing} className="bg-green-600 hover:bg-green-700 text-white">
                                    <CheckCircle className="w-4 h-4 mr-2" /> Validate
                                </Button>
                            ) : (
                                <span className="px-4 py-2 bg-gray-100 text-gray-500 font-bold rounded border border-gray-300">
                                    ✅ Done
                                </span>
                            )}
                        </div>
                    </div>

                    {/* Header Info */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <Card className="md:col-span-2 border-t-4 border-t-indigo-500">
                            <CardHeader className="pb-2"><CardTitle className="text-sm text-gray-500 flex items-center gap-2"><User className="w-4 h-4" /> Contact / Partner</CardTitle></CardHeader>
                            <CardContent>
                                <div className="text-lg font-bold text-gray-800">{transfer.contact ? transfer.contact.name : 'Unknown'}</div>
                                <div className="text-sm text-gray-500">{transfer.contact?.address}</div>
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-2"><CardTitle className="text-sm text-gray-500 flex items-center gap-2"><Calendar className="w-4 h-4" /> Info</CardTitle></CardHeader>
                            <CardContent className="space-y-2">
                                <div className="flex justify-between text-sm"><span>Date:</span> <span className="font-medium">{transfer.scheduled_date}</span></div>
                                <div className="flex justify-between text-sm"><span>Type:</span> <span className="font-medium uppercase">{transfer.type}</span></div>
                                <div className="flex justify-between text-sm"><span>Status:</span> <span className="font-bold text-blue-600 uppercase">{transfer.status}</span></div>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Locations */}
                    <Card className="mb-6 bg-gray-50/50">
                        <CardContent className="p-4 flex items-center justify-between">
                            <div className="flex flex-col">
                                <span className="text-xs text-gray-400 uppercase font-bold mb-1">From Source</span>
                                <div className="flex items-center gap-2 font-semibold text-gray-700">
                                    <MapPin className="w-4 h-4 text-red-500" /> {transfer.source_location?.name}
                                </div>
                            </div>
                            <div className="flex-1 border-b border-dashed border-gray-300 mx-8 relative top-1"></div>
                            <div className="flex flex-col text-right">
                                <span className="text-xs text-gray-400 uppercase font-bold mb-1">To Destination</span>
                                <div className="flex items-center gap-2 font-semibold text-gray-700 justify-end">
                                    {transfer.destination_location?.name} <MapPin className="w-4 h-4 text-green-500" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Items Table */}
                    <Card>
                        <CardHeader><CardTitle>Product Moves</CardTitle></CardHeader>
                        <CardContent>
                            <table className="w-full text-sm text-left">
                                <thead className="text-gray-500 bg-gray-50 uppercase">
                                    <tr>
                                        <th className="px-4 py-2">Product</th>
                                        <th className="px-4 py-2 text-right">Demand</th>
                                        <th className="px-4 py-2 text-right">Done</th>
                                        <th className="px-4 py-2 text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {transfer.moves.map((move: any) => (
                                        <tr key={move.id}>
                                            <td className="px-4 py-3 font-medium">{move.item.name}</td>
                                            <td className="px-4 py-3 text-right text-gray-500">{Number(move.quantity_demand).toLocaleString()} {move.item.uom.symbol}</td>
                                            <td className="px-4 py-3 text-right font-bold text-indigo-600">
                                                {/* ถ้ายังไม่ Done ให้โชว์ Input หรือค่า Demand เพื่อบอกว่าจะรับเท่าไหร่ */}
                                                {transfer.status === 'done' ? Number(move.quantity_done).toLocaleString() : (
                                                    <span className="text-gray-400 italic">Wait to validate</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3 text-center">
                                                {move.state === 'done' ? <span className="text-green-600">Done</span> : <span className="text-orange-500">Reserved</span>}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </CardContent>
                    </Card>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
