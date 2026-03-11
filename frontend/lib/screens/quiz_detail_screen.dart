import 'package:flutter/material.dart';
import '../services/auth_service.dart';

class QuizDetailScreen extends StatefulWidget {
  final int quizId;
  final String quizTitle;

  const QuizDetailScreen({
    super.key,
    required this.quizId,
    required this.quizTitle,
  });

  @override
  State<QuizDetailScreen> createState() => _QuizDetailScreenState();
}

class _QuizDetailScreenState extends State<QuizDetailScreen> {
  static const Color primaryColor = Color(0xFF6C63FF);

  List<dynamic> _questions = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadQuiz();
  }

  Future<void> _loadQuiz() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    final result = await AuthService.authGet('/quizzes/${widget.quizId}');

    if (result['success']) {
      setState(() {
        _questions = result['data']['data']['questions'] ?? [];
        _loading = false;
      });
    } else {
      setState(() {
        _error = result['message'];
        _loading = false;
      });
    }
  }

  Future<void> _deleteQuestion(int questionId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Delete Question'),
        content: const Text('Are you sure you want to delete this question?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Cancel'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Delete', style: TextStyle(color: Colors.red)),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    final result = await AuthService.authDelete(
        '/quizzes/${widget.quizId}/questions/$questionId');

    if (result['success']) {
      _loadQuiz();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Question deleted.'),
              backgroundColor: Colors.green),
        );
      }
    } else {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text(result['message']),
              backgroundColor: Colors.red),
        );
      }
    }
  }

  void _navigateToAddQuestion() async {
    final result = await Navigator.pushNamed(
      context,
      '/add-question',
      arguments: {'quiz_id': widget.quizId},
    );
    if (result == true) _loadQuiz();
  }

  void _navigateToEditQuiz() async {
    final result = await Navigator.pushNamed(
      context,
      '/edit-quiz',
      arguments: {
        'quiz_id': widget.quizId,
        'title': widget.quizTitle,
        'description': '',
      },
    );
    if (result == true) _loadQuiz();
  }

  String _questionTypeLabel(String type) {
    switch (type) {
      case 'multiple_choice': return 'Multiple Choice';
      case 'true_false':      return 'True / False';
      case 'identification':  return 'Identification';
      case 'matching':        return 'Matching';
      default:                return type;
    }
  }

  Color _questionTypeColor(String type) {
    switch (type) {
      case 'multiple_choice': return Colors.blue;
      case 'true_false':      return Colors.orange;
      case 'identification':  return Colors.purple;
      case 'matching':        return Colors.teal;
      default:                return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        title: Text(widget.quizTitle, overflow: TextOverflow.ellipsis),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit),
            tooltip: 'Edit Quiz',
            onPressed: _navigateToEditQuiz,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _navigateToAddQuestion,
        backgroundColor: primaryColor,
        icon: const Icon(Icons.add, color: Colors.white),
        label: const Text('Add Question',
            style: TextStyle(color: Colors.white)),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : _error != null
              ? Center(
                  child: Text(_error!,
                      style: const TextStyle(color: Colors.red)))
              : _questions.isEmpty
                  ? _buildEmptyState()
                  : _buildQuestionList(),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.quiz_outlined, size: 80, color: Colors.grey[400]),
          const SizedBox(height: 16),
          Text('No questions yet',
              style: TextStyle(fontSize: 18, color: Colors.grey[600])),
          const SizedBox(height: 8),
          Text('Tap "Add Question" to get started',
              style: TextStyle(color: Colors.grey[500])),
        ],
      ),
    );
  }

  Widget _buildQuestionList() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
      itemCount: _questions.length,
      itemBuilder: (context, index) {
        final q = _questions[index];
        final type = q['question_type'] ?? '';
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color:
                            _questionTypeColor(type).withOpacity(0.15),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        _questionTypeLabel(type),
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: _questionTypeColor(type),
                        ),
                      ),
                    ),
                    const Spacer(),
                    Text(
                      '${q['points'] ?? 1} pt${(q['points'] ?? 1) != 1 ? 's' : ''}',
                      style: TextStyle(
                          fontSize: 12, color: Colors.grey[600]),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  'Q${index + 1}. ${q['question_text'] ?? ''}',
                  style: const TextStyle(
                      fontSize: 15, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 12),
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    TextButton.icon(
                      onPressed: () async {
                        final result = await Navigator.pushNamed(
                          context,
                          '/edit-question',
                          arguments: {
                            'quiz_id':  widget.quizId,
                            'question': Map<String, dynamic>.from(q),
                          },
                        );
                        if (result == true) _loadQuiz();
                      },
                      icon: const Icon(Icons.edit, size: 16, color: Color(0xFF6C63FF)),
                      label: const Text('Edit', style: TextStyle(color: Color(0xFF6C63FF))),
                    ),
                    const SizedBox(width: 8),
                    TextButton.icon(
                      onPressed: () => _deleteQuestion(q['id']),
                      icon: const Icon(Icons.delete, size: 16, color: Colors.red),
                      label: const Text('Delete', style: TextStyle(color: Colors.red)),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}