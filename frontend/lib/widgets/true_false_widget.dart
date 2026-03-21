import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'video_player_widget.dart';


class TrueFalseWidget extends StatefulWidget {
  final Map<String, dynamic> question;
  final Function(int?) onAnswerSelected;
  final int? selectedAnswerId;

  const TrueFalseWidget({
    super.key,
    required this.question,
    required this.onAnswerSelected,
    this.selectedAnswerId,
  });

  @override
  State<TrueFalseWidget> createState() => _TrueFalseWidgetState();
}

class _TrueFalseWidgetState extends State<TrueFalseWidget> {
  int? _selectedOptionId;

  @override
  void initState() {
    super.initState();
    _selectedOptionId = widget.selectedAnswerId;
  }

  @override
  Widget build(BuildContext context) {
    final options = widget.question['answer_options'] as List;
    final mediaPath = widget.question['media_path'];
    final mediaType = widget.question['media_type'];

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
                      'True or False',
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
              if (mediaPath != null && mediaPath.toString().isNotEmpty && mediaType == 'image')...[
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
                    errorBuilder: (context, error, stackTrace) => const SizedBox(),
                  ),
                ),
              ],
              if (mediaPath != null && mediaPath.toString().isNotEmpty && mediaType == 'video') ...[
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
        const SizedBox(height: 24),

        // True and False buttons
        Row(
          children: options.map((option) {
            final isTrue = option['option_text'] == 'True';
            final isSelected = _selectedOptionId == option['id'];
            final color = isTrue ? Colors.green : Colors.red;

            return Expanded(
              child: GestureDetector(
                onTap: () {
                  setState(() => _selectedOptionId = option['id']);
                  widget.onAnswerSelected(option['id']);
                },
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  margin: EdgeInsets.only(
                    right: isTrue ? 8 : 0,
                    left: isTrue ? 0 : 8,
                  ),
                  padding: const EdgeInsets.symmetric(vertical: 30),
                  decoration: BoxDecoration(
                    color: isSelected ? color : Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isSelected ? color : Colors.grey.shade300,
                      width: isSelected ? 2 : 1,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: isSelected
                            ? color.withOpacity(0.3)
                            : Colors.black.withOpacity(0.05),
                        blurRadius: 8,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        isTrue ? Icons.check_circle : Icons.cancel,
                        color: isSelected ? Colors.white : color,
                        size: 48,
                      ),
                      const SizedBox(height: 12),
                      Text(
                        option['option_text'],
                        style: TextStyle(
                          color: isSelected ? Colors.white : color,
                          fontSize: 22,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }
}