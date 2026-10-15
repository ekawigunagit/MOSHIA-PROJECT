<?php
namespace App\Modules\Products\Wedding\Services;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
class GuestCaptcha {
    public function issue(Request $request, int $invitationId): array {
        $a = random_int(1, 9); $b = random_int(1, 9); $token = (string) Str::uuid();
        $items = array_filter($request->session()->get('wedding_captcha', []), fn ($c) => $c['expires'] >= time());
        $items = array_slice($items, -19, null, true);
        $items[$token] = ['answer' => $a + $b, 'invitation_id' => $invitationId, 'expires' => time() + 1800];
        $request->session()->put('wedding_captcha', $items);
        return ['token' => $token, 'question' => "$a + $b = ?"];
    }
    public function verify(Request $request, int $invitationId): void {
        $token = (string) $request->input('captcha_token', '');
        $challenge = $request->session()->pull('wedding_captcha.'.$token);
        $answer = (string) $request->input('captcha_answer', '');
        if (!$challenge || $challenge['invitation_id'] !== $invitationId || $challenge['expires'] < time()
            || !ctype_digit($answer) || (int) $answer !== $challenge['answer']) {
            throw ValidationException::withMessages(['captcha_answer' => 'Jawaban verifikasi tidak tepat atau sudah kedaluwarsa. Silakan coba soal baru.']);
        }
    }
}
