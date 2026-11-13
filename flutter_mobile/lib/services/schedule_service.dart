import '../constants/api_constants.dart';
import '../models/schedule_model.dart';
import 'api_service.dart';

class ScheduleService {
  Future<List<TrainingScheduleModel>> getSchedules({
    int? month,
    int? year,
    String? date,
    bool myDuties = false,
  }) async {
    Map<String, String> params = {};
    if (month != null) params['month'] = month.toString();
    if (year != null) params['year'] = year.toString();
    if (date != null) params['date'] = date;
    if (myDuties) params['my_duties'] = '1';

    final response = await ApiService.get(ApiConstants.schedules, queryParams: params);

    List<TrainingScheduleModel> list = [];
    if (response['success'] == true && response['data'] is List) {
      for (var item in response['data']) {
        list.add(TrainingScheduleModel.fromJson(item));
      }
    }
    return list;
  }

  Future<TrainingScheduleModel> createSchedule({
    required String activityName,
    required String date,
    required String startTime,
    required String endTime,
    required String location,
    String? description,
    String? shiftName,
    String? dutyNotes,
    List<Map<String, dynamic>>? assignedCoaches,
  }) async {
    final body = {
      'activity_name': activityName,
      'date': date,
      'start_time': startTime,
      'end_time': endTime,
      'location': location,
      'description': description,
      'shift_name': shiftName ?? 'Piket Mengajar Utama',
      'duty_notes': dutyNotes,
      'assigned_coaches': assignedCoaches,
    };

    final response = await ApiService.post(ApiConstants.schedules, body);
    if (response['success'] == true) {
      return TrainingScheduleModel.fromJson(response['data']['schedule']);
    } else {
      throw Exception(response['message'] ?? 'Gagal membuat jadwal latihan');
    }
  }

  Future<void> deleteSchedule(int id) async {
    await ApiService.delete('${ApiConstants.schedules}/$id');
  }

  Future<List<DutyAssignmentModel>> getMyDutyAssignments() async {
    final response = await ApiService.get(ApiConstants.myDuties);
    List<DutyAssignmentModel> list = [];
    if (response['success'] == true && response['data'] != null) {
      final data = response['data']['data'] ?? response['data'];
      if (data is List) {
        for (var item in data) {
          list.add(DutyAssignmentModel.fromJson(item));
        }
      }
    }
    return list;
  }
}

