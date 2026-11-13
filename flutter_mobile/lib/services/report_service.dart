import '../constants/api_constants.dart';
import '../models/report_model.dart';
import 'api_service.dart';

class ReportService {
  Future<ReportModel> getReport({
    String? startDate,
    String? endDate,
    int? userId,
    String? status,
  }) async {
    Map<String, String> params = {};
    if (startDate != null) params['start_date'] = startDate;
    if (endDate != null) params['end_date'] = endDate;
    if (userId != null) params['user_id'] = userId.toString();
    if (status != null && status.isNotEmpty) params['status'] = status;

    final response = await ApiService.get(ApiConstants.reports, queryParams: params);

    if (response['success'] == true && response['data'] != null) {
      return ReportModel.fromJson(response['data']);
    } else {
      throw Exception(response['message'] ?? 'Gagal memuat laporan');
    }
  }

  String getExcelExportUrl({String? startDate, String? endDate, int? userId, String? status}) {
    String url = '${ApiConstants.baseUrl}${ApiConstants.exportExcel}?';
    if (startDate != null) url += 'start_date=$startDate&';
    if (endDate != null) url += 'end_date=$endDate&';
    if (userId != null) url += 'user_id=$userId&';
    if (status != null) url += 'status=$status&';
    return url;
  }

  String getPdfExportUrl({String? startDate, String? endDate, int? userId, String? status}) {
    String url = '${ApiConstants.baseUrl}${ApiConstants.exportPdf}?';
    if (startDate != null) url += 'start_date=$startDate&';
    if (endDate != null) url += 'end_date=$endDate&';
    if (userId != null) url += 'user_id=$userId&';
    if (status != null) url += 'status=$status&';
    return url;
  }
}

