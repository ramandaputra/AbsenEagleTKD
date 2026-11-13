import 'package:flutter/foundation.dart';
import '../models/schedule_model.dart';
import '../services/schedule_service.dart';

class ScheduleProvider with ChangeNotifier {
  final ScheduleService _scheduleService = ScheduleService();

  List<TrainingScheduleModel> _schedules = [];
  List<DutyAssignmentModel> _myDuties = [];
  bool _isLoading = false;
  bool _isCreating = false;
  String? _errorMessage;

  DateTime _focusedDay = DateTime.now();
  DateTime? _selectedDay;

  List<TrainingScheduleModel> get schedules => _schedules;
  List<DutyAssignmentModel> get myDuties => _myDuties;
  bool get isLoading => _isLoading;
  bool get isCreating => _isCreating;
  String? get errorMessage => _errorMessage;
  DateTime get focusedDay => _focusedDay;
  DateTime? get selectedDay => _selectedDay;

  void setSelectedDay(DateTime day) {
    _selectedDay = day;
    notifyListeners();
  }

  void setFocusedDay(DateTime day) {
    _focusedDay = day;
    notifyListeners();
  }

  List<TrainingScheduleModel> get schedulesForSelectedDay {
    if (_selectedDay == null) return _schedules;
    final dateStr = "${_selectedDay!.year}-${_selectedDay!.month.toString().padLeft(2, '0')}-${_selectedDay!.day.toString().padLeft(2, '0')}";
    return _schedules.where((s) => s.date == dateStr).toList();
  }

  Future<void> fetchSchedules({int? month, int? year, String? date, bool myDuties = false}) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _schedules = await _scheduleService.getSchedules(
        month: month ?? _focusedDay.month,
        year: year ?? _focusedDay.year,
        date: date,
        myDuties: myDuties,
      );
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<void> fetchMyDuties() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _myDuties = await _scheduleService.getMyDutyAssignments();
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
    }
  }

  Future<bool> createSchedule({
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
    _isCreating = true;
    _errorMessage = null;
    notifyListeners();

    try {
      final newSchedule = await _scheduleService.createSchedule(
        activityName: activityName,
        date: date,
        startTime: startTime,
        endTime: endTime,
        location: location,
        description: description,
        shiftName: shiftName,
        dutyNotes: dutyNotes,
        assignedCoaches: assignedCoaches,
      );
      _schedules.add(newSchedule);
      _schedules.sort((a, b) => b.date.compareTo(a.date));
      _isCreating = false;
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isCreating = false;
      notifyListeners();
      return false;
    }
  }

  Future<bool> deleteSchedule(int id) async {
    try {
      await _scheduleService.deleteSchedule(id);
      _schedules.removeWhere((s) => s.id == id);
      notifyListeners();
      return true;
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      notifyListeners();
      return false;
    }
  }
}

