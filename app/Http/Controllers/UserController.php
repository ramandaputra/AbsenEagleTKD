<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Dojang;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of coaches and administrators.
     */
    public function index(Request $request): View
    {
        $roleFilter = $request->input('role');
        $dojangFilter = $request->input('dojang_id');

        $query = User::with(['dojang', 'role']);

        if ($roleFilter) {
            $query->whereHas('role', fn ($query) => $query->where('name', $roleFilter));
        }
        if ($dojangFilter) {
            $query->where('dojang_id', $dojangFilter);
        }

        $users = $query->orderBy('role_id')->orderBy('name')->paginate(15)->withQueryString();
        $dojangs = Dojang::where('is_active', true)->get();

        return view('users.index', compact('users', 'dojangs', 'roleFilter', 'dojangFilter'));
    }

    /**
     * Store a newly created coach or admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'asisten_pelatih'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'rate_per_session' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = UserRole::from($validated['role']);

        User::create($validated);

        return back()->with('success', 'Akun pelatih baru berhasil didaftarkan ke dalam sistem.');
    }

    /**
     * Update user details and honor rate.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'asisten_pelatih'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'rate_per_session' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['role'] = UserRole::from($validated['role']);
        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return back()->with('success', 'Data pelatih dan tarif honor berhasil diperbarui.');
    }

    /**
     * Delete user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $user->delete();

        return back()->with('success', 'Akun pelatih berhasil dihapus.');
    }
}

