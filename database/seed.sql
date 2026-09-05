SET NAMES utf8mb4;
SET time_zone = '+00:00';

INSERT INTO categories (name, slug) VALUES
('Baby Care', 'baby-care'),
('Cold & Fever', 'cold-fever'),
('Diabetes Care', 'diabetes-care'),
('Pain Relief', 'pain-relief'),
('Personal Care', 'personal-care'),
('Vitamins & Supplements', 'vitamins')
ON DUPLICATE KEY UPDATE name = VALUES(name);

INSERT INTO products (category_id, name, slug, description, price, mrp, cost_price, stock, brand, salt_name, rx_required, published)
SELECT c.id, seed.name, seed.slug, seed.description, seed.price, seed.mrp, seed.cost_price, seed.stock, 'Generic', seed.salt_name, 0, 1
FROM (
  SELECT 'Cold & Fever' category_name, 'ORS Electrolyte Powder (Pack of 10 Sachets)' name, 'ors-electrolyte-powder-pack-of-10-sachets' slug, 'Oral rehydration salts for replacing fluids and electrolytes during dehydration.' description, 90.00 price, 110.00 mrp, 60.00 cost_price, 160 stock, 'Oral Rehydration Salts' salt_name
  UNION ALL SELECT 'Cold & Fever','Cetirizine 10mg Tablets (Strip of 10)','cetirizine-10mg-tablets-strip-of-10','Cetirizine antihistamine tablets for common allergy symptoms, dispensed with pharmacist guidance.',25.00,32.00,16.00,145,'Cetirizine 10mg'
  UNION ALL SELECT 'Vitamins & Supplements','Vitamin C 500mg Chewable Tablets (Strip of 15)','vitamin-c-500mg-chewable-tablets-strip-of-15','Chewable vitamin C supplement, strip of 15 tablets.',110.00,140.00,74.00,132,'Ascorbic Acid 500mg'
  UNION ALL SELECT 'Pain Relief','Paracetamol 650mg Tablets (Strip of 15)','paracetamol-650mg-tablets-strip-of-15','Paracetamol 650mg tablets for fever and mild to moderate pain. Use exactly as directed.',30.00,35.00,20.00,120,'Paracetamol 650mg'
  UNION ALL SELECT 'Vitamins & Supplements','Iron + Folic Acid Tablets (Strip of 15)','iron-folic-acid-tablets-strip-of-15','Iron and folic acid supplement for use when recommended by a healthcare professional.',85.00,105.00,57.00,112,'Ferrous Ascorbate + Folic Acid'
  UNION ALL SELECT 'Pain Relief','Ibuprofen 400mg Tablets (Strip of 15)','ibuprofen-400mg-tablets-strip-of-15','Anti-inflammatory pain relief tablets. Check with the pharmacist if you have stomach, kidney or asthma concerns.',42.00,52.00,28.00,100,'Ibuprofen 400mg'
  UNION ALL SELECT 'Vitamins & Supplements','Calcium + Vitamin D3 Tablets (Strip of 15)','calcium-vitamin-d3-tablets-strip-of-15','Calcium and vitamin D3 supplement for bone health when advised by a healthcare professional.',145.00,180.00,97.00,98,'Calcium Carbonate + Vitamin D3'
  UNION ALL SELECT 'Cold & Fever','Cough Syrup 100ml (Dry & Wet Cough)','cough-syrup-100ml-dry-wet-cough','Pharmacist-guided cough syrup for common dry and wet cough symptoms.',105.00,130.00,70.00,94,NULL
) seed
JOIN categories c ON c.name = seed.category_name
ON DUPLICATE KEY UPDATE
  description = VALUES(description), price = VALUES(price), mrp = VALUES(mrp),
  cost_price = VALUES(cost_price), stock = VALUES(stock), salt_name = VALUES(salt_name), updated_at = UTC_TIMESTAMP();
