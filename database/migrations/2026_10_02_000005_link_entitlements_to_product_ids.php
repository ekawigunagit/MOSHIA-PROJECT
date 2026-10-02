<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL commits DDL independently: support retry after a partial migration.
        if (Schema::hasColumn('core_entitlements', 'product_slug')) {
            $orphan = DB::table('core_entitlements as e')->leftJoin('core_products as p', 'p.slug', '=', 'e.product_slug')
                ->whereNull('p.id')->exists();
            if ($orphan) {
                throw new RuntimeException('Entitlement contains an unknown product slug. Resolve it before migration; no data has been removed.');
            }

            if (! Schema::hasColumn('core_entitlements', 'product_id')) {
                Schema::table('core_entitlements', fn (Blueprint $table) => $table->unsignedBigInteger('product_id')->nullable());
            }
            foreach (DB::table('core_products')->get(['id', 'slug']) as $product) {
                DB::table('core_entitlements')->where('product_slug', $product->slug)->update(['product_id' => $product->id]);
            }
        }

        $orphan = DB::table('core_entitlements as e')->leftJoin('core_products as p', 'p.id', '=', 'e.product_id')
            ->whereNull('p.id')->exists();
        if ($orphan) {
            throw new RuntimeException('Entitlement contains an unknown product ID. Resolve it before migration; no data has been removed.');
        }

        $this->ensureTenantIndex();
        Schema::table('core_entitlements', fn (Blueprint $table) => $table->unsignedBigInteger('product_id')->nullable(false)->change());

        // Build replacement constraints before removing legacy ones.
        if (! Schema::hasIndex('core_entitlements', ['tenant_id', 'product_id'], 'unique')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->unique(['tenant_id', 'product_id']));
        }
        if (! $this->productForeignKey()) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->foreign('product_id')->references('id')->on('core_products')->restrictOnDelete());
        }
        if (Schema::hasIndex('core_entitlements', ['tenant_id', 'product_slug'], 'unique')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->dropUnique(['tenant_id', 'product_slug']));
        }
        if (Schema::hasColumn('core_entitlements', 'product_slug')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->dropColumn('product_slug'));
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('core_entitlements', 'product_id')) {
            $orphan = DB::table('core_entitlements as e')->leftJoin('core_products as p', 'p.id', '=', 'e.product_id')
                ->whereNull('p.id')->exists();
            if ($orphan) {
                throw new RuntimeException('Entitlement contains an unknown product ID. Resolve it before rollback; no data has been removed.');
            }
            if (! Schema::hasColumn('core_entitlements', 'product_slug')) {
                Schema::table('core_entitlements', fn (Blueprint $table) => $table->string('product_slug', 50)->nullable());
            }
            foreach (DB::table('core_products')->get(['id', 'slug']) as $product) {
                DB::table('core_entitlements')->where('product_id', $product->id)->update(['product_slug' => $product->slug]);
            }
        }

        $this->ensureTenantIndex();
        Schema::table('core_entitlements', fn (Blueprint $table) => $table->string('product_slug', 50)->nullable(false)->change());
        if (! Schema::hasIndex('core_entitlements', ['tenant_id', 'product_slug'], 'unique')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->unique(['tenant_id', 'product_slug']));
        }
        if ($this->productForeignKey()) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->dropForeign(['product_id']));
        }
        if (Schema::hasIndex('core_entitlements', ['tenant_id', 'product_id'], 'unique')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->dropUnique(['tenant_id', 'product_id']));
        }
        if (Schema::hasColumn('core_entitlements', 'product_id')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->dropColumn('product_id'));
        }
    }

    private function ensureTenantIndex(): void
    {
        // Keep an independent index for the tenant FK in both migration directions.
        if (! Schema::hasIndex('core_entitlements', 'core_entitlements_tenant_id_index')) {
            Schema::table('core_entitlements', fn (Blueprint $table) => $table->index('tenant_id'));
        }
    }

    private function productForeignKey(): bool
    {
        foreach (Schema::getForeignKeys('core_entitlements') as $foreignKey) {
            if ($foreignKey['columns'] === ['product_id']) {
                return true;
            }
        }

        return false;
    }
};