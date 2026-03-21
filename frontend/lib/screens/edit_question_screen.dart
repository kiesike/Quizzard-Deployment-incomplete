import 'package:flutter/material.dart';
import '../services/auth_service.dart';
import '../widgets/image_picker_widget.dart';

class EditQuestionScreen extends StatefulWidget {
  final int quizId;
  final Map<String, dynamic> question;

  const EditQuestionScreen({
    super.key,
    required this.quizId,
    required this.question,
  });

  @override
  State<EditQuestionScreen> createState() => _EditQuestionScreenState();
}

class _EditQuestionScreenState extends State<EditQuestionScreen> {
  static const Color primaryColor = Color(0xFF6C63FF);

  late String _questionType;
  bool _loading = false;

  // Common
  late TextEditingController _questionTextController;
  late TextEditingController _pointsController;
  String? _questionImagePath;
  String? _questionImageUrl;

  // Multiple choice
  late List<TextEditingController> _mcOptions;
  final List<String?> _mcOptionImagePaths = List.generate(4, (_) => null);
  final List<String?> _mcOptionImageUrls = List.generate(4, (_) => null);
  int _mcCorrectIndex = 0;

  // True/False
  bool _tfCorrectAnswer = true;

  // Identification
  late TextEditingController _identAnswerController;

  // Matching
  late List<TextEditingController> _matchLeft;
  late List<TextEditingController> _matchRight;

  @override
  void initState() {
    super.initState();
    _questionType = widget.question['question_type'] ?? 'multiple_choice';

    _questionTextController = TextEditingController(
      text: widget.question['question_text'] ?? '',
    );
    _pointsController = TextEditingController(
      text: '${widget.question['points'] ?? 1}',
    );

    // Load existing question image
    final existingMediaPath = widget.question['media_path'];
    if (existingMediaPath != null && existingMediaPath.toString().isNotEmpty) {
      _questionImageUrl = existingMediaPath.toString().startsWith('http')
          ? existingMediaPath
          : '${AuthService.storageUrl}/$existingMediaPath';
      _questionImagePath = existingMediaPath;
    }

    final options = List<Map<String, dynamic>>.from(
      widget.question['answer_options'] ?? [],
    );

    // Multiple choice
    _mcOptions = List.generate(4, (i) {
      return TextEditingController(
        text: i < options.length ? (options[i]['option_text'] ?? '') : '',
      );
    });
    for (int i = 0; i < options.length; i++) {
      if (options[i]['is_correct'] == true || options[i]['is_correct'] == 1) {
        _mcCorrectIndex = i;
      }
      // Load existing option images
      final optionImage = options[i]['image_path'];
      if (optionImage != null && optionImage.toString().isNotEmpty) {
        _mcOptionImageUrls[i] = optionImage.toString().startsWith('http')
            ? optionImage
            : '${AuthService.storageUrl}/$optionImage';
        _mcOptionImagePaths[i] = optionImage;
      }
    }

    // True/False
    if (_questionType == 'true_false') {
      final trueOption = options.firstWhere(
        (o) => (o['option_text'] ?? '').toString().toLowerCase() == 'true',
        orElse: () => {},
      );
      _tfCorrectAnswer = trueOption['is_correct'] == true ||
          trueOption['is_correct'] == 1;
    }

    // Identification
    _identAnswerController = TextEditingController(
      text: options.isNotEmpty ? (options[0]['option_text'] ?? '') : '',
    );

    // Matching
    _matchLeft = List.generate(4, (i) => TextEditingController(
      text: i < options.length ? (options[i]['option_text'] ?? '') : '',
    ));
    _matchRight = List.generate(4, (i) => TextEditingController(
      text: i < options.length ? (options[i]['match_pair'] ?? '') : '',
    ));
  }

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

    Map<String, dynamic> body = {
      'question_text': _questionTextController.text.trim(),
      'points': int.tryParse(_pointsController.text) ?? 1,
      'media_path': _questionImagePath,
    };

    switch (_questionType) {
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
        body['options'] = List.generate(4, (i) => {
          'option_text': options[i],
          'is_correct': i == _mcCorrectIndex,
          'image_path': _mcOptionImagePaths[i],
        });
        break;

      case 'true_false':
        body['correct_answer'] = _tfCorrectAnswer;
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
        body['answer'] = _identAnswerController.text.trim();
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
        body['pairs'] = List.generate(4, (i) => {
          'left': lefts[i],
          'right': rights[i],
        });
        break;
    }

    final questionId = widget.question['id'];
    final result = await AuthService.authPut(
      '/quizzes/${widget.quizId}/questions/$questionId',
      body,
    );

    setState(() => _loading = false);

    if (result['success']) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
              content: Text('Question updated!'),
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

  String _questionTypeLabel(String type) {
    switch (type) {
      case 'multiple_choice': return 'Multiple Choice';
      case 'true_false':      return 'True / False';
      case 'identification':  return 'Identification';
      case 'matching':        return 'Matching';
      default:                return type;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: primaryColor,
        foregroundColor: Colors.white,
        title: Text('Edit ${_questionTypeLabel(_questionType)}'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // Question type label
            Container(
              padding:
                  const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: primaryColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
                border:
                    Border.all(color: primaryColor.withOpacity(0.3)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline,
                      size: 16, color: primaryColor),
                  const SizedBox(width: 8),
                  Text(
                    'Type: ${_questionTypeLabel(_questionType)}',
                    style: const TextStyle(
                        color: primaryColor,
                        fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),

            // Question text
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

            // Question image
            const Text('Question Image (optional)',
                style: TextStyle(
                    fontWeight: FontWeight.w600,
                    fontSize: 13,
                    color: Colors.grey)),
            const SizedBox(height: 8),
            ImagePickerWidget(
              currentImageUrl: _questionImageUrl,
              label: 'Add image to question',
              onImageSelected: (path, url) => setState(() {
                _questionImagePath = path;
                _questionImageUrl = url;
              }),
            ),
            const SizedBox(height: 16),

            // Points
            TextField(
              controller: _pointsController,
              keyboardType: TextInputType.number,
              decoration: InputDecoration(
                labelText: 'Points',
                prefixIcon:
                    const Icon(Icons.star, color: primaryColor),
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

            // Type-specific fields
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
                  : const Text('Save Changes',
                      style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTypeFields() {
    switch (_questionType) {
      case 'multiple_choice': return _buildMCFields();
      case 'true_false':      return _buildTFFields();
      case 'identification':  return _buildIdentFields();
      case 'matching':        return _buildMatchingFields();
      default:                return const SizedBox();
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
                                borderRadius:
                                    BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Padding(
                    padding: const EdgeInsets.only(left: 48),
                    child: ImagePickerWidget(
                      currentImageUrl: _mcOptionImageUrls[i],
                      label: 'Add image to option ${i + 1}',
                      onImageSelected: (path, url) => setState(() {
                        _mcOptionImagePaths[i] = path;
                        _mcOptionImageUrls[i] = url;
                      }),
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
                    color:
                        _tfCorrectAnswer ? primaryColor : Colors.white,
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
                    color:
                        !_tfCorrectAnswer ? primaryColor : Colors.white,
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
            border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12)),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(12),
              borderSide:
                  const BorderSide(color: primaryColor, width: 2),
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
        const Text('Left column → Right column',
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
                                borderRadius:
                                    BorderRadius.circular(10)),
                          ),
                        ),
                      ),
                      const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 8),
                        child: Icon(Icons.arrow_forward,
                            color: Colors.grey),
                      ),
                      Expanded(
                        child: TextField(
                          controller: _matchRight[i],
                          decoration: InputDecoration(
                            labelText: 'Right ${i + 1}',
                            border: OutlineInputBorder(
                              borderRadius:
                                  BorderRadius.circular(10)
                                ),
                          ),
                        ),
                      ),
                    ],
                  ),
                )
              ),
      ],
    );
  }
}