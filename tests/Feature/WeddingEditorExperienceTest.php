<?php
namespace Tests\Feature;

use App\Models\User;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Wedding\Models\Invitation;
use App\Modules\Core\Media\Models\Media;
use App\Modules\Wedding\Models\GuestResponse;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class WeddingEditorExperienceTest extends TestCase
{
    use RefreshDatabase;
    private User $owner;
    private User $admin;
    private Tenant $tenant;

    protected function setUp(): void {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->seed(RoleSeeder::class);
        $this->owner = User::factory()->create();
        $this->admin = User::factory()->create();
        $this->admin->assignRole('super-admin');
        $this->tenant = app(CreateWorkspace::class)->handle($this->owner, 'Wedding');
        $order = app(ManualWeddingBilling::class)->create($this->owner, $this->tenant, 'gold');
        app(ManualWeddingBilling::class)->accept($this->admin, $order);
        $this->actingAs($this->owner);
    }
    public function test_media_migration_preserves_existing_ids_and_published_references(): void {
        $media = Media::create(['tenant_id' => $this->tenant->id, 'collection' => 'wedding', 'kind' => 'image',
            'path' => 'wedding/legacy.png', 'mime' => 'image/png', 'size' => 100]);
        $this->save(['cover_id' => $media->id]);
        $this->publish();
        $migration = require database_path('migrations/2026_10_08_000011_move_wedding_media_to_core.php');
        $migration->down();
        $this->assertDatabaseHas('wedding_media', ['id' => $media->id, 'path' => 'wedding/legacy.png']);
        $migration->up();
        $this->assertDatabaseHas('core_media', ['id' => $media->id, 'collection' => 'wedding', 'path' => 'wedding/legacy.png']);
        $this->assertEquals($media->id, $this->invitation()->published_content['cover_id']);
    }

    public function test_media_rollback_refuses_to_discard_other_product_collections(): void {
        Media::create(['tenant_id' => $this->tenant->id, 'collection' => 'jastip', 'kind' => 'image',
            'path' => 'jastip/photo.png', 'mime' => 'image/png', 'size' => 100]);
        $migration = require database_path('migrations/2026_10_08_000011_move_wedding_media_to_core.php');
        $this->expectException(\RuntimeException::class);
        $migration->down();
    }
    private function content(array $overrides = []): array {
        return array_replace([
            'partner_one' => 'Ayu', 'partner_two' => 'Eka', 'event_date' => '2027-01-01',
            'event_time' => '10:00', 'timezone' => 'Asia/Jakarta', 'venue' => 'Jakarta',
            'template' => 'garden', 'message' => 'Selamat datang',
            'rsvp_enabled' => true, 'wishes_enabled' => true, 'gallery_ids' => [],
        ], $overrides);
    }
    private function invitation(): Invitation { return Invitation::where('tenant_id', $this->tenant->id)->firstOrFail(); }
    private function save(array $overrides = []): void {
        $this->patch(route('wedding.update', $this->tenant), $this->content($overrides))->assertSessionHasNoErrors();
    }
    private function publish(): void {
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasNoErrors();
    }
    private function imageFile(): \Illuminate\Http\Testing\File {
        return UploadedFile::fake()->createWithContent('photo.png', base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jRZkAAAAASUVORK5CYII='));
    }
    private function photo(): Media {
        $this->post(route('wedding.media.store', $this->tenant), [
            'kind' => 'image', 'file' => $this->imageFile(),
        ])->assertSessionHasNoErrors();
        return Media::where('tenant_id', $this->tenant->id)->latest('id')->firstOrFail();
    }
    public function test_editor_templates_and_preview_publish_snapshot(): void {
        $this->get(route('wedding.edit', $this->tenant))->assertInertia(fn (Assert $page) => $page
            ->has('templates', 3)->has('media', 0)->has('responses.data', 0));
        $this->save(['story' => 'Our first story']);
        $this->get(route('wedding.preview', $this->tenant))->assertOk()->assertSee('Our first story')->assertSee('Form nonaktif pada preview');
        $this->publish();
        $end = $this->invitation()->publishedOrder->ends_at->toIso8601String();
        $this->save(['template' => 'midnight', 'story' => 'Unpublished story']);
        $this->get(route('wedding.public', $this->invitation()->slug))->assertSee('Our first story')->assertDontSee('Unpublished story');
        $this->publish();
        $this->get(route('wedding.public', $this->invitation()->slug))->assertSee('Unpublished story');
        $this->assertSame($end, $this->invitation()->publishedOrder->ends_at->toIso8601String());
    }
    public function test_media_is_private_until_referenced_in_published_snapshot(): void {
        $first = $this->photo();
        $second = $this->photo();
        Storage::disk('local')->assertExists($first->path);
        $this->get(route('wedding.media.private', [$this->tenant, $first]))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get(route('wedding.media.public', [$this->invitation()->slug, $first]))->assertNotFound();
        $this->save(['cover_id' => $first->id]);
        $this->publish();
        $this->save(['cover_id' => $second->id]);
        $this->get(route('wedding.media.public', [$this->invitation()->slug, $first]))->assertOk();
        $this->get(route('wedding.media.public', [$this->invitation()->slug, $second]))->assertNotFound();
        $this->publish();
        $this->get(route('wedding.media.public', [$this->invitation()->slug, $first]))->assertNotFound();
        $this->get(route('wedding.media.public', [$this->invitation()->slug, $second]))->assertOk();
        $end = $this->invitation()->publishedOrder->ends_at;
        $this->travelTo($end);
        $this->get(route('wedding.media.public', [$this->invitation()->slug, $second]))->assertNotFound();
    }
    public function test_foreign_media_invalid_template_and_executable_map_are_rejected(): void {
        $other = app(CreateWorkspace::class)->handle(User::factory()->create(), 'Other');
        $foreign = Media::create(['tenant_id' => $other->id, 'kind' => 'image', 'path' => 'private.jpg', 'mime' => 'image/jpeg', 'size' => 1]);
        $this->patch(route('wedding.update', $this->tenant), $this->content(['cover_id' => $foreign->id]))->assertSessionHasErrors('cover_id');
        $this->get(route('wedding.media.private', [$this->tenant, $foreign]))->assertNotFound();
        $this->patch(route('wedding.update', $this->tenant), $this->content(['template' => '../other']))->assertSessionHasErrors('template');
        $this->patch(route('wedding.update', $this->tenant), $this->content(['map_url' => 'javascript:alert(1)']))->assertSessionHasErrors('map_url');
        $this->assertNull($this->invitation()->draft_content);
    }
    public function test_invalid_uploads_limits_and_media_kind_are_rejected(): void {
        $this->post(route('wedding.media.store', $this->tenant), [
            'kind' => 'image', 'file' => UploadedFile::fake()->create('bad.svg', 2, 'image/svg+xml'),
        ])->assertSessionHasErrors('file');
        $this->post(route('wedding.media.store', $this->tenant), [
            'kind' => 'image', 'file' => $this->imageFile()->size(5121),
        ])->assertSessionHasErrors('file');
        $this->post(route('wedding.media.store', $this->tenant), [
            'kind' => 'music', 'file' => $this->imageFile(),
        ])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('core_media', 0);
        config(['core_media.items_per_workspace_collection' => 0]);
        $this->post(route('wedding.media.store', $this->tenant), [
            'kind' => 'image', 'file' => $this->imageFile(),
        ])->assertSessionHasErrors('file');
        $this->assertCount(0, Storage::disk('local')->allFiles());
    }
    public function test_responses_are_idempotent_private_and_wishes_need_owner_moderation(): void {
        $this->save(); $this->publish();
        $slug = $this->invitation()->slug;
        $payload = ['submission_id' => (string) Str::uuid(), 'name' => 'Guest-private',
            'attendance' => 'yes', 'guests' => 2, 'wish' => '<script>unsafe()</script>'];
        $this->post(route('wedding.responses.store', $slug), $payload)->assertSessionHasNoErrors();
        $this->post(route('wedding.responses.store', $slug), $payload)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('wedding_responses', 1);
        $response = GuestResponse::firstOrFail();
        $this->get(route('wedding.public', $slug))->assertDontSee('Guest-private');
        $this->get(route('wedding.edit', $this->tenant))->assertInertia(fn (Assert $page) => $page
            ->where('responseStats.attending', 2)->where('responses.data.0.name', 'Guest-private'));
        $this->patch(route('wedding.responses.moderate', [$this->tenant, $response]), ['approved' => true])->assertSessionHasNoErrors();
        $this->get(route('wedding.public', $slug))->assertSee('Guest-private')
            ->assertDontSee('<script>unsafe()</script>', false)->assertSee('&lt;script&gt;', false);
        $this->patch(route('wedding.responses.moderate', [$this->tenant, $response]), ['approved' => false])->assertSessionHasNoErrors();
        $this->get(route('wedding.public', $slug))->assertDontSee('Guest-private');
    }
    public function test_disabled_or_expired_forms_reject_guest_responses(): void {
        $this->save(['rsvp_enabled' => false, 'wishes_enabled' => false]); $this->publish();
        $slug = $this->invitation()->slug;
        $payload = ['submission_id' => (string) Str::uuid(), 'name' => 'Guest', 'attendance' => 'yes'];
        $this->post(route('wedding.responses.store', $slug), $payload)->assertNotFound();
        $this->save(); $this->publish();
        $this->post(route('wedding.responses.store', $slug), array_replace($payload, ['guests' => 11]))->assertSessionHasErrors('guests');
        $this->travelTo($this->invitation()->publishedOrder->ends_at);
        $this->post(route('wedding.responses.store', $slug), $payload)->assertNotFound();
        $this->assertDatabaseCount('wedding_responses', 0);
    }
    public function test_other_owner_cannot_upload_read_or_moderate(): void {
        $photo = $this->photo();
        $this->save(); $this->publish();
        $response = GuestResponse::create(['invitation_id' => $this->invitation()->id,
            'submission_id' => (string) Str::uuid(), 'name' => 'Guest', 'wish' => 'Hello']);
        $this->actingAs(User::factory()->create());
        $this->get(route('wedding.media.private', [$this->tenant, $photo]))->assertForbidden();
        $this->post(route('wedding.media.store', $this->tenant), ['kind' => 'image'])->assertForbidden();
        $this->patch(route('wedding.responses.moderate', [$this->tenant, $response]), ['approved' => true])->assertForbidden();
        $this->assertFalse($response->fresh()->approved);
    }
}
