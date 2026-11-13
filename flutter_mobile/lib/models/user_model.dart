class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String roleDisplay;
  final String? phone;
  final String? beltLevel;
  final String? avatar;
  final double ratePerSession;
  final String? bankName;
  final String? bankAccountNumber;
  final String? bankAccountHolder;
  final String? dojangName;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.roleDisplay,
    this.phone,
    this.beltLevel,
    this.avatar,
    this.ratePerSession = 50000.0,
    this.bankName,
    this.bankAccountNumber,
    this.bankAccountHolder,
    this.dojangName,
  });

  bool get isSuperAdmin => role == 'superadmin';
  bool get isAdmin => role == 'admin' || role == 'superadmin';
  bool get isAssistantCoach => role == 'asisten_pelatih';

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'] is int ? json['id'] : int.parse(json['id'].toString()),
      name: json['name'] ?? '',
      email: json['email'] ?? '',
      role: json['role'] ?? json['role_name'] ?? 'asisten_pelatih',
      roleDisplay: json['role_display'] ?? json['role_display_name'] ?? 'Asisten Pelatih',
      phone: json['phone'],
      beltLevel: json['belt_level'],
      avatar: json['avatar'],
      ratePerSession: (json['rate_per_session'] != null)
          ? double.tryParse(json['rate_per_session'].toString()) ?? 50000.0
          : 50000.0,
      bankName: json['bank_name'],
      bankAccountNumber: json['bank_account_number'],
      bankAccountHolder: json['bank_account_holder'],
      dojangName: json['dojang'] is Map ? json['dojang']['name'] : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'role_display': roleDisplay,
      'phone': phone,
      'belt_level': beltLevel,
      'avatar': avatar,
      'rate_per_session': ratePerSession,
      'bank_name': bankName,
      'bank_account_number': bankAccountNumber,
      'bank_account_holder': bankAccountHolder,
    };
  }
}

