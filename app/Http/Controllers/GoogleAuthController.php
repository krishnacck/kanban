<?php

namespace App\Http\Controllers;

use App\Helpers\UserDefaults;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect('/login')->withErrors([
                'email' => 'Google authentication failed. Please try again.',
            ]);
        }

        if (empty($googleUser->getEmail())) {
            return redirect('/login')->withErrors([
                'email' => 'Could not retrieve email from Google. Please try again.',
            ]);
        }

        $isNew = !User::where('email', $googleUser->getEmail())->exists();

        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()],
            [
                'name' => $googleUser->getName() ?? $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'role' => 'user',
            ]
        );

        // Seed default statuses and categories for new users
        if ($isNew) {
            UserDefaults::seedForUser($user);
        }

        Auth::login($user, true);

        return redirect('/board');
    }
}
