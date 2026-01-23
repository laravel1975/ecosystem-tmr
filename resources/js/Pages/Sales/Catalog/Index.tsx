import React, { useState } from 'react';
import AppPanelLayout from '@/Layouts/AppPanelLayout';
import { Head } from '@inertiajs/react';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/Components/ui/table";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Tabs, TabsContent, TabsList, TabsTrigger } from "@/Components/ui/tabs";
import { CatalogItem, PriceList } from '@/types/catalog';
import ItemImageManager from '../Partials/ItemImageManager';
import PricePointManager from '../Partials/PricePointManager';
import { Image as ImageIcon, Tag, Package } from 'lucide-react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import SaleNavigation from '../Partials/SaleNavigation';

interface Props {
    items: CatalogItem[];
    priceLists: PriceList[];
}

export default function CatalogIndex({ items, priceLists }: Props) {
    const [selectedItem, setSelectedItem] = useState<CatalogItem | null>(items[0] || null);

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200">Price List</h2>}
            navigation={<SaleNavigation />}
        >
            <Head title="Catalog Management" />

            <div className="grid grid-cols-12 gap-6 p-6">
                {/* รายการสินค้าทางซ้าย */}
                <Card className="col-span-12 lg:col-span-5">
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2">
                            <Package className="w-5 h-5" /> รายการสินค้า
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>สินค้า</TableHead>
                                    <TableHead className="text-right">สถานะ</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item) => (
                                    <TableRow
                                        key={item.id}
                                        className={`cursor-pointer hover:bg-muted/50 ${selectedItem?.id === item.id ? 'bg-muted' : ''}`}
                                        onClick={() => setSelectedItem(item)}
                                    >
                                        <TableCell>
                                            <div className="flex items-center gap-3">
                                                <div className="w-10 h-10 rounded bg-muted overflow-hidden flex-shrink-0">
                                                    {item.images.find(i => i.is_main) ? (
                                                        <img src={`/storage/${item.images.find(i => i.is_main)?.file_path}`} className="w-full h-full object-cover" />
                                                    ) : <ImageIcon className="w-full h-full p-2 text-muted-foreground" />}
                                                </div>
                                                <div>
                                                    <div className="font-medium text-sm">{item.name}</div>
                                                    <div className="text-xs text-muted-foreground">{item.sku}</div>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="text-xs space-y-1">
                                                <div>📸 {item.images.length}/5</div>
                                                <div>💰 {item.price_points.length} ระดับราคา</div>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {/* ส่วนจัดการสินค้าที่เลือกทางขวา */}
                <div className="col-span-12 lg:col-span-7">
                    {selectedItem ? (
                        <Card>
                            <CardHeader>
                                <CardTitle>{selectedItem.name}</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <Tabs defaultValue="prices">
                                    <TabsList className="grid w-full grid-cols-2 mb-6">
                                        <TabsTrigger value="prices" className="flex items-center gap-2">
                                            <Tag className="w-4 h-4" /> จัดการราคา
                                        </TabsTrigger>
                                        <TabsTrigger value="images" className="flex items-center gap-2">
                                            <ImageIcon className="w-4 h-4" /> จัดการรูปภาพ
                                        </TabsTrigger>
                                    </TabsList>

                                    <TabsContent value="prices">
                                        <PricePointManager item={selectedItem} priceLists={priceLists} />
                                    </TabsContent>

                                    <TabsContent value="images">
                                        <ItemImageManager item={selectedItem} />
                                    </TabsContent>
                                </Tabs>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="h-full flex items-center justify-center text-muted-foreground border-2 border-dashed rounded-lg">
                            กรุณาเลือกสินค้าเพื่อเริ่มจัดการ
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
