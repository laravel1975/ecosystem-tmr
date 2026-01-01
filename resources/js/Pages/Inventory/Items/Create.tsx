import React, { FormEventHandler } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
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
import { ChevronLeft, Save } from 'lucide-react';

// Types สำหรับ Props ที่รับมาจาก Controller
interface Category {
    id: number;
    name: string;
}

interface Uom {
    id: number;
    name: string;
    symbol: string;
}

interface Props {
    categories: Category[];
    uoms: Uom[];
}

export default function CreateItem({ categories, uoms }: Props) {
    // ใช้ useForm ของ Inertia เพื่อจัดการ State และการ Submit
    const { data, setData, post, processing, errors, reset } = useForm({
        sku: '',
        name: '',
        description: '',
        category_id: '',
        uom_id: '',
        cost: '',
        price: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        // ส่งข้อมูลไปที่ Route 'inventory.items.store'
        post(route('inventory.items.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                        Create New Product
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
            <Head title="Create Product" />

            <div className="py-12">
                <div className="max-w-3xl mx-auto sm:px-6 lg:px-8">
                    <form onSubmit={submit}>
                        <Card className="bg-white dark:bg-gray-800 shadow-sm">
                            <CardHeader>
                                <CardTitle>Product Information</CardTitle>
                                <CardDescription>
                                    Define the basic details, pricing, and classification of the item.
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                {/* Row 1: SKU & Name */}
                                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div className="md:col-span-1 space-y-2">
                                        <Label htmlFor="sku">SKU (Stock Keeping Unit) <span className="text-red-500">*</span></Label>
                                        <Input
                                            id="sku"
                                            value={data.sku}
                                            onChange={(e) => setData('sku', e.target.value)}
                                            placeholder="e.g. ITEM-001"
                                            autoFocus
                                        />
                                        <InputError message={errors.sku} />
                                    </div>

                                    <div className="md:col-span-2 space-y-2">
                                        <Label htmlFor="name">Product Name <span className="text-red-500">*</span></Label>
                                        <Input
                                            id="name"
                                            value={data.name}
                                            onChange={(e) => setData('name', e.target.value)}
                                            placeholder="e.g. Wireless Mouse"
                                        />
                                        <InputError message={errors.name} />
                                    </div>
                                </div>

                                {/* Row 2: Category & UOM */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>Category</Label>
                                        <Select
                                            onValueChange={(value) => setData('category_id', value)}
                                            defaultValue={data.category_id}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a category" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {categories.map((category) => (
                                                    <SelectItem key={category.id} value={category.id.toString()}>
                                                        {category.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.category_id} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label>Unit of Measure (UOM) <span className="text-red-500">*</span></Label>
                                        <Select
                                            onValueChange={(value) => setData('uom_id', value)}
                                            defaultValue={data.uom_id}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select Unit" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {uoms.map((uom) => (
                                                    <SelectItem key={uom.id} value={uom.id.toString()}>
                                                        {uom.name} ({uom.symbol})
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.uom_id} />
                                    </div>
                                </div>

                                {/* Row 3: Pricing */}
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label htmlFor="cost">Cost Price</Label>
                                        <div className="relative">
                                            <span className="absolute left-3 top-2 text-gray-500">฿</span>
                                            <Input
                                                id="cost"
                                                type="number"
                                                className="pl-8"
                                                value={data.cost}
                                                onChange={(e) => setData('cost', e.target.value)}
                                                placeholder="0.00"
                                            />
                                        </div>
                                        <InputError message={errors.cost} />
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="price">Sales Price</Label>
                                        <div className="relative">
                                            <span className="absolute left-3 top-2 text-gray-500">฿</span>
                                            <Input
                                                id="price"
                                                type="number"
                                                className="pl-8"
                                                value={data.price}
                                                onChange={(e) => setData('price', e.target.value)}
                                                placeholder="0.00"
                                            />
                                        </div>
                                        <InputError message={errors.price} />
                                    </div>
                                </div>

                                {/* Row 4: Description */}
                                <div className="space-y-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Textarea
                                        id="description"
                                        className="resize-none"
                                        rows={4}
                                        value={data.description}
                                        onChange={(e) => setData('description', e.target.value)}
                                        placeholder="Detailed product specification..."
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                {/* Actions */}
                                <div className="flex justify-end space-x-2 pt-4">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => window.history.back()}
                                        disabled={processing}
                                    >
                                        Cancel
                                    </Button>
                                    <Button type="submit" disabled={processing} className="bg-indigo-600 hover:bg-indigo-700">
                                        <Save className="w-4 h-4 mr-2" />
                                        Save Product
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
