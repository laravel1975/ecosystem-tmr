/**
 * Interface สำหรับข้อมูลพื้นฐานของสินค้า (Inventory Module)
 */
export interface InventoryItem {
    id: number;
    name: string;
    sku: string;
    barcode?: string;
    description?: string;
    category_id: number;
    uom_id: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

/**
 * Interface สำหรับรูปภาพสินค้า (จำกัดสูงสุด 5 รูป)
 */
export interface InventoryItemImage {
    id: number;
    inventory_item_id: number;
    file_path: string;
    file_name: string;
    is_main: boolean;
    sort_order: number;
    // URL ที่แปลงมาเพื่อให้แสดงผลใน <img> tag ได้ทันที
    preview_url?: string;
}

/**
 * Interface สำหรับจุดราคา (Price Points)
 */
export interface PricePoint {
    id?: number;
    inventory_item_id: number;
    price_list_id: number;
    amount: number;
    currency: string;
    valid_from?: string;
    valid_to?: string;
    // ข้อมูลเพิ่มเติมจาก Price List (ถ้ามีการ Join ข้อมูลมา)
    price_list?: {
        name: string;
        code: string;
    };
}

/**
 * Interface หลักสำหรับ Catalog Item (Composition)
 * ใช้สำหรับหน้าจอ Catalog Price Point Management
 */
export interface CatalogItem extends InventoryItem {
    images: InventoryItemImage[];
    main_image?: InventoryItemImage;
    price_points: PricePoint[];
}

/**
 * Interface สำหรับรายการกลุ่มราคา (Price Lists)
 */
export interface PriceList {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
}
