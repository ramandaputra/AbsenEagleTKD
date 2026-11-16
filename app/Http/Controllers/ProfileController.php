<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Show profile page.
     */
    public function show(Request $request): View
    {
        $user = $request->user()->load('dojang');
        $dojangs = \App\Models\Dojang::where('is_active', true)->get();

        return view('profile.show', compact('user', 'dojangs'));
    }

    /**
     * Update profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'avatar' => ['nullable', 'image', 'max:3072'],
        ]);

        if ($request->hasFile('avatar')) {
            $uploadDir = public_path('uploads/profiles');
            if (! File::exists($uploadDir)) {
                File::makeDirectory($uploadDir, 0755, true, true);
            }

            $file = $request->file('avatar');
            $filename = 'avatar_'.$user->id.'_'.time().'_'.Str::random(6).'.'.$file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $validated['avatar'] = 'uploads/profiles/'.$filename;
        }

        $user->update($validated);

        return back()->with('success', 'Profil dan informasi rekening honor berhasil diperbarui.');
    }

    /**
     * Change user PIN (6-digit, with old PIN verification + confirm).
     */
    public function updatePin(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'old_pin'          => ['required', 'string', 'regex:/^[0-9]{4,6}$/'],
            'pin'              => ['required', 'string', 'regex:/^[0-9]{6}$/'],
            'pin_confirmation' => ['required', 'string', 'same:pin'],
        ];

        $validated = $request->validate($rules, [
            'old_pin.required'          => 'PIN lama wajib diisi.',
            'old_pin.regex'             => 'PIN lama harus berupa angka.',
            'pin.required'              => 'PIN baru wajib diisi.',
            'pin.regex'                 => 'PIN baru harus tepat 6 digit angka.',
            'pin_confirmation.required' => 'Konfirmasi PIN baru wajib diisi.',
            'pin_confirmation.same'     => 'Konfirmasi PIN baru tidak sesuai.',
        ]);

        $currentPin = (string) ($user->pin ?: '1234');
        if (trim($validated['old_pin']) !== $currentPin) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN lama tidak sesuai. Silakan periksa kembali.',
                ], 422);
            }
            return back()->withErrors(['old_pin' => 'PIN lama tidak sesuai. Silakan periksa kembali.']);
        }

        $newPin = trim($validated['pin']);
        if ($newPin === $currentPin) {
            return back()->withErrors(['pin' => 'PIN baru tidak boleh sama dengan PIN lama.']);
        }

        $user->update(['pin' => $newPin]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'PIN Presensi & Keamanan berhasil diperbarui.',
            ]);
        }

        return back()->with('success', 'PIN Presensi & Keamanan berhasil diperbarui.');
    }

    /**
     * Change user password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }
}

