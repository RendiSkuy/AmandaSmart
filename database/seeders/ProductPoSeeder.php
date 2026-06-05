<?php
 
namespace Database\Seeders;
 
use Illuminate\Database\Seeder;
use App\Models\Product;
 
class ProductPoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. PT Unilever Indonesia (SUP-001)
        Product::create([
            'plu_code'  => 'SUP-001-P1',
            'name'      => 'Dove Beauty Bar 100gr',
            'minor'     => 24,
            'max_stock' => 1200,
            'on_hand'   => 200,
        ]);
        Product::create([
            'plu_code'  => 'SUP-001-P2',
            'name'      => 'Lifebuoy Red Soap 110g',
            'minor'     => 36,
            'max_stock' => 1500,
            'on_hand'   => 300,
        ]);
        Product::create([
            'plu_code'  => 'SUP-001-P3',
            'name'      => 'Pepsodent Cool Action 190g',
            'minor'     => 20,
            'max_stock' => 1000,
            'on_hand'   => 150,
        ]);
        Product::create([
            'plu_code'  => 'SUP-001-P4',
            'name'      => 'Sunsilk Black Active 160ml',
            'minor'     => 12,
            'max_stock' => 800,
            'on_hand'   => 100,
        ]);
        Product::create([
            'plu_code'  => 'SUP-001-P5',
            'name'      => 'Rinso Anti Noda 700g',
            'minor'     => 12,
            'max_stock' => 1200,
            'on_hand'   => 200,
        ]);

        // 2. PT Indofood CBP Sukses Makmur (SUP-002)
        Product::create([
            'plu_code'  => 'SUP-002-P1',
            'name'      => 'Indomie Goreng 85gr',
            'minor'     => 40,
            'max_stock' => 1500,
            'on_hand'   => 300,
        ]);
        Product::create([
            'plu_code'  => 'SUP-002-P2',
            'name'      => 'Indomie Rasa Ayam Bawang 75g',
            'minor'     => 40,
            'max_stock' => 1500,
            'on_hand'   => 250,
        ]);
        Product::create([
            'plu_code'  => 'SUP-002-P3',
            'name'      => 'Pop Mie Ayam 75g',
            'minor'     => 24,
            'max_stock' => 1000,
            'on_hand'   => 200,
        ]);
        Product::create([
            'plu_code'  => 'SUP-002-P4',
            'name'      => 'Chitato Sapi Panggang 68g',
            'minor'     => 30,
            'max_stock' => 800,
            'on_hand'   => 120,
        ]);
        Product::create([
            'plu_code'  => 'SUP-002-P5',
            'name'      => 'Indomilk Kental Manis 370g',
            'minor'     => 48,
            'max_stock' => 1200,
            'on_hand'   => 180,
        ]);

        // 3. PT Wings Surya (SUP-003)
        Product::create([
            'plu_code'  => 'SUP-003-P1',
            'name'      => 'So Klin Liquid 800ml',
            'minor'     => 12,
            'max_stock' => 1800,
            'on_hand'   => 300,
        ]);
        Product::create([
            'plu_code'  => 'SUP-003-P2',
            'name'      => 'Daia Deterjen Putih 1.8kg',
            'minor'     => 6,
            'max_stock' => 1000,
            'on_hand'   => 150,
        ]);
        Product::create([
            'plu_code'  => 'SUP-003-P3',
            'name'      => 'Mama Lemon Jeruk Nipis 780ml',
            'minor'     => 12,
            'max_stock' => 1200,
            'on_hand'   => 200,
        ]);
        Product::create([
            'plu_code'  => 'SUP-003-P4',
            'name'      => 'WPC Pembersih Porselen 750ml',
            'minor'     => 12,
            'max_stock' => 800,
            'on_hand'   => 100,
        ]);
        Product::create([
            'plu_code'  => 'SUP-003-P5',
            'name'      => 'Mie Sedaap Goreng 90g',
            'minor'     => 40,
            'max_stock' => 1500,
            'on_hand'   => 250,
        ]);

        // 4. PT Mayora Indah Tbk (SUP-004)
        Product::create([
            'plu_code'  => 'SUP-004-P1',
            'name'      => 'Roma Kelapa 300gr',
            'minor'     => 20,
            'max_stock' => 2000,
            'on_hand'   => 400,
        ]);
        Product::create([
            'plu_code'  => 'SUP-004-P2',
            'name'      => 'Kopiko Candy Pouch 150g',
            'minor'     => 30,
            'max_stock' => 1200,
            'on_hand'   => 200,
        ]);
        Product::create([
            'plu_code'  => 'SUP-004-P3',
            'name'      => 'Beng Beng Chocolate 20g',
            'minor'     => 120,
            'max_stock' => 2500,
            'on_hand'   => 500,
        ]);
        Product::create([
            'plu_code'  => 'SUP-004-P4',
            'name'      => 'Torabika Duo Kopi Gula 25g',
            'minor'     => 100,
            'max_stock' => 1800,
            'on_hand'   => 300,
        ]);
        Product::create([
            'plu_code'  => 'SUP-004-P5',
            'name'      => 'Malkist Crackers 120g',
            'minor'     => 30,
            'max_stock' => 1500,
            'on_hand'   => 250,
        ]);

        // 5. PT Nestle Indonesia (SUP-005)
        Product::create([
            'plu_code'  => 'SUP-005-P1',
            'name'      => 'Bear Brand 189ml',
            'minor'     => 30,
            'max_stock' => 2200,
            'on_hand'   => 200,
        ]);
        Product::create([
            'plu_code'  => 'SUP-005-P2',
            'name'      => 'Dancow Fortigro Chocolate 800g',
            'minor'     => 12,
            'max_stock' => 800,
            'on_hand'   => 100,
        ]);
        Product::create([
            'plu_code'  => 'SUP-005-P3',
            'name'      => 'Milo Activ-Go 800g',
            'minor'     => 12,
            'max_stock' => 1000,
            'on_hand'   => 150,
        ]);
        Product::create([
            'plu_code'  => 'SUP-005-P4',
            'name'      => 'Nescafe Classic Jar 100g',
            'minor'     => 24,
            'max_stock' => 800,
            'on_hand'   => 120,
        ]);
        Product::create([
            'plu_code'  => 'SUP-005-P5',
            'name'      => 'Koko Krunch Cereal 330g',
            'minor'     => 18,
            'max_stock' => 1200,
            'on_hand'   => 200,
        ]);
    }
}
