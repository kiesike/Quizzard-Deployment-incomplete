// frontend/lib/screens/teacher_dashboard_screen.dart
// QZ-17: Added publish/unpublish toggle with badge, confirmation dialog, and dashboard refresh.

import 'package:flutter/material.dart';
import '../services/auth_service.dart';

class TeacherDashboardScreen extends StatefulWidget {
  const TeacherDashboardScreen({super.key});

  @override
  State<TeacherDashboardScreen> createState() => _TeacherDashboardScreenState();
}

class _TeacherDashboardScreenState extends State<TeacherDashboardScreen> {
  // ─── Theme ────────────────────────────────────────────────
  static const Color _purple = Color(0xFF6C63FF);
  static const Color _green  = Color(0xFF4CAF50);

  // ─── State ────────────────────────────────────────────────
  int    _selectedIndex = 0;
  bool   _isLoading     = true;
  String _teacherName   = '';
  String _teacherEmail  = '';
  List<Map<String, dynamic>> _quizzes     = [];
  Map<String, dynamic>       _stats       = {};
  // Track which quiz ids are currently being toggled (to show per-card loader)
  final Set<int> _togglingIds = {};

  // ─── Lifecycle ────────────────────────────────────────────
  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  // ─── API calls ────────────────────────────────────────────

  Future<void> _loadDashboard() async {
    setState(() => _isLoading = true);
    try {
      final response = await AuthService.authGet('/teacher/dashboard');
      if (response['success'] == true) {
        final data = response['data'];
        setState(() {
          _teacherName  = data['teacher']['name']  ?? '';
          _teacherEmail = data['teacher']['email'] ?? '';
          _quizzes = List<Map<String, dynamic>>.from(data['quizzes'] ?? []);
          _stats   = Map<String, dynamic>.from(data['stats']   ?? {});
        });
      } else {
        _showSnackbar(response['message'] ?? 'Failed to load dashboard', isError: true);
      }
    } catch (e) {
      _showSnackbar('Network error: $e', isError: true);
    } finally {
      setState(() => _isLoading = false);
    }
  }

  /// QZ-17: Toggle publish status for a single quiz.
  Future<void> _togglePublish(Map<String, dynamic> quiz) async {
    final int    quizId      = quiz['id'];
    final bool   isPublished = quiz['is_published'] == true || quiz['is_published'] == 1;
    final String quizTitle   = quiz['title'] ?? 'this quiz';

    // Confirmation dialog
    final confirmed = await _showPublishConfirmation(quizTitle, isPublished);
    if (!confirmed) return;

    setState(() => _togglingIds.add(quizId));

    try {
      final response = await AuthService.authPatch('/quizzes/$quizId/publish-toggle', {});
      if (response['success'] == true) {
        final updatedQuiz = response['data'];
        setState(() {
          final idx = _quizzes.indexWhere((q) => q['id'] == quizId);
          if (idx != -1) {
            _quizzes[idx] = {
              ..._quizzes[idx],
              'is_published': updatedQuiz['is_published'],
            };
          }
        });
        _showSnackbar(response['message'] ?? 'Status updated.');
      } else {
        _showSnackbar(response['message'] ?? 'Failed to toggle publish status.', isError: true);
      }
    } catch (e) {
      _showSnackbar('Network error: $e', isError: true);
    } finally {
      setState(() => _togglingIds.remove(quizId));
    }
  }

  // ─── Helpers ──────────────────────────────────────────────

  void _showSnackbar(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red.shade700 : _green,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  Future<bool> _showPublishConfirmation(String quizTitle, bool isCurrentlyPublished) async {
    final action      = isCurrentlyPublished ? 'unpublish' : 'publish';
    final actionLabel = isCurrentlyPublished ? 'Unpublish'  : 'Publish';
    final color       = isCurrentlyPublished ? Colors.orange : _green;

    final result = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Text('$actionLabel Quiz?'),
        content: Text(
          isCurrentlyPublished
              ? 'Are you sure you want to unpublish "$quizTitle"? Students will no longer be able to access it.'
              : 'Are you sure you want to publish "$quizTitle"? Students will be able to see and take it.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(false),
            child: const Text('Cancel'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(backgroundColor: color),
            onPressed: () => Navigator.of(ctx).pop(true),
            child: Text(actionLabel, style: const TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  Future<void> _logout() async {
    await AuthService.authPost('/logout', {});
    await AuthService.clearToken();
    if (mounted) Navigator.pushReplacementNamed(context, '/login');
  }

  // ─── Build ────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F5F5),
      appBar: AppBar(
        backgroundColor: _green,
        elevation: 0,
        title: const Text(
          'Teacher Dashboard',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Colors.white),
            onPressed: _loadDashboard,
            tooltip: 'Refresh',
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.white),
            onPressed: _logout,
            tooltip: 'Logout',
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : IndexedStack(
              index: _selectedIndex,
              children: [
                _buildQuizzesTab(),
                _buildProfileTab(),
              ],
            ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _selectedIndex,
        selectedItemColor: _green,
        unselectedItemColor: Colors.grey,
        onTap: (i) => setState(() => _selectedIndex = i),
        items: const [
          BottomNavigationBarItem(icon: Icon(Icons.quiz),   label: 'My Quizzes'),
          BottomNavigationBarItem(icon: Icon(Icons.person), label: 'Profile'),
        ],
      ),
    );
  }

  // ─── Quizzes Tab ──────────────────────────────────────────

  Widget _buildQuizzesTab() {
    return RefreshIndicator(
      onRefresh: _loadDashboard,
      color: _green,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(child: _buildStatsBar()),
          if (_quizzes.isEmpty)
            const SliverFillRemaining(
              child: Center(
                child: Text(
                  'No quizzes yet.\nCreate your first quiz!',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 16, color: Colors.grey),
                ),
              ),
            )
          else
            SliverList(
              delegate: SliverChildBuilderDelegate(
                (context, index) => _buildQuizCard(_quizzes[index]),
                childCount: _quizzes.length,
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildStatsBar() {
    final totalQuizzes      = _stats['total_quizzes']      ?? _quizzes.length;
    final publishedQuizzes  = _stats['published_quizzes']  ?? _quizzes.where((q) => q['is_published'] == true || q['is_published'] == 1).length;
    final totalStudents     = _stats['total_students']     ?? 0;

    return Container(
      color: _green,
      padding: const EdgeInsets.fromLTRB(16, 0, 16, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Welcome, $_teacherName 👋',
            style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              _statChip(Icons.quiz_outlined,    '$totalQuizzes',     'Total Quizzes'),
              const SizedBox(width: 10),
              _statChip(Icons.publish,           '$publishedQuizzes', 'Published'),
              const SizedBox(width: 10),
              _statChip(Icons.people_outline,    '$totalStudents',    'Students'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _statChip(IconData icon, String value, String label) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.2),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          children: [
            Icon(icon, color: Colors.white, size: 20),
            const SizedBox(height: 4),
            Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 16)),
            Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
          ],
        ),
      ),
    );
  }

  Widget _buildQuizCard(Map<String, dynamic> quiz) {
    final int    quizId      = quiz['id'];
    final bool   isPublished = quiz['is_published'] == true || quiz['is_published'] == 1;
    final bool   isToggling  = _togglingIds.contains(quizId);
    final String title       = quiz['title']       ?? 'Untitled Quiz';
    final String description = quiz['description'] ?? '';
    final int    questions   = quiz['questions_count'] ?? 0;

    return Card(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      elevation: 3,
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // ── Title row + published badge ──────────────────
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Text(
                    title,
                    style: const TextStyle(fontSize: 17, fontWeight: FontWeight.bold),
                  ),
                ),
                const SizedBox(width: 8),
                _buildPublishedBadge(isPublished),
              ],
            ),
            if (description.isNotEmpty) ...[
              const SizedBox(height: 6),
              Text(
                description,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Colors.grey, fontSize: 13),
              ),
            ],
            const SizedBox(height: 8),
            Text(
              '$questions question${questions != 1 ? 's' : ''}',
              style: const TextStyle(color: Colors.blueGrey, fontSize: 13),
            ),
            const Divider(height: 20),
            // ── Action row ───────────────────────────────────
            Row(
              mainAxisAlignment: MainAxisAlignment.end,
              children: [
                // Publish / Unpublish toggle button
                isToggling
                    ? const SizedBox(
                        width: 24, height: 24,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : OutlinedButton.icon(
                        onPressed: () => _togglePublish(quiz),
                        icon: Icon(
                          isPublished ? Icons.unpublished_outlined : Icons.publish,
                          size: 18,
                          color: isPublished ? Colors.orange : _green,
                        ),
                        label: Text(
                          isPublished ? 'Unpublish' : 'Publish',
                          style: TextStyle(color: isPublished ? Colors.orange : _green),
                        ),
                        style: OutlinedButton.styleFrom(
                          side: BorderSide(color: isPublished ? Colors.orange : _green),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                        ),
                      ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  /// QZ-17: Visual badge showing Published (green) or Draft (grey).
  Widget _buildPublishedBadge(bool isPublished) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: isPublished ? _green.withOpacity(0.15) : Colors.grey.withOpacity(0.15),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(
          color: isPublished ? _green : Colors.grey,
          width: 1,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            isPublished ? Icons.visibility : Icons.visibility_off,
            size: 13,
            color: isPublished ? _green : Colors.grey,
          ),
          const SizedBox(width: 4),
          Text(
            isPublished ? 'Published' : 'Draft',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: isPublished ? _green : Colors.grey,
            ),
          ),
        ],
      ),
    );
  }

  // ─── Profile Tab ──────────────────────────────────────────

  Widget _buildProfileTab() {
    return ListView(
      padding: const EdgeInsets.all(24),
      children: [
        Center(
          child: CircleAvatar(
            radius: 48,
            backgroundColor: _green,
            child: Text(
              _teacherName.isNotEmpty ? _teacherName[0].toUpperCase() : 'T',
              style: const TextStyle(fontSize: 36, color: Colors.white, fontWeight: FontWeight.bold),
            ),
          ),
        ),
        const SizedBox(height: 16),
        Center(
          child: Text(
            _teacherName,
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
          ),
        ),
        Center(
          child: Text(
            _teacherEmail,
            style: const TextStyle(color: Colors.grey, fontSize: 14),
          ),
        ),
        const SizedBox(height: 8),
        Center(
          child: Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
            decoration: BoxDecoration(
              color: _green.withOpacity(0.1),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: _green),
            ),
            child: const Text('Teacher', style: TextStyle(color: Color(0xFF4CAF50), fontWeight: FontWeight.w600)),
          ),
        ),
        const SizedBox(height: 32),
        ListTile(
          leading: const Icon(Icons.quiz_outlined, color: Color(0xFF4CAF50)),
          title: const Text('Total Quizzes'),
          trailing: Text(
            '${_quizzes.length}',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
        ),
        ListTile(
          leading: const Icon(Icons.publish, color: Color(0xFF4CAF50)),
          title: const Text('Published'),
          trailing: Text(
            '${_quizzes.where((q) => q['is_published'] == true || q['is_published'] == 1).length}',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
        ),
        const Divider(height: 32),
        ListTile(
          leading: const Icon(Icons.logout, color: Colors.red),
          title: const Text('Logout', style: TextStyle(color: Colors.red)),
          onTap: _logout,
        ),
      ],
    );
  }
}