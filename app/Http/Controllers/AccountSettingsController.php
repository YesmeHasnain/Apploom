<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountSettingsController extends Controller
{
    public function edit()
    {
        $user = Auth::user();

        $defaults = [
            // username remove, description add
            'description'       => $user->description,
            'phone'             => $user->phone,
            'gender'            => $user->gender,
            'language'          => $user->language,
            'notify_security'   => (bool) $user->notify_security,
            'notify_budget'     => (bool) $user->notify_budget,
            'notify_quota'      => (bool) $user->notify_quota,
            'notify_general'    => (bool) $user->notify_general,
            'notify_newsletter' => (bool) $user->notify_newsletter,
        ];

        return view('account.settings', compact('user','defaults'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'        => ['required','string','max:255'],
            // username rule removed
            'description' => ['nullable','string','max:500'],
            'phone'       => ['nullable','string','max:50'],
            'gender'      => ['nullable', Rule::in(['Male','Female','Other'])],
            'language'    => ['nullable', Rule::in(['English','Spanish'])],
            'avatar'      => ['nullable','file','mimetypes:image/jpeg,image/png,image/webp,image/gif','max:5120'],
        ]);

        // Avatar
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            if (! $file->isValid()) {
                return back()->withErrors(['avatar' => 'The selected image is invalid. Please try another file.']);
            }
            try {
                if ($user->avatar && Str::startsWith($user->avatar, 'avatars/')) {
                    Storage::disk('public')->delete($user->avatar);
                }
                $path = $file->store('avatars', 'public');
                if (! $path) {
                    return back()->withErrors(['avatar' => 'The avatar failed to upload.']);
                }
                $user->avatar = $path;
            } catch (\Throwable $e) {
                return back()->withErrors(['avatar' => 'The avatar failed to upload. '.$e->getMessage()]);
            }
        }

        // Fields
        $user->name        = $request->input('name');
        $user->description = $request->input('description'); // <-- NEW
        $user->phone       = $request->input('phone');
        $user->gender      = $request->input('gender');
        $user->language    = $request->input('language');

        // Toggles
        $user->notify_security   = $request->boolean('notify_security');
        $user->notify_budget     = $request->boolean('notify_budget');
        $user->notify_quota      = $request->boolean('notify_quota');
        $user->notify_general    = $request->boolean('notify_general');
        $user->notify_newsletter = $request->boolean('notify_newsletter');

        $user->save();

        return back()->with('status', 'Profile updated!');
    }
}
