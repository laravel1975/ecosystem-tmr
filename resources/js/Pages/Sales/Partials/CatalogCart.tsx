import React from 'react';
import { Button } from "@/Components/ui/button";
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from "@/Components/ui/sheet";
import { ShoppingCart, Trash2, FileText } from "lucide-react";
import { router } from '@inertiajs/react';

interface CartItem {
    id: number;
    name: string;
    qty: number;
    price: number;
}

export default function CatalogCart({ cart, setCart }: { cart: CartItem[], setCart: any }) {
    const total = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);

    const handleCreateQuotation = () => {
        router.post(route('catalog.prices.prepare-quotation'), {
            items: cart.map(i => ({ inventory_item_id: i.id, quantity: i.qty, unit_price: i.price }))
        });
    };

    return (
        <Sheet>
            <SheetTrigger asChild>
                <Button className="fixed bottom-6 right-6 rounded-full h-16 w-16 shadow-2xl z-50">
                    <ShoppingCart className="h-6 w-6" />
                    {cart.length > 0 && (
                        <span className="absolute -top-2 -right-2 bg-destructive text-destructive-foreground rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold">
                            {cart.length}
                        </span>
                    )}
                </Button>
            </SheetTrigger>
            <SheetContent>
                <SheetHeader>
                    <SheetTitle>รายการใบเสนอราคา</SheetTitle>
                </SheetHeader>
                <div className="mt-8 space-y-4">
                    {cart.map((item, idx) => (
                        <div key={idx} className="flex justify-between items-center border-b pb-2">
                            <div>
                                <div className="font-medium text-sm">{item.name}</div>
                                <div className="text-xs text-muted-foreground">{item.qty} x ฿{item.price.toLocaleString()}</div>
                            </div>
                            <Button variant="ghost" size="icon" onClick={() => setCart(cart.filter((_, i) => i !== idx))}>
                                <Trash2 className="h-4 w-4 text-destructive" />
                            </Button>
                        </div>
                    ))}
                    {cart.length === 0 && <div className="text-center text-muted-foreground py-10">ตะกร้าว่างเปล่า</div>}
                </div>
                {cart.length > 0 && (
                    <div className="absolute bottom-0 left-0 w-full p-6 bg-background border-t">
                        <div className="flex justify-between mb-4 font-bold">
                            <span>ยอดรวมสุทธิ</span>
                            <span>฿{total.toLocaleString()}</span>
                        </div>
                        <Button className="w-full" size="lg" onClick={handleCreateQuotation}>
                            <FileText className="mr-2 h-4 w-4" /> สร้างใบเสนอราคา
                        </Button>
                    </div>
                )}
            </SheetContent>
        </Sheet>
    );
}
