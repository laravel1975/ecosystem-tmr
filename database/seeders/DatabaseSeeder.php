<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use TmrEcosystem\Inventory\Infrastructure\Database\Seeders\InventoryLocationSeeder;
use TmrEcosystem\Inventory\Infrastructure\Database\Seeders\InventoryMasterDataSeeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. สร้าง Admin User (ตรวจสอบก่อนว่ามี Email นี้หรือยัง เพื่อป้องกัน Error ซ้ำ)
        User::firstOrCreate(
            ['email' => 'bestcsv@gmail.com'],
            [
                'name' => 'Pornchai Sangpongpan',
                'password' => Hash::make('password'),
                'email_verified_at' => now(), // กำหนดเวลาปัจจุบัน = Verified แล้ว
            ]
        );

        // 2. เรียก Seeder ของระบบอื่นๆ
        // เรียงลำดับสำคัญ: Location -> Master Data (UOM/Cat/Item) -> Vendors/Customers
        $this->call([
            InventoryLocationSeeder::class,
            InventoryMasterDataSeeder::class,
            \TmrEcosystem\Purchase\Infrastructure\Database\Seeders\VendorSeeder::class,
            \TmrEcosystem\Sales\Infrastructure\Database\Seeders\CustomerSeeder::class,
        ]);
    }
}
