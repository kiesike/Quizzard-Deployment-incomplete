import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../widgets/image_picker_widget.dart';
import '../widgets/video_picker_widget.dart';
import '../widgets/video_player_widget.dart';

class AddQuestionScreen extends StatefulWidget {
  final int quizId;
  const AddQuestionScreen({super.key, required this.quizId});

  @override
  State<AddQuestionScreen> createState() => _AddQuestionScreenState();
}

class _AddQuestionScreenState extends State<AddQuestionScreen> {
  static const Color primaryColor = Color(0xFF6C63FF);

  String _selectedType = 'multiple_choice';
  bool _loading = false;

  // Common fields
  final _questionTextController = TextEditingController();
  final _pointsController = TextEditingController(text: '1');
  String? _questionMediaPath;
  String? _questionMediaType;
  String? _questionVideoUrl; // full URL for previewing uploaded question video

  // Multiple choice
  final List<TextEditingController> _mcOptions =
      List.generate(4, (_) => TextEditingController());
  final List<String?> _mcOptionImagePaths = List.generate(4, (_) => null);
  final List<String?> _mcOptionVideoPaths = List.generate(4, (_) => null);
  final List<String?> _mcOptionVideoUrls  = List.generate(4, (_) => null);
  int _mcCorrectIndex = 0;

  // True/False
  bool _tfCorrectAnswer = true;

  // Identification
  final _identAnswerController = TextEditingController();

  // Matching (4 pairs)
  final List<TextEditingController> _matchLeft =
      List.generate(4, (_) => TextEditingController());
  final List<TextEditingController> _matchRight =
      List.generate(4, (_) => TextEditingController());

  @override
  void dispose() {
    _questionTextController.dispose();
    _pointsController.dispose();
    for (var c in _mcOptions) c.dispose();
    _identAnswerController.dispose();
    for (var c in _matchLeft) c.dispose();
    for (var c in _matchRight) c.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (_questionTextController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
            content: Text('Question text is required.'),
            backgroundColor: Colors.red),
      );
      return;
    }

    setState(() => _loading = true);

    Map<String, dynamic> body = {};
    String endpoint = '';

    switch (_selectedType) {
      case 'multiple_choice':
        final options = _mcOptions.map((c) => c.text.trim()).toList();
        if (options.any((o) => o.isEmpty)) {
          setState(() => _loading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Fill in all 4 options.'),
                backgroundColor: Colors.red),
          );
          return;
        }
        body = {
          'question_text': _questionTextController.text.trim(),
          'media_path': _questionMediaPath,
          'media_type': _questionMediaType,
          'points': int.tryParse(_pointsController.text) ?? 1,
          'options': List.generate(4, (i) => {
                'option_text': options[i],
                'is_correct': i == _mcCorrectIndex,
                'image_path': _mcOptionImagePaths[i],
                'video_path': _mcOptionVideoPaths[i],
              }),
        };
        endpoint = '/quizzes/${widget.quizId}/questions/multiple-choice';
        break;

      case 'true_false':
        body = {
          'question_text': _questionTextController.text.trim(),
          'media_path': _questionMediaPath,
          'media_type': _questionMediaType,
          'points': int.tryParse(_pointsController.text) ?? 1,
          'correct_answer': _tfCorrectAnswer,
        };
        endpoint = '/quizzes/${widget.quizId}/questions/true-false';
        break;

      case 'identification':
        if (_identAnswerController.text.trim().isEmpty) {
          setState(() => _loading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Answer is required.'),
                backgroundColor: Colors.red),
          );
          return;
        }
        body = {
          'question_text': _questionTextController.text.trim(),
          'media_path': _questionMediaPath,
          'media_type': _questionMediaType,
          'points': int.tryParse(_pointsController.text) ?? 1,
          'answer': _identAnswerController.text.trim(),
        };
        endpoint = '/quizzes/${widget.quizId}/questions/identification';
        break;

      case 'matching':
        final lefts = _matchLeft.map((c) => c.text.trim()).toList();
        final rights = _matchRight.map((c) => c.text.trim()).toList();
        if (lefts.any((v) => v.isEmpty) || rights.any((v) => v.isEmpty)) {
          setState(() => _loading = false);
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
                content: Text('Fill in all 4 matching pairs.'),
                backgroundColor: Colors.red),
          );
          return;
        }
        body = {
          'question_text': _questionTextController.text.trim(),
          'media_path': _questionMediaPath,
          'media_type': _questionMediaType,
          'points': int.tryParse(_pointsController.text) ?? 1,
          'pairs': List.generate(4, (i) => {
                'left': lefts[i],
                'right': rights[i],
              }),
        };
        endpoint = '/quizzes/${widget.quizId}/questions/matching';
        break;
    }

    final result = await AuthService.authPost(endpoint, body);
    setState(() => _loading = false);

    if (result['success']) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Question added!'),
              backgroundColor: Colors.green),
        );
        Navigator.pop(context, true);
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        title: const Text('Add Question'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Text('Question Type',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
            const SizedBox(height: 8),
            _buildTypeSelector(),
            const SizedBox(height: 20),
            TextField(
              controller: _questionTextController,
              maxLines: 3,
              decoration: InputDecoration(
                labelText: 'Question Text *',
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide:
                      const BorderSide(color: primaryColor, width: 2),
                ),
              ),
            ),
            const SizedBox(height: 12),

            // Question image picker
            const Text('Question Image (optional)',
                style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                    color: Colors.grey)),
            const SizedBox(height: 4),
            ImagePickerWidget(
              label: 'Add image to question',
              onImageSelected: (path, url) => setState(() {
                _questionMediaPath = path;
                _questionMediaType = 'image';
                _questionVideoUrl = null;
              }),
            ),
            const SizedBox(height: 12),

            // Question video picker
            const Text('Question Video (optional)',
                style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                    color: Colors.grey)),
            const SizedBox(height: 4),
            VideoPickerWidget(
              onVideoUploaded: (videoUrl, videoPath) => setState(() {
                _questionMediaPath = videoPath;
                _questionMediaType = 'video';
                _questionVideoUrl = videoUrl;
              }),
              onVideoRemoved: () => setState(() {
                _questionMediaPath = null;
                _questionMediaType = null;
                _questionVideoUrl = null;
              }),
            ),
            if (_questionMediaType == 'video' && _questionVideoUrl != null) ...[
              const SizedBox(height: 8),
              VideoPlayerWidget(videoUrl: _questionVideoUrl!),
            ],
            const SizedBox(height: 16),

            TextField(
              controller: _pointsController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Points',
                prefixIcon: const Icon(Icons.star, color: primaryColor),
                border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12)),
                focusedBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide:
                      const BorderSide(color: primaryColor, width: 2),
                ),
              ),
            ),
            const SizedBox(height: 24),
            _buildTypeFields(),
            const SizedBox(height: 32),
            ElevatedButton(
              onPressed: _loading ? null : _submit,
              style: ElevatedButton.styleFrom(
                backgroundColor: primaryColor,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 16),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              child: _loading
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(
                          color: Colors.white, strokeWidth: 2))
                  : const Text('Add Question',
                      style: TextStyle(
                          fontSize: 16, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTypeSelector() {
    final types = [
      {'value': 'multiple_choice', 'label': 'Multiple Choice', 'icon': Icons.radio_button_checked},
      {'value': 'true_false', 'label': 'True / False', 'icon': Icons.check_circle_outline},
      {'value': 'identification', 'label': 'Identification', 'icon': Icons.edit},
      {'value': 'matching', 'label': 'Matching', 'icon': Icons.compare_arrows},
    ];

    return Wrap(
      spacing: 8,
      runSpacing: 8,
      children: types.map((t) {
        final selected = _selectedType == t['value'];
        return ChoiceChip(
          label: Text(t['label'] as String),
          avatar: Icon(t['icon'] as IconData,
              size: 16, color: selected ? Colors.white : primaryColor),
          selected: selected,
          selectedColor: primaryColor,
          labelStyle: TextStyle(
              color: selected ? Colors.white : Colors.black87,
              fontWeight: FontWeight.w600),
          onSelected: (_) =>
              setState(() => _selectedType = t['value'] as String),
        );
      }).toList(),
    );
  }

  Widget _buildTypeFields() {
    switch (_selectedType) {
      case 'multiple_choice':
        return _buildMCFields();
      case 'true_false':
        return _buildTFFields();
      case 'identification':
        return _buildIdentFields();
      case 'matching':
        return _buildMatchingFields();
      default:
        return const SizedBox();
    }
  }

  Widget _buildMCFields() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Answer Options',
            style: TextStyle(fontWeight: FontWeight.bold)),
        const Text('Tap the circle to mark the correct answer',
            style: TextStyle(fontSize: 12, color: Colors.grey)),
        const SizedBox(height: 12),
        ...List.generate(4, (i) => Padding(
              padding: const EdgeInsets.only(bottom: 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Radio<int>(
                        value: i,
                        groupValue: _mcCorrectIndex,
                        activeColor: primaryColor,
                        onChanged: (v) =>
                            setState(() => _mcCorrectIndex = v!),
                      ),
                      Expanded(
                        child: TextField(
                          controller: _mcOptions[i],
                          decoration: InputDecoration(
                            labelText: 'Option ${i + 1}',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Padding(
                    padding: const EdgeInsets.only(left: 48),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ImagePickerWidget(
                          label: 'Add image to option ${i + 1}',
                          onImageSelected: (path, url) => setState(
                              () => _mcOptionImagePaths[i] = path),
                        ),
                        const SizedBox(height: 6),
                        VideoPickerWidget(
                          onVideoUploaded: (videoUrl, videoPath) {
                            final index = i;
                            setState(() {
                              _mcOptionVideoPaths[index] = videoPath;
                              _mcOptionVideoUrls[index] = videoUrl;
                            });
                          },
                          onVideoRemoved: () {
                            final index = i;
                            setState(() {
                              _mcOptionVideoPaths[index] = null;
                              _mcOptionVideoUrls[index] = null;
                            });
                          },
                        ),
                        if (_mcOptionVideoUrls[i] != null) ...[
                          const SizedBox(height: 6),
                          VideoPlayerWidget(
                            videoUrl: _mcOptionVideoUrls[i]!,
                          ),
                        ],
                      ],
                    ),
                  ),
                ],
              ),
            )),
      ],
    );
  }

  Widget _buildTFFields() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Correct Answer',
            style: TextStyle(fontWeight: FontWeight.bold)),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _tfCorrectAnswer = true),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: _tfCorrectAnswer ? primaryColor : Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: primaryColor),
                  ),
                  child: Center(
                    child: Text('TRUE',
                        style: TextStyle(
                            color: _tfCorrectAnswer
                                ? Colors.white
                                : primaryColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 16)),
                  ),
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: GestureDetector(
                onTap: () => setState(() => _tfCorrectAnswer = false),
                child: Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: !_tfCorrectAnswer ? primaryColor : Colors.white,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: primaryColor),
                  ),
                  child: Center(
                    child: Text('FALSE',
                        style: TextStyle(
                            color: !_tfCorrectAnswer
                                ? Colors.white
                                : primaryColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 16)),
                  ),
                ),
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildIdentFields() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Correct Answer',
            style: TextStyle(fontWeight: FontWeight.bold)),
        const SizedBox(height: 12),
        TextField(
          controller: _identAnswerController,
          decoration: InputDecoration(
            labelText: 'Answer *',
            hintText: 'e.g. Filipino',
            border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12)),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide: const BorderSide(color: primaryColor, width: 2),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildMatchingFields() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Matching Pairs',
            style: TextStyle(fontWeight: FontWeight.bold)),
        const Text('Left column → Right column (correct pairs)',
            style: TextStyle(fontSize: 12, color: Colors.grey)),
        const SizedBox(height: 12),
        ...List.generate(
            4,
            (i) => Padding(
                  padding: const EdgeInsets.only(bottom: 12),
                  child: Row(
                    children: [
                      Expanded(
                        child: TextField(
                          controller: _matchLeft[i],
                          decoration: InputDecoration(
                            labelText: 'Left ${i + 1}',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 8),
                        child: Icon(Icons.arrow_forward, color: Colors.grey),
                      ),
                      Expanded(
                        child: TextField(
                          controller: _matchRight[i],
                          decoration: InputDecoration(
                            labelText: 'Right ${i + 1}',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                    ],
                  ),
                )),
      ],
    );
  }
}