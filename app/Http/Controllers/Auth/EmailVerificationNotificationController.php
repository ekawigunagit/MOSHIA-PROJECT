<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Core\Catalog\DashboardDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request, DashboardDestination $destination): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->to($destination->afterLogin($request));
        }

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withErrors(['email' => 'Email verifikasi gagal dikirim. Silakan coba lagi nanti.']);
        }

        return back()->with('status', 'verification-link-sent');
    }
}
