<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_plans', function (Blueprint $table) {
            $table->json('commercial_terms')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('core_plans', function (Blueprint $table) {
            $table->dropColumn('commercial_terms');
        });
    }
};
