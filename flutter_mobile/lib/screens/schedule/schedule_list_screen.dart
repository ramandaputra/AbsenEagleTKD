import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:table_calendar/table_calendar.dart';
import '../../constants/app_colors.dart';
import '../../providers/auth_provider.dart';
import '../../providers/schedule_provider.dart';
import '../../widgets/schedule_card.dart';
import 'create_schedule_screen.dart';

class ScheduleListScreen extends StatefulWidget {
  const ScheduleListScreen({Key? key}) : super(key: key);

  @override
  State<ScheduleListScreen> createState() => _ScheduleListScreenState();
}

class _ScheduleListScreenState extends State<ScheduleListScreen> {
  CalendarFormat _calendarFormat = CalendarFormat.twoWeeks;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Provider.of<ScheduleProvider>(context, listen: false).fetchSchedules();
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final schedProvider = Provider.of<ScheduleProvider>(context);
    final canManage = auth.isAdmin || auth.isSuperAdmin;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Jadwal Latihan & Piket', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      floatingActionButton: canManage
          ? FloatingActionButton.extended(
              onPressed: () {
                Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const CreateScheduleScreen()),
                ).then((_) => schedProvider.fetchSchedules());
              },
              backgroundColor: AppColors.primary,
              icon: const Icon(Icons.add, color: Colors.white),
              label: const Text('Jadwal Baru', style: TextStyle(fontWeight: FontWeight.bold, color: Colors.white)),
            )
          : null,
      body: Column(
        children: [
          // TableCalendar Widget
          Container(
            color: AppColors.surface,
            child: TableCalendar(
              firstDay: DateTime.utc(2025, 1, 1),
              lastDay: DateTime.utc(2030, 12, 31),
              focusedDay: schedProvider.focusedDay,
              calendarFormat: _calendarFormat,
              selectedDayPredicate: (day) => isSameDay(schedProvider.selectedDay, day),
              onDaySelected: (selectedDay, focusedDay) {
                schedProvider.setSelectedDay(selectedDay);
                schedProvider.setFocusedDay(focusedDay);
              },
              onFormatChanged: (format) {
                setState(() => _calendarFormat = format);
              },
              onPageChanged: (focusedDay) {
                schedProvider.setFocusedDay(focusedDay);
                schedProvider.fetchSchedules(month: focusedDay.month, year: focusedDay.year);
              },
              calendarStyle: const CalendarStyle(
                defaultTextStyle: TextStyle(color: AppColors.textPrimary),
                weekendTextStyle: TextStyle(color: AppColors.primaryLight),
                selectedDecoration: BoxDecoration(color: AppColors.primary, shape: BoxShape.circle),
                todayDecoration: BoxDecoration(color: AppColors.accent, shape: BoxShape.circle),
                outsideTextStyle: TextStyle(color: AppColors.textMuted),
              ),
              headerStyle: const HeaderStyle(
                titleCentered: true,
                formatButtonVisible: true,
                titleTextStyle: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 15),
                formatButtonTextStyle: TextStyle(color: AppColors.textPrimary, fontSize: 12),
                formatButtonDecoration: BoxDecoration(
                  border: Border.fromBorderSide(BorderSide(color: AppColors.border)),
                  borderRadius: BorderRadius.all(Radius.circular(12)),
                ),
                leftChevronIcon: Icon(Icons.chevron_left, color: AppColors.textPrimary),
                rightChevronIcon: Icon(Icons.chevron_right, color: AppColors.textPrimary),
              ),
            ),
          ),

          // Schedule Filter Banner
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            color: AppColors.surfaceLight,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  schedProvider.selectedDay != null
                      ? 'Jadwal Tanggal: ${schedProvider.selectedDay!.day}/${schedProvider.selectedDay!.month}/${schedProvider.selectedDay!.year}'
                      : 'Semua Jadwal Bulan Ini',
                  style: const TextStyle(color: AppColors.textSecondary, fontSize: 13, fontWeight: FontWeight.w600),
                ),
                if (schedProvider.selectedDay != null)
                  InkWell(
                    onTap: () => schedProvider.setSelectedDay(DateTime.now()),
                    child: const Text('Hari Ini', style: TextStyle(color: AppColors.primaryLight, fontSize: 12, fontWeight: FontWeight.bold)),
                  ),
              ],
            ),
          ),

          // Schedules List
          Expanded(
            child: schedProvider.isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : schedProvider.schedulesForSelectedDay.isEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: const [
                            Icon(Icons.event_busy_rounded, size: 48, color: AppColors.textMuted),
                            SizedBox(height: 12),
                            Text('Tidak ada jadwal latihan pada periode ini.', style: TextStyle(color: AppColors.textMuted, fontSize: 14)),
                          ],
                        ),
                      )
                    : RefreshIndicator(
                        onRefresh: () => schedProvider.fetchSchedules(),
                        color: AppColors.primary,
                        child: ListView.builder(
                          padding: const EdgeInsets.all(16),
                          itemCount: schedProvider.schedulesForSelectedDay.length,
                          itemBuilder: (context, index) {
                            final sched = schedProvider.schedulesForSelectedDay[index];
                            return ScheduleCard(
                              schedule: sched,
                              canManage: canManage,
                              onDelete: canManage
                                  ? () async {
                                      final confirm = await showDialog<bool>(
                                        context: context,
                                        builder: (_) => AlertDialog(
                                          backgroundColor: AppColors.surface,
                                          title: const Text('Hapus Jadwal?', style: TextStyle(color: AppColors.textPrimary)),
                                          content: Text('Yakin ingin menghapus ${sched.activityName}?', style: const TextStyle(color: AppColors.textSecondary)),
                                          actions: [
                                            TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('Batal')),
                                            ElevatedButton(
                                              onPressed: () => Navigator.pop(context, true),
                                              style: ElevatedButton.styleFrom(backgroundColor: AppColors.alpa),
                                              child: const Text('Hapus'),
                                            ),
                                          ],
                                        ),
                                      );
                                      if (confirm == true) {
                                        await schedProvider.deleteSchedule(sched.id);
                                      }
                                    }
                                  : null,
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

