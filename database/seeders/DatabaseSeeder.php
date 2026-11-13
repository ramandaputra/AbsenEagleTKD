<?php

namespace Database\Seeders;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\AttendancePhoto;
use App\Models\Dojang;
use App\Models\DutyAssignment;
use App\Models\DutySchedule;
use App\Models\Payroll;
use App\Models\Role;
use App\Models\TrainingSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $superadminRole = Role::create([
            'name' => 'superadmin',
            'display_name' => 'Superadmin',
            'description' => 'Akses penuh seluruh sistem, pengguna, jadwal, laporan, dan log aktivitas.',
        ]);

        $adminRole = Role::create([
            'name' => 'admin',
            'display_name' => 'Admin / Pelatih',
            'description' => 'Mengelola jadwal latihan, jadwal piket mengajar, koreksi absensi, dan unduh laporan.',
        ]);

        $asistenRole = Role::create([
            'name' => 'asisten_pelatih',
            'display_name' => 'User / Asisten Pelatih',
            'description' => 'Absensi mandiri berfoto, melihat jadwal latihan, melihat jadwal piket mengajar, dan profil.',
        ]);

        // 2. Seed Dojang (Lokasi Latihan)
        $dojangPusat = Dojang::create([
            'name' => 'Dojang Eagle Pusat GOR Pajajaran',
            'code' => 'DOJ-GOR-01',
            'address' => 'Jl. Pemuda No. 4, Tanah Sareal, Kota Bogor',
            'phone' => '0812-3456-7890',
            'is_active' => true,
        ]);

        $dojangSukasari = Dojang::create([
            'name' => 'Dojang Eagle Unit Sukasari',
            'code' => 'DOJ-SUKA-02',
            'address' => 'Jl. Siliwangi No. 120, Kota Bogor',
            'phone' => '0812-9876-5432',
            'is_active' => true,
        ]);

        // 3. Seed Users with Roles
        $superadmin = User::create([
            'name' => 'Grand Master Bambang Soedjarwo',
            'email' => 'superadmin@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '9999',
            'role_id' => $superadminRole->id,
            'phone' => '0812-1111-2222',
            'belt_level' => 'Dan 7 Kukkiwon',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 0.00,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Bambang Soedjarwo',
            'is_active' => true,
        ]);

        $pelatihUtama = User::create([
            'name' => 'Sabeum Nim Dimas Wicaksono',
            'email' => 'pelatih.utama@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '8888',
            'role_id' => $adminRole->id,
            'phone' => '0813-2222-3333',
            'belt_level' => 'Dan 4 Kukkiwon',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 100000.00,
            'bank_name' => 'BCA',
            'bank_account_number' => '8820192831',
            'bank_account_holder' => 'Dimas Wicaksono',
            'is_active' => true,
        ]);

        $asisten1 = User::create([
            'name' => 'Sabeum Bryan Pratama',
            'email' => 'asisten1@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '1111',
            'role_id' => $asistenRole->id,
            'phone' => '0857-3333-4444',
            'belt_level' => 'Dan 2 Kukkiwon',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 60000.00,
            'bank_name' => 'BCA',
            'bank_account_number' => '8830192831',
            'bank_account_holder' => 'Bryan Pratama',
            'is_active' => true,
        ]);

        $asisten2 = User::create([
            'name' => 'Sabeum Siti Rahmawati',
            'email' => 'asisten2@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '2222',
            'role_id' => $asistenRole->id,
            'phone' => '0878-4444-5555',
            'belt_level' => 'Dan 1 Kukkiwon',
            'dojang_id' => $dojangSukasari->id,
            'rate_per_session' => 50000.00,
            'bank_name' => 'Mandiri',
            'bank_account_number' => '133002910283',
            'bank_account_holder' => 'Siti Rahmawati',
            'is_active' => true,
        ]);

        $asisten3 = User::create([
            'name' => 'Sabeum Kevin Sanjaya',
            'email' => 'asisten3@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '3333',
            'role_id' => $asistenRole->id,
            'phone' => '0896-5555-6666',
            'belt_level' => 'Geup 1 (Merah Strip)',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 45000.00,
            'bank_name' => 'BRI',
            'bank_account_number' => '0129381928310',
            'bank_account_holder' => 'Kevin Sanjaya',
            'is_active' => true,
        ]);

        // 3b. Seed Requested Named Users (PIN 1234)
        $mingle = User::create([
            'name' => 'mingle',
            'email' => 'mingle@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '1234',
            'role_id' => $superadminRole->id,
            'phone' => '0812-0001-0001',
            'belt_level' => 'Dan Kukkiwon',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 0.00,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_holder' => null,
            'is_active' => true,
        ]);

        $lukman = User::create([
            'name' => 'Lukman',
            'email' => 'lukman@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '1234',
            'role_id' => $adminRole->id,
            'phone' => '0812-0002-0002',
            'belt_level' => 'Dan Kukkiwon',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 100000.00,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_holder' => null,
            'is_active' => true,
        ]);

        $raja = User::create([
            'name' => 'Raja',
            'email' => 'raja@eagletkd.com',
            'password' => Hash::make('password'),
            'pin' => '1234',
            'role_id' => $asistenRole->id,
            'phone' => '0812-0003-0003',
            'belt_level' => 'Dan Kukkiwon',
            'dojang_id' => $dojangPusat->id,
            'rate_per_session' => 60000.00,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_holder' => null,
            'is_active' => true,
        ]);

        // 4. Seed Training Schedules (Jadwal Latihan) & Duty Schedules (Jadwal Piket Mengajar)
        $today = Carbon::today();

        // Schedule 1: Today's training
        $sched1 = TrainingSchedule::create([
            'activity_name' => 'Kelas Reguler (Poomsae & Kyorugi Dasar)',
            'date' => $today->format('Y-m-d'),
            'start_time' => '16:00:00',
            'end_time' => '18:00:00',
            'location' => 'Dojang Eagle Pusat GOR Pajajaran',
            'description' => 'Fokus materi kuda-kuda ap koobi, tendangan ap chagi, dan poomsae taegeuk 1-3.',
            'status' => 'scheduled',
            'created_by' => $pelatihUtama->id,
        ]);

        $duty1 = DutySchedule::create([
            'training_schedule_id' => $sched1->id,
            'shift_name' => 'Piket Mengajar Kelas Sore',
            'notes' => 'Pembagian tugas instruktur teknik dan fisik.',
            'created_by' => $pelatihUtama->id,
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty1->id,
            'user_id' => $asisten1->id,
            'role_task' => 'Instruktur Utama Poomsae',
            'training_materials' => 'Koreksi gerakan Poomsae Taegeuk 1 - 3',
            'status' => 'assigned',
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty1->id,
            'user_id' => $asisten2->id,
            'role_task' => 'Pelatih Fisik & Pemanasan',
            'training_materials' => 'Peregangan dinamis dan drill koordinasi kaki',
            'status' => 'assigned',
        ]);

        // Schedule 2: Upcoming in 2 days
        $sched2 = TrainingSchedule::create([
            'activity_name' => 'Kelas Prestasi Atlet Kyorugi (Sparring)',
            'date' => $today->copy()->addDays(2)->format('Y-m-d'),
            'start_time' => '18:30:00',
            'end_time' => '20:30:00',
            'location' => 'Dojang Eagle Pusat GOR Pajajaran',
            'description' => 'Latihan intensif sparring sensor, strategi counter dwi chagi, dan stamina atlet.',
            'status' => 'scheduled',
            'created_by' => $pelatihUtama->id,
        ]);

        $duty2 = DutySchedule::create([
            'training_schedule_id' => $sched2->id,
            'shift_name' => 'Piket Mengajar Kelas Prestasi',
            'notes' => 'Wajib membawa pelindung dada dan sasaran target pad.',
            'created_by' => $pelatihUtama->id,
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty2->id,
            'user_id' => $asisten1->id,
            'role_task' => 'Pegang Target & Drill Sparring',
            'training_materials' => 'Drill kecepatan counter dolyo chagi dan dwi chagi',
            'status' => 'assigned',
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty2->id,
            'user_id' => $asisten3->id,
            'role_task' => 'Pencatat Waktu & Kondisi Fisik',
            'training_materials' => 'Interval training 3 ronde x 2 menit',
            'status' => 'assigned',
        ]);

        // Schedule 3: Weekend Joint Training
        $sched3 = TrainingSchedule::create([
            'activity_name' => 'Latihan Gabungan Seluruh Unit & Ujian Kenaikan Tingkat',
            'date' => $today->copy()->addDays(4)->format('Y-m-d'),
            'start_time' => '07:30:00',
            'end_time' => '10:30:00',
            'location' => 'GOR Pajajaran Lapangan Indoor',
            'description' => 'Latihan akbar seluruh sabuk putih hingga merah strip hitam.',
            'status' => 'scheduled',
            'created_by' => $superadmin->id,
        ]);

        $duty3 = DutySchedule::create([
            'training_schedule_id' => $sched3->id,
            'shift_name' => 'Piket Mengajar Massal Akhir Pekan',
            'notes' => 'Seluruh asisten pelatih bertugas mendampingi regu masing-masing.',
            'created_by' => $superadmin->id,
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty3->id,
            'user_id' => $asisten1->id,
            'role_task' => 'Koordinator Sabuk Biru-Merah',
            'training_materials' => 'Poomsae Taegeuk 4-6 dan Kyukpa pemecahan papan',
            'status' => 'assigned',
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty3->id,
            'user_id' => $asisten2->id,
            'role_task' => 'Koordinator Sabuk Kuning-Hijau',
            'training_materials' => 'Poomsae Taegeuk 1-3 dan dasar pukulan',
            'status' => 'assigned',
        ]);

        DutyAssignment::create([
            'duty_schedule_id' => $duty3->id,
            'user_id' => $asisten3->id,
            'role_task' => 'Koordinator Sabuk Putih (Pemula)',
            'training_materials' => 'Pengenalan disiplin dojang, etika, dan kuda-kuda dasar',
            'status' => 'assigned',
        ]);

        // 5. Seed Past Attendances & Attendance Photos (without GPS requirement, instant save)
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // Bryan attendances (10 Hadir, 1 Izin, 1 Sakit)
        for ($i = 1; $i <= 10; $i++) {
            $pastDate = Carbon::create($currentYear, $currentMonth, max(1, $today->day - $i * 2));
            if ($pastDate->gt($today)) {
                continue;
            }

            $att = Attendance::create([
                'user_id' => $asisten1->id,
                'training_schedule_id' => $sched1->id,
                'date' => $pastDate->format('Y-m-d'),
                'time' => '15:55:00',
                'status' => 'Hadir',
                'notes' => 'Melatih sesi latihan reguler sore dengan lancar.',
                'session_rate' => $asisten1->rate_per_session,
            ]);

            AttendancePhoto::create([
                'attendance_id' => $att->id,
                'photo_path' => 'uploads/attendances/demo_bryan_'.$i.'.jpg',
                'file_name' => 'demo_bryan_'.$i.'.jpg',
                'file_size' => 102400,
            ]);
        }

        // Bryan 1 Izin
        Attendance::create([
            'user_id' => $asisten1->id,
            'training_schedule_id' => $sched1->id,
            'date' => Carbon::create($currentYear, $currentMonth, max(1, $today->day - 1))->format('Y-m-d'),
            'time' => '08:30:00',
            'status' => 'Izin',
            'notes' => 'Izin tidak dapat mengajar karena ada ujian kuliah di kampus.',
            'session_rate' => $asisten1->rate_per_session,
        ]);

        // Siti attendances (8 Hadir)
        for ($i = 1; $i <= 8; $i++) {
            $pastDate = Carbon::create($currentYear, $currentMonth, max(1, $today->day - $i * 3));
            if ($pastDate->gt($today)) {
                continue;
            }

            $att = Attendance::create([
                'user_id' => $asisten2->id,
                'training_schedule_id' => $sched1->id,
                'date' => $pastDate->format('Y-m-d'),
                'time' => '15:50:00',
                'status' => 'Hadir',
                'notes' => 'Melatih teknik tendangan dasar dan fisik.',
                'session_rate' => $asisten2->rate_per_session,
            ]);

            AttendancePhoto::create([
                'attendance_id' => $att->id,
                'photo_path' => 'uploads/attendances/demo_siti_'.$i.'.jpg',
                'file_name' => 'demo_siti_'.$i.'.jpg',
                'file_size' => 98500,
            ]);
        }

        // Kevin attendances (5 Hadir, 1 Sakit)
        for ($i = 1; $i <= 5; $i++) {
            $pastDate = Carbon::create($currentYear, $currentMonth, max(1, $today->day - $i * 4));
            if ($pastDate->gt($today)) {
                continue;
            }

            $att = Attendance::create([
                'user_id' => $asisten3->id,
                'training_schedule_id' => $sched1->id,
                'date' => $pastDate->format('Y-m-d'),
                'time' => '16:05:00',
                'status' => 'Hadir',
                'notes' => 'Mendampingi Sabeum Bryan melatih sabuk putih.',
                'session_rate' => $asisten3->rate_per_session,
            ]);

            AttendancePhoto::create([
                'attendance_id' => $att->id,
                'photo_path' => 'uploads/attendances/demo_kevin_'.$i.'.jpg',
                'file_name' => 'demo_kevin_'.$i.'.jpg',
                'file_size' => 110200,
            ]);
        }

        Attendance::create([
            'user_id' => $asisten3->id,
            'training_schedule_id' => $sched1->id,
            'date' => Carbon::create($currentYear, $currentMonth, max(1, $today->day - 2))->format('Y-m-d'),
            'time' => '14:00:00',
            'status' => 'Sakit',
            'notes' => 'Sakit demam flu, beristirahat sesuai anjuran dokter.',
            'session_rate' => $asisten3->rate_per_session,
        ]);

        // 6. Seed Activity Logs
        ActivityLog::create([
            'user_id' => $superadmin->id,
            'action' => 'LOGIN',
            'description' => 'Superadmin masuk ke sistem dari perangkat mobile.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)',
        ]);

        ActivityLog::create([
            'user_id' => $pelatihUtama->id,
            'action' => 'CREATE_SCHEDULE',
            'description' => 'Admin membuat jadwal latihan baru: Kelas Reguler (Poomsae & Kyorugi Dasar).',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Dart/3.0 (dart:io) Flutter Android',
        ]);

        ActivityLog::create([
            'user_id' => $pelatihUtama->id,
            'action' => 'ASSIGN_DUTY',
            'description' => 'Admin menetapkan jadwal piket mengajar untuk Sabeum Bryan Pratama & Sabeum Siti.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Dart/3.0 (dart:io) Flutter Android',
        ]);

        ActivityLog::create([
            'user_id' => $asisten1->id,
            'action' => 'CHECK_IN',
            'description' => 'Asisten Pelatih Sabeum Bryan Pratama melakukan absensi mandiri Hadir dengan bukti foto.',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Dart/3.0 (dart:io) Flutter iOS',
        ]);

        // 7. Seed Previous Month Payroll Records
        $prevMonth = $today->copy()->subMonth();
        Payroll::create([
            'user_id' => $asisten1->id,
            'period_month' => $prevMonth->month,
            'period_year' => $prevMonth->year,
            'total_sessions_attended' => 12,
            'rate_per_session' => 60000.00,
            'base_salary' => 720000.00,
            'bonus_allowance' => 50000.00,
            'deductions' => 0.00,
            'total_salary' => 770000.00,
            'payment_status' => PaymentStatus::Paid,
            'payment_method' => PaymentMethod::Transfer,
            'payment_date' => $today->format('Y-m-d'),
            'payment_notes' => 'Transfer via BCA. Bonus kehadiran disiplin.',
            'approved_by' => $superadmin->id,
            'approved_at' => now(),
        ]);
    }
}
