<?php
namespace Tests\Feature;

use App\Models\User;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Actions\CreateWorkspace;
use App\Modules\Core\Tenancy\Models\Tenant;
use App\Modules\Products\Wedding\Models\Invitation;
use App\Modules\Core\Media\Models\Media;
use App\Modules\Products\Wedding\Models\GuestResponse;
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


    public function test_custom_slug_is_normalized_unique_and_routes_to_published_invitation(): void {
        $old = $this->invitation()->slug;
        $this->getJson(route('wedding.slug.check', ['tenant' => $this->tenant, 'slug' => 'AditSella']))->assertOk()->assertJson(['available' => true, 'slug' => 'aditsella']);
        $this->assertSame($old, $this->invitation()->slug);
        $this->patch(route('wedding.slug.update', $this->tenant), ['slug' => ' AditSella '])->assertSessionHasNoErrors();
        $this->assertSame('aditsella', $this->invitation()->slug);
        $this->getJson(route('wedding.slug.check', ['tenant' => $this->tenant, 'slug' => 'aditsella']))->assertOk();
        $other = app(CreateWorkspace::class)->handle($this->owner, 'Other wedding');
        Invitation::create(['tenant_id' => $other->id, 'slug' => 'taken-slug']);
        $this->getJson(route('wedding.slug.check', ['tenant' => $this->tenant, 'slug' => 'taken-slug']))->assertUnprocessable()->assertJsonValidationErrors('slug');
        $this->patch(route('wedding.slug.update', $this->tenant), ['slug' => 'taken-slug'])->assertSessionHasErrors('slug');
        $this->assertSame('aditsella', $this->invitation()->slug);
        $this->save();
        $this->publish();
        $this->get(route('wedding.public', 'aditsella'))->assertOk();
        $this->get(route('wedding.public', $old))->assertNotFound();
    }

    public function test_custom_slug_rejects_invalid_values_and_other_workspace_access(): void {
        foreach (['ab', 'with space', '../bad', '-bad', 'bad-', 'admin', str_repeat('a', 37)] as $slug) {
            $this->patch(route('wedding.slug.update', $this->tenant), ['slug' => $slug])->assertSessionHasErrors('slug');
        }
        $old = $this->invitation()->slug;
        $this->actingAs(User::factory()->create());
        $this->getJson(route('wedding.slug.check', ['tenant' => $this->tenant, 'slug' => 'some-slug']))->assertForbidden();
        $this->patch(route('wedding.slug.update', $this->tenant), ['slug' => 'some-slug'])->assertForbidden();
        $this->assertSame($old, $this->invitation()->slug);
    }

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

    public function test_parent_names_and_order_follow_draft_and_published_snapshots(): void {
        $details = [
            'partner_one' => 'Pria Alpha', 'partner_two' => 'Wanita Beta',
            'partner_one_father' => 'Ayah Alpha', 'partner_one_mother' => 'Ibu Alpha',
            'partner_two_father' => 'Ayah Beta', 'partner_two_mother' => '<script>Nama Ibu</script>',
            'partner_order' => 'bride_first',
        ];
        $this->save($details);
        $this->assertSame('Ayah Alpha', $this->invitation()->draft_content['partner_one_father']);
        $this->get(route('wedding.preview', $this->tenant))->assertOk()
            ->assertSeeInOrder(['Wanita Beta', 'Pria Alpha'])
            ->assertSee('Ayah Alpha')->assertSee('Ibu Alpha')->assertSee('Ayah Beta')
            ->assertSee('&lt;script&gt;Nama Ibu&lt;/script&gt;', false)
            ->assertDontSee('<script>Nama Ibu</script>', false);
        $this->publish();
        $slug = $this->invitation()->slug;
        $this->save(array_replace($details, ['partner_order' => 'groom_first']));
        $this->get(route('wedding.preview', $this->tenant))->assertSeeInOrder(['Pria Alpha', 'Wanita Beta']);
        $this->get(route('wedding.public', $slug))->assertSeeInOrder(['Wanita Beta', 'Pria Alpha']);
        $this->publish();
        $this->get(route('wedding.public', $slug))->assertSeeInOrder(['Pria Alpha', 'Wanita Beta']);
        $this->assertSame('Ayah Beta', $this->invitation()->published_content['partner_two_father']);
    }

    public function test_legacy_family_text_and_default_order_remain_supported(): void {
        $this->save(['partner_one_parents' => 'Keluarga Lama Alpha']);
        $this->get(route('wedding.preview', $this->tenant))->assertOk()
            ->assertSeeInOrder(['Ayu', 'Eka'])->assertSee('Keluarga Lama Alpha');
        $this->save(['partner_one_parents' => 'Keluarga Lama Alpha', 'partner_one_father' => 'Nama Bapak Baru']);
        $this->get(route('wedding.preview', $this->tenant))->assertSee('Nama Bapak Baru')->assertDontSee('Keluarga Lama Alpha');
        $this->assertSame('Keluarga Lama Alpha', $this->invitation()->draft_content['partner_one_parents']);
    }

    public function test_invalid_partner_order_and_parent_names_do_not_replace_saved_draft(): void {
        $this->save();
        $before = $this->invitation()->draft_content;
        $this->patch(route('wedding.update', $this->tenant), $this->content([
            'partner_order' => 'unknown', 'partner_one_father' => str_repeat('x', 201),
            'partner_one_mother' => [], 'partner_two_father' => str_repeat('y', 201),
            'partner_two_mother' => ['invalid'],
        ]))->assertSessionHasErrors(['partner_order','partner_one_father','partner_one_mother','partner_two_father','partner_two_mother']);
        $this->assertSame($before, $this->invitation()->draft_content);
    }
    public function test_product_abilities_and_workspace_storage_are_isolated(): void {
        $this->assertTrue(\Illuminate\Support\Facades\Gate::forUser($this->owner)->allows('wedding.invitations.view', $this->tenant));
        $media = $this->photo();
        $this->assertStringStartsWith('workspaces/'.$this->tenant->id.'/wedding/', $media->path);
        Storage::disk('local')->assertExists($media->path);
        $this->actingAs($this->admin)->get(route('wedding.edit', $this->tenant))->assertForbidden();
    }


    public function test_event_names_reception_toggle_and_map_follow_published_snapshot(): void {
        $details = ['event_name' => 'Akad Nikah', 'reception_enabled' => true, 'reception_name' => 'Syukuran Keluarga',
            'reception_date' => '2027-01-02', 'reception_venue' => 'Gedung Bahagia, Jalan Mawar',
            'reception_map_url' => 'https://maps.google.com/?q=Gedung'];
        $this->save($details);
        $this->get(route('wedding.preview', $this->tenant))->assertOk()->assertSee('Akad Nikah')
            ->assertSee('Syukuran Keluarga')->assertSee('Gedung Bahagia, Jalan Mawar')->assertSee('Buka peta resepsi');
        $this->publish();
        $this->save(array_replace($details, ['reception_enabled' => false]));
        $this->get(route('wedding.preview', $this->tenant))->assertDontSee('Syukuran Keluarga')->assertDontSee('Buka peta resepsi');
        $this->assertSame('Gedung Bahagia, Jalan Mawar', $this->invitation()->draft_content['reception_venue']);
        $this->get(route('wedding.public', $this->invitation()->slug))->assertSee('Syukuran Keluarga');
        $this->publish();
        $this->get(route('wedding.public', $this->invitation()->slug))->assertDontSee('Syukuran Keluarga');
    }

    public function test_event_defaults_legacy_reception_and_conditional_validation(): void {
        $this->save();
        $this->get(route('wedding.preview', $this->tenant))->assertSee('Pemberkatan Pernikahan');
        $this->save(['reception_date' => '2027-01-02', 'reception_venue' => 'Lokasi lama']);
        $this->get(route('wedding.preview', $this->tenant))->assertSee('Lokasi lama');
        $this->patch(route('wedding.update', $this->tenant), $this->content([
            'event_name' => '', 'reception_enabled' => true, 'reception_name' => '',
            'reception_map_url' => 'javascript:alert(1)',
        ]))->assertSessionHasErrors(['event_name','reception_name','reception_date','reception_venue','reception_map_url']);
        $this->save(['reception_enabled' => false, 'reception_name' => '']);
    }


    public function test_complete_sections_and_gift_bank_snapshot_are_published_safely(): void {
        $photo = $this->photo();
        $bank = \App\Modules\Core\Catalog\Models\Bank::where('name', 'Bank BCA')->firstOrFail();
        $details = [
            'header_type' => 'image', 'header_image_id' => $photo->id,
            'wedding_photo_id' => $photo->id, 'wedding_quote' => '<script>quote</script>',
            'save_date_title' => 'Save Our Date', 'save_date_message' => 'Datang bersama kami',
            'stories' => [['title' => 'Perkenalan', 'body' => 'Cerita pertama', 'image_id' => $photo->id],
                ['title' => 'Engagement', 'body' => 'Cerita lamaran', 'image_id' => null]],
            'gift_enabled' => true, 'gift_accounts' => [['bank_id' => $bank->id, 'account_number' => '0012345678', 'account_holder' => 'Nama Pengantin']],
            'thank_you_image_id' => $photo->id, 'thank_you_title' => 'Thank You', 'thank_you_message' => 'Terima kasih sudah datang',
        ];
        $this->save($details);
        $this->get(route('wedding.preview', $this->tenant))->assertOk()->assertSee('Cerita pertama')
            ->assertSee('Bank BCA')->assertSee('0012345678')->assertSee('Save Our Date')
            ->assertSee('&lt;script&gt;quote&lt;/script&gt;', false)->assertDontSee('<script>quote</script>', false)
            ->assertSee('Thank You')->assertSee('Made with love by Moshia');
        $this->publish();
        $slug = $this->invitation()->slug;
        $this->get(route('wedding.media.public', [$slug, $photo->id]))->assertOk();
        $bank->update(['name' => 'Bank renamed']);
        $this->get(route('wedding.public', $slug))->assertSee('Bank BCA')->assertDontSee('Bank renamed');
        $this->save(array_replace($details, ['stories' => [], 'gift_enabled' => false]));
        $this->get(route('wedding.preview', $this->tenant))->assertDontSee('Cerita pertama')->assertDontSee('0012345678');
        $this->get(route('wedding.public', $slug))->assertSee('Cerita pertama')->assertSee('0012345678');
    }

    public function test_gold_cannot_upload_select_or_publish_video_and_paid_tiers_can(): void {
        $upload = fn () => UploadedFile::fake()->create('header.mp4', 100, 'video/mp4');
        $this->post(route('wedding.media.store', $this->tenant), ['kind' => 'video', 'file' => $upload()])->assertSessionHasErrors('file');
        $this->patch(route('wedding.update', $this->tenant), $this->content(['header_type' => 'video']))->assertSessionHasErrors('header_type');
        $order = app(ManualWeddingBilling::class)->editorOrder($this->owner, $this->tenant);
        foreach (['emerald', 'diamond'] as $key) {
            $order->update(['terms' => config('wedding_plans.packages.'.$key)]);
            $this->post(route('wedding.media.store', $this->tenant), ['kind' => 'video', 'file' => $upload()])->assertSessionHasNoErrors();
            $media = Media::where('kind', 'video')->latest('id')->firstOrFail();
            $this->save(['header_type' => 'video', 'header_video_id' => $media->id]);
            $this->get(route('wedding.preview', $this->tenant))->assertOk()->assertSee('Video header pernikahan');
        }
        $order->update(['terms' => config('wedding_plans.packages.gold')]);
        $this->post(route('wedding.publish', $this->tenant))->assertSessionHasErrors('publish');
    }

    public function test_section_validation_rejects_foreign_photos_invalid_banks_and_excess_stories(): void {
        $other = User::factory()->create();
        $tenant = app(CreateWorkspace::class)->handle($other, 'Other');
        $media = Media::create(['tenant_id' => $tenant->id, 'collection' => 'wedding', 'kind' => 'image', 'path' => 'other.png', 'mime' => 'image/png', 'size' => 10]);
        $this->patch(route('wedding.update', $this->tenant), $this->content([
            'header_image_id' => $media->id, 'thank_you_image_id' => $media->id,
            'stories' => array_fill(0, 13, ['title' => 'Story', 'body' => '', 'image_id' => $media->id]),
            'gift_enabled' => true, 'gift_accounts' => [['bank_id' => 99999, 'account_number' => '<script>', 'account_holder' => '']],
        ]))->assertSessionHasErrors(['header_image_id','thank_you_image_id','stories','stories.0.image_id','gift_accounts.0.bank_id','gift_accounts.0.account_number','gift_accounts.0.account_holder']);
    }

    public function test_only_superadmin_can_add_banks_for_gift_selector(): void {
        $this->get(route('admin.banks.index'))->assertForbidden();
        $this->post(route('admin.banks.store'), ['name' => 'Bank Customer'])->assertForbidden();
        $this->actingAs($this->admin)->post(route('admin.banks.store'), ['name' => 'Bank Tambahan'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('core_banks', ['name' => 'Bank Tambahan']);
        $this->post(route('admin.banks.store'), ['name' => 'Bank Tambahan'])->assertSessionHasErrors('name');
        $this->actingAs($this->owner)->get(route('wedding.edit', $this->tenant))->assertInertia(fn (Assert $page) => $page
            ->component('Wedding/Edit')->has('banks', 7)->where('videoAllowed', false));
    }

    public function test_configured_public_domain_keeps_slug_and_redirects_legacy_links(): void {
        $this->save(); $this->publish();
        $slug = $this->invitation()->slug;
        config(['wedding_editor.public_domain' => 'invitation.moshia.test']);
        \Illuminate\Support\Facades\Route::middleware('web')->group(app_path('Modules/Products/Wedding/Routes/web.php'));
        app('router')->getRoutes()->refreshNameLookups();
        $url = route('wedding.public', $slug);
        $this->assertStringContainsString('invitation.moshia.test/'.$slug, $url);
        $this->get($url)->assertOk();
        $this->get('/invitation/'.$slug.'?to=Tamu')->assertRedirect(route('wedding.public', ['slug' => $slug, 'to' => 'Tamu']));
    }


    private function postGuest(string $url, array $payload) {
        $token = (string) Str::uuid();
        $this->withSession(['wedding_captcha' => [$token => ['answer' => 7, 'invitation_id' => $this->invitation()->id, 'expires' => time()+300]]]);
        return $this->post($url, $payload + ['captcha_token' => $token, 'captcha_answer' => '7']);
    }

    public function test_layout_order_visibility_guest_link_and_snapshot(): void {
        $sections = \App\Modules\Products\Wedding\Services\InvitationLayout::sections([]);
        $sections = array_reverse($sections);
        foreach ($sections as &$section) if (in_array($section['key'], ['story','groom'])) $section['enabled'] = false;
        unset($section);
        $this->save(['sections' => $sections, 'cover_title' => 'Pernikahan Kami', 'story' => 'Hidden story text']);
        $this->get(route('wedding.preview', $this->tenant).'?to=%3Cscript%3ETamu%3C%2Fscript%3E')
            ->assertOk()->assertSee('Pernikahan Kami')->assertDontSee('Hidden story text')
            ->assertSee('&lt;script&gt;Tamu&lt;/script&gt;', false)->assertDontSee('<script>Tamu</script>', false)
            ->assertSeeInOrder(['id="thanks"', 'id="event"', 'id="bride"', 'id="header"', 'id="cover"'], false)
            ->assertDontSee('id="groom"', false);
        $this->publish();
        $this->save(['story' => 'New draft story']);
        $this->get(route('wedding.public', $this->invitation()->slug))->assertDontSee('New draft story')->assertSee('Pernikahan Kami');
        $this->patch(route('wedding.update', $this->tenant), $this->content(['sections' => array_fill(0, 13, ['key'=>'header','enabled'=>true])]))->assertSessionHasErrors('sections.0.key');
    }

    public function test_captcha_is_required_one_time_expiring_and_invitation_scoped(): void {
        $this->save(); $this->publish();
        $url = route('wedding.responses.store', $this->invitation()->slug);
        $data = ['submission_id'=>(string) Str::uuid(),'name'=>'Tamu','attendance'=>'yes','guests'=>1,'wish'=>'Selamat'];
        $this->post($url, $data)->assertSessionHasErrors('captcha_token');
        foreach ([
            ['answer'=>7,'invitation_id'=>$this->invitation()->id,'expires'=>time()-1],
            ['answer'=>7,'invitation_id'=>99999,'expires'=>time()+60],
        ] as $challenge) {
            $token=(string) Str::uuid();
            $this->withSession(['wedding_captcha'=>[$token=>$challenge]])->post($url,$data+['captcha_token'=>$token,'captcha_answer'=>'7'])->assertSessionHasErrors('captcha_answer');
        }
        $this->get(route('wedding.public', $this->invitation()->slug))->assertOk()->assertSee('Verifikasi sederhana');
        $challenges=session('wedding_captcha'); $token=array_key_last($challenges);
        $payload=$data+['captcha_token'=>$token,'captcha_answer'=>(string)$challenges[$token]['answer']];
        $this->post($url,$payload)->assertSessionHasNoErrors();
        $this->post($url,array_replace($payload,['submission_id'=>(string) Str::uuid()]))->assertSessionHasErrors('captcha_answer');
        $this->assertDatabaseCount('wedding_responses',1);
    }

    public function test_approved_messages_are_paginated_and_hidden_bank_is_not_rendered(): void {
        $bank=\App\Modules\Core\Catalog\Models\Bank::first();
        $this->save(['gift_enabled'=>true,'gift_accounts'=>[
            ['bank_id'=>$bank->id,'account_holder'=>'Visible Holder','account_number'=>'00111111','enabled'=>true],
            ['bank_id'=>$bank->id,'account_holder'=>'Hidden Holder','account_number'=>'00222222','enabled'=>false],
        ]]); $this->publish();
        for($i=1;$i<=7;$i++) GuestResponse::create(['invitation_id'=>$this->invitation()->id,'submission_id'=>(string)Str::uuid(),'name'=>'Tamu '.$i,'wish'=>'Ucapan '.$i,'approved'=>true]);
        GuestResponse::create(['invitation_id'=>$this->invitation()->id,'submission_id'=>(string)Str::uuid(),'name'=>'Belum disetujui','wish'=>'PRIVATE WISH','approved'=>false]);
        $url=route('wedding.public',$this->invitation()->slug);
        $this->get($url.'?to=Eka')->assertOk()->assertSee('Visible Holder')->assertDontSee('Hidden Holder')
            ->assertSee('Ucapan 7')->assertDontSee('Ucapan 1')->assertDontSee('PRIVATE WISH')->assertSee('wishes_page=2',false)->assertSee('to=Eka',false);
        $this->get($url.'?wishes_page=2')->assertSee('Ucapan 1')->assertDontSee('Ucapan 7');
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
        $media = Media::where('tenant_id', $this->tenant->id)->latest('id')->firstOrFail();
        $this->assertSame($media->id, session('wedding_uploaded_media_id'));
        return $media;
    }
    public function test_unpublish_closes_all_public_endpoints_and_republish_preserves_period(): void {
        $photo = $this->photo();
        $this->save(['cover_id' => $photo->id]);
        $this->publish();
        $before = $this->invitation();
        $end = $before->publishedOrder->ends_at->toIso8601String();
        $this->post(route('wedding.unpublish', $this->tenant))->assertSessionHasNoErrors();
        $this->post(route('wedding.unpublish', $this->tenant))->assertSessionHasNoErrors();
        $closed = $this->invitation();
        $this->assertNull($closed->published_at);
        $this->assertSame($before->published_content, $closed->published_content);
        $this->assertSame($before->draft_content, $closed->draft_content);
        $this->assertTrue($before->first_published_at->equalTo($closed->first_published_at));
        $this->get(route('wedding.preview', $this->tenant))->assertOk();
        $this->get(route('wedding.media.private', [$this->tenant, $photo]))->assertOk();
        $this->get(route('wedding.public', $closed->slug))->assertNotFound();
        $this->get(route('wedding.media.public', [$closed->slug, $photo]))->assertNotFound();
        $this->postGuest(route('wedding.responses.store', $closed->slug), [
            'submission_id' => (string) Str::uuid(), 'name' => 'Guest', 'attendance' => 'yes', 'guests' => 1,
        ])->assertNotFound();
        $this->assertDatabaseCount('wedding_responses', 0);
        $this->travel(1)->days();
        $this->publish();
        $this->get(route('wedding.public', $closed->slug))->assertOk();
        $this->get(route('wedding.media.public', [$closed->slug, $photo]))->assertOk();
        $this->assertSame($end, $this->invitation()->publishedOrder->ends_at->toIso8601String());
    }

    public function test_unpublish_requires_verified_owner_and_does_not_enable_expired_republish(): void {
        $this->save();
        $this->publish();
        $other = User::factory()->create();
        $this->tenant->members()->attach($other);
        foreach ([$other, $this->admin] as $user) {
            $this->actingAs($user)->post(route('wedding.unpublish', $this->tenant))->assertForbidden();
        }
        $this->assertNotNull($this->invitation()->published_at);
        $this->owner->forceFill(['email_verified_at' => null])->save();
        $this->actingAs($this->owner)->post(route('wedding.unpublish', $this->tenant))->assertRedirect(route('verification.notice'));
        $this->owner->forceFill(['email_verified_at' => now()])->save();
        $this->actingAs($this->owner)->post(route('wedding.unpublish', $this->tenant))->assertSessionHasNoErrors();
        $this->travelTo($this->invitation()->publishedOrder->ends_at);
        $this->post(route('wedding.publish', $this->tenant))->assertForbidden();
        $this->assertNull($this->invitation()->published_at);
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
        $this->postGuest(route('wedding.responses.store', $slug), $payload)->assertSessionHasNoErrors();
        $this->postGuest(route('wedding.responses.store', $slug), $payload)->assertSessionHasNoErrors();
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
        $this->postGuest(route('wedding.responses.store', $slug), $payload)->assertNotFound();
        $this->save(); $this->publish();
        $this->postGuest(route('wedding.responses.store', $slug), array_replace($payload, ['guests' => 11]))->assertSessionHasErrors('guests');
        $this->travelTo($this->invitation()->publishedOrder->ends_at);
        $this->postGuest(route('wedding.responses.store', $slug), $payload)->assertNotFound();
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
