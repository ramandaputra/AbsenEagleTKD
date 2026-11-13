import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/attendance_provider.dart';
import '../../providers/schedule_provider.dart';
import '../../widgets/stat_card.dart';
import '../../widgets/photo_preview_dialog.dart';
import '../schedule/create_schedule_screen.dart';
import '../attendance/attendance_history_screen.dart';

class AdminDashboardScreen extends StatefulWidget {
  final VoidCallback onNavigateToSchedule;
  final VoidCallback onNavigateToReports;

  const AdminDashboardScreen({
    Key? key,
    required this.onNavigateToSchedule,
    required this.onNavigateToReports,
  }) : super(key: key);

  @override
  State<AdminDashboardScreen> createState() => _AdminDashboardScreenState();
}

class _AdminDashboardScreenState extends State<AdminDashboardScreen> {
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

    final hadirCount = attendances.where((a) => a.status.toLowerCase() == 'hadir').length;
    final izinCount = attendances.where((a) => a.status.toLowerCase() == 'izin').length;
    final sakitCount = attendances.where((a) => a.status.toLowerCase() == 'sakit').length;
    final alpaCount = attendances.where((a) => a.status.toLowerCase() == 'alpa').length;

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
                      backgroundColor: AppColors.accent.withOpacity(0.2),
                      child: const Icon(Icons.sports_martial_arts_rounded, color: AppColors.accent, size: 28),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Sabeum ${user?.name ?? "Pelatih"}',
                            style: const TextStyle(
                              color: AppColors.textPrimary,
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Tingkat: ${user?.beltLevel ?? "Dan IV"} • Dojang: ${user?.dojangName ?? "Eagle Central"}',
                            style: const TextStyle(color: AppColors.textSecondary, fontSize: 13),
                          ),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: AppColors.accent.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: const Text(
                              'Role: Admin / Pelatih Utama',
                              style: TextStyle(color: AppColors.accent, fontSize: 11, fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Quick Action Bar
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
                      label: const Text('Buat Jadwal', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13)),
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
                      icon: const Icon(Icons.file_download_outlined, size: 18, color: AppColors.accent),
                      label: const Text('Export Laporan', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary)),
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

              // Statistics Section
              const Text(
                'Ringkasan Absensi & Latihan',
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
                    value: '$hadirCount',
                    icon: Icons.check_circle_rounded,
                    color: AppColors.hadir,
                    subtitle: 'Semua Asisten',
                  ),
                  StatCard(
                    title: 'Izin & Sakit',
                    value: '${izinCount + sakitCount}',
                    icon: Icons.info_outline_rounded,
                    color: AppColors.izin,
                    subtitle: '$izinCount Izin, $sakitCount Sakit',
                  ),
                  StatCard(
                    title: 'Jadwal Latihan',
                    value: '${schedules.length}',
                    icon: Icons.event_note_rounded,
                    color: AppColors.primaryLight,
                    subtitle: 'Bulan ini',
                  ),
                  StatCard(
                    title: 'Alpa / Terlewat',
                    value: '$alpaCount',
                    icon: Icons.cancel_outlined,
                    color: AppColors.alpa,
                    subtitle: 'Perlu follow up',
                  ),
                ],
              ),
              const SizedBox(height: 24),

              // Upcoming Schedules with Assigned Coaches
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Jadwal & Petugas Piket Mengajar',
                    style: TextStyle(color: AppColors.textPrimary, fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  TextButton(
                    onPressed: widget.onNavigateToSchedule,
                    child: const Text('Lihat Kalender', style: TextStyle(color: AppColors.primaryLight, fontSize: 13)),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              if (schedules.isEmpty)
                Container(
                  padding: const EdgeInsets.all(20),
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: const Center(
                    child: Text('Belum ada jadwal latihan yang dibuat.', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
                  ),
                )
              else
                ...schedules.take(3).map((sched) {
                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              sched.activityName,
                              style: const TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 14),
                            ),
                            Text(
                              sched.date,
                              style: const TextStyle(color: AppColors.accent, fontSize: 12, fontWeight: FontWeight.w600),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            const Icon(Icons.access_time, size: 13, color: AppColors.textMuted),
                            const SizedBox(width: 4),
                            Text('${sched.startTime.substring(0, 5)} - ${sched.endTime.substring(0, 5)} WIB', style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                            const SizedBox(width: 12),
                            const Icon(Icons.location_on, size: 13, color: AppColors.textMuted),
                            const SizedBox(width: 4),
                            Expanded(child: Text(sched.location, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12), overflow: TextOverflow.ellipsis)),
                          ],
                        ),
                        if (sched.dutySchedule != null && sched.dutySchedule!.assignedCoaches.isNotEmpty) ...[
                          const SizedBox(height: 10),
                          const Divider(color: AppColors.border, height: 1),
                          const SizedBox(height: 8),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text('Piket: ', style: TextStyle(color: AppColors.textMuted, fontSize: 11, fontWeight: FontWeight.bold)),
                              Expanded(
                                child: Wrap(
                                  spacing: 6,
                                  runSpacing: 4,
                                  children: sched.dutySchedule!.assignedCoaches.map((c) {
                                    return Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                      decoration: BoxDecoration(
                                        color: AppColors.surfaceLight,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Text(
                                        '${c.userName ?? "Asisten"} (${c.taskRole})',
                                        style: const TextStyle(color: AppColors.textPrimary, fontSize: 10),
                                      ),
                                    );
                                  }).toList(),
                                ),
                              ),
                            ],
                          ),
                        ],
                      ],
                    ),
                  );
                }).toList(),
              const SizedBox(height: 20),

              // Real-time Attendance Feed
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text(
                    'Aktivitas Absensi Terbaru',
                    style: TextStyle(color: AppColors.textPrimary, fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                  TextButton(
                    onPressed: () {
                      Navigator.of(context).push(
                        MaterialPageRoute(builder: (_) => const AttendanceHistoryScreen()),
                      );
                    },
                    child: const Text('Kelola Absensi', style: TextStyle(color: AppColors.primaryLight, fontSize: 13)),
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
                    child: Text('Belum ada aktivitas absensi.', style: TextStyle(color: AppColors.textMuted, fontSize: 13)),
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
                                '${att.date} • ${att.time.substring(0, 5)} WIB',
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
                                  title: 'Bukti Foto: ${att.userName}',
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

