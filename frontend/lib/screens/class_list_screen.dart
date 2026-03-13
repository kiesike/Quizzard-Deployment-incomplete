import 'package:flutter/material.dart';
import '../services/auth_service.dart';

class ClassListScreen extends StatefulWidget {
  const ClassListScreen({super.key});

  @override
  State<ClassListScreen> createState() => _ClassListScreenState();
}

class _ClassListScreenState extends State<ClassListScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _classes = [];

  @override
  void initState() {
    super.initState();
    _loadClasses();
  }

  Future<void> _loadClasses() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await AuthService.authGet('/classes');

    setState(() {
      _isLoading = false;
      if (result['success']) {
        _classes = result['data']['classes'] as List;
      } else {
        _errorMessage = result['message'];
      }
    });
  }

  Future<void> _createClass() async {
    final nameController = TextEditingController();
    final descController = TextEditingController();

    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16)),
        title: const Text('Create New Class'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameController,
              decoration: InputDecoration(
                labelText: 'Class Name',
                hintText: 'e.g. Math 101',
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: descController,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Description (optional)',
                hintText: 'e.g. Introduction to Mathematics',
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4CAF50),
            ),
            child: const Text('Create',
                style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;
    if (nameController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Class name is required!'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final result = await AuthService.authPost('/classes', {
      'name': nameController.text.trim(),
      'description': descController.text.trim(),
    });

    if (result['success']) {
      _loadClasses();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Class created successfully!'),
          backgroundColor: Colors.green,
        ),
      );
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(result['message']),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _editClass(Map<String, dynamic> cls) async {
    final nameController =
        TextEditingController(text: cls['name']);
    final descController =
        TextEditingController(text: cls['description'] ?? '');

    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16)),
        title: const Text('Edit Class'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: nameController,
              decoration: InputDecoration(
                labelText: 'Class Name',
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: descController,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Description (optional)',
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF4CAF50),
            ),
            child: const Text('Save',
                style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final result = await AuthService.authPut(
      '/classes/${cls['id']}',
      {
        'name': nameController.text.trim(),
        'description': descController.text.trim(),
      },
    );

    if (result['success']) {
      _loadClasses();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Class updated successfully!'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  Future<void> _deleteClass(Map<String, dynamic> cls) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16)),
        title: const Text('Delete Class'),
        content: Text(
            'Are you sure you want to delete "${cls['name']}"? This cannot be undone.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red),
            child: const Text('Delete',
                style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final result =
        await AuthService.authDelete('/classes/${cls['id']}');

    if (result['success']) {
      _loadClasses();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Class deleted successfully!'),
          backgroundColor: Colors.green,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: const Text('My Classes'),
        backgroundColor: const Color(0xFF4CAF50),
        foregroundColor: Colors.white,
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _createClass,
        backgroundColor: const Color(0xFF4CAF50),
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('New Class',
            style: TextStyle(color: Colors.white)),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child:
            CircularProgressIndicator(color: Color(0xFF4CAF50)),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline,
                  size: 60, color: Colors.red),
              const SizedBox(height: 16),
              Text(_errorMessage!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.red)),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadClasses,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    if (_classes.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.class_outlined,
                size: 80, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              'No classes yet.',
              style: TextStyle(
                  fontSize: 18, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 8),
            Text(
              'Tap the button below to create your first class!',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey.shade500),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadClasses,
      color: const Color(0xFF4CAF50),
      child: ListView.builder(
        padding:
            const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        itemCount: _classes.length,
        itemBuilder: (context, index) {
          final cls = Map<String, dynamic>.from(_classes[index]);
          return _buildClassCard(cls);
        },
      ),
    );
  }

  Widget _buildClassCard(Map<String, dynamic> cls) {
    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape:
          RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 3,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: () async {
          await Navigator.pushNamed(
            context,
            '/class-detail',
            arguments: {
              'class_id': cls['id'],
              'class_name': cls['name'],
            },
          );
          _loadClasses();
        },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Title row
              Row(
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color:
                          const Color(0xFF4CAF50).withOpacity(0.1),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.class_,
                        color: Color(0xFF4CAF50), size: 28),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          cls['name'],
                          style: const TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.bold,
                            color: Color(0xFF333333),
                          ),
                        ),
                        if (cls['description'] != null &&
                            cls['description'].isNotEmpty)
                          Text(
                            cls['description'],
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                                fontSize: 13,
                                color: Colors.grey.shade600),
                          ),
                      ],
                    ),
                  ),
                  const Icon(Icons.chevron_right,
                      color: Colors.grey),
                ],
              ),
              const SizedBox(height: 12),

              // Class code
              Container(
                padding: const EdgeInsets.symmetric(
                    horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF4CAF50).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                      color: const Color(0xFF4CAF50)
                          .withOpacity(0.3)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.key,
                        size: 14, color: Color(0xFF4CAF50)),
                    const SizedBox(width: 6),
                    Text(
                      'Code: ${cls['class_code']}',
                      style: const TextStyle(
                        color: Color(0xFF4CAF50),
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        letterSpacing: 1.5,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),

              // Stats row
              Row(
                children: [
                  _buildStat(Icons.people,
                      '${cls['students_count']} students'),
                  const SizedBox(width: 16),
                  _buildStat(Icons.quiz,
                      '${cls['quizzes_count']} quizzes'),
                ],
              ),

              const Divider(height: 20),

              // Action buttons
              Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  TextButton.icon(
                    onPressed: () => _editClass(cls),
                    icon: const Icon(Icons.edit,
                        size: 16, color: Color(0xFF6C63FF)),
                    label: const Text('Edit',
                        style:
                            TextStyle(color: Color(0xFF6C63FF))),
                  ),
                  const SizedBox(width: 4),
                  TextButton.icon(
                    onPressed: () => _deleteClass(cls),
                    icon: const Icon(Icons.delete,
                        size: 16, color: Colors.red),
                    label: const Text('Delete',
                        style: TextStyle(color: Colors.red)),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildStat(IconData icon, String label) {
    return Row(
      children: [
        Icon(icon, size: 16, color: Colors.grey),
        const SizedBox(width: 4),
        Text(label,
            style:
                const TextStyle(fontSize: 13, color: Colors.grey)),
      ],
    );
  }
}