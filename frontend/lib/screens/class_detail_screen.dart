import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../services/auth_service.dart';

class ClassDetailScreen extends StatefulWidget {
  final int classId;
  final String className;

  const ClassDetailScreen({
    super.key,
    required this.classId,
    required this.className,
  });

  @override
  State<ClassDetailScreen> createState() => _ClassDetailScreenState();
}

class _ClassDetailScreenState extends State<ClassDetailScreen>
    with SingleTickerProviderStateMixin {
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _classData;
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _loadClassDetail();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _loadClassDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result =
        await AuthService.authGet('/classes/${widget.classId}');

    setState(() {
      _isLoading = false;
      if (result['success']) {
        _classData = result['data']['class'];
      } else {
        _errorMessage = result['message'];
      }
    });
  }

  void _copyClassCode() {
    final code = _classData?['class_code'] ?? '';
    Clipboard.setData(ClipboardData(text: code));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Class code "$code" copied to clipboard!'),
        backgroundColor: Colors.green,
      ),
    );
  }

  Future<void> _assignQuiz() async {
    // Get teacher's quizzes
    final result = await AuthService.authGet('/quizzes');
    if (!result['success']) return;

    final data = result['data'];
    final allQuizzes = (data['quizzes'] ?? data['data'] ?? []) as List;
    final assignedIds = (_classData!['quizzes'] as List)
        .map((q) => q['id'])
        .toSet();

    final availableQuizzes = allQuizzes
        .where((q) => !assignedIds.contains(q['id']))
        .toList();

    if (!mounted) return;

    if (availableQuizzes.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('All your quizzes are already assigned to this class.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    final selectedQuiz = await showDialog<Map<String, dynamic>>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16)),
        title: const Text('Assign Quiz'),
        content: SizedBox(
          width: double.maxFinite,
          child: ListView.builder(
            shrinkWrap: true,
            itemCount: availableQuizzes.length,
            itemBuilder: (context, index) {
              final quiz = availableQuizzes[index];
              return ListTile(
                title: Text(quiz['title']),
                subtitle: Text(
                  quiz['is_published'] == true
                      ? 'Published'
                      : 'Draft',
                  style: TextStyle(
                    color: quiz['is_published'] == true
                        ? Colors.green
                        : Colors.grey,
                  ),
                ),
                leading: const Icon(Icons.quiz,
                    color: Color(0xFF4CAF50)),
                onTap: () => Navigator.pop(
                    context, Map<String, dynamic>.from(quiz)),
              );
            },
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Cancel'),
          ),
        ],
      ),
    );

    if (selectedQuiz == null) return;

    final assignResult = await AuthService.authPost(
      '/classes/${widget.classId}/assign-quiz',
      {'quiz_id': selectedQuiz['id']},
    );

    if (assignResult['success']) {
      _loadClassDetail();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Quiz assigned successfully!'),
          backgroundColor: Colors.green,
        ),
      );
    } else {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(assignResult['message']),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _unassignQuiz(Map<String, dynamic> quiz) async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16)),
        title: const Text('Remove Quiz'),
        content: Text(
            'Remove "${quiz['title']}" from this class?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red),
            child: const Text('Remove',
                style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );

    if (confirm != true) return;

    final result = await AuthService.authDelete(
      '/classes/${widget.classId}/quizzes/${quiz['id']}',
    );

    if (result['success']) {
      _loadClassDetail();
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Quiz removed from class!'),
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
        title: Text(
          widget.className,
          style: const TextStyle(fontSize: 16),
        ),
        backgroundColor: const Color(0xFF4CAF50),
        foregroundColor: Colors.white,
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: Colors.white,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(icon: Icon(Icons.people), text: 'Students'),
            Tab(icon: Icon(Icons.quiz), text: 'Quizzes'),
          ],
        ),
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
                onPressed: _loadClassDetail,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final students = _classData!['students'] as List;
    final quizzes = _classData!['quizzes'] as List;
    final classCode = _classData!['class_code'] as String;

    return Column(
      children: [
        // Class code banner
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(
              horizontal: 20, vertical: 12),
          color: const Color(0xFF4CAF50).withOpacity(0.1),
          child: Row(
            children: [
              const Icon(Icons.key,
                  color: Color(0xFF4CAF50), size: 20),
              const SizedBox(width: 8),
              Text(
                'Class Code: ',
                style: TextStyle(
                    color: Colors.grey.shade600, fontSize: 14),
              ),
              Text(
                classCode,
                style: const TextStyle(
                  color: Color(0xFF4CAF50),
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                  letterSpacing: 2,
                ),
              ),
              const Spacer(),
              IconButton(
                onPressed: _copyClassCode,
                icon: const Icon(Icons.copy,
                    color: Color(0xFF4CAF50), size: 20),
                tooltip: 'Copy code',
              ),
            ],
          ),
        ),

        // Tab views
        Expanded(
          child: TabBarView(
            controller: _tabController,
            children: [
              // ── Students Tab ──
              _buildStudentsTab(students),

              // ── Quizzes Tab ──
              _buildQuizzesTab(quizzes),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildStudentsTab(List students) {
    if (students.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.people_outline,
                size: 70, color: Colors.grey.shade400),
            const SizedBox(height: 16),
            Text(
              'No students yet.',
              style: TextStyle(
                  fontSize: 16, color: Colors.grey.shade600),
            ),
            const SizedBox(height: 8),
            Text(
              'Share the class code with your students!',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey.shade500),
            ),
            const SizedBox(height: 16),
            ElevatedButton.icon(
              onPressed: _copyClassCode,
              icon: const Icon(Icons.copy, color: Colors.white),
              label: Text(
                'Copy Code: ${_classData!['class_code']}',
                style: const TextStyle(color: Colors.white),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF4CAF50),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadClassDetail,
      color: const Color(0xFF4CAF50),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: students.length,
        itemBuilder: (context, index) {
          final student =
              Map<String, dynamic>.from(students[index]);
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                CircleAvatar(
                  backgroundColor:
                      const Color(0xFF4CAF50).withOpacity(0.1),
                  child: Text(
                    student['name'][0].toUpperCase(),
                    style: const TextStyle(
                      color: Color(0xFF4CAF50),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        student['name'],
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: Color(0xFF333333),
                        ),
                      ),
                      Text(
                        student['email'],
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey.shade600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildQuizzesTab(List quizzes) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _assignQuiz,
        backgroundColor: const Color(0xFF4CAF50),
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Assign Quiz',
            style: TextStyle(color: Colors.white)),
      ),
      body: quizzes.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.quiz_outlined,
                      size: 70, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  Text(
                    'No quizzes assigned yet.',
                    style: TextStyle(
                        fontSize: 16,
                        color: Colors.grey.shade600),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Tap the button below to assign a quiz!',
                    textAlign: TextAlign.center,
                    style:
                        TextStyle(color: Colors.grey.shade500),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: _loadClassDetail,
              color: const Color(0xFF4CAF50),
              child: ListView.builder(
                padding: const EdgeInsets.all(16),
                itemCount: quizzes.length,
                itemBuilder: (context, index) {
                  final quiz =
                      Map<String, dynamic>.from(quizzes[index]);
                  return Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.05),
                          blurRadius: 6,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Row(
                      children: [
                        Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            color: const Color(0xFF4CAF50)
                                .withOpacity(0.1),
                            borderRadius:
                                BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.quiz,
                              color: Color(0xFF4CAF50)),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment:
                                CrossAxisAlignment.start,
                            children: [
                              Text(
                                quiz['title'],
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 15,
                                  color: Color(0xFF333333),
                                ),
                              ),
                              const SizedBox(height: 4),
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets
                                        .symmetric(
                                        horizontal: 8,
                                        vertical: 2),
                                    decoration: BoxDecoration(
                                      color: quiz[
                                                  'is_published'] ==
                                              true
                                          ? Colors.green
                                              .withOpacity(0.1)
                                          : Colors.grey
                                              .withOpacity(0.1),
                                      borderRadius:
                                          BorderRadius.circular(
                                              8),
                                    ),
                                    child: Text(
                                      quiz['is_published'] == true
                                          ? 'Published'
                                          : 'Draft',
                                      style: TextStyle(
                                        fontSize: 11,
                                        color: quiz['is_published'] ==
                                                true
                                            ? Colors.green
                                            : Colors.grey,
                                        fontWeight:
                                            FontWeight.bold,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    '${quiz['questions_count'] ?? 0} questions',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: Colors.grey.shade600,
                                    ),
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                        IconButton(
                          onPressed: () => _unassignQuiz(quiz),
                          icon: const Icon(Icons.remove_circle,
                              color: Colors.red),
                          tooltip: 'Remove quiz',
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
    );
  }
}