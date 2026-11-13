import 'package:flutter/foundation.dart';
import '../models/report_model.dart';
import '../services/report_service.dart';

class ReportProvider with ChangeNotifier {
  final ReportService _reportService = ReportService();

  ReportModel? _report;
  bool _isLoading = false;
  String? _errorMessage;

  String? _startDate;
  String? _endDate;
  int? _userId;
  String? _status;

  ReportModel? get report => _report;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;

  String? get startDate => _startDate;
  String? get endDate => _endDate;
  int? get userId => _userId;
  String? get status => _status;

  void setFilter({String? startDate, String? endDate, int? userId, String? status}) {
    _startDate = startDate;
    _endDate = endDate;
    _userId = userId;
    _status = status;
    notifyListeners();
  }

  void resetFilter() {
    _startDate = null;
    _endDate = null;
    _userId = null;
    _status = null;
    notifyListeners();
  }

  Future<void> fetchReport() async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    try {
      _report = await _reportService.getReport(
        startDate: _startDate,
        endDate: _endDate,
        userId: _userId,
        status: _status,
      );
      _isLoading = false;
      notifyListeners();
    } catch (e) {
      _errorMessage = e.toString().replaceAll('Exception: ', '');
      _isLoading = false;
      notifyListeners();
    }
  }

  String getExcelExportUrl() {
    return _reportService.getExcelExportUrl(
      startDate: _startDate,
      endDate: _endDate,
      userId: _userId,
      status: _status,
    );
  }

  String getPdfExportUrl() {
    return _reportService.getPdfExportUrl(
      startDate: _startDate,
      endDate: _endDate,
      userId: _userId,
      status: _status,
    );
  }
}

