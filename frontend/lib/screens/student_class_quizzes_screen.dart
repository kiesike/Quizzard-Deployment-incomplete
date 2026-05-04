import 'package:flutter/material.dart';
import '../services/auth_service.dart';

// ─── THEME CONSTANTS (mirrored from dashboard) ────────────────────────────────
class _AppTheme {
  static const Color primary = Color(0xFF6C63FF);
  static const Color primaryDark = Color(0xFF4B44CC);
  static const Color primaryLight = Color(0xFFEEEDFF);
  static const Color bg = Color(0xFFF4F6FB);
  static const Color surface = Colors.white;
  static const Color textDark = Color(0xFF1A1D2E);
  static const Color textMid = Color(0xFF6B7080);
  static const Color textLight = Color(0xFFADB5BD);
  static const Color success = Color(0xFF22C55E);
  static const Color warning = Color(0xFFF59E0B);
  static const Color danger = Color(0xFFEF4444);

  static BoxDecoration get cardDecoration => BoxDecoration(
        color: surface,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      );
}

class StudentClassQuizzesScreen extends StatefulWidget {
  final int classId;
  final String className;

  const StudentClassQuizzesScreen({
    super.key,
    required this.classId,
    required this.className,
  });

  @override
  State<StudentClassQuizzesScreen> createState() => _StudentClassQuizzesScreenState();
}

class _StudentClassQuizzesScreenState extends State<StudentClassQuizzesScreen> {
  bool _isLoading = true;
  String? _errorMessage;
  List<dynamic> _quizzes = [];
  List<dynamic> _filtered = [];
  final TextEditingController _searchController = TextEditingController();
  String _filter = 'all';

  @override
  void initState() {
    super.initState();
    _loadQuizzes();
    _searchController.addListener(_applyFilter);
  }

  @override
  void dispose() {
    _searchController.removeListener(_applyFilter);
    _searchController.dispose();
    super.dispose();
  }

  void _applyFilter() {
    final query = _searchController.text.trim().toLowerCase();
    setState(() {
      _filtered = _quizzes.where((q) {
        final title = (q['title'] as String).toLowerCase();
        final desc = ((q['description'] ?? '') as String).toLowerCase();
        final matchesQuery = query.isEmpty || title.contains(query) || desc.contains(query);
        final alreadyTaken = q['already_taken'] == true;
        final matchesFilter = _filter == 'all'
            ? true
            : _filter == 'pending'
                ? !alreadyTaken
                : alreadyTaken;
        return matchesQuery && matchesFilter;
      }).toList();
    });
  }

  void _setFilter(String val) {
    setState(() => _filter = val);
    _applyFilter();
  }

  Future<void> _loadQuizzes() async {
    setState(() { _isLoading = true; _errorMessage = null; });

    final result = await AuthService.authGet('/student/classes/${widget.classId}/quizzes');

    setState(() {
      _isLoading = false;
      if (result['success']) {
        _quizzes = result['data']['quizzes'] as List;
        _filtered = _quizzes;
      } else {
        _errorMessage = result['message'];
      }
    });
  }

  Color _getScoreColor(int percentage) {
    if (percentage >= 80) return _AppTheme.success;
    if (percentage >= 60) return _AppTheme.warning;
    return _AppTheme.danger;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: _AppTheme.bg,
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: _AppTheme.primary));
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: _AppTheme.danger.withOpacity(0.1),
                  shape: BoxShape.circle,
                ),
                child: Icon(Icons.error_outline_rounded, size: 48, color: _AppTheme.danger),
              ),
              const SizedBox(height: 16),
              Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: _AppTheme.textMid)),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadQuizzes,
                style: ElevatedButton.styleFrom(backgroundColor: _AppTheme.primary),
                child: const Text('Retry', style: TextStyle(color: Colors.white)),
              ),
            ],
          ),
        ),
      );
    }

    final doneCount = _quizzes.where((q) => q['already_taken'] == true).length;
    final pendingCount = _quizzes.length - doneCount;

    return RefreshIndicator(
      onRefresh: _loadQuizzes,
      color: _AppTheme.primary,
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        slivers: [
          // ── Header ──
          SliverToBoxAdapter(
            child: Container(
              padding: const EdgeInsets.fromLTRB(20, 50, 20, 24),
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  colors: [_AppTheme.primary, _AppTheme.primaryDark],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(32),
                  bottomRight: Radius.circular(32),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Back button + title
                  Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                            color: Colors.white.withOpacity(0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.arrow_back_ios_new_rounded, color: Colors.white, size: 18),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          widget.className,
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            letterSpacing: -0.3,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // Stats row
                  Row(
                    children: [
                      _buildHeaderStat('${_quizzes.length}', 'Total'),
                      const SizedBox(width: 10),
                      _buildHeaderStat('$doneCount', 'Done'),
                      const SizedBox(width: 10),
                      _buildHeaderStat('$pendingCount', 'Pending'),
                    ],
                  ),
                  const SizedBox(height: 18),

                  // Search
                  TextField(
                    controller: _searchController,
                    style: const TextStyle(fontSize: 14, color: _AppTheme.textDark),
                    decoration: InputDecoration(
                      hintText: 'Search quizzes...',
                      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
                      prefixIcon: const Icon(Icons.search_rounded, color: _AppTheme.primary, size: 20),
                      suffixIcon: _searchController.text.isNotEmpty
                          ? IconButton(
                              icon: const Icon(Icons.clear_rounded, size: 18),
                              color: _AppTheme.textMid,
                              onPressed: () => _searchController.clear(),
                            )
                          : null,
                      filled: true,
                      fillColor: Colors.white,
                      contentPadding: const EdgeInsets.symmetric(vertical: 12),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: BorderSide.none),
                    ),
                  ),
                ],
              ),
            ),
          ),

          // ── Filter chips ──
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 0),
              child: Row(
                children: [
                  _buildFilterChip('all', 'All (${_quizzes.length})'),
                  const SizedBox(width: 8),
                  _buildFilterChip('pending', 'Pending ($pendingCount)'),
                  const SizedBox(width: 8),
                  _buildFilterChip('done', 'Done ($doneCount)'),
                ],
              ),
            ),
          ),

          // ── List or empty ──
          _quizzes.isEmpty
              ? SliverFillRemaining(
                  hasScrollBody: false,
                  child: Center(
                    child: Padding(
                      padding: const EdgeInsets.all(40),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(24),
                            decoration: BoxDecoration(color: _AppTheme.primaryLight, shape: BoxShape.circle),
                            child: Icon(Icons.quiz_outlined, size: 48, color: _AppTheme.primary.withOpacity(0.5)),
                          ),
                          const SizedBox(height: 16),
                          const Text('No quizzes yet.', style: TextStyle(color: _AppTheme.textMid, fontSize: 16, fontWeight: FontWeight.w500)),
                          const SizedBox(height: 4),
                          const Text("Your teacher hasn't assigned any quizzes yet.", textAlign: TextAlign.center, style: TextStyle(color: _AppTheme.textLight, fontSize: 13)),
                        ],
                      ),
                    ),
                  ),
                )
              : _filtered.isEmpty
                  ? SliverFillRemaining(
                      hasScrollBody: false,
                      child: Center(
                        child: Padding(
                          padding: const EdgeInsets.all(40),
                          child: Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Container(
                                padding: const EdgeInsets.all(24),
                                decoration: BoxDecoration(color: _AppTheme.primaryLight, shape: BoxShape.circle),
                                child: Icon(Icons.search_off_rounded, size: 48, color: _AppTheme.primary.withOpacity(0.5)),
                              ),
                              const SizedBox(height: 16),
                              const Text('No quizzes found.', style: TextStyle(color: _AppTheme.textMid, fontSize: 16, fontWeight: FontWeight.w500)),
                            ],
                          ),
                        ),
                      ),
                    )
                  : SliverPadding(
                      padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
                      sliver: SliverList(
                        delegate: SliverChildBuilderDelegate(
                          (context, index) => _buildQuizCard(Map<String, dynamic>.from(_filtered[index])),
                          childCount: _filtered.length,
                        ),
                      ),
                    ),
        ],
      ),
    );
  }

  Widget _buildHeaderStat(String value, String label) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: Colors.white.withOpacity(0.18),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: Colors.white.withOpacity(0.25)),
        ),
        child: Column(
          children: [
            Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18)),
            Text(label, style: const TextStyle(color: Colors.white70, fontSize: 11)),
          ],
        ),
      ),
    );
  }

  Widget _buildFilterChip(String value, String label) {
    final isSelected = _filter == value;
    return GestureDetector(
      onTap: () => _setFilter(value),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 180),
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
        decoration: BoxDecoration(
          color: isSelected ? _AppTheme.primary : Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: isSelected ? _AppTheme.primary : Colors.grey.shade300),
          boxShadow: isSelected
              ? [BoxShadow(color: _AppTheme.primary.withOpacity(0.25), blurRadius: 8, offset: const Offset(0, 2))]
              : [],
        ),
        child: Text(
          label,
          style: TextStyle(
            color: isSelected ? Colors.white : _AppTheme.textMid,
            fontWeight: isSelected ? FontWeight.bold : FontWeight.normal,
            fontSize: 12,
          ),
        ),
      ),
    );
  }

  Widget _buildQuizCard(Map<String, dynamic> quiz) {
    final alreadyTaken = quiz['already_taken'] == true;

    return Container(
      margin: const EdgeInsets.only(bottom: 14),
      decoration: _AppTheme.cardDecoration,
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: alreadyTaken
            ? null
            : () async {
                await Navigator.pushNamed(
                  context,
                  '/quiz-taking',
                  arguments: {
                    'quiz_id': quiz['id'],
                    'quiz_title': quiz['title'],
                  },
                );
                _loadQuizzes();
              },
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 48,
                    height: 48,
                    decoration: BoxDecoration(
                      color: alreadyTaken ? _AppTheme.success.withOpacity(0.1) : _AppTheme.primaryLight,
                      borderRadius: BorderRadius.circular(13),
                    ),
                    child: Icon(
                      alreadyTaken ? Icons.check_circle_rounded : Icons.quiz_rounded,
                      color: alreadyTaken ? _AppTheme.success : _AppTheme.primary,
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          quiz['title'],
                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: _AppTheme.textDark),
                        ),
                        if (quiz['description'] != null && (quiz['description'] as String).isNotEmpty) ...[
                          const SizedBox(height: 4),
                          Text(
                            quiz['description'],
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontSize: 12, color: _AppTheme.textMid),
                          ),
                        ],
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  // Status badge
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: alreadyTaken ? _AppTheme.success.withOpacity(0.1) : _AppTheme.primaryLight,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: alreadyTaken ? _AppTheme.success.withOpacity(0.3) : _AppTheme.primary.withOpacity(0.3),
                      ),
                    ),
                    child: Text(
                      alreadyTaken ? '✓ Done' : 'Pending',
                      style: TextStyle(
                        color: alreadyTaken ? _AppTheme.success : _AppTheme.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 11,
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 14),
              Divider(color: Colors.grey.shade100, height: 1),
              const SizedBox(height: 12),

              // Meta row
              Row(
                children: [
                  _buildMeta(Icons.help_outline_rounded, '${quiz['questions_count']} questions'),
                  const Spacer(),
                  if (alreadyTaken)
                    _buildMeta(Icons.star_rounded, 'Score: ${quiz['score'] ?? '?'}/${quiz['total_points'] ?? '?'}')
                  else
                    SizedBox(
                      height: 38,
                      child: ElevatedButton.icon(
                        onPressed: () async {
                          await Navigator.pushNamed(
                            context,
                            '/quiz-taking',
                            arguments: {
                              'quiz_id': quiz['id'],
                              'quiz_title': quiz['title'],
                            },
                          );
                          _loadQuizzes();
                        },
                        icon: const Icon(Icons.play_arrow_rounded, size: 16),
                        label: const Text('Start Quiz'),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: _AppTheme.primary,
                          foregroundColor: Colors.white,
                          textStyle: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                          padding: const EdgeInsets.symmetric(horizontal: 16),
                        ),
                      ),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildMeta(IconData icon, String label) {
    return Row(
      children: [
        Icon(icon, size: 14, color: _AppTheme.textLight),
        const SizedBox(width: 5),
        Text(label, style: const TextStyle(fontSize: 12, color: _AppTheme.textMid)),
      ],
    );
  }
}