<?php

declare(strict_types=1);

use App\Core\Env;

return [
    'name' => 'Genezenz Pharmacy',
    'tagline' => 'Your Trusted Online Pharmacy',
    'founded' => '2014',
    'description' => 'Order genuine medicines, healthcare products, and vitamins online from Genezenz Pharmacy in Ganapathy, Coimbatore. Upload your prescription for fast, pharmacist-verified medicine delivery across Coimbatore.',
    'phone' => '+918044560873',
    'phone_display' => '+91 80445 60873',
    'email' => 'care@genezenz-pharmacy.in',
    'whatsapp' => Env::get('WHATSAPP_NUMBER', '918044560873'),
    'address' => 'No. 6 & 7, Adhi Vinayagar Complex, Gopalsamy Temple Street, Ganapathy, Coimbatore – 641006',
    'hours' => 'Mon – Sat, 9 AM – 8 PM',
    'facebook' => 'https://www.facebook.com/people/Genezenz-Pharmacy/100076967169526/',
    'offers' => [
        'free_delivery_above' => 499,
        'dispatch_cutoff' => '2 PM',
        'first_order_code' => 'GENEZENZ10',
    ],
    'compliance' => [
        'gstin' => Env::get('GSTIN', 'GSTIN: to be added'),
        'drug_licence' => Env::get('DRUG_LICENCE', 'Drug Licence No.: to be added'),
        'pharmacist' => Env::get('PHARMACIST_NAME', 'Pharmacist-in-charge: to be added'),
        'pharmacist_registration' => Env::get('PHARMACIST_REGISTRATION'),
    ],
    'service_areas' => [
        'Ganapathy', 'Saibaba Colony', 'RS Puram', 'Peelamedu',
        'Gandhipuram', 'Singanallur', 'Saravanampatti', 'Thudiyalur',
    ],
];
