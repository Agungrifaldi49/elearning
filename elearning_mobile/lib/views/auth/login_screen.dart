import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../siswa/siswa_main_screen.dart';
import '../guru/guru_main_screen.dart';
import 'onboarding_screen.dart';
import 'widgets/splash_background_painter.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen>
    with SingleTickerProviderStateMixin {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscureText = true;
  String _selectedRole = 'siswa'; // 'siswa' or 'guru'

  late AnimationController _floatingPulseController;

  @override
  void initState() {
    super.initState();
    ApiService.initBaseUrl();

    _floatingPulseController = AnimationController(
      vsync: this,
      duration: const Duration(seconds: 4),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _usernameController.dispose();
    _passwordController.dispose();
    _floatingPulseController.dispose();
    super.dispose();
  }

  final List<String> _siswaQuotes = [
    "🚀 Setiap langkah kecil dalam belajar adalah lompatan besar menuju cita-cita impianmu! Semangat KBM di SMK Muthia Harapan Cicalengka!",
    "💡 Masa depan adalah milik mereka yang mempersiapkan hari ini dengan tekun dan disiplin. Selamat belajar!",
    "🔥 Sukses tidak datang dari kenyamanan, melainkan dari semangat dan kerja keras yang tak pernah padam. Tetap optimis!",
    "🎓 Jadikan setiap ilmu sebagai bekal berharga untuk membuka gerbang kesuksesanmu di masa depan!"
  ];

  final List<String> _guruQuotes = [
    "👨‍🏫 Selamat bertugas Bpk/Ibu Guru! Terima kasih atas dedikasi tanpa henti dalam mencerdaskan generasi penerus bangsa!",
    "💡 Mengajar bukan sekadar memberikan materi, melainkan menyalakan api inspirasi dan semangat belajar di hati siswa.",
    "⭐ Setiap bimbingan dan kesabaran Anda adalah pondasi kokoh bagi masa depan para siswa. Tetap semangat!",
    "📚 Dedikasi Anda hari ini menciptakan para pemimpin dan profesional hebat di masa esok!"
  ];

  void _showServerConfigDialog() {
    final serverController = TextEditingController(text: ApiService.baseUrl);

    showDialog(
      context: context,
      builder: (context) {
        final isDark = Theme.of(context).brightness == Brightness.dark;
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppTheme.primaryColor.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.dns_rounded, color: AppTheme.primaryColor, size: 22),
              ),
              const SizedBox(width: 12),
              Text(
                'Pengaturan Server API',
                style: GoogleFonts.outfit(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : AppTheme.lightTextPrimary,
                ),
              ),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Pilih atau masukkan URL server backend E-Learning:',
                  style: GoogleFonts.inter(
                    fontSize: 12,
                    color: isDark ? Colors.white60 : Colors.grey.shade600,
                  ),
                ),
                const SizedBox(height: 14),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.cloud_done_rounded, color: Colors.green),
                  title: Text(
                    'Domain Online (Resmi)',
                    style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  subtitle: const Text('smkmuthiaharapancicalengka.my.id', style: TextStyle(fontSize: 11)),
                  onTap: () => serverController.text = ApiService.defaultOnlineUrl,
                ),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.computer_rounded, color: Colors.purple),
                  title: Text(
                    'Localhost PC (Apache / XAMPP)',
                    style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  subtitle: const Text('http://localhost/elearning/api.php?action=', style: TextStyle(fontSize: 11)),
                  onTap: () => serverController.text = 'http://localhost/elearning/api.php?action=',
                ),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.phone_android_rounded, color: Colors.amber),
                  title: Text(
                    'Emulator Android (10.0.2.2)',
                    style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  subtitle: const Text('http://10.0.2.2/elearning/api.php?action=', style: TextStyle(fontSize: 11)),
                  onTap: () => serverController.text = 'http://10.0.2.2/elearning/api.php?action=',
                ),
                ListTile(
                  contentPadding: EdgeInsets.zero,
                  leading: const Icon(Icons.wifi_rounded, color: Colors.blue),
                  title: Text(
                    'Server Wi-Fi Lokal (192.168.100.26)',
                    style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold),
                  ),
                  subtitle: const Text('http://192.168.100.26/api.php?action=', style: TextStyle(fontSize: 11)),
                  onTap: () => serverController.text = 'http://192.168.100.26/api.php?action=',
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: serverController,
                  style: const TextStyle(fontSize: 12),
                  decoration: InputDecoration(
                    labelText: 'Custom Server Base URL',
                    hintText: 'https://...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () async {
                final newUrl = serverController.text.trim();
                if (newUrl.isNotEmpty) {
                  await ApiService.setBaseUrl(newUrl);
                  if (mounted) {
                    Navigator.pop(context);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('URL Server berhasil diubah ke:\n$newUrl'),
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  }
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Simpan URL'),
            ),
          ],
        );
      },
    );
  }

  void _showModernValidationToast(String message) {
    ScaffoldMessenger.of(context).hideCurrentSnackBar();
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        backgroundColor: const Color(0xFF0F172A),
        elevation: 8,
        content: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: Colors.amber.withValues(alpha: 0.2),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.warning_amber_rounded, color: Colors.amberAccent, size: 20),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                message,
                style: GoogleFonts.inter(
                  color: Colors.white,
                  fontWeight: FontWeight.w600,
                  fontSize: 13,
                ),
              ),
            ),
          ],
        ),
        duration: const Duration(seconds: 4),
      ),
    );
  }

  void _showModernErrorDialog(String? rawError) {
    final String errorMsg = rawError ?? 'Username atau Password yang Anda masukkan tidak valid.';
    final bool isDark = Theme.of(context).brightness == Brightness.dark;

    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (context) {
        return Dialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
          elevation: 12,
          backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
          child: Container(
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(28),
              gradient: LinearGradient(
                colors: isDark
                    ? [const Color(0xFF1E293B), const Color(0xFF0F172A)]
                    : [Colors.white, Colors.red.shade50.withValues(alpha: 0.5)],
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
              ),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 72,
                  height: 72,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: const LinearGradient(
                      colors: [Color(0xFFEF4444), Color(0xFFDC2626)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.redAccent.withValues(alpha: 0.35),
                        blurRadius: 18,
                        offset: const Offset(0, 6),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.lock_reset_rounded,
                    color: Colors.white,
                    size: 38,
                  ),
                ),
                const SizedBox(height: 18),
                Text(
                  'Gagal Masuk',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.outfit(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white : const Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 8),
                Text(
                  errorMsg,
                  textAlign: TextAlign.center,
                  style: GoogleFonts.inter(
                    fontSize: 14,
                    height: 1.4,
                    color: isDark ? Colors.red.shade200 : Colors.red.shade700,
                    fontWeight: FontWeight.w500,
                  ),
                ),
                const SizedBox(height: 18),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: isDark ? const Color(0xFF334155) : Colors.amber.shade50,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isDark ? Colors.amber.shade700.withValues(alpha: 0.3) : Colors.amber.shade300,
                    ),
                  ),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Icon(
                        Icons.lightbulb_rounded,
                        size: 20,
                        color: isDark ? Colors.amber.shade400 : Colors.amber.shade800,
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Pastikan Anda telah memilih peranan (${_selectedRole == 'guru' ? 'Guru' : 'Siswa'}) dengan benar & penulisan password sudah tepat.',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            height: 1.35,
                            color: isDark ? Colors.amber.shade100 : Colors.amber.shade900,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 22),
                Column(
                  children: [
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: () => Navigator.pop(context),
                        icon: const Icon(Icons.refresh_rounded, color: Colors.white),
                        label: Text(
                          'Coba Lagi',
                          style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                        ),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.dangerColor,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                          elevation: 4,
                        ),
                      ),
                    ),
                    const SizedBox(height: 8),
                    SizedBox(
                      width: double.infinity,
                      height: 44,
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Navigator.pop(context);
                          _showServerConfigDialog();
                        },
                        icon: const Icon(Icons.settings_suggest_rounded, size: 18),
                        label: Text(
                          'Pengaturan Server API',
                          style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.w600),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: isDark ? Colors.white70 : AppTheme.primaryColor,
                          side: BorderSide(
                            color: isDark ? Colors.white24 : AppTheme.primaryColor.withValues(alpha: 0.5),
                          ),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _handleLogin() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text.trim();

    if (username.isEmpty || password.isEmpty) {
      _showModernValidationToast('Username dan Password wajib diisi!');
      return;
    }

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.login(username, password);

    if (!mounted) return;

    if (success) {
      final user = authProvider.currentUser!;
      final bool isGuru = user.isGuru;
      final String name = user.fullName.isNotEmpty ? user.fullName : (isGuru ? 'Bapak/Ibu Guru' : 'Siswa');

      final quotes = isGuru ? _guruQuotes : _siswaQuotes;
      final String selectedQuote = (quotes..shuffle()).first;

      await showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) {
          return Dialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(28)),
            elevation: 10,
            child: Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(28),
                gradient: LinearGradient(
                  colors: [
                    Colors.white,
                    isGuru ? Colors.blue.shade50 : Colors.indigo.shade50,
                  ],
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                ),
              ),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 72,
                    height: 72,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      gradient: isGuru ? AppTheme.primaryGradient : AppTheme.siswaGradient,
                      boxShadow: [
                        BoxShadow(
                          color: (isGuru ? AppTheme.primaryColor : AppTheme.secondaryColor).withValues(alpha: 0.4),
                          blurRadius: 16,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Icon(
                      isGuru ? Icons.auto_awesome_rounded : Icons.rocket_launch_rounded,
                      color: Colors.white,
                      size: 36,
                    ),
                  ),
                  const SizedBox(height: 18),
                  Text(
                    'Halo, $name! 👋',
                    textAlign: TextAlign.center,
                    style: GoogleFonts.outfit(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Kata Semangat Hari Ini:',
                    style: GoogleFonts.inter(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: isGuru ? AppTheme.primaryColor : AppTheme.secondaryColor,
                    ),
                  ),
                  const SizedBox(height: 14),
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(
                        color: (isGuru ? AppTheme.primaryColor : AppTheme.secondaryColor).withValues(alpha: 0.2),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.03),
                          blurRadius: 8,
                          offset: const Offset(0, 2),
                        ),
                      ],
                    ),
                    child: Text(
                      selectedQuote,
                      textAlign: TextAlign.center,
                      style: GoogleFonts.inter(
                        fontSize: 14,
                        height: 1.4,
                        fontStyle: FontStyle.italic,
                        color: Colors.black87,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  const SizedBox(height: 22),
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.arrow_forward_rounded, color: Colors.white),
                      label: Text(
                        'Masuk ke Dashboard 🚀',
                        style: GoogleFonts.outfit(fontSize: 15, fontWeight: FontWeight.bold),
                      ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: isGuru ? AppTheme.primaryColor : AppTheme.secondaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 4,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      );

      if (!mounted) return;

      if (user.isGuru) {
        Navigator.of(context).pushAndRemoveUntil(
          PageRouteBuilder(
            transitionDuration: const Duration(milliseconds: 450),
            pageBuilder: (context, animation, secondaryAnimation) => const GuruMainScreen(),
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
          (route) => false,
        );
      } else {
        Navigator.of(context).pushAndRemoveUntil(
          PageRouteBuilder(
            transitionDuration: const Duration(milliseconds: 450),
            pageBuilder: (context, animation, secondaryAnimation) => const SiswaMainScreen(),
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
          (route) => false,
        );
      }
    } else {
      _showModernErrorDialog(authProvider.errorMessage);
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final primaryGradient = _selectedRole == 'guru'
        ? const LinearGradient(
            colors: [Color(0xFF4F46E5), Color(0xFF3730A3)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          )
        : const LinearGradient(
            colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          );

    final activeColor = _selectedRole == 'guru' ? const Color(0xFF4F46E5) : const Color(0xFF0EA5E9);

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      body: Stack(
        children: [
          // Ambient Background Grid Painter
          Positioned.fill(
            child: AnimatedBuilder(
              animation: _floatingPulseController,
              builder: (context, child) {
                return CustomPaint(
                  painter: SplashBackgroundPainter(
                    pulseProgress: _floatingPulseController.value,
                    gridOpacity: isDark ? 0.25 : 0.15,
                  ),
                );
              },
            ),
          ),

          // Main Screen Scrollable Content
          SingleChildScrollView(
            physics: const BouncingScrollPhysics(),
            child: Column(
              children: [
                // 1. Premium Header Hero Section
                Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: primaryGradient,
                    borderRadius: const BorderRadius.only(
                      bottomLeft: Radius.circular(40),
                      bottomRight: Radius.circular(40),
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: activeColor.withValues(alpha: 0.35),
                        blurRadius: 24,
                        offset: const Offset(0, 10),
                      ),
                    ],
                  ),
                  child: SafeArea(
                    bottom: false,
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(24, 12, 24, 36),
                      child: Column(
                        children: [
                          // Top Header Actions Pill Bar
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              // School Brand Tag
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(
                                    color: Colors.white.withValues(alpha: 0.25),
                                  ),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.school_rounded, color: Colors.white, size: 16),
                                    const SizedBox(width: 6),
                                    Text(
                                      'MHC E-LEARNING',
                                      style: GoogleFonts.outfit(
                                        color: Colors.white,
                                        fontSize: 12,
                                        fontWeight: FontWeight.bold,
                                        letterSpacing: 0.5,
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              // Header Action Icons
                              Row(
                                children: [
                                  IconButton(
                                    icon: Container(
                                      padding: const EdgeInsets.all(7),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.15),
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.rocket_launch_rounded, color: Colors.white, size: 18),
                                    ),
                                    tooltip: 'Perkenalan Aplikasi',
                                    onPressed: () {
                                      Navigator.of(context).push(
                                        MaterialPageRoute(builder: (_) => const OnboardingScreen()),
                                      );
                                    },
                                  ),
                                  IconButton(
                                    icon: Container(
                                      padding: const EdgeInsets.all(7),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(alpha: 0.15),
                                        shape: BoxShape.circle,
                                      ),
                                      child: const Icon(Icons.settings_suggest_rounded, color: Colors.white, size: 18),
                                    ),
                                    tooltip: 'Pengaturan Server API',
                                    onPressed: _showServerConfigDialog,
                                  ),
                                ],
                              ),
                            ],
                          ),

                          const SizedBox(height: 20),

                          // Floating MHC Brand Logo
                          AnimatedBuilder(
                            animation: _floatingPulseController,
                            builder: (context, child) {
                              return Transform.translate(
                                offset: Offset(0, -4 + (_floatingPulseController.value * 8)),
                                child: Container(
                                  padding: const EdgeInsets.all(14),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    shape: BoxShape.circle,
                                    boxShadow: [
                                      BoxShadow(
                                        color: Colors.black.withValues(alpha: 0.2),
                                        blurRadius: 20,
                                        offset: const Offset(0, 8),
                                      ),
                                    ],
                                  ),
                                  child: Image.asset(
                                    'assets/logo/mhc_logo.png',
                                    width: 64,
                                    height: 64,
                                    fit: BoxFit.contain,
                                  ),
                                ),
                              );
                            },
                          ),

                          const SizedBox(height: 18),

                          // School Name Title
                          Text(
                            'SMK Muthia Harapan',
                            style: GoogleFonts.outfit(
                              color: Colors.white,
                              fontSize: 26,
                              fontWeight: FontWeight.bold,
                              letterSpacing: 0.5,
                            ),
                          ),
                          const SizedBox(height: 6),

                          // Subtitle Pill
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.18),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.white.withValues(alpha: 0.3)),
                            ),
                            child: Text(
                              'Sistem Pembelajaran Digital Terpadu',
                              style: GoogleFonts.inter(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),

                // 2. Main Login Form Container
                Padding(
                  padding: const EdgeInsets.all(24.0),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Selamat Datang! 👋',
                        style: GoogleFonts.outfit(
                          fontSize: 24,
                          fontWeight: FontWeight.bold,
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 6),
                      Text(
                        'Silakan login menggunakan akun Guru atau Siswa Anda.',
                        style: GoogleFonts.inter(
                          color: isDark ? AppTheme.darkTextSecondary : AppTheme.lightTextSecondary,
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 24),

                      // Animated Role Switcher Tabs (Siswa / Guru)
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF1E293B) : Colors.white,
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(
                            color: isDark ? Colors.white.withValues(alpha: 0.1) : Colors.grey.shade300,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.04),
                              blurRadius: 10,
                              offset: const Offset(0, 4),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            // Siswa Role Switcher Button
                            Expanded(
                              child: GestureDetector(
                                onTap: () => setState(() => _selectedRole = 'siswa'),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 250),
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  decoration: BoxDecoration(
                                    gradient: _selectedRole == 'siswa'
                                        ? const LinearGradient(
                                            colors: [Color(0xFF0EA5E9), Color(0xFF0284C7)],
                                          )
                                        : null,
                                    borderRadius: BorderRadius.circular(16),
                                    boxShadow: _selectedRole == 'siswa'
                                        ? [
                                            BoxShadow(
                                              color: const Color(0xFF0EA5E9).withValues(alpha: 0.35),
                                              blurRadius: 10,
                                              offset: const Offset(0, 4),
                                            ),
                                          ]
                                        : [],
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.school_rounded,
                                        color: _selectedRole == 'siswa'
                                            ? Colors.white
                                            : (isDark ? Colors.white60 : Colors.grey.shade600),
                                        size: 20,
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        'Siswa',
                                        style: GoogleFonts.outfit(
                                          color: _selectedRole == 'siswa'
                                              ? Colors.white
                                              : (isDark ? Colors.white70 : Colors.grey.shade700),
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),

                            // Guru Role Switcher Button
                            Expanded(
                              child: GestureDetector(
                                onTap: () => setState(() => _selectedRole = 'guru'),
                                child: AnimatedContainer(
                                  duration: const Duration(milliseconds: 250),
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  decoration: BoxDecoration(
                                    gradient: _selectedRole == 'guru'
                                        ? const LinearGradient(
                                            colors: [Color(0xFF4F46E5), Color(0xFF3730A3)],
                                          )
                                        : null,
                                    borderRadius: BorderRadius.circular(16),
                                    boxShadow: _selectedRole == 'guru'
                                        ? [
                                            BoxShadow(
                                              color: const Color(0xFF4F46E5).withValues(alpha: 0.35),
                                              blurRadius: 10,
                                              offset: const Offset(0, 4),
                                            ),
                                          ]
                                        : [],
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      Icon(
                                        Icons.co_present_rounded,
                                        color: _selectedRole == 'guru'
                                            ? Colors.white
                                            : (isDark ? Colors.white60 : Colors.grey.shade600),
                                        size: 20,
                                      ),
                                      const SizedBox(width: 8),
                                      Text(
                                        'Guru',
                                        style: GoogleFonts.outfit(
                                          color: _selectedRole == 'guru'
                                              ? Colors.white
                                              : (isDark ? Colors.white70 : Colors.grey.shade700),
                                          fontWeight: FontWeight.bold,
                                          fontSize: 15,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Username Input Field
                      TextField(
                        controller: _usernameController,
                        style: GoogleFonts.inter(
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                          fontSize: 15,
                        ),
                        decoration: InputDecoration(
                          labelText: 'Username / NIP / NISN',
                          labelStyle: GoogleFonts.inter(
                            color: isDark ? Colors.white60 : Colors.grey.shade600,
                          ),
                          prefixIcon: Icon(
                            Icons.account_circle_outlined,
                            color: activeColor,
                          ),
                          filled: true,
                          fillColor: isDark ? const Color(0xFF1E293B) : Colors.white,
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(
                              color: isDark ? Colors.white.withValues(alpha: 0.1) : Colors.grey.shade300,
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: activeColor, width: 2),
                          ),
                          contentPadding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
                        ),
                      ),

                      const SizedBox(height: 16),

                      // Password Input Field
                      TextField(
                        controller: _passwordController,
                        obscureText: _obscureText,
                        style: GoogleFonts.inter(
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                          fontSize: 15,
                        ),
                        decoration: InputDecoration(
                          labelText: 'Password',
                          labelStyle: GoogleFonts.inter(
                            color: isDark ? Colors.white60 : Colors.grey.shade600,
                          ),
                          prefixIcon: Icon(
                            Icons.lock_outline_rounded,
                            color: activeColor,
                          ),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscureText ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                              color: isDark ? Colors.white60 : Colors.grey.shade600,
                            ),
                            onPressed: () {
                              setState(() {
                                _obscureText = !_obscureText;
                              });
                            },
                          ),
                          filled: true,
                          fillColor: isDark ? const Color(0xFF1E293B) : Colors.white,
                          enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(
                              color: isDark ? Colors.white.withValues(alpha: 0.1) : Colors.grey.shade300,
                            ),
                          ),
                          focusedBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(16),
                            borderSide: BorderSide(color: activeColor, width: 2),
                          ),
                          contentPadding: const EdgeInsets.symmetric(vertical: 18, horizontal: 16),
                        ),
                      ),

                      const SizedBox(height: 28),

                      // Main Gradient Login Button
                      SizedBox(
                        width: double.infinity,
                        height: 54,
                        child: ElevatedButton(
                          onPressed: authProvider.isLoading ? null : _handleLogin,
                          style: ElevatedButton.styleFrom(
                            padding: EdgeInsets.zero,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                            elevation: 8,
                            shadowColor: activeColor.withValues(alpha: 0.4),
                          ),
                          child: Ink(
                            decoration: BoxDecoration(
                              gradient: primaryGradient,
                              borderRadius: BorderRadius.circular(16),
                            ),
                            child: Container(
                              alignment: Alignment.center,
                              child: authProvider.isLoading
                                  ? const SizedBox(
                                      width: 24,
                                      height: 24,
                                      child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                                    )
                                  : Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        const Icon(Icons.login_rounded, color: Colors.white, size: 20),
                                        const SizedBox(width: 10),
                                        Text(
                                          'Masuk Sebagai ${_selectedRole == 'guru' ? 'Guru' : 'Siswa'}',
                                          style: GoogleFonts.outfit(
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold,
                                            color: Colors.white,
                                            letterSpacing: 0.5,
                                          ),
                                        ),
                                      ],
                                    ),
                            ),
                          ),
                        ),
                      ),

                      const SizedBox(height: 28),

                      // Footer System Info
                      Center(
                        child: Text(
                          'MHC E-Learning Mobile v2.0 • SMK Muthia Harapan Cicalengka',
                          style: GoogleFonts.inter(
                            fontSize: 12,
                            color: isDark ? Colors.white38 : Colors.grey.shade500,
                            fontWeight: FontWeight.w500,
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
