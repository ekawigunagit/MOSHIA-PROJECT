<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_products', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('title', 100);
            $table->string('icon', 30);
            $table->string('summary', 255);
            $table->text('description');
            $table->string('status', 20)->default('planned');
            $table->unsignedSmallInteger('phase');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'sort_order']);
        });

        // Snapshot of the original registry. Do not re-seed over admin edits.
        $products = [
            ['id' => 'wedding', 'title' => 'Wedding Invitation', 'icon' => 'link',
                'summary' => 'Undangan digital untuk momen istimewa Anda.',
                'description' => 'Pilih template, isi konten, preview, lalu terbitkan undangan. RSVP, tamu, dan domain menyusul secara bertahap.',
                'phase' => 2, 'sort_order' => 10],
            ['id' => 'jastip', 'title' => 'Jastip Manager', 'icon' => 'grid',
                'summary' => 'Kelola trip, pesanan pelanggan, dan pengiriman.',
                'description' => 'Workspace bisnis jastip dengan trip, order, kurs manual, status pesanan, dan notifikasi WhatsApp.',
                'phase' => 3, 'sort_order' => 20],
            ['id' => 'photobooth', 'title' => 'Photo Booth', 'icon' => 'chip',
                'summary' => 'Dari pengambilan foto hingga hasil cetak.',
                'description' => 'Event, live capture, frame, render, dan antrean cetak. Generasi foto AI hadir setelah alur dasar stabil.',
                'phase' => 4, 'sort_order' => 30],
            ['id' => 'restaurant', 'title' => 'Restaurant', 'icon' => 'bolt',
                'summary' => 'Hubungkan menu, meja, pesanan, dan dapur.',
                'description' => 'Mulai dari menu dan order, dilanjutkan kitchen, POS, pembayaran, struk, serta antrean WhatsApp.',
                'phase' => 5, 'sort_order' => 40],
        ];

        DB::table('core_products')->insert(array_map(fn ($product) => [
            ...$product, 'status' => 'planned', 'created_at' => now(), 'updated_at' => now(),
        ], $products));
    }

    public function down(): void
    {
        Schema::dropIfExists('core_products');
    }
};
