<?php

namespace TmrEcosystem\Purchase\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use TmrEcosystem\Purchase\Infrastructure\Persistence\Eloquent\Models\Vendor;

class VendorSeeder extends Seeder
{
    public function run(): void
    {
        $vendors = [
            [
                'name' => 'Supplier A (Raw Materials)',
                'email' => 'contact@supplier-a.com',
                'phone' => '02-111-1111',
                'address' => '123 Industrial Park, Bangkok',
                'tax_id' => '1234567890123',
            ],
            [
                'name' => 'Supplier B (Packaging)',
                'email' => 'sales@supplier-b.com',
                'phone' => '02-222-2222',
                'address' => '456 Packaging Road, Samut Prakan',
                'tax_id' => '9876543210987',
            ],
            [
                'name' => 'Supplier C (General)',
                'email' => 'info@supplier-c.com',
                'phone' => '02-333-3333',
                'address' => '789 General Street, Nonthaburi',
                'tax_id' => '5555555555555',
            ],
        ];

        foreach ($vendors as $data) {
            Vendor::firstOrCreate(
                ['email' => $data['email']], // เช็คจาก email เพื่อไม่ให้ซ้ำ
                array_merge($data, [
                    'uuid' => Str::uuid(),
                    'is_active' => true,
                ])
            );
        }
    }
}
