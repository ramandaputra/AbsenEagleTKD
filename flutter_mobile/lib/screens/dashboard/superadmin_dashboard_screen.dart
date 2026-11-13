import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/attendance_provider.dart';
import '../../providers/schedule_provider.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/photo_preview_dialog.dart';
import '../schedule/create_schedule_screen.dart';
import '../attendance/attendance_history_screen.dart';

class SuperadminDashboardScreen extends StatefulWidget {
  final VoidCallback onNavigateToSchedule;
  final VoidCallback onNavigateToReports;

  const SuperadminDashboardScreen({
    Key? key,
    required this.onNavigateToSchedule,
    required this.onNavigateToReports,
  }) : super(key: key);

  @override
  State<SuperadminDashboardScreen> createState() => _SuperadminDashboardScreenState();
}

class _SuperadminDashboardScreenState extends State<SuperadminDashboardScreen> {
  final currencyFormatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  Future<void> _loadData() async {
    final attProvider = Provider.of<AttendanceProvider>(context, listen: false);
    final schedProvider = Provider.of<ScheduleProvider>(context, listen: false);

    await Future.wait([
      attProvider.fetchAttendances(),
      schedProvider.fetchSchedules(),
    ]);
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final attProvider = Provider.of<AttendanceProvider>(context);
    final schedProvider = Provider.of<ScheduleProvider>(context);

    final user = auth.user;
    final attendances = attProvider.attendances;
    final schedules = schedProvider.schedules;

    final totalHadir = attendances.where((a) => a.status.toLowerCase() == 'hadir').length;
    final totalIzin = attendances.where((a) => a.status.toLowerCase() == 'izin').length;
    final totalSakit = attendances.where((a) => a.status.toLowerCase() == 'sakit').length;
    final totalAlpa = attendances.where((a) => a.status.toLowerCase() == 'alpa').length;

    // Calculate total salary sum from Hadir attendances
    final totalHonor = attendances
        .where((a) => a.status.toLowerCase() == 'hadir')
        .fold<double>(0, (sum, a) => sum + a.sessionRate);

    return Scaffold(
      backgroundColor: AppColors.background,
      body: RefreshIndicator(
        onRefresh: _loadData,
        color: AppColors.primary,
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [AppColors.surface, AppColors.surfaceLight],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: AppColors.border),
                ),
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 28,
                      backgroundColor: AppColors.primary.withOpacity(0.2),
                      child: const Icon(Icons.admin_panel_settings_rounded, color: AppColors.primary, size: 30),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Grandmaster ${user?.name ?? "Superadmin"}',
                            style: const TextStyle(
                              color: AppColors.textPrimary,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Klub Taekwondo Eagle • Pusat Kontrol Penuh',
                            style: TextStyle(color: AppColors.textSecondary, fontSize: 13),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.primary.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'Hak Akses: Superadmin',
                              style: TextStyle(color: AppColors.primaryLight, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Action Toolbar
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (_) => const CreateScheduleScreen()),
                        ).then((_) => _loadData());
                      },
                      icon: const Icon(Icons.add_circle_outline_rounded, size: 18),
                      label: const Text('Jadwal Baru', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: widget.onNavigateToReports,
                      icon: const Icon(Icons.analytics_outlined, size: 18, color: AppColors.accent),
                      label: const Text('Laporan & Export', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary)),
                      style: OutlinedButton.styleFrom(
                        side: const BorderSide(color: AppColors.accent),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // Key Overview Statistics
              const Text(
                'Ringkasan Sistem & Operasional',
                style: TextStyle(color: AppColors.textPrimary, fontSize: 16, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 12),
              GridView.count(
                crossAxisCount: 2,
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 1.4,
                children: [
                  StatCard(
                    title: 'Total Hadir',
                    value: '$totalHadir Sesi',
                    icon: Icons.check_circle_outline_rounded,
                    color: AppColors.hadir,
                    subtitle: 'Semua Asisten',
                  ),
                  StatCard(
                    title: 'Akumulasi Honor',
                    value: currencyFormatter.format(totalHonor),
                    icon: Icons.account_balance_wallet_outlined,
                    color: AppColors.accent,
                    subtitle: 'Bulan berjalan',
                  ),
                  StatCard(
                    title: 'Total Jadwal',
                    value: '${schedules.length}',
                    icon: Icons.calendar_today_rounded,
                    color: AppColors.primaryLight,
                    subtitle: 'Sesi latihan',
                  ),
                  StatCard(
                    title: 'Izin / Sakit / Alpa',
                    value: '${totalIzin + totalSakit + totalAlpa}',
                    icon: Icons.warning_amber_rounded,
                    color: AppColors.izin,
                    subtitle: '$totalIzin Izin • $totalSakit Sakit • $totalAlpa Alpa',
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Real-time Attendance Feed with correction & photo view
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Seluruh Riwayat & Bukti Absensi',
                    style: TextStyle(color: AppColors.textPrimary, fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const AttendanceHistoryScreen()),
                      );
                    },
                    child: const Text('Kelola Semua', style: TextStyle(color: AppColors.primaryLight, fontSize: 13)),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              if (attendances.isEmpty)
                Container(
                  padding: const EdgeInsets.all(20),
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: const Center(
                    child: Text('Belum ada data absensi.', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
                  ),
                )
              else
                ...attendances.take(5).map((att) {
                  Color statusColor = AppColors.hadir;
                  if (att.status.toLowerCase() == 'izin') statusColor = AppColors.izin;
                  if (att.status.toLowerCase() == 'sakit') statusColor = AppColors.sakit;
                  if (att.status.toLowerCase() == 'alpa') statusColor = AppColors.alpa;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 8),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Row(
                      children: [
                        CircleAvatar(
                          radius: 18,
                          backgroundColor: statusColor.withOpacity(0.2),
                          child: Text(
                            (att.userName.isNotEmpty ? att.userName[0] : 'U').toUpperCase(),
                            style: TextStyle(color: statusColor, fontWeight: FontWeight.bold, fontSize: 13),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                att.userName,
                                style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                '${att.date} • ${att.time.substring(0, 5)} WIB • ${currencyFormatter.format(att.sessionRate)}',
                                style: const TextStyle(color: AppColors.textSecondary, fontSize: 11),
                              ),
                            ],
                          ),
                        ),
                        if (att.primaryPhotoUrl != null && att.primaryPhotoUrl!.isNotEmpty)
                          IconButton(
                            icon: const Icon(Icons.image_outlined, color: AppColors.accent, size: 20),
                            onPressed: () {
                              showDialog(
                                context: context,
                                builder: (_) => PhotoPreviewDialog(
                                  photoUrl: att.primaryPhotoUrl!,
                                  title: 'Foto: ${att.userName}',
                                  subtitle: '${att.date} • Status: ${att.status}',
                                ),
                              );
                            },
                          ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: statusColor.withOpacity(0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            att.status,
                            style: TextStyle(color: statusColor, fontSize: 11, fontWeight: FontWeight.bold),
                          ),
                        ),
                      ],
                    ),
                  );
                }).toList(),
            ],
          ),
        ),
      ),
    );
  }
}

