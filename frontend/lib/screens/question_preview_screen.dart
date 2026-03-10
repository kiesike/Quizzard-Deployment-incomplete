import 'package:flutter/material.dart';
import '../widgets/multiple_choice_widget.dart';
import '../widgets/multiple_choice_result_widget.dart';

class QuestionPreviewScreen extends StatefulWidget {
  const QuestionPreviewScreen({super.key});

  @override
  State<QuestionPreviewScreen> createState() => _QuestionPreviewScreenState();
}

class _QuestionPreviewScreenState extends State<QuestionPreviewScreen> {
  int? _selectedAnswerId;
  bool _submitted = false;

  // Sample question data matching our API response
  final Map<String, dynamic> _sampleQuestion = {
    'id': 1,
    'question_text': 'What is the capital of the Philippines?',
    'question_type': 'multiple_choice',
    'points': 1,
    'answer_options': [
      {'id': 1, 'option_text': 'Cebu', 'is_correct': false, 'order': 1},
      {'id': 2, 'option_text': 'Manila', 'is_correct': true, 'order': 2},
      {'id': 3, 'option_text': 'Davao', 'is_correct': false, 'order': 3},
      {'id': 4, 'option_text': 'Quezon City', 'is_correct': false, 'order': 4},
    ],
  };

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        title: const Text('Multiple Choice Preview'),
        backgroundColor: const Color(0xFF6C63FF),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Show question or result based on submission
            _submitted
                ? MultipleChoiceResultWidget(
                    question: _sampleQuestion,
                    selectedAnswerId: _selectedAnswerId,
                  )
                : MultipleChoiceWidget(
                    question: _sampleQuestion,
                    selectedAnswerId: _selectedAnswerId,
                    onAnswerSelected: (id) {
                      setState(() => _selectedAnswerId = id);
                    },
                  ),

            const SizedBox(height: 30),

            // Submit / Reset button
            SizedBox(
              width: double.infinity,
              height: 55,
              child: ElevatedButton(
                onPressed: () {
                  if (_submitted) {
                    setState(() {
                      _submitted = false;
                      _selectedAnswerId = null;
                    });
                  } else {
                    if (_selectedAnswerId == null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Please select an answer first!'),
                          backgroundColor: Colors.orange,
                        ),
                      );
                      return;
                    }
                    setState(() => _submitted = true);
                  }
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: _submitted
                      ? Colors.grey
                      : const Color(0xFF6C63FF),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                ),
                child: Text(
                  _submitted ? 'Try Again' : 'Submit Answer',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 16,
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