import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../constants/app_colors.dart';
import '../../models/attendance_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/attendance_provider.dart';
import '../../widgets/photo_preview_dialog.dart';

class AttendanceHistoryScreen extends StatefulWidget {
  const AttendanceHistoryScreen({Key? key}) : super(key: key);

  @override
  State<AttendanceHistoryScreen> createState() => _AttendanceHistoryScreenState();
}

class _AttendanceHistoryScreenState extends State<AttendanceHistoryScreen> {
  final List<String> _statuses = ['Semua', 'Hadir', 'Izin', 'Sakit', 'Alpa'];
  String _selectedStatus = 'Semua';

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadData();
    });
  }

  Future<void> _loadData() async {
    final auth = Provider.of<AuthProvider>(context, listen: false);
    final att = Provider.of<AttendanceProvider>(context, listen: false);
    if (auth.isAssistant) {
      await att.fetchAttendances(userId: auth.user?.id);
    } else {
      await att.fetchAttendances();
    }
  }

  void _onStatusSelected(String status) {
    setState(() => _selectedStatus = status);
    final att = Provider.of<AttendanceProvider>(context, listen: false);
    att.setFilter(status: status == 'Semua' ? null : status);
    _loadData();
  }

  void _showCorrectionDialog(AttendanceModel attendance) {
    String newStatus = attendance.status;
    final notesCtrl = TextEditingController(text: attendance.notes ?? '');

    showDialog(
      context: context,
      builder: (ctx) {
        return StatefulBuilder(
          builder: (context, setStateDialog) {
            return AlertDialog(
              backgroundColor: AppColors.surface,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              title: Text('Koreksi Absensi: ${attendance.userName}', style: const TextStyle(color: AppColors.textPrimary, fontSize: 16)),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Ubah Status:', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    value: newStatus,
                    dropdownColor: AppColors.surface,
                    style: const TextStyle(color: AppColors.textPrimary),
                    decoration: InputDecoration(
                      filled: true,
                      fillColor: AppColors.surfaceLight,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                    items: ['Hadir', 'Izin', 'Sakit', 'Alpa'].map((s) {
                      return DropdownMenuItem(value: s, child: Text(s));
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) setStateDialog(() => newStatus = val);
                    },
                  ),
                  const SizedBox(height: 14),
                  const Text('Catatan Koreksi:', style: TextStyle(color: AppColors.textSecondary, fontSize: 13)),
                  const SizedBox(height: 8),
                  TextField(
                    controller: notesCtrl,
                    style: const TextStyle(color: AppColors.textPrimary),
                    decoration: InputDecoration(
                      hintText: 'Alasan koreksi data...',
                      hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                      filled: true,
                      fillColor: AppColors.surfaceLight,
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppColors.border)),
                    ),
                  ),
                ],
              ),
              actions: [
                TextButton(
                  onPressed: () => Navigator.of(ctx).pop(),
                  child: const Text('Batal', style: TextStyle(color: AppColors.textMuted)),
                ),
                ElevatedButton(
                  onPressed: () async {
                    Navigator.of(ctx).pop();
                    final attProvider = Provider.of<AttendanceProvider>(context, listen: false);
                    final ok = await attProvider.correctAttendance(attendance.id, newStatus, notesCtrl.text);
                    if (ok && mounted) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(content: Text('Absensi berhasil dikoreksi'), backgroundColor: AppColors.hadir),
                      );
                    }
                  },
                  style: ElevatedButton.styleFrom(backgroundColor: AppColors.primary),
                  child: const Text('Simpan Koreksi'),
                ),
              ],
            );
          },
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final attProvider = Provider.of<AttendanceProvider>(context);
    final canCorrect = auth.isAdmin || auth.isSuperAdmin;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Riwayat Absensi', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: Column(
        children: [
          // Filter Status Horizontal List
          Container(
            padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
            color: AppColors.surface,
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: _statuses.map((status) {
                  final isSelected = _selectedStatus == status;
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      label: Text(status),
                      selected: isSelected,
                      selectedColor: AppColors.primary,
                      backgroundColor: AppColors.surfaceLight,
                      labelStyle: TextStyle(
                        color: isSelected ? Colors.white : AppColors.textSecondary,
                        fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
                        fontSize: 12,
                      ),
                      onSelected: (_) => _onStatusSelected(status),
                    ),
                  );
                }).toList(),
              ),
            ),
          ),

          // Attendance List
          Expanded(
            child: attProvider.isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : attProvider.attendances.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: const [
                            Icon(Icons.history_toggle_off_rounded, size: 48, color: AppColors.textMuted),
                            SizedBox(height: 12),
                            Text('Tidak ada riwayat absensi.', style: TextStyle(color: AppColors.textMuted, fontSize: 14)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: _loadData,
                        color: AppColors.primary,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: attProvider.attendances.length,
                          itemBuilder: (context, index) {
                            final att = attProvider.attendances[index];
                            Color statusColor = AppColors.hadir;
                            if (att.status.toLowerCase() == 'izin') statusColor = AppColors.izin;
                            if (att.status.toLowerCase() == 'sakit') statusColor = AppColors.sakit;
                            if (att.status.toLowerCase() == 'alpa') statusColor = AppColors.alpa;

                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: AppColors.border),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Expanded(
                                        child: Text(
                                          att.userName,
                                          style: const TextStyle(
                                            color: AppColors.textPrimary,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 14,
                                          ),
                                        ),
                                      ),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                        decoration: BoxDecoration(
                                          color: statusColor.withOpacity(0.15),
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: Text(
                                          att.status,
                                          style: TextStyle(
                                            color: statusColor,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 12,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      const Icon(Icons.access_time_rounded, size: 14, color: AppColors.textMuted),
                                      const SizedBox(width: 6),
                                      Text(
                                        '${att.date} • ${att.time.substring(0, 5)} WIB',
                                        style: const TextStyle(color: AppColors.textSecondary, fontSize: 12),
                                      ),
                                      if (att.trainingSchedule != null) ...[
                                        const SizedBox(width: 12),
                                        Expanded(
                                          child: Text(
                                            '• ${att.trainingSchedule!.activityName}',
                                            style: const TextStyle(color: AppColors.accent, fontSize: 12),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        ),
                                      ],
                                    ],
                                  ),
                                  if (att.notes != null && att.notes!.isNotEmpty) ...[
                                    const SizedBox(height: 6),
                                    Text(
                                      'Catatan: ${att.notes}',
                                      style: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                                    ),
                                  ],
                                  const SizedBox(height: 10),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      if (att.primaryPhotoUrl != null && att.primaryPhotoUrl!.isNotEmpty)
                                        TextButton.icon(
                                          onPressed: () {
                                            showDialog(
                                              context: context,
                                              builder: (_) => PhotoPreviewDialog(
                                                photoUrl: att.primaryPhotoUrl!,
                                                title: 'Foto Bukti: ${att.userName}',
                                                subtitle: '${att.date} • ${att.status}',
                                              ),
                                            );
                                          },
                                          icon: const Icon(Icons.remove_red_eye_outlined, size: 16, color: AppColors.primaryLight),
                                          label: const Text('Lihat Foto Bukti', style: TextStyle(color: AppColors.primaryLight, fontSize: 12)),
                                        )
                                      else
                                        const Text('Tanpa foto bukti', style: TextStyle(color: AppColors.textMuted, fontSize: 11)),
                                      if (canCorrect)
                                        OutlinedButton(
                                          onPressed: () => _showCorrectionDialog(att),
                                          style: OutlinedButton.styleFrom(
                                            side: const BorderSide(color: AppColors.border),
                                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                                          ),
                                          child: const Text('Koreksi', style: TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                                        ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
                      ),
          ),
        ],
      ),
    );
  }
}

