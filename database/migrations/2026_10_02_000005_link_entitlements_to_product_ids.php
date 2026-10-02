<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $orphan = DB::table('core_entitlements as e')->leftJoin('core_products as p', 'p.slug', '=', 'e.product_slug')
            ->whereNull('p.id')->exists();
        if ($orphan) {
            throw new RuntimeException('Entitlement contains an unknown product slug. Resolve it before migration; no data has been removed.');
        }

        Schema::table('core_entitlements', fn (Blueprint $table) => $table->unsignedBigInteger('product_id')->nullable());
        foreach (DB::table('core_products')->get(['id', 'slug']) as $product) {
            DB::table('core_entitlements')->where('product_slug', $product->slug)->update(['product_id' => $product->id]);
        }
        Schema::table('core_entitlements', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->dropUnique(['tenant_id', 'product_slug']);
            $table->dropColumn('product_slug');
            $table->foreign('product_id')->references('id')->on('core_products')->restrictOnDelete();
            $table->unique(['tenant_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('core_entitlements', fn (Blueprint $table) => $table->string('product_slug', 50)->nullable());
        foreach (DB::table('core_products')->get(['id', 'slug']) as $product) {
            DB::table('core_entitlements')->where('product_id', $product->id)->update(['product_slug' => $product->slug]);
        }
        Schema::table('core_entitlements', function (Blueprint $table) {
            $table->string('product_slug', 50)->nullable(false)->change();
            $table->dropForeign(['product_id']);
            $table->dropUnique(['tenant_id', 'product_id']);
            $table->dropColumn('product_id');
            $table->unique(['tenant_id', 'product_slug']);
        });
    }
};
