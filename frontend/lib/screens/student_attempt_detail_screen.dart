import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../widgets/multiple_choice_result_widget.dart';
import '../widgets/true_false_result_widget.dart';
import '../widgets/identification_result_widget.dart';
import '../widgets/matching_result_widget.dart';

class StudentAttemptDetailScreen extends StatefulWidget {
  final int quizId;
  final String quizTitle;
  final int attemptId;
  final String studentName;

  const StudentAttemptDetailScreen({
    super.key,
    required this.quizId,
    required this.quizTitle,
    required this.attemptId,
    required this.studentName,
  });

  @override
  State<StudentAttemptDetailScreen> createState() =>
      _StudentAttemptDetailScreenState();
}

class _StudentAttemptDetailScreenState
    extends State<StudentAttemptDetailScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  Map<String, dynamic>? _data;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    final result = await AuthService.authGet(
      '/teacher/quizzes/${widget.quizId}/results/${widget.attemptId}',
    );

    setState(() {
      _isLoading = false;
      if (result['success']) {
        _data = result['data'];
      } else {
        _errorMessage = result['message'];
      }
    });
  }

  Color _getScoreColor(int percentage) {
    if (percentage >= 80) return const Color(0xFF2E7D32);
    if (percentage >= 60) return const Color(0xFFF9A825);
    return const Color(0xFFC62828);
  }

  Widget _buildResultWidget(Map<String, dynamic> question) {
    final qType = question['question_type'] as String;
    final answerGiven = question['answer_given'] as String;

    switch (qType) {
      case 'multiple_choice':
        return MultipleChoiceResultWidget(
          question: question,
          selectedAnswerId: int.tryParse(answerGiven),
        );
      case 'true_false':
        return TrueFalseResultWidget(
          question: question,
          selectedAnswerId: int.tryParse(answerGiven),
        );
      case 'identification':
        return IdentificationResultWidget(
          question: question,
          studentAnswer: answerGiven,
        );
      case 'matching':
        Map<String, String> matches = {};
        try {
          final rawMap = _parseJsonMap(answerGiven);
          matches = rawMap.map((key, value) => MapEntry(key, value.toString()));
        } catch (e) {
          matches = {};
        }
        return MatchingResultWidget(
          question: question,
          studentAnswers: matches,
        );
      default:
        return const SizedBox();
    }
  }

  Map<String, dynamic> _parseJsonMap(String jsonString) {
    try {
      final clean = jsonString.trim().replaceAll('{', '').replaceAll('}', '');
      final pairs = clean.split('","');
      final result = <String, dynamic>{};
      for (var pair in pairs) {
        final parts = pair.replaceAll('"', '').split(':');
        if (parts.length >= 2) {
          final key = parts[0].trim();
          final value = parts.sublist(1).join(':').trim();
          result[key] = value;
        }
      }
      return result;
    } catch (e) {
      return {};
    }
  }

  @override
  Widget build(BuildContext context) {
    final attempt =
        _data != null ? Map<String, dynamic>.from(_data!['attempt']) : null;
    final percentage = attempt != null ? attempt['percentage'] as int : 0;
    final bool isPassed = percentage >= 60;

    final Color themeColor =
        isPassed ? const Color(0xFF2E7D32) : const Color(0xFFC62828);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: Text(
          widget.studentName,
          style: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w600,
          ),
        ),
        backgroundColor: themeColor,
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: Color(0xFF4CAF50)),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 60, color: Colors.red),
              const SizedBox(height: 16),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(color: Colors.red),
              ),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: _loadDetail,
                child: const Text('Retry'),
              ),
            ],
          ),
        ),
      );
    }

    final attempt = Map<String, dynamic>.from(_data!['attempt']);
    final student = Map<String, dynamic>.from(_data!['student']);
    final questionResults = _data!['question_results'] as List;
    final percentage = attempt['percentage'] as int;
    final scoreColor = _getScoreColor(percentage);

    final bool isPassed = percentage >= 60;
    final Color themeColor =
        isPassed ? const Color(0xFF2E7D32) : const Color(0xFFC62828);
    final Color lightThemeColor =
        isPassed ? const Color(0xFFE8F5E9) : const Color(0xFFFFEBEE);

    final int correctCount =
        questionResults.where((q) => q['is_correct'] == true).length;
    final int wrongCount =
        questionResults.where((q) => q['is_correct'] == false).length;
    final int totalCount = questionResults.length;

    final String performanceMessage;
    if (percentage >= 90) {
      performanceMessage =
          'Excellent performance. The student demonstrates strong mastery of this quiz.';
    } else if (percentage >= 75) {
      performanceMessage =
          'Good performance. The student understands most of the assessed concepts.';
    } else if (percentage >= 60) {
      performanceMessage =
          'Passed the quiz, but there is still room for improvement in some areas.';
    } else {
      performanceMessage =
          'The student did not meet the passing mark. Review and remediation are recommended.';
    }

    return SingleChildScrollView(
      child: Column(
        children: [
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              color: themeColor,
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(28),
                bottomRight: Radius.circular(28),
              ),
              boxShadow: [
                BoxShadow(
                  color: themeColor.withOpacity(0.22),
                  blurRadius: 18,
                  offset: const Offset(0, 8),
                ),
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 20, 20, 24),
              child: Column(
                children: [
                  CircleAvatar(
                    radius: 34,
                    backgroundColor: Colors.white.withOpacity(0.18),
                    child: Text(
                      student['name'][0].toUpperCase(),
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 26,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    student['name'],
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    student['email'],
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white70,
                      fontSize: 13,
                    ),
                  ),
                  const SizedBox(height: 20),

                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(30),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          isPassed ? Icons.check_circle : Icons.cancel,
                          color: themeColor,
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        Text(
                          isPassed ? 'PASSED' : 'FAILED',
                          style: TextStyle(
                            color: themeColor,
                            fontWeight: FontWeight.bold,
                            letterSpacing: 0.6,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  Container(
                    width: 132,
                    height: 132,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.10),
                          blurRadius: 18,
                          offset: const Offset(0, 8),
                        ),
                      ],
                    ),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          '$percentage%',
                          style: TextStyle(
                            fontSize: 32,
                            fontWeight: FontWeight.bold,
                            color: scoreColor,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${attempt['score']}/${attempt['total_points']}',
                          style: const TextStyle(
                            fontSize: 13,
                            color: Colors.grey,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 18),

                  Text(
                    performanceMessage,
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 13,
                      height: 1.45,
                    ),
                  ),
                  const SizedBox(height: 20),

                  Row(
                    children: [
                      Expanded(
                        child: _buildSummaryCard(
                          label: 'Correct',
                          value: correctCount.toString(),
                          icon: Icons.check_circle,
                          iconColor: const Color(0xFF2E7D32),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _buildSummaryCard(
                          label: 'Wrong',
                          value: wrongCount.toString(),
                          icon: Icons.cancel,
                          iconColor: const Color(0xFFC62828),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: _buildSummaryCard(
                          label: 'Total',
                          value: totalCount.toString(),
                          icon: Icons.quiz,
                          iconColor: themeColor,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          Padding(
            padding: const EdgeInsets.fromLTRB(16, 20, 16, 24),
            child: Column(
              children: questionResults.asMap().entries.map((entry) {
                final index = entry.key;
                final q = Map<String, dynamic>.from(entry.value);
                final bool isCorrect = q['is_correct'] == true;

                return Container(
                  margin: const EdgeInsets.only(bottom: 18),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(18),
                    border: Border.all(
                      color: isCorrect
                          ? Colors.green.withOpacity(0.20)
                          : Colors.red.withOpacity(0.20),
                    ),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 34,
                            height: 34,
                            decoration: BoxDecoration(
                              color: isCorrect
                                  ? const Color(0xFF2E7D32)
                                  : const Color(0xFFC62828),
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                '${index + 1}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 13,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                          Text(
                            isCorrect ? 'Correct Answer' : 'Incorrect Answer',
                            style: TextStyle(
                              color: isCorrect
                                  ? const Color(0xFF2E7D32)
                                  : const Color(0xFFC62828),
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      _buildResultWidget(q),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryCard({
    required String label,
    required String value,
    required IconData icon,
    required Color iconColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        children: [
          Icon(icon, color: iconColor, size: 20),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              fontSize: 20,
              fontWeight: FontWeight.bold,
              color: Color(0xFF222222),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            label,
            style: const TextStyle(
              fontSize: 12,
              color: Colors.grey,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }
}