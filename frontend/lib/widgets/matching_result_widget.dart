import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'video_player_widget.dart';

class MatchingResultWidget extends StatelessWidget {
  final Map<String, dynamic> question;
  final Map<String, String>? studentAnswers;

  const MatchingResultWidget({
    super.key,
    required this.question,
    this.studentAnswers,
  });

  @override
  Widget build(BuildContext context) {
    final options = question['answer_options'] as List;
    final mediaPath = question['media_path'];
    final mediaType = question['media_type'];

    int correctCount = 0;
    for (var option in options) {
      final columnA = option['option_text'].toString();
      final correctB = option['match_pair'].toString();
      final studentB = studentAnswers?[columnA] ?? '';
      if (studentB.trim().toLowerCase() ==
          correctB.trim().toLowerCase()) {
        correctCount++;
      }
    }

    final totalPairs = options.length;
    final pointsPerPair = (question['points'] as int) ~/ totalPairs;
    final earnedPoints = correctCount * pointsPerPair;
    final allCorrect = correctCount == totalPairs;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Header with score
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: allCorrect
                ? Colors.green.withOpacity(0.05)
                : correctCount > 0
                    ? Colors.orange.withOpacity(0.05)
                    : Colors.red.withOpacity(0.05),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: allCorrect
                  ? Colors.green.withOpacity(0.3)
                  : correctCount > 0
                      ? Colors.orange.withOpacity(0.3)
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
                      color: allCorrect
                          ? Colors.green
                          : correctCount > 0
                              ? Colors.orange
                              : Colors.red,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      allCorrect
                          ? 'Perfect!'
                          : '$correctCount/$totalPairs correct',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                  ),
                  const Spacer(),
                  Text(
                    '+$earnedPoints pts',
                    style: TextStyle(
                      color: allCorrect ? Colors.green : Colors.orange,
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
        const SizedBox(height: 16),

        // Results for each pair
        ...options.asMap().entries.map((entry) {
          final option = Map<String, dynamic>.from(entry.value);
          final columnA = option['option_text'].toString();
          final correctB = option['match_pair'].toString();
          final studentB = studentAnswers?[columnA] ?? '';
          final isPairCorrect = studentB.trim().toLowerCase() ==
              correctB.trim().toLowerCase();

          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: isPairCorrect
                  ? Colors.green.withOpacity(0.05)
                  : Colors.red.withOpacity(0.05),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: isPairCorrect ? Colors.green : Colors.red,
              ),
            ),
            child: Row(
              children: [
                Icon(
                  isPairCorrect ? Icons.check_circle : Icons.cancel,
                  color: isPairCorrect ? Colors.green : Colors.red,
                  size: 20,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        columnA,
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 13,
                          color: Color(0xFF333333),
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Text(
                            'Your answer: ',
                            style:
                                TextStyle(fontSize: 12, color: Colors.grey),
                          ),
                          Text(
                            studentB.isEmpty ? '(no answer)' : studentB,
                            style: TextStyle(
                              fontSize: 12,
                              color:
                                  isPairCorrect ? Colors.green : Colors.red,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                      if (!isPairCorrect) ...[
                        const SizedBox(height: 2),
                        Row(
                          children: [
                            const Text(
                              'Correct: ',
                              style: TextStyle(
                                  fontSize: 12, color: Colors.grey),
                            ),
                            Text(
                              correctB,
                              style: const TextStyle(
                                fontSize: 12,
                                color: Colors.green,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ],
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