<?php
/**
 * Payment Configuration
 * Bank cards and payment gateway settings
 */
return [
    'manual_transfer' => [
        'enabled' => true,
        'cards' => [
            // Example: Multiple cards with priority
            [
                'bank' => 'ملی',
                'number' => '6037-9918-XXXX-XXXX',
                'holder' => 'نام صاحب حساب',
                'priority' => 1,
                'active' => true,
            ],
            // Add more cards as needed
        ],
        'display_mode' => 'priority', // priority, random, single
        'require_receipt' => true,
        'receipt_upload_path' => __DIR__ . '/../public/uploads/receipts/',
    ],
    
    'gateways' => [
        'ayepardakht' => [
            'enabled' => false, // Enable in phase 2
            'merchant_id' => getenv('AYEPARDACHT_MERCHANT_ID') ?: '',
            'api_url' => 'https://shaparak.ayepardakht.ir',
        ],
        'zarinpal' => [
            'enabled' => false, // Enable in phase 2
            'merchant_id' => getenv('ZARINPAL_MERCHANT_ID') ?: '',
            'api_url' => 'https://api.zarinpal.com/pg/v4/payment/',
        ],
    ],
    
    'order_statuses' => [
        'pending' => 'معلق',
        'paid' => 'پرداخت شده',
        'confirmed' => 'تأیید شده',
        'completed' => 'تکمیل شده',
        'cancelled' => 'لغو شده',
        'failed' => 'خطا',
    ],
];
