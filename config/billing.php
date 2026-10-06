<?php

return [
    // Dummy transfers are only available in local/testing, never production.
    'manual_development_enabled' => env('MANUAL_DEVELOPMENT_PAYMENTS', true),
    'manual_bank' => [
        'bank' => 'BCA',
        'account_number' => '12345678',
        'account_name' => 'MOSHIA CORPORATE',
    ],
    'planned_gateway' => 'midtrans',
];
