<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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

        $key = Str::lower($data['user_id']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
             $detik = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'user_id' => "Terlalu banyak percobaan. Coba lagi dalam {$detik} detik.",
            ]);
        }
    

     $ok = Auth::attempt([
            'user_id'  => $data['user_id'],
            'password' => $data['password'],
            'aktif'    => true,
        ]);

        if (! $ok) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages([
                'user_id' => 'ID pengguna atau kata sandi salah, atau akun tidak aktif.',
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        if (in_array($this->currentUser()->role?->role_code, ['VENDOR_TIANG', 'VENDOR_TIANG2', '5170T1', '5170T2', '5170T3', '5170T4', '5170T5', '5170T6', '5170T7', '5170T8', '5170T9'], true)) {
            return redirect()->route('vendor.tiang');
        }
        if (in_array($this->currentUser()->role?->role_code, ['VENDOR_KONSTRUKSI', 'VENDOR_KONSTRUKSI2', '5170K1', '5170K2', '5170K3', '5170K4', '5170K5', '5170K6', '5170K7'], true)) {
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
