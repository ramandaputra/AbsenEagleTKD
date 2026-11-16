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
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class BlackBoxIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected Role $roleSuperadmin;
    protected Role $roleAdmin;
    protected Role $roleAsisten;
    protected Dojang $dojangPusat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->roleSuperadmin = Role::firstOrCreate(['name' => 'superadmin'], ['display_name' => 'Superadmin']);
        $this->roleAdmin = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Pelatih Utama']);
        $this->roleAsisten = Role::firstOrCreate(['name' => 'asisten_pelatih'], ['display_name' => 'Asisten Pelatih']);

        $this->dojangPusat = Dojang::firstOrCreate(
            ['code' => 'DJ-PST'],
            [
                'name' => 'Dojang Eagle Pusat',
                'address' => 'Jl. Mt Haryono Km.4 Tanjungpinang',
                'is_active' => true,
            ]
        );
    }

    /**
     * Test BB-01: Authentication, input validation, and security boundaries.
     */
    public function test_blackbox_authentication_and_security_boundary(): void
    {
        $coach = User::create([
            'name' => 'Pelatih Blackbox',
            'email' => 'bb_coach@eagletkd.com',
            'password' => Hash::make('secret123'),
            'role_id' => $this->roleAsisten->id,
            'pin' => '9876',
            'is_active' => true,
        ]);

        // Input A: Valid Name + Valid PIN via AJAX
        $validResponse = $this->postJson('/login-pin', [
            'name' => 'Pelatih Blackbox',
            'pin' => '9876',
        ]);
        $validResponse->assertStatus(200);
        $validResponse->assertJson(['success' => true]);
        $this->assertAuthenticatedAs($coach);

        $this->post('/logout');
        $this->assertGuest();

        // Input B: Valid Name + Wrong PIN
        $wrongPinResponse = $this->postJson('/login-pin', [
            'name' => 'Pelatih Blackbox',
            'pin' => '0000',
        ]);
        $wrongPinResponse->assertStatus(422);
        $wrongPinResponse->assertJson(['success' => false]);
        $this->assertGuest();

        // Input C: Non-existent user
        $unknownUserResponse = $this->postJson('/login-pin', [
            'name' => 'Ghost User',
            'pin' => '9876',
        ]);
        $unknownUserResponse->assertStatus(422);
        $this->assertGuest();

        // Input D: Security check - Verify login view does not leak usernames/roles or suggestions
        $loginPage = $this->get('/login');
        $loginPage->assertStatus(200);
        $loginPage->assertDontSee('selectSuggestion');
        $loginPage->assertDontSee('suggestions');
        $loginPage->assertDontSee('Gunakan nama:');
    }

    /**
     * Test BB-02: Role-Based Access Control (RBAC) boundaries.
     */
    public function test_blackbox_rbac_access_control_across_roles(): void
    {
        $asisten = User::create([
            'name' => 'Asisten RBAC',
            'email' => 'asisten_rbac@eagletkd.com',
            'password' => Hash::make('password'),
            'role_id' => $this->roleAsisten->id,
            'pin' => '1111',
            'is_active' => true,
        ]);

        $superadmin = User::create([
            'name' => 'Superadmin RBAC',
            'email' => 'super_rbac@eagletkd.com',
            'password' => Hash::make('password'),
            'role_id' => $this->roleSuperadmin->id,
            'pin' => '9999',
            'is_active' => true,
        ]);

        // 1. Unauthenticated guest trying to access protected paths
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/users')->assertRedirect('/login');
        $this->get('/activity-logs')->assertRedirect('/login');
        $this->get('/admin/salary')->assertRedirect('/login');

        // 2. Asisten Pelatih trying to access admin & superadmin paths (Should receive 403 Forbidden)
        $this->actingAs($asisten)->get('/users')->assertForbidden();
        $this->actingAs($asisten)->get('/activity-logs')->assertForbidden();
        $this->actingAs($asisten)->get('/admin/salary')->assertForbidden();
        $this->actingAs($asisten)->get('/reports')->assertForbidden();

        // 3. Superadmin accessing administrative tools
        $this->actingAs($superadmin)->get('/users')->assertOk();
        $this->actingAs($superadmin)->get('/activity-logs')->assertOk();
        $this->actingAs($superadmin)->get('/admin/salary')->assertOk();
        $this->actingAs($superadmin)->get('/reports')->assertOk();

        // 4. Superadmin footer navigation should only show Dashboard and Akun Saya
        $superDash = $this->actingAs($superadmin)->get('/dashboard');
        $superDash->assertOk();
        $superDash->assertSee('Dashboard');
        $superDash->assertSee('Akun Saya');
        $superDash->assertDontSee('Check-in');
    }

    /**
     * Test BB-03: End-to-end integration: User creation -> Schedule -> Attendance -> Salary -> Reports.
     */
    public function test_blackbox_full_lifecycle_data_integration(): void
    {
        $superadmin = User::create([
            'name' => 'Sabeum Lukman Test',
            'email' => 'lukman_super@eagletkd.com',
            'password' => Hash::make('password'),
            'role_id' => $this->roleSuperadmin->id,
            'pin' => '123456',
            'belt_level' => 'DAN II Kukkiwon',
            'dojang_id' => $this->dojangPusat->id,
            'is_active' => true,
        ]);

        // 1. Superadmin adds a new Assistant Coach via POST /users
        $createUserResponse = $this->actingAs($superadmin)->post('/users', [
            'name' => 'Ahmad Asisten Baru',
            'email' => 'ahmad_asisten@eagletkd.com',
            'password' => 'pass1234',
            'pin' => '4321',
            'role' => 'asisten_pelatih',
            'belt_level' => 'DAN I Kukkiwon',
            'dojang_id' => $this->dojangPusat->id,
            'rate_per_session' => 65000,
            'phone' => '081298765432',
        ]);
        $createUserResponse->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'ahmad_asisten@eagletkd.com',
            'name' => 'Ahmad Asisten Baru',
            'rate_per_session' => 65000,
        ]);

        $newCoach = User::where('email', 'ahmad_asisten@eagletkd.com')->first();
        $this->assertNotNull($newCoach);

        // Verify activity log audit trail for user creation
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'CREATE_USER',
            'user_id' => $superadmin->id,
        ]);

        // 2. Superadmin creates a training schedule and assigns the new coach
        $scheduleDate = Carbon::now()->format('Y-m-d');
        $createScheduleResponse = $this->actingAs($superadmin)->post('/schedules', [
            'date' => $scheduleDate,
            'activity_name' => 'Latihan Poomsae Dasar & Kyorugi',
            'description' => 'Materi teknik tendangan dasar dan poomsae 1',
            'coaches' => [$newCoach->id],
        ]);
        $createScheduleResponse->assertRedirect();

        $this->assertDatabaseHas('training_schedules', [
            'activity_name' => 'Latihan Poomsae Dasar & Kyorugi',
        ]);

        $schedule = TrainingSchedule::where('activity_name', 'Latihan Poomsae Dasar & Kyorugi')->first();
        $this->assertNotNull($schedule);

        // Verify schedule assignment linkage
        $dutySchedule = DutySchedule::where('training_schedule_id', $schedule->id)->first();
        $this->assertNotNull($dutySchedule);
        $this->assertDatabaseHas('duty_assignments', [
            'duty_schedule_id' => $dutySchedule->id,
            'user_id' => $newCoach->id,
        ]);

        // Verify activity log audit trail for schedule creation
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'CREATE_SCHEDULE',
            'user_id' => $superadmin->id,
        ]);

        // 3. New Coach checks in for attendance with photo proof
        $checkInResponse = $this->actingAs($newCoach)->post('/attendances/check-in', [
            'training_schedule_id' => $schedule->id,
            'status' => 'Hadir',
            'notes' => 'Hadir tepat waktu dan mendampingi materi.',
            'photo_base64' => 'data:image/jpeg;base64,' . base64_encode('fake-test-image-content'),
        ]);
        $checkInResponse->assertRedirect();

        $this->assertDatabaseHas('attendances', [
            'user_id' => $newCoach->id,
            'training_schedule_id' => $schedule->id,
            'status' => 'Hadir',
            'session_rate' => 65000,
        ]);

        // Verify activity log audit trail for check-in
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'CHECK_IN',
            'user_id' => $newCoach->id,
        ]);

        // 4. Verify Superadmin Dashboard reflects the attendance in Tab Presensi
        $dashboardResponse = $this->actingAs($superadmin)->get('/dashboard');
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('Ahmad Asisten Baru');
        $dashboardResponse->assertSee('Latihan Poomsae Dasar & Kyorugi');
        $dashboardResponse->assertSee('Hadir');

        // 5. Verify Rekap Gaji & Atur Tarif incorporates the new coach and calculates honor dynamically
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $salaryPageResponse = $this->actingAs($superadmin)->get("/admin/salary?month={$currentMonth}&year={$currentYear}");
        $salaryPageResponse->assertOk();
        $salaryPageResponse->assertSee('Ahmad Asisten Baru');
        $salaryPageResponse->assertSee('65.000');

        // 6. Superadmin updates the coach's per-session rate
        $updateRateResponse = $this->actingAs($superadmin)->put("/admin/coaches/{$newCoach->id}/rate", [
            'coach_id' => $newCoach->id,
            'rate_per_session' => 80000,
            'month' => $currentMonth,
            'year' => $currentYear,
        ]);
        $updateRateResponse->assertRedirect();

        $newCoach->refresh();
        $this->assertEquals(80000, (int) $newCoach->rate_per_session);

        // Verify activity log audit trail for rate update
        $this->assertDatabaseHas('activity_logs', [
            'action' => 'UPDATE_RATE',
            'user_id' => $superadmin->id,
        ]);

        // 7. Verify Reports page & PDF Export integration
        $reportPageResponse = $this->actingAs($superadmin)->get('/reports');
        $reportPageResponse->assertOk();
        $reportPageResponse->assertSee('Ahmad Asisten Baru');
        $reportPageResponse->assertSee('Cetak PDF');
        $reportPageResponse->assertDontSee('Unduh Excel');

        $pdfExportResponse = $this->actingAs($superadmin)->get('/reports/export/pdf');
        $pdfExportResponse->assertOk();
        $this->assertStringContainsString('application/pdf', (string) $pdfExportResponse->headers->get('Content-Type'));

        // 8. Superadmin reviews Activity Logs
        $logsPageResponse = $this->actingAs($superadmin)->get('/activity-logs');
        $logsPageResponse->assertOk();
        $logsPageResponse->assertSee('CREATE_USER');
        $logsPageResponse->assertSee('CREATE_SCHEDULE');
        $logsPageResponse->assertSee('CHECK_IN');
        $logsPageResponse->assertSee('UPDATE_RATE');
    }

    /**
     * Test BB-04: Negative test scenarios, boundaries, and validation integrity.
     */
    public function test_blackbox_boundary_and_validation_integrity(): void
    {
        $superadmin = User::create([
            'name' => 'Validator Superadmin',
            'email' => 'validator@eagletkd.com',
            'password' => Hash::make('password'),
            'role_id' => $this->roleSuperadmin->id,
            'pin' => '1234',
            'is_active' => true,
        ]);

        // Validation A: Duplicate email for user creation
        $dupEmailResponse = $this->actingAs($superadmin)->post('/users', [
            'name' => 'Cloned Email User',
            'email' => 'validator@eagletkd.com', // Duplicate
            'password' => 'secret123',
            'role' => 'asisten_pelatih',
        ]);
        $dupEmailResponse->assertSessionHasErrors('email');

        // Validation B: Creating schedule with empty coaches array
        $emptyCoachesResponse = $this->actingAs($superadmin)->post('/schedules', [
            'date' => Carbon::now()->format('Y-m-d'),
            'activity_name' => 'Latihan Tanpa Pelatih',
            'coaches' => [],
        ]);
        $emptyCoachesResponse->assertSessionHasErrors('coaches');

        // Validation C: Rate per session cannot be negative
        $negativeRateResponse = $this->actingAs($superadmin)->put("/admin/coaches/{$superadmin->id}/rate", [
            'coach_id' => $superadmin->id,
            'rate_per_session' => -5000,
        ]);
        $negativeRateResponse->assertForbidden(); // Superadmin is not an assistant coach

        // Validation D: Deleting own account is prohibited
        $deleteSelfResponse = $this->actingAs($superadmin)->delete("/users/{$superadmin->id}");
        $deleteSelfResponse->assertSessionHasErrors('error');
        $this->assertDatabaseHas('users', ['id' => $superadmin->id]);
    }
}
