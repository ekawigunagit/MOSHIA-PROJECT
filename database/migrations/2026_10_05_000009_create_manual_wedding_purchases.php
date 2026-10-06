<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('core_tenants')->restrictOnDelete();
            $table->foreignId('product_id')->constrained('core_products')->restrictOnDelete();
            $table->string('payment_method', 40)->default('manual_development');
            $table->json('terms');
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('accepted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('first_published_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'product_id', 'id']);
        });
        Schema::create('wedding_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->unique()->constrained('core_tenants')->restrictOnDelete();
            $table->uuid('slug')->unique();
            $table->json('draft_content')->nullable();
            $table->json('published_content')->nullable();
            $table->foreignId('published_order_id')->nullable()->constrained('core_purchase_orders')->restrictOnDelete();
            $table->timestamp('first_published_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_invitations');
        Schema::dropIfExists('core_purchase_orders');
    }
};
