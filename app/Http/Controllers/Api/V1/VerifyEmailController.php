<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use App\Models\User;
use Illuminate\Support\Facades\App;

class VerifyEmailController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = User::find($request->route('id')); //takes user ID from verification link. Even if somebody would hijack the URL, signature will be fail the request
        if ($user->hasVerifiedEmail()) {
            return redirect('email-verified')->with(App::environment());
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        $message = __('Your email has been verified.');

        return redirect('email-verified');

        // return redirect('login')->with('status', $message); //if user is already logged in it will redirect to the dashboard page
    }
}
