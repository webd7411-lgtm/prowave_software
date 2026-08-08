<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
            'remember' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ];
    }

    public function userByEmail(): ?User
    {
        return User::where('email', strtolower($this->string('email')))->first();
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        throw ValidationException::withMessages([
            'email' => 'Too many login attempts. Please try again in '.RateLimiter::availableIn($this->throttleKey()).' seconds.',
        ]);
    }

    public function ensureAccountIsNotLocked(): void
    {
        $user = $this->userByEmail();

        if ($user && $user->isLocked()) {
            $minutes = (int) ceil($user->remainingLockSeconds() / 60);

            throw ValidationException::withMessages([
                'email' => 'Your account is temporarily locked due to too many failed attempts. Please try again in '.$minutes.' minute(s).',
            ]);
        }
    }

    public function throttleKey(): string
    {
        return 'login:'.strtolower($this->string('email')).'|'.$this->ip();
    }

    public function hitRateLimiter(): void
    {
        RateLimiter::hit($this->throttleKey(), 60);
    }

    public function clearRateLimiter(): void
    {
        RateLimiter::clear($this->throttleKey());
    }
}
