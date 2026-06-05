<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::unprepared("
            DROP PROCEDURE IF EXISTS generate_auto_po_proc(integer, integer);
            DROP PROCEDURE IF EXISTS generate_auto_po_proc(int, int);
            
            CREATE OR REPLACE PROCEDURE generate_auto_po_proc(p_supplier_id INT, p_user_id INT)
            LANGUAGE plpgsql
            AS $$
            DECLARE
                prod RECORD;
                qty INT;
                po_num VARCHAR;
            BEGIN
                FOR prod IN 
                    SELECT id, max_stock, on_hand FROM products WHERE on_hand < max_stock
                LOOP
                    -- Cek jika draf PO dengan status PENDING_BIDDING sudah ada untuk produk ini
                    IF NOT EXISTS (
                        SELECT 1 FROM purchase_orders 
                        WHERE product_id = prod.id 
                          AND status = 'PENDING_BIDDING'
                    ) THEN
                        qty := prod.max_stock - prod.on_hand;
                        -- Generate nomor PO acak yang unik
                        po_num := 'PO-' || UPPER(SUBSTRING(MD5(RANDOM()::TEXT) FROM 1 FOR 4)) || '-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD');
                        
                        INSERT INTO purchase_orders (po_number, product_id, qty_po, status, selected_supplier_id, delivery_deadline, created_at, updated_at)
                        VALUES (po_num, prod.id, qty, 'PENDING_BIDDING', NULL, CURRENT_TIMESTAMP + INTERVAL '3 days', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
                    END IF;
                END LOOP;
            END;
            $$;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS generate_auto_po_proc(INT, INT);");
    }
};
