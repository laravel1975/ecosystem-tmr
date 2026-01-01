import React, { FormEventHandler } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import InputError from '@/Components/InputError';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { ChevronLeft, PackagePlus, ArrowRight } from 'lucide-react';

interface Props {
    items: { id: number; sku: string; name: string; uom: string }[];
    source_locations: { id: number; name: string; code: string }[];
    destination_locations: { id: number; name: string; code: string }[];
}

export default function ReceiveStock({ items, source_locations, destination_locations }: Props) {
    const { data, setData, post, processing, errors, reset } = useForm({
        item_id: '',
        source_location_id: source_locations.length > 0 ? source_locations[0].id.toString() : '',
        destination_location_id: destination_locations.length > 0 ? destination_locations[0].id.toString() : '',
        quantity: '',
        batch_number: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('inventory.operations.store_receive'));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Receive Product (Inbound)
                    </h2>
                    <Link
                        href={route('inventory.dashboard')}
                        className="flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200"
                    >
                        <ChevronLeft className="w-4 h-4 mr-1" />
                        Back to Dashboard
                    </Link>
                </div>
            }
        >
            <Head title="Receive Stock" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit}>
                        <Card className="bg-white dark:bg-gray-800 shadow-sm">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2">
                                    <PackagePlus className="h-5 w-5 text-indigo-500" />
                                    Direct Receipt
                                </CardTitle>
                                <CardDescription>
                                    Receive goods from vendors directly into stock.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">

                                {/* Movement Logic Display */}
                                <div className="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                                    <div className="text-center">
                                        <span className="block font-semibold text-gray-500">Source (Vendor)</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">
                                            {source_locations.find(l => l.id.toString() === data.source_location_id)?.name || 'Select Source'}
                                        </span>
                                    </div>
                                    <ArrowRight className="text-gray-400" />
                                    <div className="text-center">
                                        <span className="block font-semibold text-gray-500">Destination (WH)</span>
                                        <span className="font-bold text-indigo-600 dark:text-indigo-400">
                                            {destination_locations.find(l => l.id.toString() === data.destination_location_id)?.name || 'Select Dest.'}
                                        </span>
                                    </div>
                                </div>

                                {/* Form Fields */}
                                <div className="space-y-4">
                                    {/* Item Selection */}
                                    <div className="space-y-2">
                                        <Label>Product <span className="text-red-500">*</span></Label>
                                        <Select
                                            onValueChange={(value) => setData('item_id', value)}
                                            value={data.item_id}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select Product to Receive" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {items.map((item) => (
                                                    <SelectItem key={item.id} value={item.id.toString()}>
                                                        [{item.sku}] {item.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.item_id} />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        {/* Quantity */}
                                        <div className="space-y-2">
                                            <Label>Quantity <span className="text-red-500">*</span></Label>
                                            <div className="relative">
                                                <Input
                                                    type="number"
                                                    value={data.quantity}
                                                    onChange={(e) => setData('quantity', e.target.value)}
                                                    placeholder="0.00"
                                                    className="pr-12"
                                                />
                                                <span className="absolute right-3 top-2.5 text-gray-500 text-sm">
                                                    {items.find(i => i.id.toString() === data.item_id)?.uom || 'Unit'}
                                                </span>
                                            </div>
                                            <InputError message={errors.quantity} />
                                        </div>

                                        {/* Batch/Lot (Optional) */}
                                        <div className="space-y-2">
                                            <Label>Batch / Lot No.</Label>
                                            <Input
                                                value={data.batch_number}
                                                onChange={(e) => setData('batch_number', e.target.value)}
                                                placeholder="e.g. LOT-2024-X"
                                            />
                                        </div>
                                    </div>

                                    {/* Location Overrides (Optional) - Hidden mostly, but good to have control */}
                                    <div className="grid grid-cols-2 gap-4 pt-2">
                                        <div className="space-y-2">
                                            <Label className="text-xs text-gray-500">From Location</Label>
                                            <Select
                                                onValueChange={(value) => setData('source_location_id', value)}
                                                value={data.source_location_id}
                                            >
                                                <SelectTrigger className="h-8 text-xs">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {source_locations.map((loc) => (
                                                        <SelectItem key={loc.id} value={loc.id.toString()}>{loc.name}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label className="text-xs text-gray-500">To Location</Label>
                                            <Select
                                                onValueChange={(value) => setData('destination_location_id', value)}
                                                value={data.destination_location_id}
                                            >
                                                <SelectTrigger className="h-8 text-xs">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {destination_locations.map((loc) => (
                                                        <SelectItem key={loc.id} value={loc.id.toString()}>{loc.name}</SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end pt-4">
                                    <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700 w-full md:w-auto">
                                        <PackagePlus className="w-4 h-4 mr-2" />
                                        Validate & Receive
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
