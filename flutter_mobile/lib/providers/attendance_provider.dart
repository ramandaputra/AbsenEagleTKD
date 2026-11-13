import 'dart:io';
import 'package:flutter/foundation.dart';
import '../models/attendance_model.dart';
import '../services/attendance_service.dart';

class AttendanceProvider with ChangeNotifier {
  final AttendanceService _attendanceService = AttendanceService();

  List<AttendanceModel> _attendances = [];
  bool _isLoading = false;
  bool _isSubmitting = false;
  String? _errorMessage;

  String? _filterStartDate;
  String? _filterEndDate;
  String? _filterStatus;
  int? _filterUserId;

  List<AttendanceModel> get attendances => _attendances;
  bool get isLoading => _isLoading;
  bool get isSubmitting => _isSubmitting;
  String? get errorMessage => _errorMessage;

  String? get filterStartDate => _filterStartDate;
  String? get filterEndDate => _filterEndDate;
  String? get filterStatus => _filterStatus;
  int? get filterUserId => _filterUserId;

  void setFilter({String? startDate, String? endDate, String? status, int? userId}) {
    _filterStartDate = startDate;
    _filterEndDate = endDate;
    _filterStatus = status;
    _filterUserId = userId;
    notifyListeners();
  }

  void resetFilter() {
    _filterStartDate = null;
    _filterEndDate = null;
    _filterStatus = null;
    _filterUserId = null;
    notifyListeners();
  }

  Future<void> fetchAttendances({int? userId}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _attendances = await _attendanceService.getAttendances(
        startDate: _filterStartDate,
        endDate: _filterEndDate,
        status: _filterStatus,
        userId: userId ?? _filterUserId,
      );
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> checkIn({
    required String status,
    int? trainingScheduleId,
    int? dutyAssignmentId,
    String? notes,
    File? photoFile,
    String? photoBase64,
  }) async {
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final newAttendance = await _attendanceService.checkIn(
        status: status,
        trainingScheduleId: trainingScheduleId,
        dutyAssignmentId: dutyAssignmentId,
        notes: notes,
        photoFile: photoFile,
        photoBase64: photoBase64,
      );
      _attendances.insert(0, newAttendance);
      _isSubmitting = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isSubmitting = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> correctAttendance(int id, String status, String? notes) async {
    _isSubmitting = true;
    _errorMessage = null;
    notifyListeners();

    try {
      await _attendanceService.correctAttendance(id, status, notes);
      await fetchAttendances();
      _isSubmitting = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isSubmitting = false;
      notifyListeners();
      return false;
    }
  }

  void clearError() {
    _errorMessage = null;
    notifyListeners();
  }
}

