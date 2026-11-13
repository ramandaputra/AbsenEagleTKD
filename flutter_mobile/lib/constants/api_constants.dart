class ApiConstants {
  // Base URL: In Android emulator use 10.0.2.2, for real device / local network use host IP or Laragon domain
  static const String baseUrl = 'http://10.0.2.2:8000/api/v1';
  static const String webBaseUrl = 'http://localhost:8000/api/v1';

  // Auth endpoints
  static const String login = '/login';
  static const String loginPin = '/login-pin';
  static const String pinUsers = '/pin-users';
  static const String logout = '/logout';
  static const String me = '/me';

  // Dashboard endpoint
  static const String dashboard = '/dashboard';

  // Attendance endpoints
  static const String attendances = '/attendances';
  static const String checkIn = '/attendances/check-in';

  // Schedules endpoints
  static const String schedules = '/schedules';
  static const String myDuties = '/my-duty-assignments';

  // Reports endpoints
  static const String reports = '/reports';
  static const String exportExcel = '/reports/export/excel';
  static const String exportPdf = '/reports/export/pdf';

  // Users & Roles
  static const String users = '/users';
  static const String roles = '/roles';
  static const String activityLogs = '/activity-logs';
  static const String salarySummary = '/salary/summary';
}

