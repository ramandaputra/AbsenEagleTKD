class AttendanceModel {
  final int id;
  final int userId;
  final String? userName;
  final int? trainingScheduleId;
  final String? activityName;
  final String date;
  final String time;
  final String status; // Hadir, Izin, Sakit, Alpa
  final String? notes;
  final double sessionRate;
  final List<String> photos;

  AttendanceModel({
    required this.id,
    required this.userId,
    this.userName,
    this.trainingScheduleId,
    this.activityName,
    required this.date,
    required this.time,
    required this.status,
    this.notes,
    this.sessionRate = 50000.0,
    this.photos = const [],
  });

  factory AttendanceModel.fromJson(Map<String, dynamic> json) {
    List<String> photoUrls = [];
    if (json['photos'] is List) {
      for (var p in json['photos']) {
        if (p is Map && p['photo_path'] != null) {
          photoUrls.add(p['photo_path'].toString());
        }
      }
    }

    return AttendanceModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      userId: json['user_id'] is int ? json['user_id'] : int.parse(json['user_id'].toString()),
      userName: json['user'] is Map ? json['user']['name'] : null,
      trainingScheduleId: json['training_schedule_id'] != null
          ? int.tryParse(json['training_schedule_id'].toString())
          : null,
      activityName: json['training_schedule'] is Map
          ? json['training_schedule']['activity_name']
          : null,
      date: json['date'] ?? '',
      time: json['time'] ?? '',
      status: json['status'] ?? 'Hadir',
      notes: json['notes'],
      sessionRate: (json['session_rate'] != null)
          ? double.tryParse(json['session_rate'].toString()) ?? 50000.0
          : 50000.0,
      photos: photoUrls,
    );
  }
}

