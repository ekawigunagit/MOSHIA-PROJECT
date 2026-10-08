<?php
namespace App\Modules\Wedding\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Modules\Wedding\Models\GuestResponse;
use App\Modules\Wedding\Models\Invitation;
use App\Modules\Wedding\Services\PublishedInvitation;
use App\Modules\Core\Billing\Actions\ManualWeddingBilling;
use App\Modules\Core\Tenancy\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class ResponseController extends Controller {
    public function store(Request $request, string $slug, PublishedInvitation $published) {
        $invitation = $published->find($slug);
        $content = $invitation->published_content;
        abort_unless(($content['rsvp_enabled'] ?? false) || ($content['wishes_enabled'] ?? false), 404);
        $data = $request->validate([
            'submission_id' => ['required','uuid'], 'website' => ['nullable','max:0'],
            'name' => ['required','string','max:100'],
            'attendance' => [($content['rsvp_enabled'] ?? false) ? 'required' : 'prohibited', Rule::in(['yes','no','maybe'])],
            'guests' => ['nullable','integer','min:1','max:10'],
            'wish' => [($content['wishes_enabled'] ?? false) ? 'nullable' : 'prohibited','string','max:1000'],
        ]);
        GuestResponse::firstOrCreate(['invitation_id' => $invitation->id, 'submission_id' => $data['submission_id']], [
            'name' => $data['name'], 'attendance' => $data['attendance'] ?? null,
            'guests' => ($data['attendance'] ?? '') === 'yes' ? ($data['guests'] ?? 1) : 0,
            'wish' => $data['wish'] ?? null, 'approved' => false,
        ]);
        return redirect()->route('wedding.public', $slug)->with('guest_success', 'Terima kasih, respons Anda tersimpan. Ucapan ditampilkan setelah disetujui pemilik.');
    }
    public function moderate(Request $request, Tenant $tenant, GuestResponse $response, ManualWeddingBilling $billing) {
        $billing->editorOrder($request->user(), $tenant);
        $invitation = Invitation::where('tenant_id', $tenant->id)->firstOrFail();
        abort_unless($response->invitation_id === $invitation->id, 404);
        $data = $request->validate(['approved' => ['required','boolean']]);
        $response->update($data);
        return back()->with('success', 'Visibilitas ucapan diperbarui.');
    }
}
