import React from 'react';
import { useForm } from '@inertiajs/react';
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { Label } from "@/Components/ui/label";
import { CatalogItem, PriceList } from "@/types/catalog";

interface Props {
    item: CatalogItem;
    priceLists: PriceList[];
}

export default function PricePointManager({ item, priceLists }: Props) {
    return (
        <div className="space-y-4">
            <h3 className="text-lg font-medium">จัดการราคาขาย</h3>
            <div className="grid gap-4">
                {priceLists.map((list) => {
                    const existingPrice = item.price_points.find(p => p.price_list_id === list.id);
                    const { data, setData, post, processing } = useForm({
                        inventory_item_id: item.id,
                        price_list_id: list.id,
                        amount: existingPrice?.amount || 0,
                        currency: 'THB'
                    });

                    const submit = (e: React.FormEvent) => {
                        e.preventDefault();
                        post(route('catalog.prices.update'));
                    };

                    return (
                        <form key={list.id} onSubmit={submit} className="flex items-end gap-4 p-4 border rounded-lg bg-muted/50">
                            <div className="flex-1">
                                <Label>{list.name} ({list.code})</Label>
                                <div className="relative mt-1">
                                    <span className="absolute left-3 top-2.5 text-muted-foreground text-sm">฿</span>
                                    <Input
                                        type="number"
                                        value={data.amount}
                                        onChange={e => setData('amount', parseFloat(e.target.value))}
                                        className="pl-7"
                                        step="0.01"
                                    />
                                </div>
                            </div>
                            <Button type="submit" variant="outline" disabled={processing}>
                                บันทึกราคา
                            </Button>
                        </form>
                    );
                })}
            </div>
        </div>
    );
}
