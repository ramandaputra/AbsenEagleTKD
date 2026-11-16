<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show the splash screen.
     */
    public function splash(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.splash');
    }

    /**
     * Show the login page.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle standard login request.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang kembali, '.Auth::user()->name.'!');
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle 1-click quick demo login for easy testing.
     */
    public function quickLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($request->input('user_id'));
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('success', 'Berhasil masuk sebagai '.$user->name.' ('.($user->role_display_name).')');
    }

    /**
     * Handle login via name + 4-digit PIN (supports AJAX and standard form).
     */
    public function loginPin(Request $request): \Illuminate\Http\JsonResponse|RedirectResponse
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'pin'  => ['required', 'string', 'size:4'],
            'name' => ['required', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $name = trim($request->input('name'));

        $user = User::where('is_active', true)
            ->whereRaw('LOWER(name) = ?', [strtolower($name)])
            ->first();

        if (! $user || $user->pin !== $request->input('pin')) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nama atau PIN yang Anda masukkan tidak sesuai.',
                ], 422);
            }
            return back()->withErrors([
                'pin' => 'Nama atau PIN yang Anda masukkan tidak sesuai.',
            ])->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selamat datang kembali, '.$user->name.'!',
                'redirect' => route('dashboard'),
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role_name,
                    'role_display' => $user->role_display_name,
                ],
            ]);
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Selamat datang, '.$user->name.'!');
    }

    /**
     * Handle user logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('info', 'Anda telah berhasil keluar dari sistem.');
    }
}

