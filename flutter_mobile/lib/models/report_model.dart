class ReportModel {
  final String startDate;
  final String endDate;
  final int totalHadir;
  final int totalIzin;
  final int totalSakit;
  final int totalAlpa;
  final int totalDutiesOverall;
  final List<CoachReportItem> coaches;

  ReportModel({
    required this.startDate,
    required this.endDate,
    required this.totalHadir,
    required this.totalIzin,
    required this.totalSakit,
    required this.totalAlpa,
    required this.totalDutiesOverall,
    required this.coaches,
  });

  double get totalHonor => coaches.fold<double>(0.0, (sum, c) => sum + c.totalSalary);

  double get attendanceRate {
    final total = totalHadir + totalIzin + totalSakit + totalAlpa;
    if (total == 0) return 0.0;
    return double.parse(((totalHadir / total) * 100).toStringAsFixed(1));
  }

  factory ReportModel.fromJson(Map<String, dynamic> json) {
    List<CoachReportItem> items = [];
    if (json['coaches'] is List) {
      for (var c in json['coaches']) {
        items.add(CoachReportItem.fromJson(c));
      }
    }

    return ReportModel(
      startDate: json['start_date'] ?? '',
      endDate: json['end_date'] ?? '',
      totalHadir: json['total_hadir'] ?? 0,
      totalIzin: json['total_izin'] ?? 0,
      totalSakit: json['total_sakit'] ?? 0,
      totalAlpa: json['total_alpa'] ?? 0,
      totalDutiesOverall: json['total_duties_overall'] ?? 0,
      coaches: items,
    );
  }
}

class CoachReportItem {
  final int coachId;
  final String coachName;
  final String? beltLevel;
  final String? dojangName;
  final int totalDuties;
  final int hadirCount;
  final int izinCount;
  final int sakitCount;
  final int alpaCount;
  final double attendanceRate;
  final double totalSalary;

  CoachReportItem({
    required this.coachId,
    required this.coachName,
    this.beltLevel,
    this.dojangName,
    required this.totalDuties,
    required this.hadirCount,
    required this.izinCount,
    required this.sakitCount,
    required this.alpaCount,
    required this.attendanceRate,
    required this.totalSalary,
  });

  factory CoachReportItem.fromJson(Map<String, dynamic> json) {
    String name = '';
    String? belt;
    String? dojang;
    int id = 0;

    if (json['coach'] is Map) {
      name = json['coach']['name'] ?? '';
      belt = json['coach']['belt_level'];
      id = json['coach']['id'] ?? 0;
      if (json['coach']['dojang'] is Map) {
        dojang = json['coach']['dojang']['name'];
      }
    }

    return CoachReportItem(
      coachId: id,
      coachName: name,
      beltLevel: belt,
      dojangName: dojang ?? 'Dojang Eagle Pusat',
      totalDuties: json['total_duties'] ?? 0,
      hadirCount: json['hadir_count'] ?? 0,
      izinCount: json['izin_count'] ?? 0,
      sakitCount: json['sakit_count'] ?? 0,
      alpaCount: json['alpa_count'] ?? 0,
      attendanceRate: (json['attendance_rate'] != null)
          ? double.tryParse(json['attendance_rate'].toString()) ?? 0.0
          : 0.0,
      totalSalary: (json['total_salary'] != null)
          ? double.tryParse(json['total_salary'].toString()) ?? 0.0
          : 0.0,
    );
  }
}
