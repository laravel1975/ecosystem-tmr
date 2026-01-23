import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import { Card, CardContent, CardFooter } from "@/Components/ui/card";
import { Input } from "@/Components/ui/input";
import { Badge } from "@/Components/ui/badge";
import { Button } from "@/Components/ui/button";
import { Dialog, DialogContent, DialogHeader, DialogTitle } from "@/Components/ui/dialog";
import { CatalogItem } from '@/types/catalog';
import { Search, ShoppingCart, Info } from 'lucide-react';
import SaleNavigation from '../Partials/SaleNavigation';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CatalogCart from '../Partials/CatalogCart'; // นำเข้าคอมโพเนนต์ตะกร้า

interface Props {
    items: CatalogItem[];
    filters: { search?: string };
}

// กำหนด Interface สำหรับสินค้าในตะกร้า
interface CartItem {
    id: number;
    name: string;
    qty: number;
    price: number;
}

export default function CatalogGallery({ items, filters }: Props) {
    const [search, setSearch] = useState(filters.search || '');
    const [selectedItem, setSelectedItem] = useState<CatalogItem | null>(null);
    const [cart, setCart] = useState<CartItem[]>([]);

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get(route('catalog.gallery'), { search }, { preserveState: true });
    };

    // ฟังก์ชันเพิ่มสินค้าลงตะกร้า
    const addToCart = (item: CatalogItem) => {
        const defaultPrice = item.price_points[0]?.amount || 0;

        // ตรวจสอบว่ามีสินค้าในตะกร้าหรือยัง ถ้ามีให้บวกจำนวนเพิ่ม
        const existingIndex = cart.findIndex(c => c.id === item.id);
        if (existingIndex > -1) {
            const newCart = [...cart];
            newCart[existingIndex].qty += 1;
            setCart(newCart);
        } else {
            setCart([...cart, {
                id: item.id,
                name: item.name,
                qty: 1,
                price: defaultPrice
            }]);
        }

        // ปิด Modal หลังจากเพิ่มลงตะกร้า (ถ้าเปิดอยู่)
        setSelectedItem(null);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200">Product Catalog Gallery</h2>}
            navigation={<SaleNavigation />}
        >
            <Head title="Sales Catalog Gallery" />

            <div className="p-6 space-y-6 pb-24"> {/* เพิ่ม padding bottom เพื่อไม่ให้ปุ่มตะกร้าบังเนื้อหา */}
                {/* Header & Search */}
                <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <h2 className="text-3xl font-bold tracking-tight">Product Catalog</h2>
                        <p className="text-muted-foreground">เลือกชมสินค้าและสะสมรายการเพื่อสร้างใบเสนอราคา</p>
                    </div>
                    <form onSubmit={handleSearch} className="relative w-full md:w-96">
                        <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                        <Input
                            placeholder="ค้นหาชื่อสินค้า หรือ SKU..."
                            className="pl-9"
                            value={search}
                            onChange={e => setSearch(e.target.value)}
                        />
                    </form>
                </div>

                {/* Product Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {items.map((item) => (
                        <Card key={item.id} className="group overflow-hidden hover:shadow-xl transition-all duration-300 border-none bg-card/50 backdrop-blur">
                            <div className="relative aspect-[4/3] overflow-hidden bg-muted">
                                {item.main_image ? (
                                    <img
                                        src={`/storage/${item.main_image.file_path}`}
                                        alt={item.name}
                                        className="object-cover w-full h-full group-hover:scale-110 transition-transform duration-500"
                                    />
                                ) : (
                                    <div className="flex items-center justify-center h-full text-muted-foreground">No Image</div>
                                )}
                                <Badge className="absolute top-3 right-3 bg-black/60 backdrop-blur border-none">
                                    {item.sku}
                                </Badge>
                            </div>

                            <CardContent className="p-4">
                                <h3 className="font-bold text-lg line-clamp-1">{item.name}</h3>
                                <p className="text-sm text-muted-foreground line-clamp-2 mt-1 h-10">
                                    {item.description || 'ไม่มีรายละเอียดสินค้า'}
                                </p>
                            </CardContent>

                            <CardFooter className="p-4 pt-0 flex justify-between items-center gap-2">
                                <div className="flex flex-col">
                                    <span className="text-xs text-muted-foreground">เริ่มต้นที่</span>
                                    <span className="text-lg font-bold text-primary">
                                        ฿{item.price_points[0]?.amount?.toLocaleString() || '0'}
                                    </span>
                                </div>
                                <div className="flex gap-1">
                                    <Button size="icon" variant="outline" onClick={() => setSelectedItem(item)}>
                                        <Info className="w-4 h-4" />
                                    </Button>
                                    <Button size="sm" onClick={() => addToCart(item)}>
                                        <ShoppingCart className="w-4 h-4 mr-1" /> เพิ่ม
                                    </Button>
                                </div>
                            </CardFooter>
                        </Card>
                    ))}
                </div>
            </div>

            {/* Product Detail Modal */}
            <Dialog open={!!selectedItem} onOpenChange={() => setSelectedItem(null)}>
                <DialogContent className="max-w-4xl max-h-[90vh] overflow-y-auto">
                    {selectedItem && (
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-8 py-4">
                            {/* Image Preview Area */}
                            <div className="space-y-4">
                                <div className="aspect-square rounded-xl overflow-hidden bg-muted">
                                    <img
                                        src={`/storage/${selectedItem.main_image?.file_path}`}
                                        className="w-full h-full object-cover"
                                        alt={selectedItem.name}
                                    />
                                </div>
                                <div className="grid grid-cols-5 gap-2">
                                    {selectedItem.images.map(img => (
                                        <div key={img.id} className="aspect-square rounded-md overflow-hidden border">
                                            <img src={`/storage/${img.file_path}`} className="w-full h-full object-cover" />
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Info Area */}
                            <div className="flex flex-col gap-6">
                                <div>
                                    <Badge variant="outline" className="mb-2">{selectedItem.sku}</Badge>
                                    <DialogHeader>
                                        <DialogTitle className="text-2xl">{selectedItem.name}</DialogTitle>
                                    </DialogHeader>
                                    <p className="mt-4 text-muted-foreground text-sm">
                                        {selectedItem.description || 'ไม่มีข้อมูลรายละเอียดสินค้าเพิ่มเติม'}
                                    </p>
                                </div>

                                <div className="space-y-3">
                                    <h4 className="font-semibold text-sm uppercase tracking-wider text-muted-foreground">ตารางราคาขาย</h4>
                                    <div className="grid gap-2">
                                        {selectedItem.price_points.map(price => (
                                            <div key={price.id} className="flex justify-between items-center p-3 rounded-lg bg-muted/50 border">
                                                <span className="font-medium text-sm">{(price as any).price_list?.name || 'ราคาปกติ'}</span>
                                                <span className="text-lg font-bold">฿{price.amount.toLocaleString()}</span>
                                            </div>
                                        ))}
                                    </div>
                                </div>

                                <div className="mt-auto flex gap-3">
                                    <Button className="flex-1" size="lg" onClick={() => addToCart(selectedItem)}>
                                        <ShoppingCart className="w-4 h-4 mr-2" /> เพิ่มลงใบเสนอราคา
                                    </Button>
                                </div>
                            </div>
                        </div>
                    )}
                </DialogContent>
            </Dialog>

            {/* Floating Cart Component */}
            <CatalogCart cart={cart} setCart={setCart} />
        </AuthenticatedLayout>
    );
}
