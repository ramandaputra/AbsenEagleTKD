<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ApiUserController extends Controller
{
    /**
     * Get list of users with roles.
     */
    public function index(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $query = User::with(['role', 'dojang']);

        if ($request->filled('role')) {
            $query->whereHas('role', fn ($q) => $q->where('name', $request->input('role')));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Get list of roles.
     */
    public function roles(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Role::all(),
        ]);
    }

    /**
     * Store a new user (Superadmin only).
     */
    public function store(Request $request): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isSuperAdmin()) {
            return response()->json(['message' => 'Hanya Superadmin yang dapat membuat pengguna baru.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'rate_per_session' => ['required', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        ActivityLog::record(
            $actor->id,
            'CREATE_USER',
            "Superadmin {$actor->name} menambahkan pengguna baru: {$user->name}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna baru berhasil ditambahkan.',
            'data' => $user->load('role'),
        ], 201);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isSuperAdmin() && $actor->id !== $user->id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['sometimes', 'exists:roles,id'],
            'phone' => ['nullable', 'string', 'max:30'],
            'belt_level' => ['nullable', 'string', 'max:50'],
            'dojang_id' => ['nullable', 'exists:dojangs,id'],
            'rate_per_session' => ['sometimes', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::record(
            $actor->id,
            'UPDATE_USER',
            "Data pengguna {$user->name} berhasil diperbarui."
        );

        return response()->json([
            'success' => true,
            'message' => 'Data pengguna berhasil diperbarui.',
            'data' => $user->load('role'),
        ]);
    }

    /**
     * Delete user.
     */
    public function destroy(Request $request, User $user): JsonResponse
    {
        $actor = $request->user();
        if (! $actor->isSuperAdmin()) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        if ($user->id === $actor->id) {
            return response()->json(['message' => 'Tidak dapat menghapus akun Anda sendiri.'], 422);
        }

        $name = $user->name;
        $user->delete();

        ActivityLog::record(
            $actor->id,
            'DELETE_USER',
            "Superadmin {$actor->name} menghapus pengguna: {$name}."
        );

        return response()->json([
            'success' => true,
            'message' => 'Pengguna berhasil dihapus.',
        ]);
    }
}

