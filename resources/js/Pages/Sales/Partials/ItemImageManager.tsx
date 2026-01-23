import React from 'react';
import { useForm, router } from '@inertiajs/react';
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";
import { Badge } from "@/Components/ui/badge";
import { ImagePlus, Trash2, Star } from "lucide-react";
import { CatalogItem, InventoryItemImage } from "@/types/catalog";

interface Props {
    item: CatalogItem;
}

export default function ItemImageManager({ item }: Props) {
    const { data, setData, post, processing, errors } = useForm({
        image: null as File | null,
        is_main: false
    });

    const handleUpload = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('items.images.store', item.id), {
            onSuccess: () => setData('image', null),
        });
    };

    const deleteImage = (id: number) => {
        if (confirm('ยืนยันการลบรูปภาพ?')) {
            router.delete(route('items.images.destroy', id));
        }
    };

    const setMain = (id: number) => {
        router.patch(route('items.images.set-main', id));
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center justify-between">
                <h3 className="text-lg font-medium">รูปภาพสินค้า ({item.images.length}/5)</h3>
                {item.images.length < 5 && (
                    <form onSubmit={handleUpload} className="flex items-center gap-2">
                        <input
                            type="file"
                            onChange={e => setData('image', e.target.files?.[0] || null)}
                            className="text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary file:text-primary-foreground hover:file:opacity-90"
                            accept="image/*"
                        />
                        <Button type="submit" disabled={processing || !data.image} size="sm">
                            <ImagePlus className="w-4 h-4 mr-2" /> อัปโหลด
                        </Button>
                    </form>
                )}
            </div>

            <div className="grid grid-cols-5 gap-4">
                {item.images.map((img) => (
                    <Card key={img.id} className={`relative overflow-hidden ${img.is_main ? 'ring-2 ring-primary' : ''}`}>
                        <CardContent className="p-0">
                            <img src={`/storage/${img.file_path}`} className="object-cover w-full aspect-square" />
                            {img.is_main && (
                                <Badge className="absolute top-2 left-2 bg-yellow-400 text-black">
                                    <Star className="w-3 h-3 mr-1 fill-current" /> รูปหลัก
                                </Badge>
                            )}
                            <div className="absolute bottom-0 flex w-full gap-1 p-2 bg-black/50 opacity-0 hover:opacity-100 transition-opacity">
                                {!img.is_main && (
                                    <Button variant="secondary" size="icon" className="h-8 w-8" onClick={() => setMain(img.id)}>
                                        <Star className="w-4 h-4" />
                                    </Button>
                                )}
                                <Button variant="destructive" size="icon" className="h-8 w-8 ml-auto" onClick={() => deleteImage(img.id)}>
                                    <Trash2 className="w-4 h-4" />
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                ))}
            </div>
        </div>
    );
}
