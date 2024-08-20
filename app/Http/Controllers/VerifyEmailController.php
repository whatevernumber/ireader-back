<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VerifyEmailController
{
    public function verifyEmail(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id'));

        if ($user->hasVerifiedEmail()) {
            return redirect(env('FRONTEND_ADDRESS'));
        }

        $user->markEmailAsVerified();
        return redirect(env('FRONTEND_ADDRESS'));
    }
}
