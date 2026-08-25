import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../siswa/siswa_main_screen.dart';
import '../guru/guru_main_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _usernameController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscureText = true;
  String _selectedRole = 'siswa'; // 'siswa' or 'guru'

  @override
  void initState() {
    super.initState();
    ApiService.initBaseUrl();
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
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
          title: const Row(
            children: [
              Icon(Icons.dns_rounded, color: AppTheme.primaryColor),
              SizedBox(width: 10),
              Text('Pengaturan Server API', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Pilih atau masukkan URL server backend E-Learning:',
                style: TextStyle(fontSize: 12, color: Colors.grey),
              ),
              const SizedBox(height: 12),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.cloud_done_rounded, color: Colors.green),
                title: const Text('Domain Online (Resmi)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                subtitle: const Text('smkmuthiaharapancicalengka.my.id', style: TextStyle(fontSize: 11)),
                onTap: () {
                  serverController.text = ApiService.defaultOnlineUrl;
                },
              ),
              ListTile(
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.wifi_rounded, color: Colors.blue),
                title: const Text('Server Wi-Fi Lokal (IP 192.168.100.26)', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                subtitle: const Text('http://192.168.100.26/api.php?action=', style: TextStyle(fontSize: 11)),
                onTap: () {
                  serverController.text = 'http://192.168.100.26/api.php?action=';
                },
              ),
              const SizedBox(height: 10),
              TextField(
                controller: serverController,
                style: const TextStyle(fontSize: 12),
                decoration: InputDecoration(
                  labelText: 'Custom Server Base URL',
                  hintText: 'https://...',
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                  contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                ),
              ),
            ],
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
                      SnackBar(content: Text('URL Server berhasil diubah ke:\n$newUrl')),
                    );
                  }
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('Simpan URL'),
            ),
          ],
        );
      },
    );
  }

  void _handleLogin() async {
    final username = _usernameController.text.trim();
    final password = _passwordController.text.trim();

    if (username.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Username dan Password wajib diisi!')),
      );
      return;
    }

    final authProvider = Provider.of<AuthProvider>(context, listen: false);
    final success = await authProvider.login(username, password);

    if (!mounted) return;

    if (success) {
      final user = authProvider.currentUser!;
      final bool isGuru = user.isGuru;
      final String name = user.fullName.isNotEmpty ? user.fullName : (isGuru ? 'Bapak/Ibu Guru' : 'Siswa');
      
      // Select random quote
      final quotes = isGuru ? _guruQuotes : _siswaQuotes;
      final String selectedQuote = (quotes..shuffle()).first;

      // Show Motivational Dialog Popup
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
                  // Animated Rocket / Badge Icon
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

                  // Greeting Title
                  Text(
                    'Halo, $name! 👋',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                      color: Colors.black87,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Kata Semangat Hari Ini:',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: isGuru ? AppTheme.primaryColor : AppTheme.secondaryColor,
                    ),
                  ),
                  const SizedBox(height: 14),

                  // Motivational Quote Container
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
                      style: const TextStyle(
                        fontSize: 14,
                        height: 1.4,
                        fontStyle: FontStyle.italic,
                        color: Colors.black87,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ),
                  const SizedBox(height: 22),

                  // Enter Dashboard Button
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.of(context).pop();
                      },
                      icon: const Icon(Icons.arrow_forward_rounded, color: Colors.white),
                      label: const Text(
                        'Masuk ke Dashboard 🚀',
                        style: TextStyle(fontSize: 15, fontWeight: FontWeight.bold),
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

      // Navigate to destination screen
      if (user.isGuru) {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const GuruMainScreen()),
        );
      } else {
        Navigator.of(context).pushReplacement(
          MaterialPageRoute(builder: (_) => const SiswaMainScreen()),
        );
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(authProvider.errorMessage ?? 'Login gagal! Periksa koneksi internet Anda.'),
          backgroundColor: AppTheme.dangerColor,
          duration: const Duration(seconds: 6),
          action: SnackBarAction(
            label: 'Ganti Server',
            textColor: Colors.amber,
            onPressed: _showServerConfigDialog,
          ),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final authProvider = Provider.of<AuthProvider>(context);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      body: SingleChildScrollView(
        child: Column(
          children: [
            // Modern Header Hero Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.only(top: 45, bottom: 35, left: 24, right: 24),
              decoration: const BoxDecoration(
                gradient: AppTheme.primaryGradient,
                borderRadius: BorderRadius.only(
                  bottomLeft: Radius.circular(36),
                  bottomRight: Radius.circular(36),
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black26,
                    blurRadius: 15,
                    offset: Offset(0, 8),
                  ),
                ],
              ),
              child: Column(
                children: [
                  Align(
                    alignment: Alignment.topRight,
                    child: IconButton(
                      icon: const Icon(Icons.settings_suggest_rounded, color: Colors.white),
                      tooltip: 'Pengaturan Server API',
                      onPressed: _showServerConfigDialog,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      shape: BoxShape.circle,
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.15),
                          blurRadius: 15,
                          offset: const Offset(0, 5),
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
                  const SizedBox(height: 16),
                  const Text(
                    'SMK Muthia Harapan',
                    style: TextStyle(
                      color: Colors.white,
                      fontSize: 24,
                      fontWeight: FontWeight.bold,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Text(
                      'E-Learning Mobile Digital System',
                      style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),

            Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Selamat Datang! 👋',
                    style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Silakan login menggunakan akun Guru atau Siswa Anda.',
                    style: TextStyle(
                      color: isDark ? AppTheme.darkTextSecondary : AppTheme.lightTextSecondary,
                      fontSize: 14,
                    ),
                  ),
                  const SizedBox(height: 24),

                  // Role Selector Tabs
                  Container(
                    padding: const EdgeInsets.all(6),
                    decoration: BoxDecoration(
                      color: isDark ? const Color(0xFF1E293B) : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Row(
                      children: [
                        Expanded(
                          child: GestureDetector(
                            onTap: () => setState(() => _selectedRole = 'siswa'),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: _selectedRole == 'siswa'
                                    ? AppTheme.secondaryColor
                                    : Colors.transparent,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: _selectedRole == 'siswa'
                                    ? [
                                        BoxShadow(
                                          color: AppTheme.secondaryColor.withValues(alpha: 0.3),
                                          blurRadius: 8,
                                          offset: const Offset(0, 3),
                                        ),
                                      ]
                                    : [],
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.person_rounded,
                                    color: _selectedRole == 'siswa' ? Colors.white : Colors.grey,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Siswa',
                                    style: TextStyle(
                                      color: _selectedRole == 'siswa' ? Colors.white : Colors.grey.shade700,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        Expanded(
                          child: GestureDetector(
                            onTap: () => setState(() => _selectedRole = 'guru'),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              padding: const EdgeInsets.symmetric(vertical: 12),
                              decoration: BoxDecoration(
                                color: _selectedRole == 'guru'
                                    ? AppTheme.primaryColor
                                    : Colors.transparent,
                                borderRadius: BorderRadius.circular(12),
                                boxShadow: _selectedRole == 'guru'
                                    ? [
                                        BoxShadow(
                                          color: AppTheme.primaryColor.withValues(alpha: 0.3),
                                          blurRadius: 8,
                                          offset: const Offset(0, 3),
                                        ),
                                      ]
                                    : [],
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    Icons.co_present_rounded,
                                    color: _selectedRole == 'guru' ? Colors.white : Colors.grey,
                                    size: 20,
                                  ),
                                  const SizedBox(width: 8),
                                  Text(
                                    'Guru',
                                    style: TextStyle(
                                      color: _selectedRole == 'guru' ? Colors.white : Colors.grey.shade700,
                                      fontWeight: FontWeight.bold,
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

                  // Username Input
                  TextField(
                    controller: _usernameController,
                    decoration: InputDecoration(
                      labelText: 'Username / NIP / NISN',
                      prefixIcon: const Icon(Icons.account_circle_outlined),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                    ),
                  ),
                  const SizedBox(height: 16),

                  // Password Input
                  TextField(
                    controller: _passwordController,
                    obscureText: _obscureText,
                    decoration: InputDecoration(
                      labelText: 'Password',
                      prefixIcon: const Icon(Icons.lock_outline_rounded),
                      suffixIcon: IconButton(
                        icon: Icon(_obscureText ? Icons.visibility_off_rounded : Icons.visibility_rounded),
                        onPressed: () {
                          setState(() {
                            _obscureText = !_obscureText;
                          });
                        },
                      ),
                      border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
                      contentPadding: const EdgeInsets.symmetric(vertical: 16, horizontal: 16),
                    ),
                  ),

                  const SizedBox(height: 28),

                  // Login Button
                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton.icon(
                      onPressed: authProvider.isLoading ? null : _handleLogin,
                      icon: authProvider.isLoading
                          ? const SizedBox.shrink()
                          : const Icon(Icons.login_rounded, color: Colors.white),
                      label: authProvider.isLoading
                          ? const SizedBox(
                              width: 24,
                              height: 24,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5),
                            )
                          : Text(
                              'Masuk Sebagai ${_selectedRole == 'guru' ? 'Guru' : 'Siswa'}',
                              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                            ),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: _selectedRole == 'guru' ? AppTheme.primaryColor : AppTheme.secondaryColor,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        elevation: 4,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
