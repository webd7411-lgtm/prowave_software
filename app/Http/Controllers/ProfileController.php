<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = auth()->user();

        $user->load(['roles.permissions', 'department', 'reportingManager']);

        return view('profile.show', [
            'user' => $user,
            'loginLogs' => $user->loginLogs()->latest()->paginate(10),
            'activities' => $user->activities()->with('causer')->latest()->paginate(10),
        ]);
    }

    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = auth()->user();

        $user->update($request->validated());

        app(AuditService::class)->log('Profile updated', 'profile', $user);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $user = auth()->user();

        if (! Hash::check($request->string('current_password'), $user->password)) {
            return back()->withErrors(['current_password' => 'Your current password is incorrect.']);
        }

        $user->update(['password' => Hash::make($request->string('password'))]);

        app(AuditService::class)->log('Password changed', 'security', $user);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Password updated successfully.',
        ]);
    }

    public function updateAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = auth()->user();

        if ($user->avatar && file_exists(public_path($user->avatar))) {
            unlink(public_path($user->avatar));
        }

        $file = $request->file('avatar');
        $name = 'avatars/'.$user->id.'-'.time().'.'.$file->getClientOriginalExtension();

        $file->move(public_path('avatars'), basename($name));

        $user->update(['avatar' => $name]);

        app(AuditService::class)->log('Avatar updated', 'profile', $user);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Avatar updated successfully.',
        ]);
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $request->validate([
            'theme' => ['required', 'string', 'in:light,dark,system'],
            'language' => ['required', 'string', 'in:en'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'date_format' => ['nullable', 'string', 'max:20'],
        ]);

        $user = auth()->user();
        $user->update($request->only(['theme', 'language', 'timezone', 'date_format']));

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Preferences updated successfully.',
        ]);
    }
}
