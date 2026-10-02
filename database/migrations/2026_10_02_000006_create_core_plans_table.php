<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('core_products')->restrictOnDelete();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->unique(['product_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_plans');
    }
};
