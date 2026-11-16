<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test guest sees splash screen and login page.
     */
    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('EAGLE TAEKWONDO');
        $response->assertSee('splashContainer');

        $loginResponse = $this->get('/login');
        $loginResponse->assertStatus(200);
        $loginResponse->assertSee('EAGLE TAEKWONDO');
        $loginResponse->assertSee('PIN Keamanan');
        $loginResponse->assertSee('keypad-btn');
    }

    /**
     * Test web PIN authentication via AJAX — name + PIN flow.
     */
    public function test_web_login_pin_ajax_success(): void
    {
        $role = \App\Models\Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'Asisten Pelatih',
        ]);

        $user = \App\Models\User::create([
            'name' => 'Sabeum Bryan',
            'email' => 'bryan@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'pin' => '1111',
            'is_active' => true,
        ]);

        $response = $this->postJson('/login-pin', [
            'name' => 'Sabeum Bryan',
            'pin' => '1111',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['redirect', 'user']);

        $this->assertAuthenticatedAs($user);
    }

    /**
     * Test web PIN authentication fails with wrong PIN.
     */
    public function test_web_login_pin_ajax_fails_with_wrong_pin(): void
    {
        $role = \App\Models\Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'Asisten Pelatih',
        ]);

        $user = \App\Models\User::create([
            'name' => 'Sabeum Kevin',
            'email' => 'kevin@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'pin' => '3333',
            'is_active' => true,
        ]);

        $response = $this->postJson('/login-pin', [
            'name' => 'Sabeum Kevin',
            'pin' => '0000',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);

        $this->assertGuest();
    }

    /**
     * Test asisten pelatih sees beranda and akun matching Tirtafield style.
     */
    public function test_asisten_pelatih_dashboard_and_profile_views(): void
    {
        $role = \App\Models\Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'Asisten Pelatih',
        ]);

        $dojang = \App\Models\Dojang::create([
            'name' => 'Dojang Eagle Sukasari',
            'code' => 'DOJ-SUKA',
            'address' => 'Jl. Siliwangi No. 12',
            'is_active' => true,
        ]);

        $user = \App\Models\User::create([
            'name' => 'Raja',
            'email' => 'raja@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'dojang_id' => $dojang->id,
            'belt_level' => 'Dan 2 Kukkiwon',
            'pin' => '1234',
            'is_active' => true,
        ]);

        // 1. Beranda (Dashboard) - Mengambil Dojang & Sabuk dari tabel database
        $beranda = $this->actingAs($user)->get('/dashboard');
        $beranda->assertStatus(200);
        $beranda->assertSee('Dojang Eagle Sukasari');
        $beranda->assertSee('Dan 2 Kukkiwon');
        $beranda->assertSee('Presensi Kehadiran Pelatih');
        $beranda->assertSee('Materi Latihan Hari Ini');
        $beranda->assertSee('Beranda');
        $beranda->assertSee('Akun');

        // 2. Akun (Profile) - hanya PIN, tanpa Informasi Kepelatihan/Rekening/Kata Sandi
        $akun = $this->actingAs($user)->get('/profile');
        $akun->assertStatus(200);
        $akun->assertSee('Ubah PIN Presensi', false);
        $akun->assertSee('Keamanan', false);
        $akun->assertSee('Keluar dari Akun');
        $akun->assertDontSee('Informasi Kepelatihan');
        $akun->assertDontSee('Rekening Transfer Honor');
        $akun->assertDontSee('Ubah Kata Sandi Akun');
        $akun->assertDontSee('Simpan Perubahan Profil');
    }

    /**
     * Test asisten pelatih can update their 6-digit PIN with old PIN verification.
     */
    public function test_asisten_pelatih_can_update_pin(): void
    {
        $role = \App\Models\Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'Asisten Pelatih',
        ]);

        $user = \App\Models\User::create([
            'name' => 'Raja',
            'email' => 'raja@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'pin' => '123456',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/profile/pin', [
            'old_pin'          => '123456',
            'pin'              => '567890',
            'pin_confirmation' => '567890',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id'  => $user->id,
            'pin' => '567890',
        ]);
    }

    public function test_asisten_pelatih_cannot_update_pin_with_wrong_old_pin(): void
    {
        $role = \App\Models\Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'Asisten Pelatih',
        ]);

        $user = \App\Models\User::create([
            'name' => 'Raja',
            'email' => 'raja2a@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'pin' => '123456',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/profile/pin', [
            'old_pin'          => '999999',
            'pin'              => '567890',
            'pin_confirmation' => '567890',
        ]);

        $response->assertSessionHasErrors('old_pin');
        $this->assertDatabaseHas('users', [
            'id'  => $user->id,
            'pin' => '123456', // unchanged
        ]);
    }

    public function test_attendance_check_in_screen_has_camera_switch_and_no_gallery_upload(): void
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'asisten_pelatih'], ['display_name' => 'Asisten Pelatih']);
        $user = \App\Models\User::create([
            'name' => 'Raja',
            'email' => 'raja2@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'pin' => '1234',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/attendances/check-in');

        $response->assertStatus(200);
        $response->assertSee('Ambil Foto Presensi Langsung');
        $response->assertSee('Wajib jepret langsung melalui kamera (galeri dinonaktifkan)');
        $response->assertSee('cameraswitch');
        $response->assertSee('toggleCameraFacing()');
        $response->assertDontSee('cloud_upload');
        $response->assertDontSee('Buka Kamera Ponsel / Galeri');
    }

    public function test_attendance_show_matches_tirtafield_bukti_presensi_with_kembali_ke_beranda(): void
    {
        $role = \App\Models\Role::firstOrCreate(['name' => 'asisten_pelatih'], ['display_name' => 'Asisten Pelatih']);
        $dojang = \App\Models\Dojang::firstOrCreate(['name' => 'Dojang Eagle Central'], [
            'code' => 'DOJ-CEN',
            'address' => 'Jl. Merdeka No. 45 Tanjungpinang',
            'is_active' => true,
        ]);
        $user = \App\Models\User::create([
            'name' => 'Raja',
            'email' => 'raja_bukti@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $role->id,
            'dojang_id' => $dojang->id,
            'pin' => '123456',
            'is_active' => true,
        ]);

        $attendance = \App\Models\Attendance::create([
            'user_id' => $user->id,
            'date' => '2026-11-16',
            'time' => '08:15:00',
            'status' => 'Hadir',
            'session_rate' => 50000.00,
        ]);

        \App\Models\AttendancePhoto::create([
            'attendance_id' => $attendance->id,
            'photo_path' => 'uploads/attendances/test_foto.jpg',
            'file_name' => 'test_foto.jpg',
            'file_size' => 12345,
        ]);

        $response = $this->actingAs($user)->get('/attendances/' . $attendance->id);

        $response->assertStatus(200);
        $response->assertSee('Bukti Presensi');
        $response->assertSee('Presensi Berhasil Terkirim!');
        $response->assertSee('Data kehadiran Anda telah tercatat dan terverifikasi di sistem Eagle TKD.');
        $response->assertSee('Waktu Stempel');
        $response->assertSee('Lokasi Penugasan');
        $response->assertSee('Dojang Eagle Central');
        $response->assertSee('Lampiran Bukti Fisik');
        $response->assertSee('Raja16112026.jpg');
        $response->assertDontSee('BERSTEMPEL LOKASI');
        $response->assertDontSee('Foto Presensi Sah & Terverifikasi');
        $response->assertDontSee('Tercatat: Presensi Sah');
        $response->assertDontSee('Sesi Aktivitas Berikutnya');
        $response->assertDontSee('Penugasan Dojang');
        $response->assertSee('Kembali ke Beranda');
    }

    public function test_superadmin_dashboard_has_same_style_with_activity_log_and_user_management(): void
    {
        $roleSuper = \App\Models\Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Superadmin']);
        $userSuper = \App\Models\User::create([
            'name' => 'mingle',
            'email' => 'mingle@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $roleSuper->id,
            'pin' => '123456',
            'is_active' => true,
        ]);

        $response = $this->actingAs($userSuper)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Dashboard Superadmin');
        $response->assertSee('Kelola Pengguna');
        $response->assertSee('Log Aktivitas');
        $response->assertSee('Jadwal, Tugas &amp; Materi Latihan', false);
        $response->assertSee('Rekap Gaji &amp; Atur Tarif Pertemuan', false);
        $response->assertSee('Presensi');
        $response->assertSee('Asisten');
        $response->assertSee('Jadwal');
        $response->assertSee('Log');
    }

    public function test_superadmin_can_add_asisten_pelatih_and_update_role(): void
    {
        $roleSuper = \App\Models\Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Superadmin']);
        \App\Models\Role::firstOrCreate(['name' => 'asisten_pelatih'], ['display_name' => 'Asisten Pelatih']);
        \App\Models\Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Pelatih Utama']);

        $userSuper = \App\Models\User::create([
            'name' => 'mingle_admin',
            'email' => 'mingle2@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $roleSuper->id,
            'pin' => '123456',
            'is_active' => true,
        ]);

        // 1. Visit users page
        $viewUsers = $this->actingAs($userSuper)->get('/users');
        $viewUsers->assertStatus(200);
        $viewUsers->assertSee('Kelola Pengguna &amp; Role', false);
        $viewUsers->assertSee('Tambah');

        // 2. Superadmin adds a new assistant coach
        $createResponse = $this->actingAs($userSuper)->post('/users', [
            'name' => 'Pelatih Baru',
            'email' => 'baru@eagletkd.com',
            'password' => 'secret123',
            'role' => 'asisten_pelatih',
            'rate_per_session' => 60000,
            'belt_level' => 'Dan 1 Kukkiwon',
            'phone' => '08123456789',
        ]);

        $createResponse->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'baru@eagletkd.com',
            'name' => 'Pelatih Baru',
        ]);

        $newUser = \App\Models\User::where('email', 'baru@eagletkd.com')->first();
        $this->assertEquals('asisten_pelatih', $newUser->role->name);

        // 3. Superadmin changes role to Pelatih Utama (admin)
        $updateResponse = $this->actingAs($userSuper)->put('/users/' . $newUser->id, [
            'name' => 'Pelatih Baru Dipromosikan',
            'email' => 'baru@eagletkd.com',
            'role' => 'admin',
            'rate_per_session' => 75000,
            'is_active' => 1,
        ]);

        $updateResponse->assertRedirect();
        $newUser->refresh();
        $this->assertEquals('admin', $newUser->role->name);
        $this->assertEquals('Pelatih Baru Dipromosikan', $newUser->name);
        $this->assertEquals(75000, (int) $newUser->rate_per_session);
    }

    public function test_superadmin_can_view_activity_logs(): void
    {
        $roleSuper = \App\Models\Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Superadmin']);
        $userSuper = \App\Models\User::create([
            'name' => 'mingle_logs',
            'email' => 'mingle_logs@eagletkd.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'role_id' => $roleSuper->id,
            'pin' => '123456',
            'is_active' => true,
        ]);

        \App\Models\ActivityLog::record($userSuper->id, 'LOGIN', 'Superadmin login ke sistem.');

        $response = $this->actingAs($userSuper)->get('/activity-logs');
        $response->assertStatus(200);
        $response->assertSee('Log Aktivitas Sistem');
        $response->assertSee('Superadmin login ke sistem.');
    }
}
