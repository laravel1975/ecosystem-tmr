import React from 'react';
import { useForm, Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from "@/Components/ui/select";
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import InputError from '@/Components/InputError';

// Define Interface for Props (รับมาจาก Controller)
interface Props {
    departments: { id: number; name: string }[];
    positions: { id: number; name: string }[];
}

export default function CreateEmployee({ departments, positions }: Props) {
    // Form State (ตรงกับ EmployeeData DTO)
    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        first_name: '',
        last_name: '',
        email: '',
        phone: '',
        department_id: '',
        position_id: '',
        // ERP Roles
        is_salesperson: false,
        is_technician: false,
        is_purchaser: false,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('hrm.employees.store'), {
            onSuccess: () => reset(),
        });
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Onboard New Employee</h2>}
        >
            <Head title="Onboard Employee" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <Card>
                        <CardHeader>
                            <CardTitle>Employee Information</CardTitle>
                            <CardDescription>
                                Create a new employee profile and setup their system access.
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <form onSubmit={submit} className="space-y-8">

                                {/* --- 1. Personal Info --- */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-medium text-gray-900 border-b pb-2">Personal Details</h3>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {/* First Name */}
                                        <div className="space-y-2">
                                            <Label htmlFor="first_name">First Name <span className="text-red-500">*</span></Label>
                                            <Input
                                                id="first_name"
                                                value={data.first_name}
                                                onChange={(e) => setData('first_name', e.target.value)}
                                                placeholder="John"
                                                required
                                            />
                                            <InputError message={errors.first_name} />
                                        </div>

                                        {/* Last Name */}
                                        <div className="space-y-2">
                                            <Label htmlFor="last_name">Last Name <span className="text-red-500">*</span></Label>
                                            <Input
                                                id="last_name"
                                                value={data.last_name}
                                                onChange={(e) => setData('last_name', e.target.value)}
                                                placeholder="Doe"
                                                required
                                            />
                                            <InputError message={errors.last_name} />
                                        </div>

                                        {/* Employee Code */}
                                        <div className="space-y-2">
                                            <Label htmlFor="code">Employee Code <span className="text-red-500">*</span></Label>
                                            <Input
                                                id="code"
                                                value={data.code}
                                                onChange={(e) => setData('code', e.target.value)}
                                                placeholder="EMP-001"
                                                required
                                            />
                                            <InputError message={errors.code} />
                                        </div>

                                        {/* Phone */}
                                        <div className="space-y-2">
                                            <Label htmlFor="phone">Phone Number</Label>
                                            <Input
                                                id="phone"
                                                value={data.phone}
                                                onChange={(e) => setData('phone', e.target.value)}
                                                placeholder="081-234-5678"
                                            />
                                            <InputError message={errors.phone} />
                                        </div>
                                    </div>
                                </div>

                                {/* --- 2. Organization & Access --- */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-medium text-gray-900 border-b pb-2">Organization & Access</h3>

                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        {/* Email (Auto User) */}
                                        <div className="space-y-2 md:col-span-2">
                                            <Label htmlFor="email">Email Address (System Login)</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                placeholder="john.d@company.com"
                                            />
                                            <p className="text-sm text-gray-500">
                                                If provided, a user account will be automatically created.
                                            </p>
                                            <InputError message={errors.email} />
                                        </div>

                                        {/* Department */}
                                        <div className="space-y-2">
                                            <Label htmlFor="department">Department</Label>
                                            <Select onValueChange={(value) => setData('department_id', value)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select Department" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {departments.map((dept) => (
                                                        <SelectItem key={dept.id} value={dept.id.toString()}>
                                                            {dept.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.department_id} />
                                        </div>

                                        {/* Position */}
                                        <div className="space-y-2">
                                            <Label htmlFor="position">Position</Label>
                                            <Select onValueChange={(value) => setData('position_id', value)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select Position" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {positions.map((pos) => (
                                                        <SelectItem key={pos.id} value={pos.id.toString()}>
                                                            {pos.name}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.position_id} />
                                        </div>
                                    </div>
                                </div>

                                {/* --- 3. ERP Roles (Business Logic) --- */}
                                <div className="space-y-4">
                                    <h3 className="text-lg font-medium text-gray-900 border-b pb-2">ERP Roles</h3>
                                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-lg border">

                                        {/* Salesperson */}
                                        <div className="flex flex-row items-start space-x-3 space-y-0">
                                            <Checkbox
                                                id="is_salesperson"
                                                checked={data.is_salesperson}
                                                onCheckedChange={(checked) => setData('is_salesperson', checked as boolean)}
                                            />
                                            <div className="space-y-1 leading-none">
                                                <Label htmlFor="is_salesperson">Salesperson</Label>
                                                <p className="text-sm text-muted-foreground">
                                                    Can create Sales Orders.
                                                </p>
                                            </div>
                                        </div>

                                        {/* Technician */}
                                        <div className="flex flex-row items-start space-x-3 space-y-0">
                                            <Checkbox
                                                id="is_technician"
                                                checked={data.is_technician}
                                                onCheckedChange={(checked) => setData('is_technician', checked as boolean)}
                                            />
                                            <div className="space-y-1 leading-none">
                                                <Label htmlFor="is_technician">Technician</Label>
                                                <p className="text-sm text-muted-foreground">
                                                    Auto-creates a personal stock location (Van).
                                                </p>
                                            </div>
                                        </div>

                                        {/* Purchaser */}
                                        <div className="flex flex-row items-start space-x-3 space-y-0">
                                            <Checkbox
                                                id="is_purchaser"
                                                checked={data.is_purchaser}
                                                onCheckedChange={(checked) => setData('is_purchaser', checked as boolean)}
                                            />
                                            <div className="space-y-1 leading-none">
                                                <Label htmlFor="is_purchaser">Purchaser</Label>
                                                <p className="text-sm text-muted-foreground">
                                                    Can manage Vendors and POs.
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div className="flex justify-end pt-4">
                                    <Button type="submit" disabled={processing} className="w-full md:w-auto">
                                        {processing ? 'Onboarding...' : 'Onboard Employee'}
                                    </Button>
                                </div>

                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
