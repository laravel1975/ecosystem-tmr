<?php

namespace TmrEcosystem\Sales\Infrastructure\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use TmrEcosystem\Sales\Infrastructure\Persistence\Eloquent\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Customer A (Retail)',
                'email' => 'customer.a@example.com',
                'phone' => '081-111-1111',
                'address' => '101 Condo A, Sukhumvit, Bangkok',
                'tax_id' => '1111111111111',
            ],
            [
                'name' => 'Customer B (Wholesale)',
                'email' => 'purchasing@company-b.com',
                'phone' => '089-999-9999',
                'address' => '202 Warehouse B, Pathum Thani',
                'tax_id' => '2222222222222',
            ],
            [
                'name' => 'Customer C (Online)',
                'email' => 'user.c@gmail.com',
                'phone' => '090-555-5555',
                'address' => '303 Village C, Chiang Mai',
                'tax_id' => null,
            ],
        ];

        foreach ($customers as $data) {
            Customer::firstOrCreate(
                ['email' => $data['email']],
                array_merge($data, [
                    'uuid' => Str::uuid(),
                    'is_active' => true,
                ])
            );
        }
    }
}
