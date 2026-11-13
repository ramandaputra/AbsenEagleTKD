import '../constants/api_constants.dart';
import '../models/user_model.dart';
import 'api_service.dart';

class AuthService {
  Future<UserModel> login(String email, String password) async {
    final response = await ApiService.post(ApiConstants.login, {
      'email': email,
      'password': password,
      'device_name': 'Flutter Mobile App',
    });

    if (response['success'] == true) {
      final token = response['data']['token'];
      await ApiService.setToken(token);
      return UserModel.fromJson(response['data']['user']);
    } else {
      throw Exception(response['message'] ?? 'Login gagal');
    }
  }

  Future<UserModel> loginWithPin(String pin, {int? userId, String? email}) async {
    final Map<String, dynamic> body = {
      'pin': pin,
      'device_name': 'Flutter PIN Auth',
    };
    if (userId != null) body['user_id'] = userId;
    if (email != null) body['email'] = email;

    final response = await ApiService.post(ApiConstants.loginPin, body);

    if (response['success'] == true) {
      final token = response['data']['token'];
      await ApiService.setToken(token);
      return UserModel.fromJson(response['data']['user']);
    } else {
      throw Exception(response['message'] ?? 'PIN salah');
    }
  }

  Future<List<Map<String, dynamic>>> getPinUsers() async {
    try {
      final response = await ApiService.get(ApiConstants.pinUsers);
      if (response['success'] == true && response['data'] is List) {
        return List<Map<String, dynamic>>.from(response['data']);
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  Future<UserModel?> getProfile() async {
    try {
      final response = await ApiService.get(ApiConstants.me);
      if (response['success'] == true) {
        return UserModel.fromJson(response['data']['user']);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await ApiService.post(ApiConstants.logout, {});
    } catch (_) {}
    await ApiService.clearToken();
  }
}

