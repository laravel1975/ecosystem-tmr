import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    ArrowLeft,
    ArrowRight,
    CheckCircle,
    Printer,
    FileText,
    Truck,
    Package,
    Layers,
    MapPin,
    User,
    Calendar
} from 'lucide-react';

export default function OperationsShow({ transfer }: { transfer: any }) {

    const handleValidate = () => {
        if (confirm('Are you sure you want to validate this transfer?')) {
            router.post(route('inventory.ops.validate', transfer.id));
        }
    };

    // Helper: เลือกสี Badge ตามสถานะ
    const getStatusColor = (status: string) => {
        switch (status) {
            case 'draft': return 'bg-gray-500';
            case 'waiting': return 'bg-orange-500';
            case 'ready': return 'bg-blue-500';
            case 'done': return 'bg-green-500 hover:bg-green-600';
            case 'cancelled': return 'bg-red-500';
            default: return 'bg-gray-500';
        }
    };

    // Helper: ไอคอนตามประเภทเอกสาร
    const getTypeIcon = (type: string) => {
        if (type === 'picking') return <Layers className="w-5 h-5" />;
        if (type === 'packing') return <Package className="w-5 h-5" />;
        if (type === 'outgoing') return <Truck className="w-5 h-5" />;
        return <FileText className="w-5 h-5" />;
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex justify-between items-center">
                    <div className="flex items-center gap-3">
                        <div className="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                            {getTypeIcon(transfer.type)}
                        </div>
                        <div>
                            <h2 className="font-semibold text-xl text-gray-800 leading-tight">
                                {transfer.reference}
                            </h2>
                            <div className="flex items-center gap-2 text-sm text-gray-500">
                                {transfer.source_document && (
                                    <span className="flex items-center gap-1 bg-gray-100 px-2 py-0.5 rounded text-gray-700">
                                        <FileText className="w-3 h-3" /> Origin: {transfer.source_document}
                                    </span>
                                )}
                                <span>•</span>
                                <span>{new Date(transfer.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>
                    </div>

                    <div className="flex items-center gap-3">
                        <Badge className={`${getStatusColor(transfer.status)} text-base px-4 py-1`}>
                            {transfer.status.toUpperCase()}
                        </Badge>

                        {/* ✅ Logic ปุ่มกด: ถ้า Done แล้ว ให้โชว์ Print แทน Validate */}
                        {transfer.status === 'done' ? (
                            <Button variant="outline" className="border-gray-300 text-gray-700" onClick={() => window.print()}>
                                <Printer className="w-4 h-4 mr-2" /> Print Slip
                            </Button>
                        ) : (
                            <>
                                <Link href={route('inventory.ops.edit', transfer.id)}>
                                    <Button variant="outline" className="border-gray-300">Edit</Button>
                                </Link>
                                <Button
                                    onClick={handleValidate}
                                    className="bg-indigo-600 hover:bg-indigo-700 text-white"
                                    disabled={transfer.status === 'waiting'} // ถ้าของยังไม่มา ห้ามกด
                                >
                                    <CheckCircle className="w-4 h-4 mr-2" /> Validate
                                </Button>
                            </>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`Operation ${transfer.reference}`} />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                    {/* ✅ SMART LINKS PANEL: แสดงความเชื่อมโยงเอกสาร (Previous -> Current -> Next) */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">

                        {/* Previous Step (ถ้ามี) */}
                        {transfer.previous_transfer ? (
                            <Link href={route('inventory.ops.show', transfer.previous_transfer.id)}>
                                <Card className="hover:bg-gray-50 transition-colors cursor-pointer border-l-4 border-l-gray-300 h-full">
                                    <CardContent className="p-4 flex items-center gap-4">
                                        <div className="p-2 bg-gray-200 rounded-full text-gray-600">
                                            <ArrowLeft className="w-5 h-5" />
                                        </div>
                                        <div>
                                            <div className="text-xs text-gray-500 uppercase font-bold">Previous Step</div>
                                            <div className="font-medium text-indigo-600">{transfer.previous_transfer.reference}</div>
                                            <div className="text-xs text-gray-400 capitalize">{transfer.previous_transfer.type} • {transfer.previous_transfer.status}</div>
                                        </div>
                                    </CardContent>
                                </Card>
                            </Link>
                        ) : (
                            <div className="hidden md:block"></div> // Spacer
                        )}

                        {/* Next Step(s) (ถ้ามี) */}
                        {transfer.next_transfers && transfer.next_transfers.length > 0 && (
                            <div className="flex flex-col gap-2">
                                {transfer.next_transfers.map((next: any) => (
                                    <Link key={next.id} href={route('inventory.ops.show', next.id)}>
                                        <Card className={`transition-colors cursor-pointer border-r-4 h-full ${next.status === 'ready' ? 'border-r-green-500 bg-green-50 hover:bg-green-100' : 'border-r-orange-300 hover:bg-gray-50'}`}>
                                            <CardContent className="p-4 flex items-center justify-between">
                                                <div>
                                                    <div className="text-xs text-gray-500 uppercase font-bold">Next Step</div>
                                                    <div className="font-medium text-indigo-600">{next.reference}</div>
                                                    <div className="text-xs text-gray-400 capitalize">{next.type} • {next.status}</div>
                                                </div>
                                                <div className={`p-2 rounded-full ${next.status === 'ready' ? 'bg-green-200 text-green-700' : 'bg-gray-200 text-gray-600'}`}>
                                                    <ArrowRight className="w-5 h-5" />
                                                </div>
                                            </CardContent>
                                        </Card>
                                    </Link>
                                ))}
                            </div>
                        )}
                    </div>

                    {/* รายละเอียดเอกสาร (ข้อมูลหลัก) */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {/* Left Column: Contact & Locations */}
                        <div className="md:col-span-1 space-y-6">
                            <Card>
                                <CardHeader><CardTitle className="text-base">Logistics Details</CardTitle></CardHeader>
                                <CardContent className="space-y-4 text-sm">
                                    <div>
                                        <div className="text-gray-500">Contact</div>
                                        <div className="font-medium">{transfer.contact?.name || '-'}</div>
                                    </div>
                                    <div className="grid grid-cols-2 gap-4">
                                        <div>
                                            <div className="text-gray-500">From</div>
                                            <div className="font-medium">{transfer.source_location?.name}</div>
                                        </div>
                                        <div>
                                            <div className="text-gray-500">To</div>
                                            <div className="font-medium">{transfer.destination_location?.name}</div>
                                        </div>
                                    </div>
                                    <div>
                                        <div className="text-gray-500">Scheduled Date</div>
                                        <div>{transfer.scheduled_date ? new Date(transfer.scheduled_date).toLocaleDateString() : '-'}</div>
                                    </div>
                                </CardContent>
                            </Card>

                            {/* Note */}
                            {transfer.note && (
                                <Card className="bg-yellow-50 border-yellow-200">
                                    <CardContent className="p-4">
                                        <div className="text-xs font-bold text-yellow-700 uppercase mb-1">Note</div>
                                        <p className="text-sm text-yellow-800">{transfer.note}</p>
                                    </CardContent>
                                </Card>
                            )}
                        </div>

                        {/* Right Column: Product Lines */}
                        <div className="md:col-span-2">
                            <Card>
                                <CardHeader><CardTitle>Product Moves</CardTitle></CardHeader>
                                <CardContent className="p-0">
                                    <table className="w-full text-sm text-left">
                                        <thead className="text-gray-500 bg-gray-50 uppercase border-b">
                                            <tr>
                                                <th className="px-6 py-3">Product</th>
                                                <th className="px-6 py-3 text-right">Demand</th>
                                                <th className="px-6 py-3 text-right">Done</th>
                                                <th className="px-6 py-3 text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-100">
                                            {transfer.moves.map((move: any) => (
                                                <tr key={move.id} className="hover:bg-gray-50">
                                                    <td className="px-6 py-4 font-medium">{move.item.name}</td>
                                                    <td className="px-6 py-4 text-right text-gray-500">
                                                        {move.quantity_demand} <span className="text-xs">{move.item.uom.symbol}</span>
                                                    </td>
                                                    <td className="px-6 py-4 text-right font-bold">
                                                        {/* ถ้า Done ไม่เท่ากับ Demand ให้โชว์สีส้ม */}
                                                        <span className={move.quantity_done < move.quantity_demand ? 'text-orange-600' : 'text-green-600'}>
                                                            {move.quantity_done}
                                                        </span>
                                                    </td>
                                                    <td className="px-6 py-4 text-center">
                                                        <span className={`px-2 py-1 rounded text-xs border ${
                                                            move.state === 'done' ? 'bg-green-50 text-green-700 border-green-200' :
                                                            'bg-gray-50 text-gray-600 border-gray-200'
                                                        }`}>
                                                            {move.state}
                                                        </span>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </CardContent>
                            </Card>
                        </div>
                    </div>

                </div>
            </div>
        </AuthenticatedLayout>
    );
}
