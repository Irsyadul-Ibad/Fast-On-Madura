<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $key = Str::lower($data['user_id']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $detik = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'user_id' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.",
            ]);
        }

        $ok = Auth::attempt([
            'user_id' => $data['user_id'],
            'password' => $data['password'],
            'aktif' => true,
        ]);

        if (! $ok) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'user_id' => 'ID pengguna atau kata sandi salah, atau akun tidak aktif.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $roleCode = $this->currentUser()->role?->role_code;

        if (in_array($roleCode, self::VENDOR_TIANG_ROLE_CODES, true)) {
            return redirect()->route('vendor.tiang');
        }

        if (in_array($roleCode, self::VENDOR_KONSTRUKSI_ROLE_CODES, true)) {
            return redirect()->route('vendor.konstruksi');
        }

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
