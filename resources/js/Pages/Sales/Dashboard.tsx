import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import {
    TrendingUp,
    Users,
    FileText,
    AlertCircle,
    ArrowUpRight,
    Package
} from 'lucide-react';
import SaleNavigation from './Partials/SaleNavigation';

interface Props {
    stats: {
        total_sales: number;
        order_count: number;
        customer_count: number;
        pending_orders: number;
    };
    recentOrders: any[];
    monthlySales: any[];
}

export default function SalesDashboard({ stats, recentOrders, monthlySales }: Props) {
    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200">Sales Dashboard</h2>}
            navigation={<SaleNavigation />}
        >
            <Head title="Sales Dashboard" />

            <div className="py-12 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">

                {/* KPI Cards */}
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Revenue</CardTitle>
                            <TrendingUp className="h-4 w-4 text-green-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">฿{stats.total_sales.toLocaleString()}</div>
                            <p className="text-xs text-muted-foreground mt-1">+12% from last month</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Active Customers</CardTitle>
                            <Users className="h-4 w-4 text-blue-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.customer_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">Total registered</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-muted-foreground">Total Orders</CardTitle>
                            <FileText className="h-4 w-4 text-indigo-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.order_count}</div>
                            <p className="text-xs text-muted-foreground mt-1">Processed in system</p>
                        </CardContent>
                    </Card>
                    <Card className="border-orange-200 bg-orange-50/50">
                        <CardHeader className="flex flex-row items-center justify-between pb-2">
                            <CardTitle className="text-sm font-medium text-orange-600">Draft Quotations</CardTitle>
                            <AlertCircle className="h-4 w-4 text-orange-500" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold text-orange-700">{stats.pending_orders}</div>
                            <p className="text-xs text-orange-600/70 mt-1">Awaiting confirmation</p>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 lg:grid-cols-7 gap-8">
                    {/* Recent Orders Table */}
                    <Card className="lg:col-span-4">
                        <CardHeader className="flex flex-row items-center">
                            <div className="grid gap-1">
                                <CardTitle>Recent Orders</CardTitle>
                                <p className="text-sm text-muted-foreground">รายการสั่งซื้อล่าสุดในระบบ</p>
                            </div>
                            <Button asChild size="sm" className="ml-auto gap-1">
                                <Link href={route('sales.orders.index')}>
                                    View All
                                    <ArrowUpRight className="h-4 w-4" />
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Customer</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">Amount</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {recentOrders.map((order) => (
                                        <TableRow key={order.id}>
                                            <TableCell>
                                                <div className="font-medium">{order.customer.name}</div>
                                                <div className="text-xs text-muted-foreground">{order.code}</div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant={order.status === 'confirmed' ? 'default' : 'outline'}>
                                                    {order.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                ฿{Number(order.total_amount).toLocaleString()}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    {/* Simple Statistics Card */}
                    <Card className="lg:col-span-3">
                        <CardHeader>
                            <CardTitle>Sales Distribution</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-6">
                            {monthlySales.map((data, idx) => (
                                <div key={idx} className="space-y-2">
                                    <div className="flex items-center justify-between text-sm">
                                        <span className="font-medium">{data.month}</span>
                                        <span className="text-muted-foreground">฿{Number(data.total).toLocaleString()}</span>
                                    </div>
                                    <div className="h-2 w-full bg-secondary rounded-full overflow-hidden">
                                        <div
                                            className="h-full bg-primary"
                                            style={{ width: `${(data.total / stats.total_sales) * 100}%` }}
                                        ></div>
                                    </div>
                                </div>
                            ))}
                            {monthlySales.length === 0 && (
                                <div className="text-center py-10 text-muted-foreground flex flex-col items-center">
                                    <Package className="h-10 w-10 mb-2 opacity-20" />
                                    No sales data available
                                </div>
                            )}
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
