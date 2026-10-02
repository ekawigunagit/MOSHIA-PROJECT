<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_tenants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('core_tenant_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('core_tenants')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tenant_id', 'user_id']);
        });

        Schema::create('core_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('core_tenants')->cascadeOnDelete();
            $table->string('product_slug', 50);
            $table->string('status', 20)->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
            $table->unique(['tenant_id', 'product_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_entitlements');
        Schema::dropIfExists('core_tenant_user');
        Schema::dropIfExists('core_tenants');
    }
};
