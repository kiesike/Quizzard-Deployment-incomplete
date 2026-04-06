import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/auth_service.dart';

class StudentInfoScreen extends StatefulWidget {
  const StudentInfoScreen({super.key});

  @override
  State<StudentInfoScreen> createState() => _StudentInfoScreenState();
}

class _StudentInfoScreenState extends State<StudentInfoScreen> {
  final _formKey = GlobalKey<FormState>();

  final _studentIdController = TextEditingController();
  final _contactController = TextEditingController();
  final _sectionController = TextEditingController();

  String? _selectedGender;
  String? _selectedGradeLevel;
  DateTime? _selectedDate;
  bool _isLoading = true;
  bool _isSaving = false;

  final List<String> _genderOptions = ['male', 'female', 'other'];

  final List<String> _gradeLevelOptions = [
    'Grade 7',
    'Grade 8',
    'Grade 9',
    'Grade 10',
    'Grade 11',
    'Grade 12',
    'Year 1',
    'Year 2',
    'Year 3',
    'Year 4',
  ];

  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    final result = await AuthService.authGet('/student/profile');
    if (result['success']) {
      final profile = result['data']['profile'];
      setState(() {
        _studentIdController.text = profile['student_id'] ?? '';
        _contactController.text = profile['contact_number'] ?? '';
        _sectionController.text = profile['section'] ?? '';
        _selectedGender = profile['gender'];
        _selectedGradeLevel = profile['grade_level'];
        if (profile['date_of_birth'] != null) {
          _selectedDate = DateTime.parse(profile['date_of_birth']);
        }
        _isLoading = false;
      });
    } else {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate ?? DateTime(2000),
      firstDate: DateTime(1950),
      lastDate: DateTime.now(),
    );
    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSaving = true);

    final result = await AuthService.authPut('/student/profile', {
      'student_id': _studentIdController.text.trim(),
      'gender': _selectedGender,
      'date_of_birth': _selectedDate != null
          ? '${_selectedDate!.year}-${_selectedDate!.month.toString().padLeft(2, '0')}-${_selectedDate!.day.toString().padLeft(2, '0')}'
          : null,
      'contact_number': _contactController.text.trim(),
      'grade_level': _selectedGradeLevel,
      'section': _sectionController.text.trim(),
    });

    setState(() => _isSaving = false);

    if (result['success']) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Profile updated successfully!'),
          backgroundColor: Colors.green,
        ),
      );
      Navigator.pop(context);
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message'] ?? 'Failed to update profile.'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  void dispose() {
    _studentIdController.dispose();
    _contactController.dispose();
    _sectionController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Student Info'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Student ID
                    TextFormField(
                      controller: _studentIdController,
                      maxLength: 15,
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                            RegExp(r'[a-zA-Z0-9\-]')),
                      ],
                      decoration: const InputDecoration(
                        labelText: 'Student ID',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.badge),
                        helperText: 'Max 15 characters, alphanumeric only',
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Gender
                    DropdownButtonFormField<String>(
                      value: _selectedGender,
                      decoration: const InputDecoration(
                        labelText: 'Gender',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.person),
                      ),
                      items: _genderOptions.map((gender) {
                        return DropdownMenuItem(
                          value: gender,
                          child: Text(
                            gender[0].toUpperCase() + gender.substring(1),
                          ),
                        );
                      }).toList(),
                      onChanged: (value) =>
                          setState(() => _selectedGender = value),
                    ),
                    const SizedBox(height: 16),

                    // Date of Birth
                    GestureDetector(
                      onTap: _pickDate,
                      child: AbsorbPointer(
                        child: TextFormField(
                          decoration: InputDecoration(
                            labelText: 'Date of Birth',
                            border: const OutlineInputBorder(),
                            prefixIcon: const Icon(Icons.calendar_today),
                          ),
                          controller: TextEditingController(
                            text: _selectedDate != null
                                ? '${_selectedDate!.year}-${_selectedDate!.month.toString().padLeft(2, '0')}-${_selectedDate!.day.toString().padLeft(2, '0')}'
                                : '',
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Contact Number
                    TextFormField(
                      controller: _contactController,
                      maxLength: 15,
                      keyboardType: TextInputType.phone,
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                            RegExp(r'[0-9\+]')),
                      ],
                      decoration: const InputDecoration(
                        labelText: 'Contact Number',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.phone),
                        helperText: 'Max 15 characters, numbers only',
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Grade Level
                    DropdownButtonFormField<String>(
                      value: _selectedGradeLevel,
                      decoration: const InputDecoration(
                        labelText: 'Grade Level',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.school),
                      ),
                      items: _gradeLevelOptions.map((level) {
                        return DropdownMenuItem(
                          value: level,
                          child: Text(level),
                        );
                      }).toList(),
                      onChanged: (value) =>
                          setState(() => _selectedGradeLevel = value),
                    ),
                    const SizedBox(height: 16),

                    // Section
                    TextFormField(
                      controller: _sectionController,
                      maxLength: 20,
                      inputFormatters: [
                        FilteringTextInputFormatter.allow(
                            RegExp(r'[a-zA-Z0-9 ]')),
                      ],
                      decoration: const InputDecoration(
                        labelText: 'Section',
                        border: OutlineInputBorder(),
                        prefixIcon: Icon(Icons.group),
                        helperText: 'Max 20 characters',
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Save Button
                    ElevatedButton(
                      onPressed: _isSaving ? null : _saveProfile,
                      style: ElevatedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 16),
                      ),
                      child: _isSaving
                          ? const CircularProgressIndicator(
                              color: Colors.white)
                          : const Text('Save',
                              style: TextStyle(fontSize: 16)),
                    ),
                  ],
                ),
              ),
            ),
    );
  }
}