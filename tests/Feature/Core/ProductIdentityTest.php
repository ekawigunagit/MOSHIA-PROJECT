<?php

namespace Tests\Feature\Core;

use App\Modules\Core\Catalog\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductIdentityTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_have_numeric_ids_and_unique_slugs(): void
    {
        $product = Product::where('slug', 'wedding')->firstOrFail();
        $this->assertIsInt($product->id);
        $this->assertGreaterThan(0, $product->id);
        $this->assertSame('wedding', $product->getRouteKey());
        $row = $product->getAttributes();
        unset($row['id']);
        $row['slug'] = 'test-product';
        $id = DB::table('core_products')->insertGetId($row);
        $this->assertGreaterThan($product->id, $id);

        $this->expectException(QueryException::class);
        DB::table('core_products')->insert($row);
    }

    public function test_identity_migration_preserves_edited_content_and_is_reversible(): void
    {
        Product::where('slug', 'wedding')->update(['title' => 'Custom title', 'status' => 'hidden', 'sort_order' => 77]);
        $before = DB::table('core_products')->orderBy('slug')->get()->map(function ($row) {
            $data = (array) $row;
            unset($data['id']);

            return $data;
        })->all();
        $migration = require database_path('migrations/2026_10_02_000003_separate_product_id_and_slug.php');
        $migration->down();
        $this->assertDatabaseHas('core_products', ['id' => 'wedding', 'title' => 'Custom title', 'status' => 'hidden']);
        $migration->up();
        $after = DB::table('core_products')->orderBy('slug')->get()->map(function ($row) {
            $data = (array) $row;
            unset($data['id']);

            return $data;
        })->all();
        $this->assertEquals($before, $after);
    }
}
