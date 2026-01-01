<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
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
        // เรียงลำดับสำคัญ: Location -> Master Data (UOM/Cat/Item)
        $this->call([
            InventoryLocationSeeder::class,
            InventoryMasterDataSeeder::class,
        ]);
    }
}
