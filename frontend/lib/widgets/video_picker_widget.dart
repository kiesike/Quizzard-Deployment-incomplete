import 'dart:io';
import 'package:flutter/material.dart';
import 'package:file_picker/file_picker.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../services/auth_service.dart';

class VideoPickerWidget extends StatefulWidget {
  final String? initialVideoUrl;
  final Function(String videoUrl, String videoPath) onVideoUploaded;
  final VoidCallback? onVideoRemoved;

  const VideoPickerWidget({
    super.key,
    this.initialVideoUrl,
    required this.onVideoUploaded,
    this.onVideoRemoved,
  });

  @override
  State<VideoPickerWidget> createState() => _VideoPickerWidgetState();
}

class _VideoPickerWidgetState extends State<VideoPickerWidget> {
  String? _videoUrl;
  bool _isUploading = false;

  @override
  @override
  void initState() {
    super.initState();
    _videoUrl = widget.initialVideoUrl != null
        ? AuthService.fixImageUrl(widget.initialVideoUrl!)
        : null;
  }

  Future<void> _pickAndUploadVideo() async {
    // Open file picker restricted to mp4 only
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['mp4'],
    );

    if (result == null || result.files.isEmpty) return;

    final file = File(result.files.single.path!);
    final fileName = result.files.single.name;

    // Double check extension
    if (!fileName.toLowerCase().endsWith('.mp4')) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Only .mp4 video files are supported.'),
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

      final uri = Uri.parse('${AuthService.baseUrl}/upload-video');
      final request = http.MultipartRequest('POST', uri);

      request.headers['Authorization'] = 'Bearer $token';
      request.headers['Accept'] = 'application/json';

      request.files.add(await http.MultipartFile.fromPath(
        'video',
        file.path,
      ));

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);

      if (response.statusCode == 200) {
        final data = AuthService.parseJson(response.body);
        final videoUrl = AuthService.fixImageUrl(data['video_url']);
        final videoPath = data['video_path'];

        setState(() => _videoUrl = videoUrl);
        widget.onVideoUploaded(videoUrl, videoPath);

        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('Video uploaded successfully!'),
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
            content: Text('Failed to upload video: $e'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } finally {
      setState(() => _isUploading = false);
    }
  }

  void _removeVideo() {
    setState(() => _videoUrl = null);
    widget.onVideoRemoved?.call();
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        if (_videoUrl != null) ...[
          // Show video attached indicator
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.green.shade50,
              border: Border.all(color: Colors.green.shade200),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Row(
              children: [
                const Icon(Icons.videocam, color: Colors.green),
                const SizedBox(width: 8),
                const Expanded(
                  child: Text(
                    'Video attached',
                    style: TextStyle(color: Colors.green),
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close, color: Colors.red),
                  onPressed: _removeVideo,
                  tooltip: 'Remove video',
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
            onPressed: _isUploading ? null : _pickAndUploadVideo,
            icon: _isUploading
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(strokeWidth: 2),
                  )
                : const Icon(Icons.video_library),
            label: Text(_isUploading
                ? 'Uploading...'
                : _videoUrl != null
                    ? 'Replace Video'
                    : 'Attach Video (.mp4)'),
            style: OutlinedButton.styleFrom(
              padding: const EdgeInsets.symmetric(vertical: 12),
            ),
          ),
        ),
      ],
    );
  }
}