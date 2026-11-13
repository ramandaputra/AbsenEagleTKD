import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:intl/intl.dart';
import '../../constants/app_colors.dart';
import '../../constants/api_constants.dart';
import '../../providers/schedule_provider.dart';
import '../../services/api_service.dart';

class CreateScheduleScreen extends StatefulWidget {
  const CreateScheduleScreen({Key? key}) : super(key: key);

  @override
  State<CreateScheduleScreen> createState() => _CreateScheduleScreenState();
}

class _CreateScheduleScreenState extends State<CreateScheduleScreen> {
  final _formKey = GlobalKey<FormState>();
  final _activityNameCtrl = TextEditingController();
  final _locationCtrl = TextEditingController(text: 'Dojang Utama Eagle');
  final _descriptionCtrl = TextEditingController();
  final _shiftNameCtrl = TextEditingController(text: 'Piket Mengajar Utama');
  final _dutyNotesCtrl = TextEditingController();

  DateTime _selectedDate = DateTime.now();
  TimeOfDay _startTime = const TimeOfDay(hour: 16, minute: 0);
  TimeOfDay _endTime = const TimeOfDay(hour: 18, minute: 0);

  List<Map<String, dynamic>> _availableCoaches = [];
  bool _isLoadingCoaches = false;

  // Selected assignments: list of items {coach_id, task_role, training_material}
  final List<Map<String, dynamic>> _dutyAssignments = [];

  final List<String> _roleOptions = [
    'Instruktur Utama',
    'Asisten Pemanasan',
    'Asisten Teknik Poomsae',
    'Asisten Sparring Kyorugi',
    'Asisten Fisik & Kelenturan',
    'Pencatat Absensi',
  ];

  @override
  void initState() {
    super.initState();
    _loadCoaches();
  }

  @override
  void dispose() {
    _activityNameCtrl.dispose();
    _locationCtrl.dispose();
    _descriptionCtrl.dispose();
    _shiftNameCtrl.dispose();
    _dutyNotesCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadCoaches() async {
    setState(() => _isLoadingCoaches = true);
    try {
      final res = await ApiService.get(ApiConstants.users);
      if (res['success'] == true && res['data'] is List) {
        setState(() {
          _availableCoaches = List<Map<String, dynamic>>.from(res['data']);
          // Add default first assignment if coaches exist
          if (_availableCoaches.isNotEmpty && _dutyAssignments.isEmpty) {
            _dutyAssignments.add({
              'user_id': _availableCoaches.first['id'],
              'task_role': 'Instruktur Utama',
              'training_material': 'Teknik Dasar & Poomsae',
            });
          }
        });
      }
    } catch (_) {}
    setState(() => _isLoadingCoaches = false);
  }

  void _addCoachAssignment() {
    if (_availableCoaches.isEmpty) return;
    setState(() {
      _dutyAssignments.add({
        'user_id': _availableCoaches.first['id'],
        'task_role': 'Asisten Pemanasan',
        'training_material': 'Pemanasan & Kicking Drills',
      });
    });
  }

  void _removeCoachAssignment(int index) {
    setState(() {
      _dutyAssignments.removeAt(index);
    });
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now().subtract(const Duration(days: 30)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.dark(
              primary: AppColors.primary,
              surface: AppColors.surface,
              onSurface: AppColors.textPrimary,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _pickTime(bool isStart) async {
    final initial = isStart ? _startTime : _endTime;
    final picked = await showTimePicker(
      context: context,
      initialTime: initial,
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.dark(
              primary: AppColors.primary,
              surface: AppColors.surface,
              onSurface: AppColors.textPrimary,
            ),
          ),
          child: child!,
        );
      },
    );
    if (picked != null) {
      setState(() {
        if (isStart) {
          _startTime = picked;
        } else {
          _endTime = picked;
        }
      });
    }
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;

    final schedProvider = Provider.of<ScheduleProvider>(context, listen: false);

    final dateStr = DateFormat('yyyy-MM-dd').format(_selectedDate);
    final startTimeStr = '${_startTime.hour.toString().padLeft(2, '0')}:${_startTime.minute.toString().padLeft(2, '0')}:00';
    final endTimeStr = '${_endTime.hour.toString().padLeft(2, '0')}:${_endTime.minute.toString().padLeft(2, '0')}:00';

    final success = await schedProvider.createSchedule(
      activityName: _activityNameCtrl.text.trim(),
      date: dateStr,
      startTime: startTimeStr,
      endTime: endTimeStr,
      location: _locationCtrl.text.trim(),
      description: _descriptionCtrl.text.trim(),
      shiftName: _shiftNameCtrl.text.trim(),
      dutyNotes: _dutyNotesCtrl.text.trim(),
      assignedCoaches: _dutyAssignments,
    );

    if (success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('✅ Jadwal dan piket mengajar berhasil dibuat!'), backgroundColor: AppColors.hadir),
      );
      Navigator.of(context).pop();
    } else if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(schedProvider.errorMessage ?? 'Gagal membuat jadwal'), backgroundColor: AppColors.alpa),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final schedProvider = Provider.of<ScheduleProvider>(context);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Buat Jadwal Latihan & Piket', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18)),
        backgroundColor: AppColors.surface,
        elevation: 0,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Activity Name
              const Text('Nama Kegiatan Latihan', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _activityNameCtrl,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  hintText: 'Contoh: Latihan Reguler Sabuk Putih - Hijau',
                  hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                  filled: true,
                  fillColor: AppColors.surface,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                ),
                validator: (v) => v == null || v.isEmpty ? 'Nama kegiatan wajib diisi' : null,
              ),
              const SizedBox(height: 16),

              // Date & Location Row
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Tanggal Latihan', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
                        const SizedBox(height: 8),
                        InkWell(
                          onTap: _pickDate,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                            decoration: BoxDecoration(
                              color: AppColors.surface,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.calendar_today, size: 16, color: AppColors.accent),
                                const SizedBox(width: 8),
                                Text(
                                  DateFormat('dd MMM yyyy').format(_selectedDate),
                                  style: const TextStyle(color: AppColors.textPrimary, fontSize: 13),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Lokasi Latihan', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
                        const SizedBox(height: 8),
                        TextFormField(
                          controller: _locationCtrl,
                          style: const TextStyle(color: AppColors.textPrimary),
                          decoration: InputDecoration(
                            filled: true,
                            fillColor: AppColors.surface,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                          validator: (v) => v == null || v.isEmpty ? 'Lokasi wajib diisi' : null,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Time Row
              Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Jam Mulai', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
                        const SizedBox(height: 8),
                        InkWell(
                          onTap: () => _pickTime(true),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                            decoration: BoxDecoration(
                              color: AppColors.surface,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.access_time, size: 16, color: AppColors.primaryLight),
                                const SizedBox(width: 8),
                                Text('${_startTime.hour.toString().padLeft(2, '0')}:${_startTime.minute.toString().padLeft(2, '0')} WIB', style: const TextStyle(color: AppColors.textPrimary, fontSize: 13)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Jam Selesai', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
                        const SizedBox(height: 8),
                        InkWell(
                          onTap: () => _pickTime(false),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
                            decoration: BoxDecoration(
                              color: AppColors.surface,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(color: AppColors.border),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.access_time, size: 16, color: AppColors.primaryLight),
                                const SizedBox(width: 8),
                                Text('${_endTime.hour.toString().padLeft(2, '0')}:${_endTime.minute.toString().padLeft(2, '0')} WIB', style: const TextStyle(color: AppColors.textPrimary, fontSize: 13)),
                              ],
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),

              // Description
              const Text('Keterangan Latihan (Opsional)', style: TextStyle(color: AppColors.textPrimary, fontWeight: FontWeight.bold, fontSize: 13)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _descriptionCtrl,
                maxLines: 2,
                style: const TextStyle(color: AppColors.textPrimary),
                decoration: InputDecoration(
                  hintText: 'Misal: Materi pemantapan fisik, sparing, dan poomsae',
                  hintStyle: const TextStyle(color: AppColors.textMuted, fontSize: 13),
                  filled: true,
                  fillColor: AppColors.surface,
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                  enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: AppColors.border)),
                ),
              ),
              const SizedBox(height: 24),

              // Duty Schedule (Piket Mengajar) Divider Banner
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.surfaceLight,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Petugas Piket Mengajar', style: TextStyle(color: AppColors.accent, fontWeight: FontWeight.bold, fontSize: 14)),
                    IconButton(
                      icon: const Icon(Icons.add_circle, color: AppColors.accent),
                      onPressed: _addCoachAssignment,
                      tooltip: 'Tambah Asisten Pelatih',
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),

              // List of assigned coach rows
              if (_isLoadingCoaches)
                const Center(child: Padding(padding: EdgeInsets.all(16), child: CircularProgressIndicator(color: AppColors.primary)))
              else if (_dutyAssignments.isEmpty)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: AppColors.surface, borderRadius: BorderRadius.circular(12), border: Border.all(color: AppColors.border)),
                  child: const Center(child: Text('Belum ada asisten pelatih yang ditugaskan.', style: TextStyle(color: AppColors.textMuted, fontSize: 12))),
                )
              else
                ..._dutyAssignments.asMap().entries.map((entry) {
                  final index = entry.key;
                  final item = entry.value;

                  return Container(
                    margin: const EdgeInsets.only(bottom: 12),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text('Petugas #${index + 1}', style: const TextStyle(color: AppColors.textSecondary, fontWeight: FontWeight.bold, fontSize: 12)),
                            if (_dutyAssignments.length > 1)
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline, color: AppColors.alpa, size: 18),
                                onPressed: () => _removeCoachAssignment(index),
                                padding: EdgeInsets.zero,
                                constraints: const BoxConstraints(),
                              ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        // Dropdown coach
                        DropdownButtonFormField<int>(
                          value: item['user_id'],
                          dropdownColor: AppColors.surface,
                          style: const TextStyle(color: AppColors.textPrimary, fontSize: 13),
                          decoration: InputDecoration(
                            labelText: 'Pilih Asisten Pelatih',
                            labelStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                            filled: true,
                            fillColor: AppColors.surfaceLight,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                          items: _availableCoaches.map((c) {
                            return DropdownMenuItem<int>(
                              value: c['id'],
                              child: Text('${c['name']} (${c['role']?['name'] ?? "Pelatih"})'),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) setState(() => item['user_id'] = val);
                          },
                        ),
                        const SizedBox(height: 8),
                        // Dropdown Role
                        DropdownButtonFormField<String>(
                          value: item['task_role'],
                          dropdownColor: AppColors.surface,
                          style: const TextStyle(color: AppColors.textPrimary, fontSize: 13),
                          decoration: InputDecoration(
                            labelText: 'Tugas / Peran',
                            labelStyle: const TextStyle(color: AppColors.textMuted, fontSize: 12),
                            filled: true,
                            fillColor: AppColors.surfaceLight,
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: AppColors.border)),
                          ),
                          items: _roleOptions.map((r) {
                            return DropdownMenuItem<String>(value: r, child: Text(r));
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) setState(() => item['task_role'] = val);
                          },
                        ),
                      ],
                    ),
                  );
                }).toList(),
              const SizedBox(height: 24),

              // Submit Button
              ElevatedButton(
                onPressed: schedProvider.isCreating ? null : _handleSubmit,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: schedProvider.isCreating
                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Text('SIMPAN JADWAL LATIHAN & PIKET', style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold, letterSpacing: 1)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

