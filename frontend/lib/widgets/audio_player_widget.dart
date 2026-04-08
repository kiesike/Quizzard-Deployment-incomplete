import 'package:flutter/material.dart';
import 'package:audioplayers/audioplayers.dart';

class AudioPlayerWidget extends StatefulWidget {
  final String audioUrl;

  const AudioPlayerWidget({
    super.key,
    required this.audioUrl,
  });

  @override
  State<AudioPlayerWidget> createState() => _AudioPlayerWidgetState();
}

class _AudioPlayerWidgetState extends State<AudioPlayerWidget> {
  final AudioPlayer _player = AudioPlayer();
  PlayerState _playerState = PlayerState.stopped;
  Duration _duration = Duration.zero;
  Duration _position = Duration.zero;
  bool _hasError = false;

  @override
  void initState() {
    super.initState();
    _setupListeners();
  }

  void _setupListeners() {
    _player.onPlayerStateChanged.listen((state) {
      if (mounted) setState(() => _playerState = state);
    });

    _player.onDurationChanged.listen((duration) {
      if (mounted) setState(() => _duration = duration);
    });

    _player.onPositionChanged.listen((position) {
      if (mounted) setState(() => _position = position);
    });

    _player.onPlayerComplete.listen((_) {
      if (mounted) {
        setState(() {
          _playerState = PlayerState.stopped;
          _position = Duration.zero;
        });
      }
    });
  }

  @override
  void dispose() {
    _player.dispose();
    super.dispose();
  }

  Future<void> _togglePlayPause() async {
    try {
      if (_playerState == PlayerState.playing) {
        await _player.pause();
      } else {
        await _player.play(UrlSource(widget.audioUrl));
      }
    } catch (e) {
      if (mounted) setState(() => _hasError = true);
    }
  }

  Future<void> _replay() async {
    try {
      await _player.seek(Duration.zero);
      await _player.play(UrlSource(widget.audioUrl));
    } catch (e) {
      if (mounted) setState(() => _hasError = true);
    }
  }

  String _formatDuration(Duration duration) {
    final minutes = duration.inMinutes.remainder(60).toString().padLeft(2, '0');
    final seconds = duration.inSeconds.remainder(60).toString().padLeft(2, '0');
    return '$minutes:$seconds';
  }

  @override
  Widget build(BuildContext context) {
    if (_hasError) {
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: Colors.red.shade50,
          border: Border.all(color: Colors.red.shade200),
          borderRadius: BorderRadius.circular(8),
        ),
        child: const Row(
          children: [
            Icon(Icons.error_outline, color: Colors.red),
            SizedBox(width: 8),
            Text('Failed to load audio', style: TextStyle(color: Colors.red)),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFF6C63FF).withOpacity(0.08),
        border: Border.all(color: const Color(0xFF6C63FF).withOpacity(0.3)),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        children: [
          Row(
            children: [
              // Play/Pause button
              IconButton(
                icon: Icon(
                  _playerState == PlayerState.playing
                      ? Icons.pause_circle_filled
                      : Icons.play_circle_filled,
                  color: const Color(0xFF6C63FF),
                  size: 36,
                ),
                onPressed: _togglePlayPause,
              ),

              // Progress bar
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SliderTheme(
                      data: SliderTheme.of(context).copyWith(
                        trackHeight: 3,
                        thumbShape: const RoundSliderThumbShape(
                          enabledThumbRadius: 6,
                        ),
                        overlayShape: const RoundSliderOverlayShape(
                          overlayRadius: 12,
                        ),
                        activeTrackColor: const Color(0xFF6C63FF),
                        inactiveTrackColor:
                            const Color(0xFF6C63FF).withOpacity(0.2),
                        thumbColor: const Color(0xFF6C63FF),
                      ),
                      child: Slider(
                        value: _duration.inSeconds > 0
                            ? _position.inSeconds
                                .toDouble()
                                .clamp(0, _duration.inSeconds.toDouble())
                            : 0,
                        min: 0,
                        max: _duration.inSeconds > 0
                            ? _duration.inSeconds.toDouble()
                            : 1,
                        onChanged: (value) async {
                          await _player
                              .seek(Duration(seconds: value.toInt()));
                        },
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            _formatDuration(_position),
                            style: const TextStyle(
                              fontSize: 11,
                              color: Color(0xFF6C63FF),
                            ),
                          ),
                          Text(
                            _formatDuration(_duration),
                            style: const TextStyle(
                              fontSize: 11,
                              color: Color(0xFF6C63FF),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              // Replay button
              IconButton(
                icon: const Icon(Icons.replay, color: Color(0xFF6C63FF)),
                onPressed: _replay,
                tooltip: 'Replay',
              ),
            ],
          ),
        ],
      ),
    );
  }
}