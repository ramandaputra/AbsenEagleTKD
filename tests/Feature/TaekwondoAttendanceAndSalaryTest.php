<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Dojang;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\Role;
use App\Models\TrainingSchedule;
use App\Models\User;
use App\Services\SalaryCalculationService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TaekwondoAttendanceAndSalaryTest extends TestCase
{
    use RefreshDatabase;

    protected Role $superadminRole;
    protected Role $adminRole;
    protected Role $assistantRole;

    protected Dojang $dojang;
    protected User $superadmin;
    protected User $pelatihUtama;
    protected User $asisten1;
    protected User $asisten2;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        // Seed Roles
        $this->superadminRole = Role::create([
            'name' => 'superadmin',
            'display_name' => 'Superadmin',
            'description' => 'Full administrative access',
        ]);

        $this->adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin / Pelatih Utama',
            'description' => 'Manage schedules, duty, and attendances',
        ]);

        $this->assistantRole = Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'Asisten Pelatih',
            'description' => 'Self check-in with photo proof and duty schedules',
        ]);

        // Seed Dojang
        $this->dojang = Dojang::create([
            'name' => 'Dojang Eagle Pusat GOR Pajajaran',
            'code' => 'DOJ-GOR-01',
            'address' => 'Jl. Pemuda No. 4, Bogor',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // Seed Users
        $this->superadmin = User::create([
            'role_id' => $this->superadminRole->id,
            'name' => 'Grand Master Bambang',
            'email' => 'superadmin@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '9999',
            'rate_per_session' => 0.00,
            'is_active' => true,
        ]);

        $this->pelatihUtama = User::create([
            'role_id' => $this->adminRole->id,
            'dojang_id' => $this->dojang->id,
            'name' => 'Sabeum Dimas Santoso',
            'email' => 'pelatih.utama@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '8888',
            'belt_level' => 'Dan IV Kukkiwon',
            'rate_per_session' => 100000.00,
            'is_active' => true,
        ]);

        $this->asisten1 = User::create([
            'role_id' => $this->assistantRole->id,
            'dojang_id' => $this->dojang->id,
            'name' => 'Sabeum Bryan Pratama',
            'email' => 'asisten1@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '1111',
            'belt_level' => 'Dan II Kukkiwon',
            'rate_per_session' => 60000.00,
            'bank_name' => 'BCA',
            'bank_account_number' => '8830192831',
            'bank_account_holder' => 'Bryan Pratama',
            'is_active' => true,
        ]);

        $this->asisten2 = User::create([
            'role_id' => $this->assistantRole->id,
            'dojang_id' => $this->dojang->id,
            'name' => 'Sabeum Kevin Sanjaya',
            'email' => 'asisten2@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '2222',
            'belt_level' => 'Dan I Kukkiwon',
            'rate_per_session' => 50000.00,
            'bank_name' => 'Mandiri',
            'bank_account_number' => '133009988776',
            'bank_account_holder' => 'Kevin Sanjaya',
            'is_active' => true,
        ]);
    }

    public function test_api_login_returns_token_and_user_profile(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'asisten1@eagletkd.com',
            'password' => 'password',
            'device_name' => 'Flutter Test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role', 'role_display'],
                ],
            ]);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->asisten1->id,
            'action' => 'LOGIN',
        ]);
    }

    public function test_api_login_with_pin_returns_token_and_user_profile(): void
    {
        $response = $this->postJson('/api/v1/login-pin', [
            'pin' => '1111',
            'user_id' => $this->asisten1->id,
            'device_name' => 'Flutter PIN Test',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Autentikasi PIN berhasil.',
            ])
            ->assertJsonPath('data.user.id', $this->asisten1->id);

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->asisten1->id,
            'action' => 'LOGIN_PIN',
        ]);
    }

    public function test_attendance_checkin_direct_save_without_verification_and_no_gps(): void
    {
        $token = $this->asisten1->createToken('test')->plainTextToken;
        $fakePhoto = UploadedFile::fake()->create('attendance_proof.jpg', 100, 'image/jpeg');

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/attendances/check-in', [
                'status' => 'Hadir',
                'notes' => 'Hadir tepat waktu di Dojang Pusat',
                'photo' => $fakePhoto,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonPath('data.attendance.user_id', $this->asisten1->id)
            ->assertJsonPath('data.attendance.status', 'Hadir');

        // Verify attendance directly saved in database
        $this->assertDatabaseHas('attendances', [
            'user_id' => $this->asisten1->id,
            'status' => 'Hadir',
            'session_rate' => 60000.00,
        ]);

        // Verify photo record was created
        $this->assertDatabaseHas('attendance_photos', [
            'attendance_id' => $response->json('data.attendance.id'),
        ]);
    }

    public function test_assistant_cannot_checkin_twice_on_same_day_without_schedule(): void
    {
        $token = $this->asisten1->createToken('test')->plainTextToken;

        // First check-in
        $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/attendances/check-in', [
                'status' => 'Hadir',
            ])->assertStatus(201);

        // Second check-in on the same day without schedule should be rejected
        $secondResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/attendances/check-in', [
                'status' => 'Hadir',
            ]);

        $secondResponse->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Anda sudah melakukan absensi hari ini.',
            ]);
    }

    public function test_admin_can_create_schedule_with_multi_coach_duty_assignments(): void
    {
        $token = $this->pelatihUtama->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/schedules', [
                'activity_name' => 'Latihan Intensif Sabuk Putih - Kuning',
                'date' => Carbon::tomorrow()->format('Y-m-d'),
                'start_time' => '16:00:00',
                'end_time' => '18:00:00',
                'location' => 'Dojang Pusat GOR Pajajaran',
                'description' => 'Fokus dasar tendangan dan poomsae',
                'shift_name' => 'Piket Sesi Sore',
                'duty_notes' => 'Pimpin pemanasan dan kyorugi',
                'assigned_coaches' => [
                    [
                        'user_id' => $this->asisten1->id,
                        'task_role' => 'Instruktur Utama',
                        'training_material' => 'Poomsae Taegeuk 1',
                    ],
                    [
                        'user_id' => $this->asisten2->id,
                        'task_role' => 'Asisten Pemanasan',
                        'training_material' => 'Pemanasan & Kicking Drills',
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
            ]);

        $this->assertDatabaseHas('training_schedules', [
            'activity_name' => 'Latihan Intensif Sabuk Putih - Kuning',
            'location' => 'Dojang Pusat GOR Pajajaran',
        ]);

        $this->assertDatabaseHas('duty_assignments', [
            'user_id' => $this->asisten1->id,
            'role_task' => 'Instruktur Utama',
        ]);

        $this->assertDatabaseHas('duty_assignments', [
            'user_id' => $this->asisten2->id,
            'role_task' => 'Asisten Pemanasan',
        ]);
    }

    public function test_assistant_can_fetch_my_duty_assignments(): void
    {
        // Create schedule and assign asisten1
        $schedule = TrainingSchedule::create([
            'activity_name' => 'Latihan Poomsae Lanjutan',
            'date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '16:00:00',
            'end_time' => '18:00:00',
            'location' => 'Dojang Pusat',
            'created_by' => $this->pelatihUtama->id,
        ]);

        $dutySchedule = DutySchedule::create([
            'training_schedule_id' => $schedule->id,
            'shift_name' => 'Piket Pagi',
            'created_by' => $this->pelatihUtama->id,
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $dutySchedule->id,
            'user_id' => $this->asisten1->id,
            'role_task' => 'Instruktur Poomsae',
            'training_materials' => 'Taegeuk 4 Jang',
            'status' => 'assigned',
        ]);

        $token = $this->asisten1->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/my-duty-assignments');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonFragment([
                'role_task' => 'Instruktur Poomsae',
                'training_materials' => 'Taegeuk 4 Jang',
            ]);
    }

    public function test_admin_and_superadmin_can_correct_attendance(): void
    {
        $attendance = Attendance::create([
            'user_id' => $this->asisten1->id,
            'date' => Carbon::yesterday()->format('Y-m-d'),
            'time' => '16:05:00',
            'status' => 'Izin',
            'session_rate' => 0.00,
            'notes' => 'Izin ada ujian kampus',
        ]);

        $token = $this->pelatihUtama->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson("/api/v1/attendances/{$attendance->id}", [
                'status' => 'Hadir',
                'notes' => 'Dikoreksi hadir oleh pelatih utama',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Data absensi berhasil dikoreksi.',
            ]);

        $this->assertDatabaseHas('attendances', [
            'id' => $attendance->id,
            'status' => 'Hadir',
            'session_rate' => 60000.00, // Updated to user's session rate
        ]);
    }

    public function test_reports_api_summary_and_coach_breakdown(): void
    {
        $date = Carbon::now()->format('Y-m-d');

        // Asisten 1: 2x Hadir
        Attendance::create([
            'user_id' => $this->asisten1->id,
            'date' => $date,
            'time' => '16:00:00',
            'status' => 'Hadir',
            'session_rate' => 60000.00,
        ]);

        // Asisten 2: 1x Izin
        Attendance::create([
            'user_id' => $this->asisten2->id,
            'date' => $date,
            'time' => '16:00:00',
            'status' => 'Izin',
            'session_rate' => 0.00,
        ]);

        $token = $this->superadmin->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/reports');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure([
                'data' => [
                    'total_hadir',
                    'total_izin',
                    'total_sakit',
                    'total_alpa',
                    'total_duties_overall',
                    'coaches',
                ],
            ]);
    }

    public function test_salary_calculation_service_calculates_monthly_pay(): void
    {
        $now = Carbon::now();

        // 3x Hadir = 3 x 60.000 = 180.000
        for ($i = 1; $i <= 3; $i++) {
            Attendance::create([
                'user_id' => $this->asisten1->id,
                'date' => $now->copy()->subDays($i)->format('Y-m-d'),
                'time' => '16:00:00',
                'status' => 'Hadir',
                'session_rate' => 60000.00,
            ]);
        }

        $service = new SalaryCalculationService();
        $summary = $service->calculateMonthlySummary($this->asisten1, $now->month, $now->year);

        $this->assertEquals(3, $summary['attendance_count']);
        $this->assertEquals(180000.00, $summary['total_salary']);
    }

    public function test_report_export_excel_csv_download(): void
    {
        $token = $this->pelatihUtama->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/v1/reports/export/excel');

        $response->assertStatus(200);
        $this->assertTrue(str_contains($response->headers->get('content-type') ?? '', 'text/csv'));
    }
}
