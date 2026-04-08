import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../services/auth_service.dart';

class AudioPickerWidget extends StatefulWidget {
  final String? initialAudioUrl;
  final Function(String audioUrl, String audioPath) onAudioUploaded;
  final VoidCallback? onAudioRemoved;

  const AudioPickerWidget({
    super.key,
    this.initialAudioUrl,
    required this.onAudioUploaded,
    this.onAudioRemoved,
  });

  @override
  State<AudioPickerWidget> createState() => _AudioPickerWidgetState();
}

class _AudioPickerWidgetState extends State<AudioPickerWidget> {
  String? _audioUrl;
  bool _isUploading = false;

  @override
  void initState() {
    super.initState();
    _audioUrl = widget.initialAudioUrl;
  }

  Future<void> _pickAndUploadAudio() async {
    // Open file picker restricted to mp3 only
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['mp3'],
    );

    if (result == null || result.files.isEmpty) return;

    final file = File(result.files.single.path!);
    final fileName = result.files.single.name;

    // Double check extension
    if (!fileName.toLowerCase().endsWith('.mp3')) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Only .mp3 audio files are supported.'),
            backgroundColor: Colors.red,
          ),
        );
      }
      return;
    }

    setState(() => _isUploading = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      final token = prefs.getString('token') ?? '';

      final uri = Uri.parse('${AuthService.baseUrl}/upload-audio');
      final request = http.MultipartRequest('POST', uri);

      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      request.files.add(await http.MultipartFile.fromPath(
        'audio',
        file.path,
      ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = AuthService.parseJson(response.body);
        final audioUrl = AuthService.fixImageUrl(data['audio_url']);
        final audioPath = data['audio_path'];

        setState(() => _audioUrl = audioUrl);
        widget.onAudioUploaded(audioUrl, audioPath);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Audio uploaded successfully!'),
              backgroundColor: Colors.green,
            ),
          );
        }
      } else {
        final data = AuthService.parseJson(response.body);
        throw Exception(data['message'] ?? 'Upload failed');
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to upload audio: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() => _isUploading = false);
    }
  }

  void _removeAudio() {
    setState(() => _audioUrl = null);
    widget.onAudioRemoved?.call();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (_audioUrl != null) ...[
          // Show audio attached indicator
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.purple.shade50,
              border: Border.all(color: Colors.purple.shade200),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                const Icon(Icons.audio_file, color: Color(0xFF6C63FF)),
                const SizedBox(width: 8),
                const Expanded(
                  child: Text(
                    'Audio attached',
                    style: TextStyle(color: Color(0xFF6C63FF)),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.red),
                  onPressed: _removeAudio,
                  tooltip: 'Remove audio',
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
        ],
        // Upload button
        SizedBox(
          width: double.infinity,
          child: OutlinedButton.icon(
            onPressed: _isUploading ? null : _pickAndUploadAudio,
            icon: _isUploading
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.audio_file),
            label: Text(_isUploading
                ? 'Uploading...'
                : _audioUrl != null
                    ? 'Replace Audio'
                    : 'Attach Audio (.mp3)'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
      ],
    );
  }
}