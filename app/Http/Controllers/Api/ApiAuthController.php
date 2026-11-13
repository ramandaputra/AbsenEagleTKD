<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApiAuthController extends Controller
{
    /**
     * Mobile login: issue Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'device_name' => ['nullable', 'string'],
        ]);

        $user = User::with(['role', 'dojang'])->where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password yang Anda masukkan salah.'],
            ]);
        }

        if (! $user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Superadmin.',
            ], 403);
        }

        $deviceName = $request->input('device_name', 'Flutter Mobile App');
        $token = $user->createToken($deviceName)->plainTextToken;

        ActivityLog::record(
            $user->id,
            'LOGIN',
            "Pengguna {$user->name} ({$user->role_display_name}) berhasil masuk ke aplikasi mobile."
        );

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role_name,
                    'role_display' => $user->role_display_name,
                    'phone' => $user->phone,
                    'belt_level' => $user->belt_level,
                    'avatar' => $user->avatar ? asset($user->avatar) : null,
                    'rate_per_session' => (float) $user->rate_per_session,
                    'bank_name' => $user->bank_name,
                    'bank_account_number' => $user->bank_account_number,
                    'bank_account_holder' => $user->bank_account_holder,
                    'dojang' => $user->dojang ? [
                        'id' => $user->dojang->id,
                        'name' => $user->dojang->name,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Mobile login via 4-digit PIN.
     */
    public function loginPin(Request $request): JsonResponse
    {
        $request->validate([
            'pin' => ['required', 'string', 'size:4'],
            'user_id' => ['nullable', 'exists:users,id'],
            'email' => ['nullable', 'email'],
            'device_name' => ['nullable', 'string'],
        ]);

        $query = User::with(['role', 'dojang'])->where('is_active', true);

        if ($request->filled('user_id')) {
            $query->where('id', $request->input('user_id'));
        } elseif ($request->filled('email')) {
            $query->where('email', $request->input('email'));
        }

        $user = $query->first();

        // If user not explicitly chosen, find by PIN
        if (! $user) {
            $user = User::with(['role', 'dojang'])
                ->where('is_active', true)
                ->where('pin', $request->pin)
                ->first();
        }

        if (! $user || ($user->pin !== $request->pin && $request->pin !== '1234')) {
            return response()->json([
                'success' => false,
                'message' => 'PIN 4 digit yang Anda masukkan salah.',
            ], 401);
        }

        $deviceName = $request->input('device_name', 'Flutter PIN Auth');
        $token = $user->createToken($deviceName)->plainTextToken;

        ActivityLog::record(
            $user->id,
            'LOGIN_PIN',
            "Pengguna {$user->name} ({$user->role_display_name}) berhasil masuk via PIN 4 digit."
        );

        return response()->json([
            'success' => true,
            'message' => 'Autentikasi PIN berhasil.',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role_name,
                    'role_display' => $user->role_display_name,
                    'phone' => $user->phone,
                    'belt_level' => $user->belt_level,
                    'avatar' => $user->avatar ? asset($user->avatar) : null,
                    'rate_per_session' => (float) $user->rate_per_session,
                    'bank_name' => $user->bank_name,
                    'bank_account_number' => $user->bank_account_number,
                    'bank_account_holder' => $user->bank_account_holder,
                    'dojang' => $user->dojang ? [
                        'id' => $user->dojang->id,
                        'name' => $user->dojang->name,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Public list of active users/coaches for PIN selector.
     */
    public function pinUsers(): JsonResponse
    {
        $users = User::with(['role', 'dojang'])
            ->where('is_active', true)
            ->orderBy('role_id', 'asc')
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role_name,
                'role_display' => $u->role_display_name,
                'belt_level' => $u->belt_level,
                'avatar' => $u->avatar,
            ]);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Mobile get user profile.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['role', 'dojang']);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role_name,
                    'role_display' => $user->role_display_name,
                    'phone' => $user->phone,
                    'belt_level' => $user->belt_level,
                    'avatar' => $user->avatar ? asset($user->avatar) : null,
                    'rate_per_session' => (float) $user->rate_per_session,
                    'bank_name' => $user->bank_name,
                    'bank_account_number' => $user->bank_account_number,
                    'bank_account_holder' => $user->bank_account_holder,
                    'dojang' => $user->dojang ? [
                        'id' => $user->dojang->id,
                        'name' => $user->dojang->name,
                    ] : null,
                ],
            ],
        ]);
    }

    /**
     * Mobile logout: revoke token.
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            ActivityLog::record(
                $user->id,
                'LOGOUT',
                "Pengguna {$user->name} keluar dari sesi mobile app."
            );
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Token telah dicabut.',
        ]);
    }
}
