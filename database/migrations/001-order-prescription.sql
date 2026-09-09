-- Existing installations only. Fresh installs already have this column in schema.sql.
ALTER TABLE orders ADD COLUMN requires_prescription TINYINT(1) NOT NULL DEFAULT 0 AFTER payment_method;
UPDATE orders o SET requires_prescription=1
WHERE EXISTS (SELECT 1 FROM order_items i JOIN products p ON p.id=i.product_id WHERE i.order_id=o.id AND p.rx_required=1)
   OR EXISTS (SELECT 1 FROM prescriptions r WHERE r.order_id=o.id);
