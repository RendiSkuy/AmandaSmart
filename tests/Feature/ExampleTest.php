<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExampleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_wings_supplier_login_and_dashboard()
    {
        // 1. Ambil user wings_sales1
        $user = User::where('username', 'wings_sales1')->first();
        $this->assertNotNull($user, 'User wings_sales1 harus ada di database.');

        // 2. Kirim POST request untuk login
        $response = $this->post('/login-supplier', [
            'username' => 'wings_sales1',
            'password' => 'password123',
        ]);

        // Harus redirect ke /dashboard
        $response->assertRedirect('/dashboard');

        // Pastikan terautentikasi sebagai wings_sales1
        $this->assertAuthenticatedAs($user);

        // 3. Akses dashboard dan cek apakah kontennya adalah Wings
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('PT Wings Surya');
        $dashboardResponse->assertSee('wings_sales1');
        $dashboardResponse->assertDontSee('PT Unilever Indonesia');
    }

    public function test_unilever_supplier_login_and_dashboard()
    {
        // 1. Ambil user unilever_sales1
        $user = User::where('username', 'unilever_sales1')->first();
        $this->assertNotNull($user, 'User unilever_sales1 harus ada di database.');

        // 2. Kirim POST request untuk login
        $response = $this->post('/login-supplier', [
            'username' => 'unilever_sales1',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // 3. Akses dashboard dan cek apakah kontennya adalah Unilever
        $dashboardResponse = $this->actingAs($user)->get('/dashboard');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('PT Unilever Indonesia');
        $dashboardResponse->assertSee('unilever_sales1');
        $dashboardResponse->assertDontSee('PT Wings Surya');
    }

    public function test_md_approve_offer_quick()
    {
        // 1. Ambil user MD dan produk
        $md = User::where('role', 'md')->first();
        $this->assertNotNull($md);

        $product = \App\Models\Product::first();
        $supplier = \App\Models\Supplier::first();

        // 2. Buat PO PENDING_BIDDING
        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-TEST-QUICK',
            'product_id' => $product->id,
            'qty_po' => 1000,
            'status' => 'PENDING_BIDDING',
        ]);

        // 3. MD melakukan Quick Approval (buat offer & approve sekaligus)
        $response = $this->actingAs($md)->post('/dashboard/offers/approve-quick', [
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'price_per_pcs' => 12500,
            'delivery_deadline' => now()->addDays(5)->format('Y-m-d'),
        ]);

        $response->assertRedirect();
        
        // Verifikasi PO terupdate
        $po->refresh();
        $this->assertEquals('APPROVED', $po->status);
        $this->assertEquals($supplier->id, $po->selected_supplier_id);

        // Verifikasi Offer dibuat
        $offer = \App\Models\Offer::where('purchase_order_id', $po->id)
            ->where('supplier_id', $supplier->id)
            ->first();
        $this->assertNotNull($offer);
        $this->assertEquals(12500, $offer->price_per_pcs);
        $this->assertEquals('accepted', $offer->status);
    }

    public function test_multiple_offers_from_same_supplier_different_accounts()
    {
        // 1. Ambil 2 akun sales unilever
        $sales1 = User::where('username', 'unilever_sales1')->first();
        $sales2 = User::where('username', 'unilever_sales2')->first();
        $this->assertNotNull($sales1);
        $this->assertNotNull($sales2);

        $product = \App\Models\Product::first();
        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-TEST-MULTIPLE',
            'product_id' => $product->id,
            'qty_po' => 1000,
            'status' => 'PENDING_BIDDING',
        ]);

        // 2. Sales 1 submit offer
        $response1 = $this->actingAs($sales1)->post("/api/supplier/purchase-orders/{$po->id}/offers", [
            'price_per_pcs' => 3000,
        ]);
        $response1->assertStatus(200);

        // 3. Sales 2 submit offer untuk PO yang sama
        $response2 = $this->actingAs($sales2)->post("/api/supplier/purchase-orders/{$po->id}/offers", [
            'price_per_pcs' => 2800,
        ]);
        $response2->assertStatus(200);

        // 4. Verifikasi di DB ada 2 baris penawaran terpisah
        $offers = \App\Models\Offer::where('purchase_order_id', $po->id)->get();
        $this->assertCount(2, $offers);
        
        $offer1 = $offers->where('user_id', $sales1->id)->first();
        $offer2 = $offers->where('user_id', $sales2->id)->first();
        
        $this->assertNotNull($offer1);
        $this->assertNotNull($offer2);
        $this->assertEquals(3000, $offer1->price_per_pcs);
        $this->assertEquals(2800, $offer2->price_per_pcs);
    }

    public function test_md_compare_offers_endpoint()
    {
        $md = User::where('role', 'md')->first();
        $sales1 = User::where('username', 'unilever_sales1')->first();
        $sales2 = User::where('username', 'unilever_sales2')->first();

        $product = \App\Models\Product::first();
        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-TEST-COMPARE',
            'product_id' => $product->id,
            'qty_po' => 1200,
            'status' => 'PENDING_BIDDING',
        ]);

        // Submit penawaran
        \App\Models\Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $sales1->supplier_id,
            'user_id' => $sales1->id,
            'price_per_pcs' => 3000,
            'status' => 'pending'
        ]);

        \App\Models\Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $sales2->supplier_id,
            'user_id' => $sales2->id,
            'price_per_pcs' => 2800,
            'status' => 'pending'
        ]);

        // Hit compare endpoint
        $response = $this->actingAs($md)->getJson("/api/md/purchase-orders/{$po->id}/compare");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data' => [
                'purchase_order',
                'offers' => [
                    '*' => [
                        'id',
                        'supplier_name',
                        'sales_username',
                        'price_per_pcs',
                        'total_gross_price',
                        'status',
                        'created_at'
                    ]
                ]
            ]
        ]);

        // Cek kalkulasi kotor (1200 * 3000 = 3,600,000 dan 1200 * 2800 = 3,360,000)
        $data = $response->json('data.offers');
        $this->assertCount(2, $data);
        
        $this->assertEquals(3600000, $data[0]['total_gross_price']);
        $this->assertEquals(3360000, $data[1]['total_gross_price']);
    }

    public function test_sales_data_isolation()
    {
        // 1. Ambil user sales1 dan sales2
        $sales1 = User::where('username', 'unilever_sales1')->first();
        $sales2 = User::where('username', 'unilever_sales2')->first();
        $this->assertNotNull($sales1);
        $this->assertNotNull($sales2);

        $product = \App\Models\Product::first();

        // 2. Buat PO
        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-ISO-TEST-1',
            'product_id' => $product->id,
            'qty_po' => 1000,
            'status' => 'APPROVED',
            'selected_supplier_id' => $sales2->supplier_id,
        ]);

        // 3. Buat tawaran diterima dari sales2
        $offer = \App\Models\Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $sales2->supplier_id,
            'user_id' => $sales2->id,
            'price_per_pcs' => 5000,
            'status' => 'accepted',
        ]);

        // 4. Buat VRS, LPB, dan TTF
        $vrs = \App\Models\VrsSchedule::create([
            'purchase_order_id' => $po->id,
            'scheduled_date' => now()->format('Y-m-d'),
            'time_slot' => '09:00 - 11:00',
            'status' => 'completed',
        ]);

        $lpb = \App\Models\GoodsReceipt::create([
            'purchase_order_id' => $po->id,
            'qty_received' => 1000,
            'received_at' => now(),
        ]);

        $ttf = \App\Models\Ttf::create([
            'goods_receipt_id' => $lpb->id,
            'total_amount' => 5000000,
            'total_deductions' => 0,
            'status_payment' => 'pending',
        ]);

        // 5. Uji Akses Dashboard sales1 (unselected)
        $responseSales1 = $this->actingAs($sales1)->get('/dashboard');
        $responseSales1->assertStatus(200);
        $responseSales1->assertDontSee('PO-ISO-TEST-1');

        // 6. Uji Akses Dashboard sales2 (selected)
        $responseSales2 = $this->actingAs($sales2)->get('/dashboard');
        $responseSales2->assertStatus(200);
        $responseSales2->assertSee('PO-ISO-TEST-1');

        // 7. Uji Akses Detail LPB sales1 (unselected) -> Harus 403
        $responseLpb1 = $this->actingAs($sales1)->get("/dashboard/lpb/{$lpb->id}/print");
        $responseLpb1->assertStatus(403);

        // 8. Uji Akses Detail LPB sales2 (selected) -> Harus 200
        $responseLpb2 = $this->actingAs($sales2)->get("/dashboard/lpb/{$lpb->id}/print");
        $responseLpb2->assertStatus(200);

        // 9. Uji Akses Detail TTF sales1 (unselected) -> Harus 403
        $responseTtf1 = $this->actingAs($sales1)->get("/dashboard/ttf/{$ttf->id}/print");
        $responseTtf1->assertStatus(403);

        // 10. Uji Akses Detail TTF sales2 (selected) -> Harus 200
        $responseTtf2 = $this->actingAs($sales2)->get("/dashboard/ttf/{$ttf->id}/print");
        $responseTtf2->assertStatus(200);
    }

    public function test_vrs_slot_quota_web_and_api()
    {
        $sales = User::where('username', 'unilever_sales1')->first();
        $this->assertNotNull($sales);

        $product = \App\Models\Product::first();

        // Buat 6 PO berbeda
        $pos = [];
        for ($i = 1; $i <= 6; $i++) {
            $pos[] = \App\Models\PurchaseOrder::create([
                'po_number' => "PO-VRS-QT-{$i}",
                'product_id' => $product->id,
                'qty_po' => 1000,
                'status' => 'APPROVED',
                'selected_supplier_id' => $sales->supplier_id,
            ]);
        }

        // Buat accepted offers untuk keenamnya
        foreach ($pos as $po) {
            \App\Models\Offer::create([
                'purchase_order_id' => $po->id,
                'supplier_id' => $sales->supplier_id,
                'user_id' => $sales->id,
                'price_per_pcs' => 5000,
                'status' => 'accepted',
            ]);
        }

        $targetDate = now()->addDays(5)->format('Y-m-d');
        $targetSlot = '08:00 - 09:00';

        // 1. Booking 1-3 via Web -> Harus Sukses
        for ($i = 0; $i < 3; $i++) {
            $response = $this->actingAs($sales)->post('/dashboard/vrs/booking', [
                'purchase_order_id' => $pos[$i]->id,
                'scheduled_date' => $targetDate,
                'time_slot' => $targetSlot,
            ]);
            $response->assertSessionHas('success');
        }

        // 2. Booking 4-5 via API -> Harus Sukses
        for ($i = 3; $i < 5; $i++) {
            $response = $this->actingAs($sales)->postJson('/api/supplier/vrs/booking', [
                'purchase_order_id' => $pos[$i]->id,
                'scheduled_date' => $targetDate,
                'time_slot' => $targetSlot,
            ]);
            $response->assertStatus(200);
        }

        // 3. Booking ke-6 via Web -> Harus Gagal (Penuh)
        $responseWeb6 = $this->actingAs($sales)->post('/dashboard/vrs/booking', [
            'purchase_order_id' => $pos[5]->id,
            'scheduled_date' => $targetDate,
            'time_slot' => $targetSlot,
        ]);
        $responseWeb6->assertSessionHas('error');

        // 4. Booking ke-6 via API -> Harus Gagal (Penuh)
        $responseApi6 = $this->actingAs($sales)->postJson('/api/supplier/vrs/booking', [
            'purchase_order_id' => $pos[5]->id,
            'scheduled_date' => $targetDate,
            'time_slot' => $targetSlot,
        ]);
        $responseApi6->assertStatus(422);
        $responseApi6->assertJsonFragment([
            'status' => 'error',
        ]);
    }

    public function test_bulk_offer_submission_web()
    {
        $sales = User::where('username', 'unilever_sales1')->first();
        $this->assertNotNull($sales);

        $product = \App\Models\Product::first();

        // Buat 2 PO baru
        $po1 = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-BULK-1',
            'product_id' => $product->id,
            'qty_po' => 500,
            'status' => 'PENDING_BIDDING',
        ]);
        $po2 = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-BULK-2',
            'product_id' => $product->id,
            'qty_po' => 600,
            'status' => 'PENDING_BIDDING',
        ]);

        // Submit bulk penawaran via Web Dashboard
        $response = $this->actingAs($sales)->post('/dashboard/offers/submit', [
            'prices' => [
                $po1->id => 12000,
                $po2->id => 11500,
            ]
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi penawaran tersimpan di DB
        $offer1 = \App\Models\Offer::where('purchase_order_id', $po1->id)
            ->where('user_id', $sales->id)
            ->first();
        $offer2 = \App\Models\Offer::where('purchase_order_id', $po2->id)
            ->where('user_id', $sales->id)
            ->first();

        $this->assertNotNull($offer1);
        $this->assertNotNull($offer2);
        $this->assertEquals(12000, $offer1->price_per_pcs);
        $this->assertEquals(11500, $offer2->price_per_pcs);
        $this->assertEquals('pending', $offer1->status);
        $this->assertEquals('pending', $offer2->status);
    }

    public function test_products_grouping_and_display_attributes()
    {
        // 1. Ambil user MD
        $md = User::where('role', 'md')->first();
        $this->assertNotNull($md);

        // 2. Ambil supplier & product untuk dicocokkan
        $supplier = \App\Models\Supplier::first();
        $this->assertNotNull($supplier);

        // 3. Akses dashboard tab products
        $response = $this->actingAs($md)->get('/dashboard?tab=products');

        $response->assertStatus(200);
        // Cek pengelompokan kategori supplier name & supplier code pada Master Produk
        $response->assertSee($supplier->name);
        $response->assertSee($supplier->supplier_code);
        $response->assertSee('Master Produk');
    }

    public function test_lpb_grouping_and_display_attributes()
    {
        // 1. Ambil user MD
        $md = User::where('role', 'md')->first();
        $this->assertNotNull($md);

        // 2. Ambil supplier & product
        $supplier = \App\Models\Supplier::first();
        $product = \App\Models\Product::first();
        $this->assertNotNull($supplier);
        $this->assertNotNull($product);

        $salesUser = User::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($salesUser);

        // 3. Buat PO berstatus APPROVED dengan selected_supplier_id
        $poNumber = 'PO-LPB-TEST-999';
        $po = \App\Models\PurchaseOrder::create([
            'po_number' => $poNumber,
            'product_id' => $product->id,
            'qty_po' => 750,
            'status' => 'APPROVED',
            'selected_supplier_id' => $supplier->id,
        ]);

        // Buat penawaran diterima (accepted offer) agar ada sales user pengirim
        \App\Models\Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'user_id' => $salesUser->id,
            'price_per_pcs' => 10000,
            'status' => 'accepted',
        ]);

        // Buat antrean VRS agar PO muncul di tab LPB
        \App\Models\VrsSchedule::create([
            'purchase_order_id' => $po->id,
            'scheduled_date' => today()->addDays(1),
            'time_slot' => '09:00 - 11:00',
            'status' => 'pending',
        ]);

        // 4. Akses dashboard tab lpb
        $response = $this->actingAs($md)->get('/dashboard?tab=lpb');

        $response->assertStatus(200);
        // Cek pengelompokan kategori supplier name & supplier code
        $response->assertSee($supplier->name);
        $response->assertSee($supplier->supplier_code);
        // Cek Faktur PO
        $response->assertSee('Faktur PO:');
        $response->assertSee($poNumber);
        $response->assertSee('PO Siap Bongkar');
        // Cek Nama Akun Supplier di detail dan header
        $response->assertSee('Akun Supplier:');
        $response->assertSee('Supplier:');
        $response->assertSee($salesUser->username);
    }

    public function test_vrs_grouping_and_display_attributes()
    {
        $md = User::where('role', 'md')->first();
        $this->assertNotNull($md);

        $supplier = \App\Models\Supplier::first();
        $product = \App\Models\Product::first();
        $salesUser = User::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($salesUser);

        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-VRS-TEST-888',
            'product_id' => $product->id,
            'qty_po' => 500,
            'status' => 'APPROVED',
            'selected_supplier_id' => $supplier->id,
        ]);

        \App\Models\Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'user_id' => $salesUser->id,
            'price_per_pcs' => 10000,
            'status' => 'accepted',
        ]);

        \App\Models\VrsSchedule::create([
            'purchase_order_id' => $po->id,
            'scheduled_date' => now()->format('Y-m-d'),
            'time_slot' => '08:00 - 10:00',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($md)->get('/dashboard?tab=vrs');
        $response->assertStatus(200);

        // Cek pengelompokan kategori supplier name & supplier code
        $response->assertSee($supplier->name);
        $response->assertSee($supplier->supplier_code);
        // Cek kolom Nama Akun Sales dan nama akun sales di header & detail
        $response->assertSee('Nama Akun Sales');
        $response->assertSee('Sales:');
        $response->assertSee($salesUser->username);
        $response->assertSee('Jadwal Truk');
    }

    public function test_ttf_grouping_and_display_attributes()
    {
        $md = User::where('role', 'md')->first();
        $this->assertNotNull($md);

        $supplier = \App\Models\Supplier::first();
        $product = \App\Models\Product::first();
        $salesUser = User::where('supplier_id', $supplier->id)->first();
        $this->assertNotNull($salesUser);

        $po = \App\Models\PurchaseOrder::create([
            'po_number' => 'PO-TTF-TEST-777',
            'product_id' => $product->id,
            'qty_po' => 800,
            'status' => 'APPROVED',
            'selected_supplier_id' => $supplier->id,
        ]);

        \App\Models\Offer::create([
            'purchase_order_id' => $po->id,
            'supplier_id' => $supplier->id,
            'user_id' => $salesUser->id,
            'price_per_pcs' => 12000,
            'status' => 'accepted',
        ]);

        $gr = \App\Models\GoodsReceipt::create([
            'purchase_order_id' => $po->id,
            'qty_received' => 800,
            'received_at' => now(),
        ]);

        $ttf = \App\Models\Ttf::create([
            'goods_receipt_id' => $gr->id,
            'total_amount' => 12000 * 800,
            'total_deductions' => 0,
            'status_payment' => 'pending',
        ]);

        $response = $this->actingAs($md)->get('/dashboard?tab=ttf');
        $response->assertStatus(200);

        // Cek pengelompokan kategori supplier name & supplier code
        $response->assertSee($supplier->name);
        $response->assertSee($supplier->supplier_code);
        // Cek kolom/label Akun Sales dan nama akun sales di header & detail
        $response->assertSee('Sales:');
        $response->assertSee($salesUser->username);
        $response->assertSee('Akun Sales:');
    }
}


