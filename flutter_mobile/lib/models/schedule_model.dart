class TrainingScheduleModel {
  final int id;
  final String activityName;
  final String date;
  final String startTime;
  final String endTime;
  final String location;
  final String? description;
  final String status;
  final String? creatorName;
  final String? shiftName;
  final List<DutyAssignmentModel> assignments;

  TrainingScheduleModel({
    required this.id,
    required this.activityName,
    required this.date,
    required this.startTime,
    required this.endTime,
    required this.location,
    this.description,
    this.status = 'scheduled',
    this.creatorName,
    this.shiftName,
    this.assignments = const [],
  });

  factory TrainingScheduleModel.fromJson(Map<String, dynamic> json) {
    List<DutyAssignmentModel> dutyList = [];
    String? shift;

    if (json['duty_schedule'] is Map) {
      shift = json['duty_schedule']['shift_name'];
      if (json['duty_schedule']['assignments'] is List) {
        for (var a in json['duty_schedule']['assignments']) {
          dutyList.add(DutyAssignmentModel.fromJson(a));
        }
      }
    }

    return TrainingScheduleModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      activityName: json['activity_name'] ?? '',
      date: json['date'] ?? '',
      startTime: json['start_time'] ?? '',
      endTime: json['end_time'] ?? '',
      location: json['location'] ?? '',
      description: json['description'],
      status: json['status'] ?? 'scheduled',
      creatorName: json['creator'] is Map ? json['creator']['name'] : null,
      shiftName: shift,
      assignments: dutyList,
    );
  }
}

class DutyAssignmentModel {
  final int id;
  final int userId;
  final String userName;
  final String? beltLevel;
  final String roleTask;
  final String? trainingMaterials;
  final String status;

  DutyAssignmentModel({
    required this.id,
    required this.userId,
    required this.userName,
    this.beltLevel,
    required this.roleTask,
    this.trainingMaterials,
    this.status = 'assigned',
  });

  factory DutyAssignmentModel.fromJson(Map<String, dynamic> json) {
    String name = 'Asisten Pelatih';
    String? belt;
    if (json['user'] is Map) {
      name = json['user']['name'] ?? 'Asisten Pelatih';
      belt = json['user']['belt_level'];
    }

    return DutyAssignmentModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      userId: json['user_id'] is int ? json['user_id'] : int.parse(json['user_id'].toString()),
      userName: name,
      beltLevel: belt,
      roleTask: json['role_task'] ?? 'Instruktur Pendamping',
      trainingMaterials: json['training_materials'],
      status: json['status'] ?? 'assigned',
    );
  }
}

