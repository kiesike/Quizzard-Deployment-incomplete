import 'package:flutter/material.dart';
import '../services/auth_service.dart';
class MatchingWidget extends StatefulWidget {
  final Map<String, dynamic> question;
  final Function(Map<String, String>) onAnswerChanged;
  final Map<String, String>? currentAnswers;

  const MatchingWidget({
    super.key,
    required this.question,
    required this.onAnswerChanged,
    this.currentAnswers,
  });

  @override
  State<MatchingWidget> createState() => _MatchingWidgetState();
}

class _MatchingWidgetState extends State<MatchingWidget> {
  late Map<String, String?> _selectedMatches;
  late List<String> _columnBOptions;

  @override
  void initState() {
    super.initState();
    final options = widget.question['answer_options'] as List;

    _selectedMatches = {};
    for (var option in options) {
      _selectedMatches[option['option_text']] =
          widget.currentAnswers?[option['option_text']];
    }

    _columnBOptions =
        options.map((o) => o['match_pair'].toString()).toList();
    _columnBOptions.shuffle();
  }

  void _updateAnswer(String columnA, String? columnB) {
    setState(() => _selectedMatches[columnA] = columnB);

    final answers = <String, String>{};
    _selectedMatches.forEach((key, value) {
      if (value != null) answers[key] = value;
    });
    widget.onAnswerChanged(answers);
  }

  @override
  Widget build(BuildContext context) {
    final options = widget.question['answer_options'] as List;
    final mediaPath = widget.question['media_path'];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Question container
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: const Color(0xFF6C63FF).withOpacity(0.05),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: const Color(0xFF6C63FF).withOpacity(0.2),
            ),
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFF6C63FF),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Text(
                      'Matching Type',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const Spacer(),
                  Text(
                    '${widget.question['points']} pt${widget.question['points'] > 1 ? 's' : ''}',
                    style: const TextStyle(
                      color: Color(0xFF6C63FF),
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                widget.question['question_text'],
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF333333),
                ),
              ),
              // Question image
              if (mediaPath != null && mediaPath.toString().isNotEmpty) ...[
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.network(
                    mediaPath.toString().startsWith('http') ? mediaPath : '${AuthService.storageUrl}/$mediaPath',
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) =>
                        const SizedBox(),
                  ),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Column headers
        Row(
          children: [
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFF6C63FF),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Center(
                  child: Text(
                    'Column A',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 8),
            const Icon(Icons.arrow_forward, color: Color(0xFF6C63FF)),
            const SizedBox(width: 8),
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFF6C63FF).withOpacity(0.7),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Center(
                  child: Text(
                    'Column B',
                    style: TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
            ),
          ],
        ),
        const SizedBox(height: 12),

        // Matching rows
        ...options.asMap().entries.map((entry) {
          final option = Map<String, dynamic>.from(entry.value);
          final columnA = option['option_text'].toString();
          final selectedB = _selectedMatches[columnA];

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            child: Row(
              children: [
                // Column A item
                Expanded(
                  child: Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: Colors.grey.shade300),
                    ),
                    child: Text(
                      columnA,
                      style: const TextStyle(
                        fontWeight: FontWeight.w600,
                        fontSize: 13,
                        color: Color(0xFF333333),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(Icons.arrow_forward,
                    color: Colors.grey, size: 16),
                const SizedBox(width: 8),

                // Column B dropdown
                Expanded(
                  child: Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8),
                    decoration: BoxDecoration(
                      color: selectedB != null
                          ? const Color(0xFF6C63FF).withOpacity(0.1)
                          : Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: selectedB != null
                            ? const Color(0xFF6C63FF)
                            : Colors.grey.shade300,
                      ),
                    ),
                    child: DropdownButtonHideUnderline(
                      child: DropdownButton<String>(
                        value: selectedB,
                        hint: const Text(
                          'Select...',
                          style: TextStyle(
                              fontSize: 12, color: Colors.grey),
                        ),
                        isExpanded: true,
                        icon: const Icon(Icons.arrow_drop_down,
                            color: Color(0xFF6C63FF)),
                        items: [
                          const DropdownMenuItem<String>(
                            value: null,
                            child: Text(
                              'Select...',
                              style: TextStyle(
                                  color: Colors.grey, fontSize: 12),
                            ),
                          ),
                          ..._columnBOptions.map((b) =>
                              DropdownMenuItem(
                                value: b,
                                child: Text(
                                  b,
                                  style:
                                      const TextStyle(fontSize: 12),
                                  overflow: TextOverflow.ellipsis,
                                ),
                              )),
                        ],
                        onChanged: (val) =>
                            _updateAnswer(columnA, val),
                        style: const TextStyle(
                          color: Color(0xFF333333),
                          fontSize: 12,
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}
