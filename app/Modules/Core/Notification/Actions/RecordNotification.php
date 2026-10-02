<?php

namespace App\Modules\Core\Notification\Actions;

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

class RecordNotification
{
    public function handle(User $recipient, string $title, string $message): DatabaseNotification
    {
        // Database-only, synchronous, and part of the caller's transaction.
        return $recipient->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => static::class,
            'data' => ['title' => $title, 'message' => $message],
            'read_at' => null,
        ]);
    }
}
