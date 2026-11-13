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
        $akun->assertSee('Ubah PIN Presensi (4 Digit)');
        $akun->assertSee('Keluar dari Akun');
        $akun->assertDontSee('Informasi Kepelatihan');
        $akun->assertDontSee('Rekening Transfer Honor');
        $akun->assertDontSee('Ubah Kata Sandi Akun');
        $akun->assertDontSee('Simpan Perubahan Profil');
    }

    /**
     * Test asisten pelatih can update their 4-digit PIN.
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
            'pin' => '1234',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->post('/profile/pin', [
            'pin' => '5678',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'pin' => '5678',
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
}
