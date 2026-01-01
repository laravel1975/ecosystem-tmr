import React, { FormEventHandler, useState } from 'react';
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
import { ChevronLeft, Truck, ArrowRight } from 'lucide-react';

interface Item {
    id: number;
    sku: string;
    name: string;
    uom: string;
    on_hand: number;
}

interface Props {
    items: Item[];
    source_locations: { id: number; name: string; code: string }[];
    destination_locations: { id: number; name: string; code: string }[];
}

export default function DeliverStock({ items, source_locations, destination_locations }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        item_id: '',
        source_location_id: source_locations.length > 0 ? source_locations[0].id.toString() : '',
        destination_location_id: destination_locations.length > 0 ? destination_locations[0].id.toString() : '',
        quantity: '',
        batch_number: '',
    });

    const [selectedItem, setSelectedItem] = useState<Item | null>(null);

    const onSelectProduct = (value: string) => {
        setData('item_id', value);
        const item = items.find(i => i.id.toString() === value) || null;
        setSelectedItem(item);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('inventory.operations.store_delivery'));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Deliver Product (Outbound)
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
            <Head title="Deliver Stock" />

            <div className="py-12">
                <div className="max-w-2xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit}>
                        <Card className="bg-white dark:bg-gray-800 shadow-sm border-orange-200 dark:border-orange-900">
                            <CardHeader>
                                <CardTitle className="flex items-center gap-2 text-orange-600 dark:text-orange-400">
                                    <Truck className="h-5 w-5" />
                                    Direct Delivery
                                </CardTitle>
                                <CardDescription>
                                    Ship items from warehouse to customer immediately.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">

                                {/* Movement Logic */}
                                <div className="flex items-center justify-between p-4 bg-orange-50 dark:bg-gray-700 rounded-lg text-sm border border-orange-100 dark:border-transparent">
                                    <div className="text-center">
                                        <span className="block font-semibold text-gray-500">Source (WH)</span>
                                        <span className="font-bold text-gray-800 dark:text-gray-200">
                                            {source_locations.find(l => l.id.toString() === data.source_location_id)?.name}
                                        </span>
                                    </div>
                                    <ArrowRight className="text-orange-400" />
                                    <div className="text-center">
                                        <span className="block font-semibold text-gray-500">Destination</span>
                                        <span className="font-bold text-orange-600 dark:text-orange-400">
                                            {destination_locations.find(l => l.id.toString() === data.destination_location_id)?.name || 'Customer'}
                                        </span>
                                    </div>
                                </div>

                                {/* Form */}
                                <div className="space-y-4">
                                    <div className="space-y-2">
                                        <Label>Product to Deliver <span className="text-red-500">*</span></Label>
                                        <Select onValueChange={onSelectProduct} value={data.item_id}>
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select Product" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {items.map((item) => (
                                                    <SelectItem key={item.id} value={item.id.toString()}>
                                                        {item.sku} - {item.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        {selectedItem && (
                                            <p className="text-xs text-gray-500 text-right">
                                                Available Stock: <span className="font-bold text-gray-800 dark:text-gray-200">{selectedItem.on_hand} {selectedItem.uom}</span>
                                            </p>
                                        )}
                                        <InputError message={errors.item_id} />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
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
                                                    {selectedItem?.uom || 'Unit'}
                                                </span>
                                            </div>
                                            <InputError message={errors.quantity} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label>Batch / Lot (Optional)</Label>
                                            <Input
                                                value={data.batch_number}
                                                onChange={(e) => setData('batch_number', e.target.value)}
                                                placeholder="Picking Lot..."
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div className="flex justify-end pt-4">
                                    <Button type="submit" disabled={processing} className="bg-orange-600 hover:bg-orange-700 w-full md:w-auto">
                                        <Truck className="w-4 h-4 mr-2" />
                                        Validate & Deliver
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
