<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Core\Catalog\DashboardDestination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EmailVerificationPromptController extends Controller
{
    /**
     * Display the email verification prompt.
     */
    public function __invoke(Request $request, DashboardDestination $destination): RedirectResponse|Response
    {
        return $request->user()->hasVerifiedEmail()
                    ? redirect()->to($destination->afterLogin($request))
                    : Inertia::render('Auth/VerifyEmail', ['status' => session('status')]);
    }
}
