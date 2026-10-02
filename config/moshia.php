<?php

return [
    // Availability describes shipped functionality, not a purchased entitlement.
    'products' => [
        'wedding' => [
            'id' => 'wedding', 'title' => 'Wedding Invitation', 'icon' => 'link',
            'summary' => 'Undangan digital untuk momen istimewa Anda.',
            'description' => 'Pilih template, isi konten, preview, lalu terbitkan undangan. RSVP, tamu, dan domain menyusul secara bertahap.',
            'status' => 'planned', 'phase' => 2,
        ],
        'jastip' => [
            'id' => 'jastip', 'title' => 'Jastip Manager', 'icon' => 'grid',
            'summary' => 'Kelola trip, pesanan pelanggan, dan pengiriman.',
            'description' => 'Workspace bisnis jastip dengan trip, order, kurs manual, status pesanan, dan notifikasi WhatsApp.',
            'status' => 'planned', 'phase' => 3,
        ],
        'photobooth' => [
            'id' => 'photobooth', 'title' => 'Photo Booth', 'icon' => 'chip',
            'summary' => 'Dari pengambilan foto hingga hasil cetak.',
            'description' => 'Event, live capture, frame, render, dan antrean cetak. Generasi foto AI hadir setelah alur dasar stabil.',
            'status' => 'planned', 'phase' => 4,
        ],
        'restaurant' => [
            'id' => 'restaurant', 'title' => 'Restaurant', 'icon' => 'bolt',
            'summary' => 'Hubungkan menu, meja, pesanan, dan dapur.',
            'description' => 'Mulai dari menu dan order, dilanjutkan kitchen, POS, pembayaran, struk, serta antrean WhatsApp.',
            'status' => 'planned', 'phase' => 5,
        ],
    ],
];
