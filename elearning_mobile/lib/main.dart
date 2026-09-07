import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'theme/app_theme.dart';
import 'providers/auth_provider.dart';
import 'providers/siswa_provider.dart';
import 'providers/guru_provider.dart';
import 'providers/theme_provider.dart';
import 'views/auth/splash_screen.dart';
import 'services/fcm_service.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();
  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => AuthProvider()),
        ChangeNotifierProvider(create: (_) => SiswaProvider()),
        ChangeNotifierProvider(create: (_) => GuruProvider()),
        ChangeNotifierProvider(create: (_) => ThemeProvider()),
      ],
      child: const ElearningMobileApp(),
    ),
  );

  // Inisialisasi FCM secara asynchronous agar tidak memblokir pembukaan aplikasi
  FcmService.initialize();
}

class ElearningMobileApp extends StatelessWidget {
  const ElearningMobileApp({super.key});

  @override
  Widget build(BuildContext context) {
    final themeProvider = Provider.of<ThemeProvider>(context);

    return MaterialApp(
      navigatorKey: FcmService.navigatorKey,
      title: 'MHC E-LEARNING',
      debugShowCheckedModeBanner: false,
      themeMode: themeProvider.themeMode,
      theme: AppTheme.lightTheme,
      darkTheme: AppTheme.darkTheme,
      home: const SplashScreen(),
    );
  }
}
