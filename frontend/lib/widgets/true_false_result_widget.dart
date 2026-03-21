import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'video_player_widget.dart';

class TrueFalseResultWidget extends StatelessWidget {
  final Map<String, dynamic> question;
  final int? selectedAnswerId;

  const TrueFalseResultWidget({
    super.key,
    required this.question,
    this.selectedAnswerId,
  });

  @override
  Widget build(BuildContext context) {
    final options = question['answer_options'] as List;
    final mediaPath = question['media_path'];
    final mediaType = question['media_type'];

    Map<String, dynamic>? correctOption;
    for (var option in options) {
      if (option['is_correct'] == true) {
        correctOption = Map<String, dynamic>.from(option);
        break;
      }
    }

    final isCorrect = selectedAnswerId != null &&
        correctOption != null &&
        selectedAnswerId == correctOption['id'];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Question with result
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: isCorrect
                ? Colors.green.withOpacity(0.05)
                : Colors.red.withOpacity(0.05),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: isCorrect
                  ? Colors.green.withOpacity(0.3)
                  : Colors.red.withOpacity(0.3),
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
                      color: isCorrect ? Colors.green : Colors.red,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          isCorrect ? Icons.check : Icons.close,
                          color: Colors.white,
                          size: 12,
                        ),
                        const SizedBox(width: 4),
                        Text(
                          isCorrect ? 'Correct!' : 'Incorrect',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                  const Spacer(),
                  Text(
                    isCorrect ? '+${question['points']} pts' : '0 pts',
                    style: TextStyle(
                      color: isCorrect ? Colors.green : Colors.red,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Text(
                question['question_text'],
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF333333),
                ),
              ),
              // Question image
              if (mediaPath != null &&
                  mediaPath.toString().isNotEmpty &&
                  mediaType == 'image') ...[
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.network(
                    AuthService.fixImageUrl(
                      mediaPath.toString().startsWith('http')
                          ? mediaPath
                          : '${AuthService.storageUrl}/$mediaPath',
                    ),
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) =>
                        const SizedBox(),
                  ),
                ),
              ],
              // Question video
              if (mediaPath != null &&
                  mediaPath.toString().isNotEmpty &&
                  mediaType == 'video') ...[
                const SizedBox(height: 12),
                VideoPlayerWidget(
                  videoUrl: AuthService.fixImageUrl(
                    mediaPath.toString().startsWith('http')
                        ? mediaPath
                        : '${AuthService.storageUrl}/$mediaPath',
                  ),
                ),
              ],
              const SizedBox(height: 8),
              // Show correct answer
              Text(
                'Correct answer: ${correctOption?['option_text'] ?? 'Unknown'}',
                style: TextStyle(
                  color: Colors.green.shade700,
                  fontWeight: FontWeight.w600,
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),

        // True and False buttons with result highlights
        Row(
          children: options.asMap().entries.map((entry) {
            final option = Map<String, dynamic>.from(entry.value);
            final isTrue = option['option_text'] == 'True';
            final isCorrectOption = option['is_correct'] == true;
            final isSelected = selectedAnswerId == option['id'];

            Color bgColor = Colors.white;
            Color borderColor = Colors.grey.shade300;
            Color iconColor = isTrue ? Colors.green : Colors.red;
            Color textColor = isTrue ? Colors.green : Colors.red;

            if (isCorrectOption) {
              bgColor = Colors.green.withOpacity(0.1);
              borderColor = Colors.green;
            } else if (isSelected && !isCorrectOption) {
              bgColor = Colors.red.withOpacity(0.1);
              borderColor = Colors.red;
            }

            return Expanded(
              child: Container(
                margin: EdgeInsets.only(
                  right: isTrue ? 8 : 0,
                  left: isTrue ? 0 : 8,
                ),
                padding: const EdgeInsets.symmetric(vertical: 24),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: borderColor, width: 2),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(
                      isTrue ? Icons.check_circle : Icons.cancel,
                      color: iconColor,
                      size: 40,
                    ),
                    const SizedBox(height: 8),
                    Text(
                      option['option_text'],
                      style: TextStyle(
                        color: textColor,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    if (isCorrectOption)
                      const Text(
                        '✓ Correct',
                        style: TextStyle(
                          color: Colors.green,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    if (isSelected && !isCorrectOption)
                      const Text(
                        '✗ Your answer',
                        style: TextStyle(
                          color: Colors.red,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                  ],
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }
}