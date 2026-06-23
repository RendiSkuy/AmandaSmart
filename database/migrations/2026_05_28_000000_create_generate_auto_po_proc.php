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
                supp RECORD;
                prod RECORD;
                qty INT;
                po_id_val INT;
                po_num VARCHAR;
                items_count INT;
            BEGIN
                -- Loop per supplier
                FOR supp IN 
                    SELECT id, supplier_code FROM suppliers 
                    WHERE (p_supplier_id IS NULL OR id = p_supplier_id)
                LOOP
                    -- Hitung jumlah item kritis milik supplier ini yang belum dibuatkan draft PO-nya
                    SELECT COUNT(*) INTO items_count
                    FROM products p
                    WHERE p.on_hand < p.max_stock
                      AND p.plu_code LIKE supp.supplier_code || '%'
                      AND NOT EXISTS (
                          SELECT 1 
                          FROM purchase_order_details pod
                          JOIN purchase_orders po ON pod.purchase_order_id = po.id
                          WHERE pod.product_id = p.id
                            AND po.status = 'PENDING_BIDDING'
                      );
                      
                    IF items_count > 0 THEN
                        -- Generate nomor PO acak unik
                        po_num := 'PO-' || UPPER(SUBSTRING(MD5(RANDOM()::TEXT) FROM 1 FOR 4)) || '-' || TO_CHAR(CURRENT_DATE, 'YYYYMMDD');
                        
                        -- Insert Header PO
                        INSERT INTO purchase_orders (po_number, status, selected_supplier_id, delivery_deadline, created_at, updated_at)
                        VALUES (po_num, 'PENDING_BIDDING', supp.id, CURRENT_TIMESTAMP + INTERVAL '3 days', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                        RETURNING id INTO po_id_val;
                        
                        -- Loop masukkan Detail Item PO
                        FOR prod IN 
                            SELECT p.id, p.max_stock, p.on_hand, p.minor
                            FROM products p
                            WHERE p.on_hand < p.max_stock
                              AND p.plu_code LIKE supp.supplier_code || '%'
                              AND NOT EXISTS (
                                  SELECT 1 
                                  FROM purchase_order_details pod
                                  JOIN purchase_orders po ON pod.purchase_order_id = po.id
                                  WHERE pod.product_id = p.id
                                    AND po.status = 'PENDING_BIDDING'
                              )
                        LOOP
                            IF prod.minor > 0 THEN
                                qty := ( (prod.max_stock - prod.on_hand) / prod.minor ) * prod.minor;
                            ELSE
                                qty := prod.max_stock - prod.on_hand;
                            END IF;
                            
                            IF qty > 0 THEN
                                INSERT INTO purchase_order_details (purchase_order_id, product_id, qty_po, price_per_pcs, created_at, updated_at)
                                VALUES (po_id_val, prod.id, qty, 0.0, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
                            END IF;
                        END LOOP;
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
