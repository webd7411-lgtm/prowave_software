<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        if (! setting('general.allow_registration', true)) {
            abort(404);
        }

        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->string('name'),
            'email' => $request->string('email'),
            'password' => Hash::make($request->string('password')),
            'theme' => 'light',
        ]);

        $user->assignRole(setting('general.default_role', 'Employee'));

        event(new Registered($user));

        Auth::login($user);

        app(AuditService::class)->logAuth('New user registered', [
            'email' => $user->email,
        ]);

        return redirect(route('dashboard'));
    }
}
