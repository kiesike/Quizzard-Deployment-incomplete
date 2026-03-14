import 'package:flutter/material.dart';
import '../services/auth_service.dart';

class StudentClassQuizzesScreen extends StatefulWidget {
  final int classId;
  final String className;

  const StudentClassQuizzesScreen({
    super.key,
    required this.classId,
    required this.className,
  });

  @override
  State<StudentClassQuizzesScreen> createState() =>
      _StudentClassQuizzesScreenState();
}

class _StudentClassQuizzesScreenState
    extends State<StudentClassQuizzesScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _quizzes = [];

  @override
  void initState() {
    super.initState();
    _loadQuizzes();
  }

  Future<void> _loadQuizzes() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await AuthService.authGet(
      '/student/classes/${widget.classId}/quizzes',
    );

    setState(() {
      _isLoading = false;
      if (result['success']) {
        _quizzes = result['data']['quizzes'] as List;
      } else {
        _errorMessage = result['message'];
      }
    });
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
        backgroundColor: const Color(0xFF6C63FF),
        foregroundColor: Colors.white,
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
            color: Color(0xFF6C63FF)),
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
                onPressed: _loadQuizzes,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    if (_quizzes.isEmpty) {
      return const Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.quiz_outlined,
                size: 80, color: Colors.grey),
            SizedBox(height: 16),
            Text(
              'No quizzes available yet.',
              style: TextStyle(
                  fontSize: 18, color: Colors.grey),
            ),
            SizedBox(height: 8),
            Text(
              'Your teacher hasn\'t assigned any quizzes yet.',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadQuizzes,
      color: const Color(0xFF6C63FF),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _quizzes.length,
        itemBuilder: (context, index) {
          final quiz =
              Map<String, dynamic>.from(_quizzes[index]);
          return _buildQuizCard(quiz);
        },
      ),
    );
  }

  Widget _buildQuizCard(Map<String, dynamic> quiz) {
    final alreadyTaken = quiz['already_taken'] == true;

    return Card(
      margin: const EdgeInsets.only(bottom: 12),
      shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16)),
      elevation: 3,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: const Color(0xFF6C63FF)
                        .withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.quiz,
                      color: Color(0xFF6C63FF), size: 28),
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
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF333333),
                        ),
                      ),
                      if (quiz['description'] != null &&
                          quiz['description'].isNotEmpty)
                        Text(
                          quiz['description'],
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: TextStyle(
                              fontSize: 13,
                              color: Colors.grey.shade600),
                        ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Stats row
            Row(
              children: [
                Icon(Icons.help_outline,
                    size: 16, color: Colors.grey.shade500),
                const SizedBox(width: 4),
                Text(
                  '${quiz['questions_count']} questions',
                  style: TextStyle(
                      fontSize: 13,
                      color: Colors.grey.shade600),
                ),
                const Spacer(),
                // Status badge
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: alreadyTaken
                        ? Colors.green.withOpacity(0.1)
                        : const Color(0xFF6C63FF)
                            .withOpacity(0.1),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(
                      color: alreadyTaken
                          ? Colors.green.withOpacity(0.3)
                          : const Color(0xFF6C63FF)
                              .withOpacity(0.3),
                    ),
                  ),
                  child: Text(
                    alreadyTaken ? '✓ Done' : 'Not taken',
                    style: TextStyle(
                      fontSize: 12,
                      color: alreadyTaken
                          ? Colors.green
                          : const Color(0xFF6C63FF),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),

            // Take button
            SizedBox(
              width: double.infinity,
              height: 44,
              child: ElevatedButton(
                onPressed: alreadyTaken
                    ? null
                    : () async {
                        await Navigator.pushNamed(
                          context,
                          '/quiz-taking',
                          arguments: {
                            'quiz_id': quiz['id'],
                            'quiz_title': quiz['title'],
                          },
                        );
                        _loadQuizzes();
                      },
                style: ElevatedButton.styleFrom(
                  backgroundColor: alreadyTaken
                      ? Colors.grey.shade300
                      : const Color(0xFF6C63FF),
                  shape: RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(12)),
                ),
                child: Text(
                  alreadyTaken
                      ? 'Already Completed'
                      : 'Take Quiz',
                  style: TextStyle(
                    color: alreadyTaken
                        ? Colors.grey.shade600
                        : Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}