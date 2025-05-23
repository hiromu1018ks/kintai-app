<?php
// app/Http/Requests/Auth/LoginRequest.php
namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

// Userモデルをインポート

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'string'], // 'email' を 'employee_id' に変更
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        // 'email' を 'employee_id' に変更
        if (!Auth::attempt($this->only('employee_id', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                // 'email' を 'employee_id' に変更
                'employee_id' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            // 'email' を 'employee_id' に変更
            'employee_id' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        // 'email' を 'employee_id' に変更
        return Str::transliterate(Str::lower($this->input('employee_id')) . '|' . $this->ip());
    }

    /**
     * Get the user model for the validation.
     * Laravel 11以降では、モデルのフィールドに基づいてバリデーションメッセージをカスタマイズするために
     * このようなメソッドが使われることがあります。
     * 不要であればこのメソッドはなくても動作する場合があります。
     *
     * @return User
     */
    // public function user() : User // このメソッドは必須ではないかもしれません。
    // {
    //     return User::where('employee_id', $this->input('employee_id'))->firstOrNew();
    // }
}
