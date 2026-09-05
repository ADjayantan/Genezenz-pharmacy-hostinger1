<?php

declare(strict_types=1);

return [
    'categories' => [
        ['id' => 1, 'name' => 'Baby Care', 'slug' => 'baby-care', 'product_count' => 2],
        ['id' => 2, 'name' => 'Cold & Fever', 'slug' => 'cold-fever', 'product_count' => 4],
        ['id' => 3, 'name' => 'Diabetes Care', 'slug' => 'diabetes-care', 'product_count' => 4],
        ['id' => 4, 'name' => 'Pain Relief', 'slug' => 'pain-relief', 'product_count' => 3],
        ['id' => 5, 'name' => 'Personal Care', 'slug' => 'personal-care', 'product_count' => 2],
        ['id' => 6, 'name' => 'Vitamins & Supplements', 'slug' => 'vitamins', 'product_count' => 5],
    ],
    'products' => [
        [
            'id' => 1, 'name' => 'ORS Electrolyte Powder (Pack of 10 Sachets)', 'slug' => 'ors-electrolyte-powder-pack-of-10-sachets',
            'description' => 'Oral rehydration salts for replacing fluids and electrolytes during dehydration.',
            'price' => 90.00, 'mrp' => 110.00, 'stock' => 160, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Oral Rehydration Salts', 'rx_required' => 0, 'category_slug' => 'cold-fever', 'category_name' => 'Cold & Fever',
        ],
        [
            'id' => 2, 'name' => 'Cetirizine 10mg Tablets (Strip of 10)', 'slug' => 'cetirizine-10mg-tablets-strip-of-10',
            'description' => 'Cetirizine antihistamine tablets for common allergy symptoms, dispensed with pharmacist guidance.',
            'price' => 25.00, 'mrp' => 32.00, 'stock' => 145, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Cetirizine 10mg', 'rx_required' => 0, 'category_slug' => 'cold-fever', 'category_name' => 'Cold & Fever',
        ],
        [
            'id' => 3, 'name' => 'Vitamin C 500mg Chewable Tablets (Strip of 15)', 'slug' => 'vitamin-c-500mg-chewable-tablets-strip-of-15',
            'description' => 'Chewable vitamin C supplement, strip of 15 tablets.',
            'price' => 110.00, 'mrp' => 140.00, 'stock' => 132, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Ascorbic Acid 500mg', 'rx_required' => 0, 'category_slug' => 'vitamins', 'category_name' => 'Vitamins & Supplements',
        ],
        [
            'id' => 4, 'name' => 'Paracetamol 650mg Tablets (Strip of 15)', 'slug' => 'paracetamol-650mg-tablets-strip-of-15',
            'description' => 'Paracetamol 650mg tablets for fever and mild to moderate pain. Use exactly as directed.',
            'price' => 30.00, 'mrp' => 35.00, 'stock' => 120, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Paracetamol 650mg', 'rx_required' => 0, 'category_slug' => 'pain-relief', 'category_name' => 'Pain Relief',
        ],
        [
            'id' => 5, 'name' => 'Iron + Folic Acid Tablets (Strip of 15)', 'slug' => 'iron-folic-acid-tablets-strip-of-15',
            'description' => 'Iron and folic acid supplement for use when recommended by a healthcare professional.',
            'price' => 85.00, 'mrp' => 105.00, 'stock' => 112, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Ferrous Ascorbate + Folic Acid', 'rx_required' => 0, 'category_slug' => 'vitamins', 'category_name' => 'Vitamins & Supplements',
        ],
        [
            'id' => 6, 'name' => 'Ibuprofen 400mg Tablets (Strip of 15)', 'slug' => 'ibuprofen-400mg-tablets-strip-of-15',
            'description' => 'Anti-inflammatory pain relief tablets. Check with the pharmacist if you have stomach, kidney or asthma concerns.',
            'price' => 42.00, 'mrp' => 52.00, 'stock' => 100, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Ibuprofen 400mg', 'rx_required' => 0, 'category_slug' => 'pain-relief', 'category_name' => 'Pain Relief',
        ],
        [
            'id' => 7, 'name' => 'Calcium + Vitamin D3 Tablets (Strip of 15)', 'slug' => 'calcium-vitamin-d3-tablets-strip-of-15',
            'description' => 'Calcium and vitamin D3 supplement for bone health when advised by a healthcare professional.',
            'price' => 145.00, 'mrp' => 180.00, 'stock' => 98, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => 'Calcium Carbonate + Vitamin D3', 'rx_required' => 0, 'category_slug' => 'vitamins', 'category_name' => 'Vitamins & Supplements',
        ],
        [
            'id' => 8, 'name' => 'Cough Syrup 100ml (Dry & Wet Cough)', 'slug' => 'cough-syrup-100ml-dry-wet-cough',
            'description' => 'Pharmacist-guided cough syrup for common dry and wet cough symptoms.',
            'price' => 105.00, 'mrp' => 130.00, 'stock' => 94, 'image_url' => null, 'brand' => 'Generic',
            'salt_name' => null, 'rx_required' => 0, 'category_slug' => 'cold-fever', 'category_name' => 'Cold & Fever',
        ],
    ],
];
