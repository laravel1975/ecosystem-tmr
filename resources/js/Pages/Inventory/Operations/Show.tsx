import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Separator } from '@/Components/ui/separator';
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
    Calendar,
    AlertCircle,
    Clock,
    PlayCircle,
    Copy, // Icon สำหรับ Backorder
    RefreshCw
} from 'lucide-react';

// ✅ รับ prop 'backorder' เพิ่มเข้ามา
export default function OperationsShow({ transfer, backorder }: { transfer: any, backorder?: any }) {

    const handleValidate = () => {
        if (confirm('Confirm validation? Inventory will be updated.')) {
            router.post(route('inventory.ops.validate', transfer.id));
        }
    };

    // Helper: Status Color & Icon
    const getStatusBadge = (status: string) => {
        const styles: any = {
            draft: { color: 'bg-gray-100 text-gray-600 border-gray-200', icon: FileText },
            waiting: { color: 'bg-orange-50 text-orange-700 border-orange-200', icon: Clock },
            ready: { color: 'bg-blue-50 text-blue-700 border-blue-200', icon: PlayCircle },
            done: { color: 'bg-green-50 text-green-700 border-green-200', icon: CheckCircle },
            cancelled: { color: 'bg-red-50 text-red-700 border-red-200', icon: AlertCircle },
        };
        const conf = styles[status] || styles.draft;
        const Icon = conf.icon;

        return (
            <span className={`flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide border ${conf.color}`}>
                <Icon className="w-3.5 h-3.5" />
                {status}
            </span>
        );
    };

    const handleCheckAvailability = () => {
        router.post(route('inventory.ops.check', transfer.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex flex-col md:flex-row md:justify-between md:items-center gap-4">
                    {/* Header Left: Title & Status */}
                    <div className="flex items-start gap-4">
                        <div className="p-3 bg-white border border-gray-200 rounded-xl shadow-sm">
                            {transfer.type === 'picking' && <Layers className="w-6 h-6 text-indigo-600" />}
                            {transfer.type === 'packing' && <Package className="w-6 h-6 text-orange-600" />}
                            {transfer.type === 'outgoing' && <Truck className="w-6 h-6 text-green-600" />}
                        </div>
                        <div>
                            <div className="flex items-center gap-3">
                                <h2 className="font-bold text-2xl text-gray-900 leading-tight tracking-tight">
                                    {transfer.reference}
                                </h2>
                                {getStatusBadge(transfer.status)}
                            </div>
                            <div className="flex items-center gap-3 mt-1.5 text-sm text-gray-500">
                                <span className="flex items-center gap-1">
                                    <Calendar className="w-3.5 h-3.5 text-gray-400" />
                                    {new Date(transfer.created_at).toLocaleDateString()}
                                </span>
                                {transfer.source_document && (
                                    <>
                                        <span className="text-gray-300">|</span>
                                        <span className="flex items-center gap-1.5 font-medium text-gray-600 bg-gray-100 px-2 py-0.5 rounded-md text-xs border border-gray-200">
                                            <FileText className="w-3 h-3" /> Origin: {transfer.source_document}
                                        </span>
                                    </>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Header Right: Actions */}
                    <div className="flex items-center gap-3">
                        {transfer.status !== 'done' ? (
                            <>
                                <Link href={route('inventory.ops.edit', transfer.id)}>
                                    <Button variant="outline" className="bg-white hover:bg-gray-50 border-gray-300 text-gray-700 shadow-sm">Edit</Button>
                                </Link>

                                {transfer.status === 'waiting' && (
                                    <Button
                                        onClick={handleCheckAvailability}
                                        className="bg-blue-600 hover:bg-blue-700 text-white shadow-sm"
                                    >
                                        <RefreshCw className="w-4 h-4 mr-2" /> Check Availability
                                    </Button>
                                )}

                                {/* ปุ่ม Validate (แสดงเมื่อ Ready หรือไม่ใช่ Waiting) */}
                                {transfer.status !== 'waiting' && (
                                    <Button
                                        onClick={handleValidate}
                                        className="bg-indigo-600 hover:bg-indigo-700 text-white shadow-sm"
                                    >
                                        <CheckCircle className="w-4 h-4 mr-2" /> Validate
                                    </Button>
                                )}
                            </>
                        ) : (
                            <a href={route('inventory.ops.print', transfer.id)} target="_blank" rel="noopener noreferrer">
                                <Button variant="outline" className="gap-2 bg-white text-gray-700 hover:bg-gray-50 border-gray-300 shadow-sm">
                                    <Printer className="w-4 h-4" /> Print Operations
                                </Button>
                            </a>
                        )}
                    </div>
                </div>
            }
        >
            <Head title={`${transfer.reference}`} />

            <div className="py-8 bg-gray-50/30 min-h-screen">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

                    {/* ✅ NEW: Backorder Notification Alert */}
                    {/* ถ้ามี Backorder ให้แสดง Card แจ้งเตือนพร้อมปุ่มกดไปหา */}
                    {backorder && (
                        <div className="bg-orange-50 border border-orange-200 rounded-xl p-4 flex items-center justify-between shadow-sm">
                            <div className="flex items-center gap-3">
                                <div className="p-2 bg-orange-100 rounded-full text-orange-600">
                                    <Copy className="w-5 h-5" />
                                </div>
                                <div>
                                    <div className="font-bold text-orange-800">Partial Delivery / Backorder Created</div>
                                    <div className="text-sm text-orange-600">
                                        Some items were not processed. A new document <strong>{backorder.reference}</strong> has been created.
                                    </div>
                                </div>
                            </div>
                            <Link href={route('inventory.ops.show', backorder.id)}>
                                <Button size="sm" className="bg-orange-600 hover:bg-orange-700 text-white border-none shadow-orange-200">
                                    Process Backorder <ArrowRight className="w-4 h-4 ml-2" />
                                </Button>
                            </Link>
                        </div>
                    )}

                    {/* 1. MODERN SMART NAVIGATION */}
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {/* Previous Link */}
                        {transfer.previous_transfer ? (
                            <Link href={route('inventory.ops.show', transfer.previous_transfer.id)} className="group block h-full">
                                <div className="bg-white border border-gray-200 rounded-xl p-5 shadow-sm hover:shadow-md hover:border-indigo-200 transition-all duration-200 h-full flex flex-col justify-center relative overflow-hidden">
                                    <div className="absolute top-0 left-0 w-1 h-full bg-gray-200 group-hover:bg-indigo-400 transition-colors"></div>
                                    <div className="flex items-center justify-between pl-2">
                                        <div className="flex items-center gap-4">
                                            <div className="w-10 h-10 rounded-full bg-gray-50 flex items-center justify-center text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors">
                                                <ArrowLeft className="w-5 h-5" />
                                            </div>
                                            <div>
                                                <div className="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-0.5">Previous Step</div>
                                                <div className="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors text-lg">{transfer.previous_transfer.reference}</div>
                                                <div className="text-xs text-gray-500 capitalize mt-0.5">{transfer.previous_transfer.type}</div>
                                            </div>
                                        </div>
                                        <Badge variant="secondary" className="uppercase text-[10px] bg-gray-100 text-gray-600 border-gray-200">{transfer.previous_transfer.status}</Badge>
                                    </div>
                                </div>
                            </Link>
                        ) : (
                            <div className="hidden md:block border-2 border-dashed border-gray-200 rounded-xl p-5 flex items-center justify-center text-gray-300 text-sm font-medium">
                                Start of Workflow
                            </div>
                        )}

                        {/* Next Link */}
                        {transfer.next_transfers && transfer.next_transfers.length > 0 ? (
                            <div className="flex flex-col gap-3">
                                {transfer.next_transfers.map((next: any) => (
                                    <Link key={next.id} href={route('inventory.ops.show', next.id)} className="group block">
                                        <div className={`border rounded-xl p-5 shadow-sm hover:shadow-md transition-all duration-200 relative overflow-hidden
                                            ${next.status === 'ready'
                                                ? 'bg-white border-green-200 hover:border-green-400'
                                                : 'bg-white border-gray-200 hover:border-indigo-200'}`}>

                                            <div className={`absolute top-0 right-0 w-1 h-full transition-colors ${next.status === 'ready' ? 'bg-green-500' : 'bg-gray-200 group-hover:bg-indigo-400'}`}></div>

                                            <div className="flex items-center justify-between pr-2">
                                                <div className="flex items-center gap-4">
                                                    <div className={`w-10 h-10 rounded-full flex items-center justify-center transition-colors
                                                        ${next.status === 'ready' ? 'bg-green-100 text-green-600' : 'bg-gray-50 text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-600'}`}>
                                                        <ArrowRight className="w-5 h-5" />
                                                    </div>
                                                    <div>
                                                        <div className="text-[10px] uppercase font-bold text-gray-400 tracking-wider mb-0.5 flex items-center gap-2">
                                                            Next Step
                                                            {next.status === 'ready' && <span className="relative flex h-2 w-2">
                                                                <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                                                <span className="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                                            </span>}
                                                        </div>
                                                        <div className="font-semibold text-gray-900 group-hover:text-indigo-700 transition-colors text-lg">{next.reference}</div>
                                                        <div className="text-xs text-gray-500 capitalize mt-0.5">{next.type}</div>
                                                    </div>
                                                </div>
                                                <div className="flex items-center gap-3">
                                                    <Badge className={`uppercase text-[10px] border-0 px-2 py-0.5
                                                        ${next.status === 'ready' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500'}`}>
                                                        {next.status}
                                                    </Badge>
                                                </div>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        ) : (
                            <div className="hidden md:block border-2 border-dashed border-gray-200 rounded-xl p-5 flex items-center justify-center text-gray-300 text-sm font-medium">
                                End of Workflow
                            </div>
                        )}
                    </div>

                    <div className="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        {/* LEFT: INFO CARD */}
                        <div className="space-y-6">
                            <Card className="shadow-sm border-gray-200 overflow-hidden">
                                <CardHeader className="bg-gray-50/80 border-b border-gray-100 py-4 px-5">
                                    <CardTitle className="text-xs font-bold text-gray-500 uppercase tracking-widest">Logistics Details</CardTitle>
                                </CardHeader>
                                <CardContent className="pt-6 px-5 space-y-6">
                                    {/* Partner */}
                                    <div className="flex gap-4">
                                        <div className="mt-0.5 p-2 bg-gray-50 rounded-lg h-fit"><User className="w-4 h-4 text-gray-500" /></div>
                                        <div>
                                            <div className="text-xs text-gray-400 uppercase font-bold mb-1">Contact</div>
                                            <div className="font-medium text-gray-900">{transfer.contact?.name || '-'}</div>
                                            <div className="text-sm text-gray-500 mt-0.5">{transfer.contact?.address || 'No address provided'}</div>
                                        </div>
                                    </div>

                                    <Separator className="bg-gray-100" />

                                    {/* Locations Pipeline Style */}
                                    <div className="flex gap-4">
                                        <div className="mt-0.5 p-2 bg-gray-50 rounded-lg h-fit"><MapPin className="w-4 h-4 text-gray-500" /></div>
                                        <div className="flex-1">
                                            <div className="text-xs text-gray-400 uppercase font-bold mb-3">Route</div>
                                            <div className="relative pl-4 border-l-2 border-gray-100 space-y-6">
                                                <div className="relative">
                                                    <div className="absolute -left-[21px] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-red-400 shadow-sm" />
                                                    <div className="text-[10px] text-gray-400 uppercase font-semibold">From</div>
                                                    <div className="font-medium text-gray-800 text-sm">{transfer.source_location?.name}</div>
                                                </div>
                                                <div className="relative">
                                                    <div className="absolute -left-[21px] top-1.5 h-3 w-3 rounded-full border-2 border-white bg-green-500 shadow-sm" />
                                                    <div className="text-[10px] text-gray-400 uppercase font-semibold">To</div>
                                                    <div className="font-medium text-gray-800 text-sm">{transfer.destination_location?.name}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <Separator className="bg-gray-100" />

                                    {/* Date */}
                                    <div className="flex gap-4">
                                        <div className="mt-0.5 p-2 bg-gray-50 rounded-lg h-fit"><Calendar className="w-4 h-4 text-gray-500" /></div>
                                        <div>
                                            <div className="text-xs text-gray-400 uppercase font-bold mb-1">Scheduled Date</div>
                                            <div className="font-medium text-gray-900">
                                                {transfer.scheduled_date ? new Date(transfer.scheduled_date).toLocaleDateString(undefined, { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' }) : '-'}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>

                            {transfer.note && (
                                <div className="bg-amber-50 border border-amber-100 rounded-xl p-5 text-sm text-amber-900 shadow-sm">
                                    <div className="flex items-center gap-2 font-bold mb-2 text-amber-700">
                                        <FileText className="w-4 h-4" /> Note
                                    </div>
                                    <p className="leading-relaxed opacity-90">{transfer.note}</p>
                                </div>
                            )}
                        </div>

                        {/* RIGHT: ITEMS TABLE */}
                        <div className="lg:col-span-2">
                            <Card className="shadow-sm border-gray-200 h-full overflow-hidden">
                                <CardHeader className="flex flex-row items-center justify-between bg-white border-b border-gray-100 py-5 px-6">
                                    <div className="flex items-center gap-3">
                                        <div className="p-2 bg-indigo-50 rounded-lg"><Package className="w-5 h-5 text-indigo-600" /></div>
                                        <CardTitle className="text-base font-bold text-gray-800">Product Moves</CardTitle>
                                    </div>
                                    <Badge variant="secondary" className="bg-gray-100 text-gray-600 border-gray-200 px-3">
                                        {transfer.moves.length} Items
                                    </Badge>
                                </CardHeader>
                                <CardContent className="p-0">
                                    <div className="overflow-x-auto">
                                        <table className="w-full text-sm">
                                            <thead className="bg-gray-50/50 text-xs text-gray-500 uppercase font-semibold tracking-wider">
                                                <tr>
                                                    <th className="px-6 py-4 text-left">Product</th>
                                                    <th className="px-6 py-4 text-right">Demand</th>
                                                    <th className="px-6 py-4 text-right w-32">Reserved</th>
                                                    <th className="px-6 py-4 text-right w-32">Done</th>
                                                    <th className="px-6 py-4 text-center w-24">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody className="divide-y divide-gray-100">
                                                {transfer.moves.map((move: any) => {
                                                    const isPartial = move.quantity_done > 0 && move.quantity_done < move.quantity_demand;
                                                    const isComplete = move.quantity_done >= move.quantity_demand;

                                                    return (
                                                        <tr key={move.id} className="hover:bg-gray-50/80 transition-colors group">
                                                            <td className="px-6 py-4">
                                                                <div className="font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">{move.item.name}</div>
                                                                <div className="text-xs text-gray-400 font-mono mt-0.5">Code: {move.item.id}</div>
                                                            </td>
                                                            <td className="px-6 py-4 text-right">
                                                                <span className="font-medium text-gray-700">{Number(move.quantity_demand).toLocaleString()}</span>
                                                                <span className="text-xs ml-1 text-gray-400">{move.item.uom.symbol}</span>
                                                            </td>
                                                            <td className="px-6 py-4 text-right text-gray-500 font-mono">
                                                                {transfer.status === 'ready' ? Number(move.quantity_demand).toLocaleString() : '-'}
                                                            </td>
                                                            <td className="px-6 py-4 text-right">
                                                                <div className={`font-bold inline-flex items-center px-2 py-0.5 rounded-md ${isPartial ? 'bg-orange-50 text-orange-700' : (isComplete && transfer.status === 'done' ? 'bg-green-50 text-green-700' : 'text-gray-400')}`}>
                                                                    {transfer.status === 'done' ? Number(move.quantity_done).toLocaleString() : (
                                                                        <span className="text-xs font-normal italic">Pending</span>
                                                                    )}
                                                                </div>
                                                            </td>
                                                            <td className="px-6 py-4 text-center">
                                                                {move.state === 'done' ? (
                                                                    <div className="w-8 h-8 rounded-full bg-green-100 text-green-600 flex items-center justify-center mx-auto">
                                                                        <CheckCircle className="w-4 h-4" />
                                                                    </div>
                                                                ) : (
                                                                    <div className="w-2 h-2 bg-orange-300 rounded-full mx-auto" />
                                                                )}
                                                            </td>
                                                        </tr>
                                                    );
                                                })}
                                            </tbody>
                                        </table>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
