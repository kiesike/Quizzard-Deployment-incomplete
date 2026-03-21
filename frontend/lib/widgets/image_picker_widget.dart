import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import '../services/auth_service.dart';

class ImagePickerWidget extends StatefulWidget {
  final String? currentImageUrl;
  final Function(String? imagePath, String? imageUrl) onImageSelected;
  final String label;

  const ImagePickerWidget({
    super.key,
    this.currentImageUrl,
    required this.onImageSelected,
    this.label = 'Add Image',
  });

  @override
  State<ImagePickerWidget> createState() => _ImagePickerWidgetState();
}

class _ImagePickerWidgetState extends State<ImagePickerWidget> {
  bool _isUploading = false;
  String? _imageUrl;

  @override
  void initState() {
    super.initState();
    _imageUrl = widget.currentImageUrl;
  }

  Future<void> _pickImage(ImageSource source) async {
    try {
      final picker = ImagePicker();
      final pickedFile = await picker.pickImage(
        source: source,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 80,
      );

      if (pickedFile == null) return;

      setState(() => _isUploading = true);

      // Convert to base64
      final bytes = await File(pickedFile.path).readAsBytes();
      final base64String = base64Encode(bytes);

      // Upload to server
      final result = await AuthService.authPost('/upload-image', {
        'image': base64String,
        'type': 'question',
      });

      setState(() => _isUploading = false);

      if (result['success']) {
        final imagePath = result['data']['image_path'];
        final imageUrl = result['data']['image_url'];
        setState(() => _imageUrl = imageUrl);
        widget.onImageSelected(imagePath, imageUrl);
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(result['message']),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      setState(() => _isUploading = false);
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Failed to pick image: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  void _showImageOptions() {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Text(
              'Add Image',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 20),
            ListTile(
              onTap: () {
                Navigator.pop(context);
                _pickImage(ImageSource.gallery);
              },
              leading: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: const Color(0xFF6C63FF).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.photo_library,
                    color: Color(0xFF6C63FF)),
              ),
              title: const Text('Choose from Gallery'),
            ),
            ListTile(
              onTap: () {
                Navigator.pop(context);
                _pickImage(ImageSource.camera);
              },
              leading: Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: const Color(0xFF4CAF50).withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.camera_alt,
                    color: Color(0xFF4CAF50)),
              ),
              title: const Text('Take a Photo'),
            ),
            if (_imageUrl != null) ...[
              ListTile(
                onTap: () {
                  Navigator.pop(context);
                  setState(() => _imageUrl = null);
                  widget.onImageSelected(null, null);
                },
                leading: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Colors.red.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: const Icon(Icons.delete, color: Colors.red),
                ),
                title: const Text('Remove Image',
                    style: TextStyle(color: Colors.red)),
              ),
            ],
            const SizedBox(height: 8),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isUploading) {
      return Container(
        height: 120,
        decoration: BoxDecoration(
          color: Colors.grey.shade100,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.grey.shade300),
        ),
        child: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              CircularProgressIndicator(color: Color(0xFF6C63FF)),
              SizedBox(height: 8),
              Text('Uploading image...'),
            ],
          ),
        ),
      );
    }

    if (_imageUrl != null) {
      return Stack(
        children: [
          ClipRRect(
            borderRadius: BorderRadius.circular(12),
            child: Image.network(
              _imageUrl!,
              height: 180,
              width: double.infinity,
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) => Container(
                height: 180,
                color: Colors.grey.shade200,
                child: const Center(
                  child: Icon(Icons.broken_image,
                      size: 40, color: Colors.grey),
                ),
              ),
            ),
          ),
          Positioned(
            top: 8,
            right: 8,
            child: GestureDetector(
              onTap: _showImageOptions,
              child: Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.black.withOpacity(0.6),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Icon(Icons.edit,
                    color: Colors.white, size: 18),
              ),
            ),
          ),
        ],
      );
    }

    return GestureDetector(
      onTap: _showImageOptions,
      child: Container(
        height: 100,
        decoration: BoxDecoration(
          color: Colors.grey.shade100,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: const Color(0xFF6C63FF).withOpacity(0.3),
            style: BorderStyle.solid,
          ),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(Icons.add_photo_alternate,
                size: 32, color: Colors.grey.shade400),
            const SizedBox(height: 8),
            Text(
              widget.label,
              style: TextStyle(
                color: Colors.grey.shade500,
                fontSize: 13,
              ),
            ),
          ],
        ),
      ),
    );
  }
}