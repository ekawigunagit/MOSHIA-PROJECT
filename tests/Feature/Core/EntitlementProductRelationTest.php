<?php

namespace Tests\Feature\Core;

use App\Models\User;
use App\Modules\Core\Catalog\Models\Product;
use App\Modules\Core\Entitlement\Models\Entitlement;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EntitlementProductRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_maps_legacy_slug_and_preserves_existing_entitlement_fields(): void
    {
        $tenant = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Legacy');
        $product = Product::where('slug', 'wedding')->firstOrFail();
        $grant = Entitlement::create(['tenant_id' => $tenant->id, 'product_id' => $product->id,
            'status' => 'trial', 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay()]);
        $before = (array) DB::table('core_entitlements')->where('id', $grant->id)->first();
        $migration = require database_path('migrations/2026_10_02_000005_link_entitlements_to_product_ids.php');
        $migration->down();
        $this->assertDatabaseHas('core_entitlements', ['id' => $grant->id, 'product_slug' => 'wedding']);
        $migration->up();
        $this->assertEquals($before, (array) DB::table('core_entitlements')->where('id', $grant->id)->first());
        $this->assertFalse(Schema::hasColumn('core_entitlements', 'product_slug'));
        $this->assertSame($product->id, $grant->fresh()->product->id);
    }

    public function test_unknown_legacy_slug_aborts_before_schema_changes(): void
    {
        $migration = require database_path('migrations/2026_10_02_000005_link_entitlements_to_product_ids.php');
        $migration->down();
        $tenant = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Legacy');
        DB::table('core_entitlements')->insert(['tenant_id' => $tenant->id, 'product_slug' => 'missing', 'status' => 'active']);
        try {
            $migration->up();
            $this->fail('Unknown slug must not be silently dropped.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('unknown product slug', $exception->getMessage());
            $this->assertFalse(Schema::hasColumn('core_entitlements', 'product_id'));
            $this->assertDatabaseHas('core_entitlements', ['product_slug' => 'missing']);
        }
    }

    public function test_migration_resumes_after_product_id_was_added_and_can_be_repeated(): void
    {
        $tenant = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Retry');
        $product = Product::where('slug', 'wedding')->firstOrFail();
        $grant = Entitlement::create(['tenant_id' => $tenant->id, 'product_id' => $product->id, 'status' => 'trial']);
        $before = (array) DB::table('core_entitlements')->where('id', $grant->id)->first();
        $migration = require database_path('migrations/2026_10_02_000005_link_entitlements_to_product_ids.php');
        $migration->down();
        $migration->down();
        Schema::table('core_entitlements', fn (\Illuminate\Database\Schema\Blueprint $table) => $table->unsignedBigInteger('product_id')->nullable());
        DB::table('core_entitlements')->where('id', $grant->id)->update(['product_id' => $product->id]);
        $migration->up();
        $migration->up();
        $this->assertEquals($before, (array) DB::table('core_entitlements')->where('id', $grant->id)->first());
        $this->assertTrue(Schema::hasIndex('core_entitlements', 'core_entitlements_tenant_id_index'));
        $this->assertFalse(Schema::hasColumn('core_entitlements', 'product_slug'));
    }
    public function test_referenced_product_cannot_be_deleted(): void
    {
        $tenant = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Protected');
        $product = Product::firstOrFail();
        Entitlement::create(['tenant_id' => $tenant->id, 'product_id' => $product->id, 'status' => 'active']);
        $this->expectException(QueryException::class);
        $product->delete();
    }

    public function test_nonexistent_product_reference_is_rejected_by_database(): void
    {
        $tenant = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Protected');
        $this->expectException(QueryException::class);
        Entitlement::create(['tenant_id' => $tenant->id, 'product_id' => 99999, 'status' => 'active']);
    }
}
