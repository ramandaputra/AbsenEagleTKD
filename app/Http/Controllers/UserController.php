<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Dojang;
use App\Models\Role;
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
            $query->whereHas('role', fn ($q) => $q->where('name', $roleFilter));
        }
        if ($dojangFilter) {
            $query->where('dojang_id', $dojangFilter);
        }

        $users = $query->orderBy('role_id')->orderBy('name')->paginate(20)->withQueryString();
        $dojangs = Dojang::where('is_active', true)->get();
        $roles = Role::all();

        return view('users.index', compact('users', 'dojangs', 'roles', 'roleFilter', 'dojangFilter'));
    }

    /**
     * Store a newly created coach or admin.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:4'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'asisten_pelatih'])],
            'pin' => ['nullable', 'string', 'digits_between:4,6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'rate_per_session' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ]);

        $roleModel = Role::firstOrCreate(
            ['name' => $validated['role']],
            ['display_name' => match ($validated['role']) {
                'superadmin' => 'Superadmin',
                'admin' => 'Pelatih Utama',
                'asisten_pelatih' => 'Asisten Pelatih',
            }]
        );

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'pin' => ! empty($validated['pin']) ? $validated['pin'] : '123456',
            'role_id' => $roleModel->id,
            'phone' => $validated['phone'] ?? null,
            'belt_level' => $validated['belt_level'] ?? null,
            'dojang_id' => $validated['dojang_id'] ?? null,
            'rate_per_session' => $validated['rate_per_session'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'is_active' => true,
        ]);

        ActivityLog::record(
            auth()->id() ?? $user->id,
            'CREATE_USER',
            "Mendaftarkan pengguna/pelatih baru: {$user->name} ({$roleModel->display_name})."
        );

        return back()->with('success', "Pengguna baru {$user->name} ({$roleModel->display_name}) berhasil didaftarkan.");
    }

    /**
     * Update user details and role.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:4'],
            'role' => ['required', Rule::in(['superadmin', 'admin', 'asisten_pelatih'])],
            'pin' => ['nullable', 'string', 'digits_between:4,6'],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'rate_per_session' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable'],
        ]);

        $roleModel = Role::firstOrCreate(
            ['name' => $validated['role']],
            ['display_name' => match ($validated['role']) {
                'superadmin' => 'Superadmin',
                'admin' => 'Pelatih Utama',
                'asisten_pelatih' => 'Asisten Pelatih',
            }]
        );

        $oldRole = $user->role?->display_name ?? 'Pelatih';

        $dataToUpdate = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $roleModel->id,
            'phone' => $validated['phone'] ?? null,
            'belt_level' => $validated['belt_level'] ?? null,
            'dojang_id' => $validated['dojang_id'] ?? null,
            'rate_per_session' => $validated['rate_per_session'],
            'bank_name' => $validated['bank_name'] ?? null,
            'bank_account_number' => $validated['bank_account_number'] ?? null,
            'bank_account_holder' => $validated['bank_account_holder'] ?? null,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];

        if (! empty($validated['password'])) {
            $dataToUpdate['password'] = Hash::make($validated['password']);
        }

        if (! empty($validated['pin'])) {
            $dataToUpdate['pin'] = $validated['pin'];
        }

        $user->update($dataToUpdate);

        ActivityLog::record(
            auth()->id(),
            'UPDATE_USER',
            "Memperbarui data pengguna & role {$user->name} ({$oldRole} -> {$roleModel->display_name})."
        );

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui (Role: {$roleModel->display_name}).");
    }

    /**
     * Delete user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $name = $user->name;
        $roleName = $user->role?->display_name ?? 'Pelatih';
        $user->delete();

        ActivityLog::record(
            auth()->id(),
            'DELETE_USER',
            "Menghapus pengguna: {$name} ({$roleName})."
        );

        return back()->with('success', "Akun pelatih {$name} berhasil dihapus.");
    }
}
