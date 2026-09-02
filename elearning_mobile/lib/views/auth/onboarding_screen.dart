import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/auth_service.dart';
import 'login_screen.dart';
import 'widgets/splash_background_painter.dart';

class OnboardingItem {
  final String title;
  final String description;
  final IconData icon;
  final List<Color> gradientColors;
  final String badgeText;

  OnboardingItem({
    required this.title,
    required this.description,
    required this.icon,
    required this.gradientColors,
    required this.badgeText,
  });
}

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen>
    with SingleTickerProviderStateMixin {
  final PageController _pageController = PageController();
  int _currentIndex = 0;
  bool _isNavigating = false;

  late AnimationController _floatingPulseController;
  late Animation<double> _floatingIconAnim;

  final List<OnboardingItem> _items = [
    OnboardingItem(
      title: 'Pembelajaran Digital Terpadu',
      description:
          'Akses seluruh modul KBM, tugas interaktif, dan materi pembelajaran digital SMK Muthia Harapan Cicalengka kapan saja & di mana saja.',
      icon: Icons.auto_stories_rounded,
      gradientColors: [const Color(0xFF10B981), const Color(0xFF059669)],
      badgeText: 'KBM Digital 📚',
    ),
    OnboardingItem(
      title: 'CBT & Ujian Real-Time',
      description:
          'Pengerjaan kuis & Ujian Berbasis Komputer (CBT) secara digital dengan sistem cepat, aman, akurat, dan hasil yang transparan.',
      icon: Icons.quiz_rounded,
      gradientColors: [const Color(0xFF0EA5E9), const Color(0xFF0284C7)],
      badgeText: 'Computer Based Test 📝',
    ),
    OnboardingItem(
      title: 'Forum Diskusi & Presensi',
      description:
          'Interaksi aktif antara Bapak/Ibu Guru dan Siswa, rekap absensi harian real-time, serta pantauan aktivitas kelas secara efisien.',
      icon: Icons.forum_rounded,
      gradientColors: [const Color(0xFFF59E0B), const Color(0xDDF59E0B)],
      badgeText: 'Komunikasi Aktif 💬',
    ),
    OnboardingItem(
      title: 'Siap Meraih Prestasi!',
      description:
          'Bergabunglah sekarang dalam ekosistem digital SMK Muthia Harapan Cicalengka dan tingkatkan prestasi belajarmu ke tingkat lebih tinggi!',
      icon: Icons.rocket_launch_rounded,
      gradientColors: [const Color(0xFF6366F1), const Color(0xFF4F46E5)],
      badgeText: 'Masa Depan Cerah 🚀',
    ),
  ];

  @override
  void initState() {
    super.initState();

    // Continuous Gentle Floating Icon Ticker
    _floatingPulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 3),
    )..repeat(reverse: true);

    _floatingIconAnim = Tween<double>(begin: -6.0, end: 6.0).animate(
      CurvedAnimation(
        parent: _floatingPulseController,
        curve: Curves.easeInOutSine,
      ),
    );
  }

  Future<void> _finishOnboarding() async {
    if (_isNavigating) return;
    setState(() {
      _isNavigating = true;
    });

    await AuthService.setHasSeenOnboarding();

    if (!mounted) return;

    // Stop continuous floating ticker before transition
    _floatingPulseController.stop();

    // Smooth, seamless page transition into LoginScreen
    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 400),
        pageBuilder: (context, animation, secondaryAnimation) => const LoginScreen(),
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          final fadeIn = Tween<double>(begin: 0.0, end: 1.0).animate(
            CurvedAnimation(parent: animation, curve: Curves.easeOut),
          );
          final slideUp = Tween<Offset>(
            begin: const Offset(0.0, 0.08),
            end: Offset.zero,
          ).animate(
            CurvedAnimation(parent: animation, curve: Curves.easeOutCubic),
          );

          return FadeTransition(
            opacity: fadeIn,
            child: SlideTransition(
              position: slideUp,
              child: child,
            ),
          );
        },
      ),
    );
  }

  void _nextPage() {
    if (_currentIndex < _items.length - 1) {
      _pageController.nextPage(
        duration: const Duration(milliseconds: 400),
        curve: Curves.easeInOutCubic,
      );
    } else {
      _finishOnboarding();
    }
  }

  @override
  void dispose() {
    _pageController.dispose();
    _floatingPulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFF3730A3),
      body: Stack(
        children: [
          // 1. Continuous Background Energy Grid Waves
          AnimatedBuilder(
            animation: _floatingPulseController,
            builder: (context, child) {
              return Container(
                width: double.infinity,
                height: double.infinity,
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    colors: isDark
                        ? [const Color(0xFF0F172A), const Color(0xFF1E293B), const Color(0xFF020617)]
                        : [const Color(0xFF4F46E5), const Color(0xFF3730A3), const Color(0xFF1E1B4B)],
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                  ),
                ),
                child: CustomPaint(
                  painter: SplashBackgroundPainter(
                    pulseProgress: _floatingPulseController.value,
                    gridOpacity: 0.35,
                  ),
                ),
              );
            },
          ),

          // 2. Screen Content Layout
          SafeArea(
            child: Column(
              children: [
                // Top Bar Header
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Brand Badge
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.12),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: Colors.white.withValues(alpha: 0.2),
                          ),
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(4),
                              decoration: const BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                              ),
                              child: Image.asset(
                                'assets/logo/mhc_logo.png',
                                width: 22,
                                height: 22,
                                fit: BoxFit.contain,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'SMK Muthia Harapan',
                              style: GoogleFonts.outfit(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                                letterSpacing: 0.5,
                              ),
                            ),
                          ],
                        ),
                      ),

                      // Skip Action Button
                      TextButton.icon(
                        onPressed: _isNavigating ? null : _finishOnboarding,
                        icon: const Icon(
                          Icons.double_arrow_rounded,
                          size: 16,
                          color: Colors.white70,
                        ),
                        label: const Text(
                          'Lewati',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                        style: TextButton.styleFrom(
                          backgroundColor: Colors.white.withValues(alpha: 0.12),
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(20),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                // PageView Carousel
                Expanded(
                  child: PageView.builder(
                    controller: _pageController,
                    physics: _isNavigating
                        ? const NeverScrollableScrollPhysics()
                        : const BouncingScrollPhysics(),
                    onPageChanged: (index) {
                      setState(() {
                        _currentIndex = index;
                      });
                    },
                    itemCount: _items.length,
                    itemBuilder: (context, index) {
                      final currentItem = _items[index];

                      return Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 24.0, vertical: 12.0),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            // Animated Glowing Icon Hub Card
                            AnimatedBuilder(
                              animation: _floatingPulseController,
                              builder: (context, child) {
                                return Transform.translate(
                                  offset: Offset(0, _floatingIconAnim.value),
                                  child: Container(
                                    width: 150,
                                    height: 150,
                                    decoration: BoxDecoration(
                                      shape: BoxShape.circle,
                                      gradient: LinearGradient(
                                        colors: currentItem.gradientColors,
                                        begin: Alignment.topLeft,
                                        end: Alignment.bottomRight,
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: currentItem.gradientColors.first.withValues(alpha: 0.5),
                                          blurRadius: 36,
                                          offset: const Offset(0, 12),
                                        ),
                                      ],
                                    ),
                                    child: Icon(
                                      currentItem.icon,
                                      size: 76,
                                      color: Colors.white,
                                    ),
                                  ),
                                );
                              },
                            ),

                            const SizedBox(height: 32),

                            // Category Pill Badge
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: Colors.white.withValues(alpha: 0.25),
                                ),
                              ),
                              child: Text(
                                currentItem.badgeText,
                                style: GoogleFonts.outfit(
                                  color: Colors.cyanAccent,
                                  fontSize: 13,
                                  fontWeight: FontWeight.bold,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),

                            const SizedBox(height: 16),

                            // Main Title
                            Text(
                              currentItem.title,
                              textAlign: TextAlign.center,
                              style: GoogleFonts.outfit(
                                fontSize: 25,
                                fontWeight: FontWeight.bold,
                                color: Colors.white,
                                letterSpacing: 0.5,
                              ),
                            ),

                            const SizedBox(height: 12),

                            // Description Glassmorphic Card
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.1),
                                borderRadius: BorderRadius.circular(20),
                                border: Border.all(
                                  color: Colors.white.withValues(alpha: 0.15),
                                ),
                              ),
                              child: Text(
                                currentItem.description,
                                textAlign: TextAlign.center,
                                style: GoogleFonts.inter(
                                  fontSize: 14,
                                  height: 1.5,
                                  color: Colors.white.withValues(alpha: 0.9),
                                  fontWeight: FontWeight.w400,
                                ),
                              ),
                            ),
                          ],
                        ),
                      );
                    },
                  ),
                ),

                // Bottom Navigation & Controls
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 20),
                  child: Column(
                    children: [
                      // Page Indicator Pills
                      Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: List.generate(
                          _items.length,
                          (index) => AnimatedContainer(
                            duration: const Duration(milliseconds: 300),
                            margin: const EdgeInsets.symmetric(horizontal: 4),
                            width: _currentIndex == index ? 30 : 10,
                            height: 10,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(6),
                              color: _currentIndex == index
                                  ? Colors.cyanAccent
                                  : Colors.white24,
                              boxShadow: _currentIndex == index
                                  ? [
                                      const BoxShadow(
                                        color: Colors.cyanAccent,
                                        blurRadius: 8,
                                        spreadRadius: 1,
                                      ),
                                    ]
                                  : [],
                            ),
                          ),
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Next / Start Action Button
                      SizedBox(
                        width: double.infinity,
                        height: 54,
                        child: ElevatedButton(
                          onPressed: _isNavigating ? null : _nextPage,
                          style: ElevatedButton.styleFrom(
                            padding: EdgeInsets.zero,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                            elevation: 8,
                            shadowColor: Colors.cyanAccent.withValues(alpha: 0.4),
                          ),
                          child: Ink(
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                colors: _items[_currentIndex].gradientColors,
                                begin: Alignment.centerLeft,
                                end: Alignment.centerRight,
                              ),
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Container(
                              alignment: Alignment.center,
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    _currentIndex == _items.length - 1
                                        ? 'Mulai Sekarang'
                                        : 'Lanjut',
                                    style: GoogleFonts.outfit(
                                      fontSize: 17,
                                      fontWeight: FontWeight.bold,
                                      color: Colors.white,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                  const SizedBox(width: 10),
                                  Icon(
                                    _currentIndex == _items.length - 1
                                        ? Icons.rocket_launch_rounded
                                        : Icons.arrow_forward_rounded,
                                    color: Colors.white,
                                    size: 22,
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
