<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            // One atomic schema change; existing identifiers become unique slugs.
            DB::statement('ALTER TABLE core_products CHANGE id slug VARCHAR(50) NOT NULL, DROP PRIMARY KEY, ADD id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST, ADD UNIQUE core_products_slug_unique (slug)');

            return;
        }

        $this->rebuildSqlite(true);
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE core_products DROP COLUMN id, DROP INDEX core_products_slug_unique, CHANGE slug id VARCHAR(50) NOT NULL, ADD PRIMARY KEY (id)');

            return;
        }

        $this->rebuildSqlite(false);
    }

    private function rebuildSqlite(bool $numeric): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            throw new RuntimeException('Product identity migration supports MySQL and SQLite.');
        }

        Schema::create('core_products_rebuilt', function (Blueprint $table) use ($numeric) {
            if ($numeric) {
                $table->id();
                $table->string('slug', 50)->unique('core_products_slug_unique');
            } else {
                $table->string('id', 50)->primary();
            }
            $table->string('title', 100);
            $table->string('icon', 30);
            $table->string('summary', 255);
            $table->text('description');
            $table->string('status', 20)->default('planned');
            $table->unsignedSmallInteger('phase');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['status', 'sort_order'], $numeric ? 'core_products_numeric_order_index' : 'core_products_legacy_order_index');
        });

        DB::table('core_products')->orderBy('sort_order')->orderBy('id')->chunk(100, function ($products) use ($numeric) {
            foreach ($products as $product) {
                $row = (array) $product;
                if ($numeric) {
                    $row['slug'] = $row['id'];
                    unset($row['id']);
                } else {
                    $row['id'] = $row['slug'];
                    unset($row['slug']);
                }
                DB::table('core_products_rebuilt')->insert($row);
            }
        });

        Schema::drop('core_products');
        Schema::rename('core_products_rebuilt', 'core_products');
    }
};
