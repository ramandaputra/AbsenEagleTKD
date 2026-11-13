import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../constants/app_colors.dart';
import '../providers/auth_provider.dart';
import '../widgets/custom_bottom_nav.dart';
import 'dashboard/assistant_dashboard_screen.dart';
import 'dashboard/admin_dashboard_screen.dart';
import 'dashboard/superadmin_dashboard_screen.dart';
import 'schedule/schedule_list_screen.dart';
import 'attendance/checkin_screen.dart';
import 'attendance/attendance_history_screen.dart';
import 'reports/report_screen.dart';
import 'profile/profile_screen.dart';

class HomeWrapper extends StatefulWidget {
  const HomeWrapper({Key? key}) : super(key: key);

  @override
  State<HomeWrapper> createState() => _HomeWrapperState();
}

class _HomeWrapperState extends State<HomeWrapper> {
  int _currentIndex = 0;

  void _onTabSelected(int index) {
    setState(() {
      _currentIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final isAssistant = auth.isAssistant;

    // Build the dashboard according to user role
    Widget dashboardWidget;
    if (auth.isSuperAdmin) {
      dashboardWidget = SuperadminDashboardScreen(
        onNavigateToSchedule: () => setState(() => _currentIndex = 1),
        onNavigateToReports: () => setState(() => _currentIndex = 3),
      );
    } else if (auth.isAdmin) {
      dashboardWidget = AdminDashboardScreen(
        onNavigateToSchedule: () => setState(() => _currentIndex = 1),
        onNavigateToReports: () => setState(() => _currentIndex = 3),
      );
    } else {
      dashboardWidget = AssistantDashboardScreen(
        onNavigateToSchedule: () {},
      );
    }

    final List<Widget> pages = isAssistant
        ? [
            dashboardWidget,
            const ProfileScreen(),
          ]
        : [
            dashboardWidget,
            const ScheduleListScreen(),
            const AttendanceHistoryScreen(),
            const ReportScreen(),
            const ProfileScreen(),
          ];

    return Scaffold(
      backgroundColor: AppColors.background,
      body: IndexedStack(
        index: _currentIndex >= pages.length ? 0 : _currentIndex,
        children: pages,
      ),
      bottomNavigationBar: CustomBottomNav(
        currentIndex: _currentIndex >= pages.length ? 0 : _currentIndex,
        onTap: _onTabSelected,
        isAssistant: isAssistant,
      ),
    );
  }
}

