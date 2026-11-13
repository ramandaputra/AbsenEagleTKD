<?php

namespace App\Http\Controllers;

use App\Models\Dojang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DojangController extends Controller
{
    /**
     * Display a listing of dojangs.
     */
    public function index(): View
    {
        $dojangs = Dojang::withCount(['coaches', 'trainingSessions', 'attendances'])->get();

        return view('dojangs.index', compact('dojangs'));
    }

    /**
     * Store a new dojang.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:dojangs,code'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:50'],
        ]);

        Dojang::create($validated);

        return back()->with('success', 'Cabang Dojang Taekwondo berhasil ditambahkan.');
    }

    /**
     * Update dojang.
     */
    public function update(Request $request, Dojang $dojang): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:dojangs,code,'.$dojang->id],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'radius_meters' => ['required', 'integer', 'min:10', 'max:2000'],
            'phone' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $dojang->update($validated);

        return back()->with('success', 'Data Dojang berhasil diperbarui.');
    }

    /**
     * Delete dojang.
     */
    public function destroy(Dojang $dojang): RedirectResponse
    {
        $dojang->delete();

        return back()->with('success', 'Dojang berhasil dihapus.');
    }
}

