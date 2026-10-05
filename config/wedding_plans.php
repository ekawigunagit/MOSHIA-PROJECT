<?php

// Harga dalam rupiah utuh, bukan sen. Penawaran masih draft, belum dapat dibeli.
return [
    'packages' => [
        'gold' => [
            'key' => 'gold', 'label' => 'Gold', 'currency' => 'IDR',
            'price_amount' => 149000, 'amount_unit' => 'whole_rupiah',
            'billing_type' => 'one_time', 'invitations_per_workspace' => 1,
            'validity_months' => 6, 'starts_on' => 'first_publish',
            'standard_templates' => true, 'video_header_request' => false,
            'custom_domain' => false, 'domain_extension' => null,
            'domain_purchase_included' => false,
            'retain_data_on_expiry' => true, 'repurchase_to_reactivate' => true,
        ],
        'emerald' => [
            'key' => 'emerald', 'label' => 'Emerald', 'currency' => 'IDR',
            'price_amount' => 249000, 'amount_unit' => 'whole_rupiah',
            'billing_type' => 'one_time', 'invitations_per_workspace' => 1,
            'validity_months' => 12, 'starts_on' => 'first_publish',
            'standard_templates' => true, 'video_header_request' => true,
            'custom_domain' => false, 'domain_extension' => null,
            'domain_purchase_included' => false,
            'retain_data_on_expiry' => true, 'repurchase_to_reactivate' => true,
        ],
        'diamond' => [
            'key' => 'diamond', 'label' => 'Diamond', 'currency' => 'IDR',
            'price_amount' => 559000, 'amount_unit' => 'whole_rupiah',
            'billing_type' => 'one_time', 'invitations_per_workspace' => 1,
            'validity_months' => 12, 'starts_on' => 'first_publish',
            'standard_templates' => true, 'video_header_request' => true,
            'custom_domain' => true, 'domain_extension' => 'com',
            'domain_purchase_included' => true,
            'domain_fulfillment' => 'manual_after_customer_approval',
            'retain_data_on_expiry' => true, 'repurchase_to_reactivate' => true,
        ],
    ],
];
