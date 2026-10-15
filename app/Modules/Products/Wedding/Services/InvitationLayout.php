<?php
namespace App\Modules\Products\Wedding\Services;
class InvitationLayout {
    public const LABELS = [
        'cover' => 'Cover / pembuka', 'header' => 'Header foto / video', 'quote' => 'Foto Wedding & ayat',
        'groom' => 'Pengantin Pria', 'bride' => 'Pengantin Wanita', 'event' => 'Save the date & acara',
        'story' => 'Our Love Story', 'gallery' => 'Galeri', 'rsvp' => 'RSVP & kirim ucapan',
        'messages' => 'Daftar ucapan', 'gift' => 'Wedding Gift', 'thanks' => 'Thank you', 'footer' => 'Footer',
    ];
    public static function sections(array $content): array {
        if (isset($content['sections'])) return $content['sections'];
        $keys = array_keys(self::LABELS);
        if (($content['partner_order'] ?? '') === 'bride_first') {
            [$keys[3], $keys[4]] = [$keys[4], $keys[3]];
        }
        return array_map(fn ($key) => ['key' => $key, 'enabled' => true], $keys);
    }
    public static function enabled(array $content, string $key): bool {
        foreach (self::sections($content) as $section) {
            if ($section['key'] === $key) return (bool) $section['enabled'];
        }
        return false;
    }
}
