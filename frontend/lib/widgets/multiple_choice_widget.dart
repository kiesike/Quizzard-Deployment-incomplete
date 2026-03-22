import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'video_player_widget.dart';
import 'audio_player_widget.dart';

class MultipleChoiceWidget extends StatefulWidget {
  final Map<String, dynamic> question;
  final Function(int?) onAnswerSelected;
  final int? selectedAnswerId;

  const MultipleChoiceWidget({
    super.key,
    required this.question,
    required this.onAnswerSelected,
    this.selectedAnswerId,
  });

  @override
  State<MultipleChoiceWidget> createState() => _MultipleChoiceWidgetState();
}

class _MultipleChoiceWidgetState extends State<MultipleChoiceWidget> {
  int? _selectedOptionId;

  @override
  void initState() {
    super.initState();
    _selectedOptionId = widget.selectedAnswerId;
  }

  @override
  Widget build(BuildContext context) {
    final options = widget.question['answer_options'] as List;
    final imagePath = widget.question['image_path'];
    final videoPath = widget.question['video_path'];
    final audioPath = widget.question['audio_path'];

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
                      'Multiple Choice',
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
              if (imagePath != null && imagePath.toString().isNotEmpty) ...[
                const SizedBox(height: 12),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: Image.network(
                    AuthService.fixImageUrl(imagePath),
                    width: double.infinity,
                    fit: BoxFit.cover,
                    errorBuilder: (context, error, stackTrace) =>
                        const SizedBox(),
                  ),
                ),
              ],
              // Question video
              if (videoPath != null && videoPath.toString().isNotEmpty) ...[
                const SizedBox(height: 12),
                VideoPlayerWidget(
                  videoUrl: AuthService.fixImageUrl(videoPath),
                ),
              ],
              // Question audio
              if (audioPath != null && audioPath.toString().isNotEmpty) ...[
                const SizedBox(height: 12),
                AudioPlayerWidget(
                  audioUrl: AuthService.fixImageUrl(audioPath),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: 16),

        // Answer options
        ...options.map((option) {
          final isSelected = _selectedOptionId == option['id'];
          final optionImage = option['image_path'];
          final optionVideo = option['video_path'];
          final optionAudio = option['audio_path'];

          return GestureDetector(
            onTap: () {
              setState(() => _selectedOptionId = option['id']);
              widget.onAnswerSelected(option['id']);
            },
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isSelected ? const Color(0xFF6C63FF) : Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                  color: isSelected
                      ? const Color(0xFF6C63FF)
                      : Colors.grey.shade300,
                  width: isSelected ? 2 : 1,
                ),
                boxShadow: [
                  BoxShadow(
                    color: isSelected
                        ? const Color(0xFF6C63FF).withOpacity(0.3)
                        : Colors.black.withOpacity(0.05),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  ),
                ],
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        width: 28,
                        height: 28,
                        decoration: BoxDecoration(
                          color: isSelected
                              ? Colors.white
                              : Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: isSelected
                                ? Colors.white
                                : Colors.grey.shade300,
                          ),
                        ),
                        child: isSelected
                            ? const Icon(Icons.check,
                                size: 16, color: Color(0xFF6C63FF))
                            : Center(
                                child: Text(
                                  String.fromCharCode(
                                      65 + options.indexOf(option)),
                                  style: TextStyle(
                                    color: Colors.grey.shade600,
                                    fontWeight: FontWeight.bold,
                                    fontSize: 12,
                                  ),
                                ),
                              ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          option['option_text'],
                          style: TextStyle(
                            color: isSelected
                                ? Colors.white
                                : const Color(0xFF333333),
                            fontSize: 15,
                            fontWeight: isSelected
                                ? FontWeight.bold
                                : FontWeight.normal,
                          ),
                        ),
                      ),
                    ],
                  ),
                  // Option image
                  if (optionImage != null &&
                      optionImage.toString().isNotEmpty) ...[
                    const SizedBox(height: 10),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(8),
                      child: Image.network(
                        AuthService.fixImageUrl(optionImage),
                        width: double.infinity,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) =>
                            const SizedBox(),
                      ),
                    ),
                  ],
                  // Option video
                  if (optionVideo != null &&
                      optionVideo.toString().isNotEmpty) ...[
                    const SizedBox(height: 10),
                    VideoPlayerWidget(
                      videoUrl: AuthService.fixImageUrl(optionVideo),
                    ),
                  ],
                  // Option audio
                  if (optionAudio != null &&
                      optionAudio.toString().isNotEmpty) ...[
                    const SizedBox(height: 10),
                    AudioPlayerWidget(
                      audioUrl: AuthService.fixImageUrl(optionAudio),
                    ),
                  ],
                ],
              ),
            ),
          );
        }),
      ],
    );
  }
}