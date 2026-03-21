import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'video_player_widget.dart';

class MultipleChoiceResultWidget extends StatelessWidget {
  final Map<String, dynamic> question;
  final int? selectedAnswerId;

  const MultipleChoiceResultWidget({
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
        // Question container
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
            ],
          ),
        ),
        const SizedBox(height: 12),

        // Options with correct/wrong highlights
        ...options.asMap().entries.map((entry) {
          final index = entry.key;
          final option = Map<String, dynamic>.from(entry.value);
          final isCorrectOption = option['is_correct'] == true;
          final isSelected = selectedAnswerId == option['id'];

          Color bgColor = Colors.white;
          Color borderColor = Colors.grey.shade300;
          Color textColor = const Color(0xFF333333);
          IconData? icon;
          Color? iconColor;

          if (isCorrectOption) {
            bgColor = Colors.green.withOpacity(0.1);
            borderColor = Colors.green;
            textColor = Colors.green.shade700;
            icon = Icons.check_circle;
            iconColor = Colors.green;
          } else if (isSelected && !isCorrectOption) {
            bgColor = Colors.red.withOpacity(0.1);
            borderColor = Colors.red;
            textColor = Colors.red.shade700;
            icon = Icons.cancel;
            iconColor = Colors.red;
          }

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: bgColor,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: borderColor),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    if (icon != null)
                      Icon(icon, color: iconColor, size: 20)
                    else
                      Container(
                        width: 20,
                        height: 20,
                        decoration: BoxDecoration(
                          color: Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: Colors.grey.shade300),
                        ),
                        child: Center(
                          child: Text(
                            String.fromCharCode(65 + index),
                            style: TextStyle(
                              color: Colors.grey.shade600,
                              fontSize: 10,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        option['option_text'],
                        style: TextStyle(
                          color: textColor,
                          fontWeight: isCorrectOption || isSelected
                              ? FontWeight.bold
                              : FontWeight.normal,
                        ),
                      ),
                    ),
                  ],
                ),
                // Option image
                if (option['image_path'] != null &&
                    option['image_path'].toString().isNotEmpty) ...[
                  const SizedBox(height: 8),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(8),
                    child: Image.network(
                      AuthService.fixImageUrl(
                        option['image_path'].toString().startsWith('http')
                            ? option['image_path']
                            : '${AuthService.storageUrl}/${option['image_path']}',
                      ),
                      width: double.infinity,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) =>
                          const SizedBox(),
                    ),
                  ),
                ],
                // Option video
                if (option['video_path'] != null &&
                    option['video_path'].toString().isNotEmpty) ...[
                  const SizedBox(height: 8),
                  VideoPlayerWidget(
                    videoUrl: AuthService.fixImageUrl(
                      option['video_path'].toString().startsWith('http')
                          ? option['video_path']
                          : '${AuthService.storageUrl}/${option['video_path']}',
                    ),
                  ),
                ],
              ],
            ),
          );
        }),
      ],
    );
  }
}