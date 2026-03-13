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
    if (percentage >= 80) return Colors.green;
    if (percentage >= 60) return Colors.orange;
    return Colors.red;
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
          matches = rawMap
              .map((key, value) => MapEntry(key, value.toString()));
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
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: Text(
          widget.studentName,
          style: const TextStyle(fontSize: 16),
        ),
        backgroundColor: const Color(0xFF4CAF50),
        foregroundColor: Colors.white,
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
              Text(_errorMessage!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(color: Colors.red)),
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

    return SingleChildScrollView(
      child: Column(
        children: [
          // Score header
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: const BoxDecoration(
              color: Color(0xFF4CAF50),
              borderRadius: BorderRadius.only(
                bottomLeft: Radius.circular(24),
                bottomRight: Radius.circular(24),
              ),
            ),
            child: Column(
              children: [
                // Student avatar and name
                CircleAvatar(
                  radius: 30,
                  backgroundColor: Colors.white.withOpacity(0.2),
                  child: Text(
                    student['name'][0].toUpperCase(),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  student['name'],
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                Text(
                  student['email'],
                  style: const TextStyle(
                    color: Colors.white70,
                    fontSize: 13,
                  ),
                ),
                const SizedBox(height: 20),

                // Score circle
                Container(
                  width: 120,
                  height: 120,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.1),
                        blurRadius: 20,
                        offset: const Offset(0, 10),
                      ),
                    ],
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        '$percentage%',
                        style: TextStyle(
                          fontSize: 30,
                          fontWeight: FontWeight.bold,
                          color: scoreColor,
                        ),
                      ),
                      Text(
                        '${attempt['score']}/${attempt['total_points']} pts',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Colors.grey,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Stats row
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                  children: [
                    _buildStat(
                      label: 'Correct',
                      value: questionResults
                          .where((q) => q['is_correct'] == true)
                          .length
                          .toString(),
                      color: Colors.white,
                    ),
                    _buildStat(
                      label: 'Wrong',
                      value: questionResults
                          .where((q) => q['is_correct'] == false)
                          .length
                          .toString(),
                      color: Colors.white,
                    ),
                    _buildStat(
                      label: 'Total',
                      value: questionResults.length.toString(),
                      color: Colors.white,
                    ),
                  ],
                ),
              ],
            ),
          ),

          // Question breakdown
          Padding(
            padding: const EdgeInsets.all(20),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Answer Breakdown',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF333333),
                  ),
                ),
                const SizedBox(height: 16),
                ...questionResults.asMap().entries.map((entry) {
                  final index = entry.key;
                  final q = Map<String, dynamic>.from(entry.value);
                  return Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 28,
                            height: 28,
                            decoration: BoxDecoration(
                              color: q['is_correct'] == true
                                  ? Colors.green
                                  : Colors.red,
                              shape: BoxShape.circle,
                            ),
                            child: Center(
                              child: Text(
                                '${index + 1}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 12,
                                ),
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          Text(
                            '${q['points_earned']}/${q['points']} pts',
                            style: TextStyle(
                              color: q['is_correct'] == true
                                  ? Colors.green
                                  : Colors.red,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      _buildResultWidget(q),
                      const SizedBox(height: 20),
                      if (index < questionResults.length - 1)
                        const Divider(height: 20),
                    ],
                  );
                }),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStat({
    required String label,
    required String value,
    required Color color,
  }) {
    return Column(
      children: [
        Text(
          value,
          style: TextStyle(
            color: color,
            fontSize: 24,
            fontWeight: FontWeight.bold,
          ),
        ),
        Text(
          label,
          style: const TextStyle(color: Colors.white70, fontSize: 12),
        ),
      ],
    );
  }
}