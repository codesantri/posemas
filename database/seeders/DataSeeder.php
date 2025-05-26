<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Customers
        DB::table('customers')->insert([
            [
                'name' => 'Andi Saputra',
                'address' => 'Jl. Kenanga No.5, Jakarta',
                'phone' => '081234567890',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Siti Aminah',
                'address' => 'Jl. Melati No.3, Bandung',
                'phone' => '081298765432',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Budi Santoso',
                'address' => 'Jl. Anggrek No.7, Surabaya',
                'phone' => '081345678901',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        DB::table('categories')->insert([
            ['name' => 'Emas'],
            ['name' => 'Perak'],
            ['name' => 'Platina'],
        ]);

        // Types
        DB::table('types')->insert([
            ['name' => 'Cincin'],
            ['name' => 'Kalung'],
            ['name' => 'Gelang'],
        ]);

        // Karats
        DB::table('karats')->insert([
            ['karat' => '24K', 'rate' => 99.9, 'buy_price' => 950000, 'sell_price' => 970000],
            ['karat' => '22K', 'rate' => 91.6, 'buy_price' => 850000, 'sell_price' => 870000],
            ['karat' => '18K', 'rate' => 75.0, 'buy_price' => 750000, 'sell_price' => 770000],
        ]);

        // Suppliers
        DB::table('suppliers')->insert([
            ['name' => 'PT. Maju Jaya', 'address' => 'Jl. Merdeka No.1', 'status' => true],
            ['name' => 'CV. Logam Mulia', 'address' => 'Jl. Emas No.12', 'status' => true],
            ['name' => 'Toko Perhiasan Kita', 'address' => 'Jl. Mawar No.8', 'status' => false],
        ]);

        DB::table('products')->insert([
            [
                'name' => 'Cincin Emas 24K',
                'category_id' => 1, // asumsi 'Emas' id = 1
                'type_id' => 1,     // asumsi 'Cincin' id = 1
                'karat_id' => 1,    // asumsi '24K' id = 1
                'weight' => 5.50,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Kalung Perak 22K',
                'category_id' => 2, // 'Perak'
                'type_id' => 2,     // 'Kalung'
                'karat_id' => 2,    // '22K'
                'weight' => 10.25,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Gelang Platina 18K',
                'category_id' => 3, // 'Platina'
                'type_id' => 3,     // 'Gelang'
                'karat_id' => 3,    // '18K'
                'weight' => 7.75,
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);


        // Stock Totals
        DB::table('stock_totals')->insert([
            ['product_id' => 1, 'total' => 10, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 2, 'total' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 3, 'total' => 20, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Stocks (Detail stok per pemasok)
        DB::table('stocks')->insert([
            [
                'product_id' => 1,
                'supplier_id' => 1,
                'stock_quantity' => 6,
                'received_at' => now()->subDays(10),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 1,
                'supplier_id' => 2,
                'stock_quantity' => 4,
                'received_at' => now()->subDays(5),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'product_id' => 2,
                'supplier_id' => 1,
                'stock_quantity' => 5,
                'received_at' => now()->subDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // No stock for product 3 to simulate empty stock
        ]);
    }
}
