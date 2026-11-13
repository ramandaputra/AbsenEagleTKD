<?php

namespace App\Http\Controllers;

use App\Models\Dojang;
use App\Models\TrainingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingSessionController extends Controller
{
    /**
     * Display a listing of training sessions.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        $query = TrainingSession::with('dojang');
        if ($user->role->value === 'admin' && $user->dojang_id) {
            $query->where('dojang_id', $user->dojang_id);
        }

        $sessions = $query->orderBy('day_of_week')->orderBy('start_time')->get();
        $dojangs = Dojang::where('is_active', true)->get();

        return view('sessions.index', compact('sessions', 'dojangs'));
    }

    /**
     * Store a new training session.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'dojang_id' => ['required', 'exists:dojangs,id'],
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'session_pin' => ['nullable', 'string', 'max:6'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        TrainingSession::create($validated);

        return back()->with('success', 'Jadwal sesi latihan berhasil ditambahkan.');
    }

    /**
     * Update training session.
     */
    public function update(Request $request, TrainingSession $trainingSession): RedirectResponse
    {
        $validated = $request->validate([
            'dojang_id' => ['required', 'exists:dojangs,id'],
            'name' => ['required', 'string', 'max:255'],
            'day_of_week' => ['required', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],
            'start_time' => ['required'],
            'end_time' => ['required'],
            'session_pin' => ['nullable', 'string', 'max:6'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $trainingSession->update($validated);

        return back()->with('success', 'Jadwal sesi latihan berhasil diperbarui.');
    }

    /**
     * Delete training session.
     */
    public function destroy(TrainingSession $trainingSession): RedirectResponse
    {
        $trainingSession->delete();

        return back()->with('success', 'Jadwal sesi latihan berhasil dihapus.');
    }
}

