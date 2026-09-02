import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../siswa/siswa_main_screen.dart';
import '../guru/guru_main_screen.dart';
import 'onboarding_screen.dart';
import 'widgets/splash_background_painter.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with TickerProviderStateMixin {
  late AnimationController _animController;
  late AnimationController _pulseController;

  // Micro-Animation Sequences
  late Animation<double> _gearRotationAnim;
  late Animation<double> _logoScaleAnim;
  late Animation<double> _logoOpacityAnim;
  late Animation<double> _greetingFadeAnim;
  late Animation<Offset> _greetingSlideAnim;

  // Transition Exit Slide & Fade
  late Animation<Offset> _exitSlideAnim;
  late Animation<double> _exitFadeAnim;

  bool _hasNavigated = false;

  @override
  void initState() {
    super.initState();

    // 1. Entrance & Exit Animation Controller (2.2 seconds for rich presentation)
    _animController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 2200),
    );

    // 2. Continuous Looping Animation Controller for Background Energy Pulse & Tech Gear
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 4),
    )..repeat();

    _gearRotationAnim = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _pulseController,
        curve: Curves.linear,
      ),
    );

    // Immediate Logo Scale-up & Opacity Reveal from Frame 0
    _logoScaleAnim = Tween<double>(begin: 0.8, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.0, 0.45, curve: Curves.easeOutBack),
      ),
    );

    _logoOpacityAnim = Tween<double>(begin: 0.2, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.0, 0.25, curve: Curves.easeIn),
      ),
    );

    // Immediate Greeting & Title Reveal
    _greetingFadeAnim = Tween<double>(begin: 0.3, end: 1.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.0, 0.35, curve: Curves.easeIn),
      ),
    );

    _greetingSlideAnim = Tween<Offset>(
      begin: const Offset(0, 0.15),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.0, 0.40, curve: Curves.easeOutCubic),
      ),
    );

    // Exit Transition (towards the end of 2.2s)
    _exitSlideAnim = Tween<Offset>(
      begin: Offset.zero,
      end: const Offset(0, -0.15),
    ).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.85, 1.0, curve: Curves.easeInOutCubic),
      ),
    );

    _exitFadeAnim = Tween<double>(begin: 1.0, end: 0.0).animate(
      CurvedAnimation(
        parent: _animController,
        curve: const Interval(0.88, 1.0, curve: Curves.easeOut),
      ),
    );

    // 3. Start Entrance Animation & Parallel Auth Check
    _animController.forward();
    _startAuthCheckAndNavigate();
  }

  Future<void> _startAuthCheckAndNavigate() async {
    final minDurationFuture = Future.delayed(const Duration(milliseconds: 2200));

    final authCheckFuture = () async {
      await ApiService.initBaseUrl();
      if (!mounted) return;
      final authProvider = Provider.of<AuthProvider>(context, listen: false);
      await authProvider.checkAutoLogin();
    }();

    await Future.wait([minDurationFuture, authCheckFuture]);

    if (mounted && !_hasNavigated) {
      _checkAndNavigate();
    }
  }

  void _checkAndNavigate() async {
    if (!mounted || _hasNavigated) return;
    _hasNavigated = true;

    _pulseController.stop();
    _animController.stop();

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    Widget destination;

    if (authProvider.isAuthenticated) {
      final user = authProvider.currentUser!;
      destination = user.isGuru ? const GuruMainScreen() : const SiswaMainScreen();
    } else {
      destination = const OnboardingScreen();
    }

    if (!mounted) return;

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
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: const Color(0xFF3730A3),
      body: GestureDetector(
        onTap: _skipAnimation,
        behavior: HitTestBehavior.opaque,
        child: AnimatedBuilder(
          animation: Listenable.merge([_animController, _pulseController]),
          builder: (context, child) {
            return Stack(
              children: [
                // 1. Continuous Energy Pulse Gradient Background
                Container(
                  width: double.infinity,
                  height: double.infinity,
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      colors: [
                        Color(0xFF4F46E5), // Indigo primary
                        Color(0xFF3730A3), // Deep Indigo
                        Color(0xFF1E1B4B), // Midnight Indigo
                      ],
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                    ),
                  ),
                  child: CustomPaint(
                    painter: SplashBackgroundPainter(
                      pulseProgress: _pulseController.value,
                      gridOpacity: _exitFadeAnim.value,
                    ),
                  ),
                ),

                // 2. Top Bar "Lewati" Action
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

                          // MHC School Identity Logo with Scale-Up & Ease-Out
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

                          // Adaptive Micro-Greeting & Identity Typography
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

                // 4. Bottom System Loading Indicator
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
