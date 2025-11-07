<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AccountSettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        // defaults to keep page happy if null
        $defaults = [
            'username'          => $user->username ?? '',
            'phone'             => $user->phone ?? '',
            'gender'            => $user->gender ?? null,
            'language'          => $user->language ?? 'English',
            'notify_security'   => (bool)($user->notify_security ?? true),
            'notify_budget'     => (bool)($user->notify_budget ?? true),
            'notify_quota'      => (bool)($user->notify_quota ?? true),
            'notify_general'    => (bool)($user->notify_general ?? true),
            'notify_newsletter' => (bool)($user->notify_newsletter ?? false),
        ];

        return view('account.settings', compact('user', 'defaults'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users','username')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:50'],
            'gender'   => ['nullable', Rule::in(['Male','Female','Other'])],
            'language' => ['nullable', Rule::in(['English','Spanish'])],
            'avatar'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // handle avatar upload (optional)
        if ($request->hasFile('avatar')) {
            // delete old if stored by us
            if ($user->avatar && str_starts_with($user->avatar, 'avatars/')) {
                Storage::disk('public')->delete($user->avatar);
            }
            $path = $request->file('avatar')->store('avatars', 'public'); // storage/app/public/avatars/...
            $user->avatar = $path;
        }

        $user->name     = $request->input('name');
        $user->username = $request->input('username');
        $user->phone    = $request->input('phone');
        $user->gender   = $request->input('gender');
        $user->language = $request->input('language');

        // toggles (checkboxes)
        $user->notify_security   = $request->boolean('notify_security');
        $user->notify_budget     = $request->boolean('notify_budget');
        $user->notify_quota      = $request->boolean('notify_quota');
        $user->notify_general    = $request->boolean('notify_general');
        $user->notify_newsletter = $request->boolean('notify_newsletter');

        $user->save();

        return back()->with('status', 'Profile updated!');
    }
}
