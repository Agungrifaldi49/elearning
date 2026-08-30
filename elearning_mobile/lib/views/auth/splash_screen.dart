import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../siswa/siswa_main_screen.dart';
import '../guru/guru_main_screen.dart';
import 'login_screen.dart';
import 'widgets/splash_background_painter.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _animController;

  // Micro-Animation Sequences (Total Duration: 1500ms / 1.5s)
  // Phase 1: Focus & Smart Tech Pulse (0.0s - 0.6s)
  late Animation<double> _focusPulseAnim;
  late Animation<double> _gearRotationAnim;

  // Phase 2: Friendly Identity & Logo Scale-up (0.2s - 0.8s)
  late Animation<double> _logoScaleAnim;
  late Animation<double> _logoOpacityAnim;
  late Animation<double> _greetingFadeAnim;
  late Animation<Offset> _greetingSlideAnim;

  // Phase 3: Smooth Exit Slide & Fade (1.2s - 1.5s)
  late Animation<Offset> _exitSlideAnim;
  late Animation<double> _exitFadeAnim;

  bool _hasNavigated = false;

  @override
  void initState() {
    super.initState();

    // 1. Initialize 1.5-second Animation Controller
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1500),
    );

    // Phase 1: Tech Data Pulse & Gear Spinning
    _focusPulseAnim = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.0, 0.5, curve: Curves.easeOut),
      ),
    );

    _gearRotationAnim = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.0, 1.0, curve: Curves.linear),
      ),
    );

    // Phase 2: Logo Scale-up + Adaptive Greeting Reveal
    _logoScaleAnim = Tween<double>(begin: 0.6, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.15, 0.65, curve: Curves.easeOutBack),
      ),
    );

    _logoOpacityAnim = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.15, 0.45, curve: Curves.easeIn),
      ),
    );

    _greetingFadeAnim = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.4, 0.8, curve: Curves.easeIn),
      ),
    );

    _greetingSlideAnim = Tween<Offset>(
      begin: const Offset(0, 0.35),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.4, 0.8, curve: Curves.easeOutCubic),
      ),
    );

    // Phase 3: Transition Exit Slide & Fade
    _exitSlideAnim = Tween<Offset>(
      begin: Offset.zero,
      end: const Offset(0, -0.15),
    ).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.8, 1.0, curve: Curves.easeInOutCubic),
      ),
    );

    _exitFadeAnim = Tween<double>(begin: 1.0, end: 0.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.85, 1.0, curve: Curves.easeOut),
      ),
    );

    // 2. Start Animation & Add Completion Listener
    _animController.forward();

    _animController.addStatusListener((status) {
      if (status == AnimationStatus.completed) {
        _checkAndNavigate();
      }
    });

    // 3. Perform Auth Check in Parallel
    _startAuthCheck();
  }

  Future<void> _startAuthCheck() async {
    await ApiService.initBaseUrl();
    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    await authProvider.checkAutoLogin();

    if (mounted) {
      // If animation is already completed, perform navigation
      if (_animController.isCompleted) {
        _checkAndNavigate();
      }
    }
  }

  void _checkAndNavigate() {
    if (!mounted || _hasNavigated) return;
    _hasNavigated = true;

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    Widget destination;

    if (authProvider.isAuthenticated) {
      final user = authProvider.currentUser!;
      destination = user.isGuru ? const GuruMainScreen() : const SiswaMainScreen();
    } else {
      destination = const LoginScreen();
    }

    Navigator.of(context).pushReplacement(
      PageRouteBuilder(
        transitionDuration: const Duration(milliseconds: 450),
        pageBuilder: (context, animation, secondaryAnimation) => destination,
        transitionsBuilder: (context, animation, secondaryAnimation, child) {
          final fadeIn = Tween<double>(begin: 0.0, end: 1.0).animate(
            CurvedAnimation(parent: animation, curve: Curves.easeOut),
          );
          final slideUp = Tween<Offset>(
            begin: const Offset(0.0, 0.06),
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

  void _skipAnimation() {
    if (_hasNavigated) return;
    _animController.stop();
    _checkAndNavigate();
  }

  String _getAdaptiveGreeting() {
    final hour = DateTime.now().hour;
    if (hour >= 4 && hour < 11) {
      return "Selamat Pagi! ☀️";
    } else if (hour >= 11 && hour < 15) {
      return "Selamat Siang! 🌤️";
    } else if (hour >= 15 && hour < 18) {
      return "Selamat Sore! 🌅";
    } else {
      return "Selamat Malam! 🌙";
    }
  }

  @override
  void dispose() {
    _animController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: const Color(0xFF3730A3), // Solid brand fallback for 0ms frame
      body: GestureDetector(
        onTap: _skipAnimation,
        behavior: HitTestBehavior.opaque,
        child: AnimatedBuilder(
          animation: _animController,
          builder: (context, child) {
            return Stack(
              children: [
                // 1. Rich Brand Gradient Background & Data Energy Pulse Lines
                Container(
                  width: double.infinity,
                  height: double.infinity,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Color(0xFF4F46E5), // Indigo primary
                        Color(0xFF3730A3), // Deep Indigo
                        Color(0xFF1E1B4B), // Midnight Indigo accent
                      ],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                  child: CustomPaint(
                    painter: SplashBackgroundPainter(
                      pulseProgress: _focusPulseAnim.value,
                      gridOpacity: _exitFadeAnim.value,
                    ),
                  ),
                ),

                // 2. Top Bar Skip Action
                SafeArea(
                  child: Align(
                    alignment: Alignment.topRight,
                    child: Padding(
                      padding: const EdgeInsets.only(top: 12, right: 16),
                      child: TextButton.icon(
                        onPressed: _skipAnimation,
                        icon: const Icon(
                          Icons.bolt_rounded,
                          color: Colors.white70,
                          size: 16,
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
                    ),
                  ),
                ),

                // 3. Central Animated Onboarding Visual Elements
                Center(
                  child: SlideTransition(
                    position: _exitSlideAnim,
                    child: FadeTransition(
                      opacity: _exitFadeAnim,
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Smart Tech Glowing Gear & Book Accent Header
                          Container(
                            width: 64,
                            height: 64,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.12),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.cyanAccent.withValues(alpha: 0.3),
                                  blurRadius: 20,
                                  spreadRadius: 1,
                                ),
                              ],
                            ),
                            child: Stack(
                              alignment: Alignment.center,
                              children: [
                                // Rotating Precision Tech Gear
                                RotationTransition(
                                  turns: _gearRotationAnim,
                                  child: Icon(
                                    Icons.settings_outlined,
                                    size: 52,
                                    color: Colors.white.withValues(alpha: 0.35),
                                  ),
                                ),
                                // Smart Glowing Education Book Icon
                                const Icon(
                                  Icons.auto_stories_rounded,
                                  size: 30,
                                  color: Colors.cyanAccent,
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 24),

                          // MHC School Identity Logo with Soft Scale-Up + Ease-Out
                          ScaleTransition(
                            scale: _logoScaleAnim,
                            child: FadeTransition(
                              opacity: _logoOpacityAnim,
                              child: Container(
                                padding: const EdgeInsets.all(18),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  shape: BoxShape.circle,
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withValues(alpha: 0.25),
                                      blurRadius: 28,
                                      offset: const Offset(0, 10),
                                    ),
                                    BoxShadow(
                                      color: Colors.cyanAccent.withValues(alpha: 0.25),
                                      blurRadius: 16,
                                      spreadRadius: 2,
                                    ),
                                  ],
                                ),
                                child: Image.asset(
                                  'assets/logo/mhc_logo.png',
                                  width: 85,
                                  height: 85,
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ),
                          ),

                          SizedBox(height: size.height * 0.03),

                          // Adaptive Micro-Greeting & Modern Identity Typography
                          FadeTransition(
                            opacity: _greetingFadeAnim,
                            child: SlideTransition(
                              position: _greetingSlideAnim,
                              child: Column(
                                children: [
                                  // Time-of-day Adaptive Greeting Tag
                                  Container(
                                    padding: const EdgeInsets.symmetric(
                                      horizontal: 14,
                                      vertical: 4,
                                    ),
                                    decoration: BoxDecoration(
                                      color: Colors.amberAccent.withValues(alpha: 0.2),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(
                                        color: Colors.amberAccent.withValues(alpha: 0.4),
                                      ),
                                    ),
                                    child: Text(
                                      _getAdaptiveGreeting(),
                                      style: GoogleFonts.outfit(
                                        color: Colors.amberAccent,
                                        fontSize: 14,
                                        fontWeight: FontWeight.bold,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 10),

                                  // Brand Title Text
                                  Text(
                                    'SMK Muthia Harapan',
                                    textAlign: TextAlign.center,
                                    style: GoogleFonts.outfit(
                                      color: Colors.white,
                                      fontSize: 26,
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                  const SizedBox(height: 6),

                                  // Friendly Prompt Subtitle
                                  Text(
                                    'Siap Belajar Hari Ini?',
                                    style: GoogleFonts.inter(
                                      color: Colors.white.withValues(alpha: 0.9),
                                      fontSize: 14,
                                      fontWeight: FontWeight.w500,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

                // 4. Subtle Bottom System Loading Indicator
                SafeArea(
                  child: Align(
                    alignment: Alignment.bottomCenter,
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 20),
                      child: FadeTransition(
                        opacity: _greetingFadeAnim,
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const SizedBox(
                              width: 14,
                              height: 14,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                valueColor: AlwaysStoppedAnimation<Color>(
                                  Colors.white70,
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              'E-Learning System',
                              style: GoogleFonts.inter(
                                color: Colors.white60,
                                fontSize: 11,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ),
              ],
            );
          },
        ),
      ),
    );
  }
}
