import 'dart:io';
import '../constants/api_constants.dart';
import '../models/attendance_model.dart';
import 'api_service.dart';

class AttendanceService {
  Future<List<AttendanceModel>> getAttendances({
    String? startDate,
    String? endDate,
    String? status,
    int? userId,
  }) async {
    Map<String, String> params = {};
    if (startDate != null) params['start_date'] = startDate;
    if (endDate != null) params['end_date'] = endDate;
    if (status != null && status.isNotEmpty) params['status'] = status;
    if (userId != null) params['user_id'] = userId.toString();

    final response = await ApiService.get(ApiConstants.attendances, queryParams: params);

    List<AttendanceModel> list = [];
    if (response['success'] == true && response['data'] != null) {
      final data = response['data']['data'] ?? response['data'];
      if (data is List) {
        for (var item in data) {
          list.add(AttendanceModel.fromJson(item));
        }
      }
    }
    return list;
  }

  Future<AttendanceModel> checkIn({
    required String status,
    int? trainingScheduleId,
    int? dutyAssignmentId,
    String? notes,
    File? photoFile,
    String? photoBase64,
  }) async {
    Map<String, String> fields = {
      'status': status,
    };
    if (trainingScheduleId != null) {
      fields['training_schedule_id'] = trainingScheduleId.toString();
    }
    if (dutyAssignmentId != null) {
      fields['duty_assignment_id'] = dutyAssignmentId.toString();
    }
    if (notes != null && notes.isNotEmpty) {
      fields['notes'] = notes;
    }

    final response = await ApiService.postMultipart(
      ApiConstants.checkIn,
      fields: fields,
      photoFile: photoFile,
      photoBase64: photoBase64,
    );

    if (response['success'] == true) {
      return AttendanceModel.fromJson(response['data']['attendance']);
    } else {
      throw Exception(response['message'] ?? 'Gagal melakukan absensi');
    }
  }

  Future<void> correctAttendance(int id, String status, String? notes) async {
    await ApiService.put('${ApiConstants.attendances}/$id', {
      'status': status,
      if (notes != null) 'notes': notes,
    });
  }
}

