<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function callback(string $provider)
    {
        try {
            $oauth = Socialite::driver($provider)->user();
        } catch (InvalidStateException $e) {
            $oauth = Socialite::driver($provider)->stateless()->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->withErrors(['oauth' => 'Sign-in failed: '.$e->getMessage()]);
        }

        $user = User::where('provider', $provider)
                    ->where('provider_id', $oauth->getId())
                    ->first();

        if (! $user) {
            $email = $oauth->getEmail();
            if ($email) {
                $user = User::where('email', $email)->first();
            }

            if (! $user) {
                $user = User::create([
                    'name'              => $oauth->getName() ?: ($oauth->getNickname() ?: 'User '.Str::upper(Str::random(5))),
                    'email'             => $email ?: strtolower($provider.'_'.$oauth->getId()).'@example.invalid',
                    // model does not hash → hash here
                    'password'          => Hash::make(Str::random(32)),
                    'avatar'            => $oauth->getAvatar(),
                    'username'          => $oauth->getNickname(),
                    'email_verified_at' => now(),
                ]);
            }

            $user->forceFill([
                'provider'       => $provider,
                'provider_id'    => $oauth->getId(),
                'provider_token' => $oauth->token ?? null,
                'avatar'         => $oauth->getAvatar() ?: $user->avatar,
                'username'       => $oauth->getNickname() ?: $user->username,
            ])->save();
        }

        Auth::login($user, remember: true);

        return redirect()->intended('/Dashboard/GettingStarted');
    }
}
