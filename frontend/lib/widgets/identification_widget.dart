import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import 'video_player_widget.dart';

class IdentificationWidget extends StatefulWidget {
  final Map<String, dynamic> question;
  final Function(String) onAnswerChanged;
  final String? currentAnswer;

  const IdentificationWidget({
    super.key,
    required this.question,
    required this.onAnswerChanged,
    this.currentAnswer,
  });

  @override
  State<IdentificationWidget> createState() => _IdentificationWidgetState();
}

class _IdentificationWidgetState extends State<IdentificationWidget> {
  late TextEditingController _controller;

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: widget.currentAnswer ?? '');
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
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
                      'Identification',
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
        const SizedBox(height: 20),

        // Answer input
        const Text(
          'Your Answer:',
          style: TextStyle(
            fontWeight: FontWeight.w600,
            color: Color(0xFF333333),
            fontSize: 15,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: _controller,
          onChanged: widget.onAnswerChanged,
          textCapitalization: TextCapitalization.words,
          decoration: InputDecoration(
            hintText: 'Type your answer here...',
            prefixIcon: const Icon(
              Icons.edit,
              color: Color(0xFF6C63FF),
            ),
            filled: true,
            fillColor: Colors.white,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide.none,
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:
                  const BorderSide(color: Color(0xFF6C63FF), width: 2),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: BorderSide(color: Colors.grey.shade300),
            ),
          ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Icon(Icons.info_outline,
                size: 14, color: Colors.grey.shade500),
            const SizedBox(width: 4),
            Text(
              'Answer is not case-sensitive',
              style:
                  TextStyle(fontSize: 12, color: Colors.grey.shade500),
            ),
          ],
        ),
      ],
    );
  }
}