import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/fcm_service.dart';

class AuthProvider with ChangeNotifier {
  UserModel? _currentUser;
  bool _isLoading = false;
  String? _errorMessage;

  UserModel? get currentUser => _currentUser;
  bool get isLoading => _isLoading;
  String? get errorMessage => _errorMessage;
  bool get isAuthenticated => _currentUser != null;

  Future<void> checkAutoLogin() async {
    _isLoading = true;
    notifyListeners();
    _currentUser = await AuthService.loadSession();
    if (_currentUser != null) {
      try {
        FcmService.sendTokenToBackend(_currentUser!.id);
      } catch (_) {}
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> login(String username, String password) async {
    _isLoading = true;
    _errorMessage = null;
    notifyListeners();

    final res = await ApiService.post('login', {
      'username': username,
      'password': password,
    });

    _isLoading = false;

    if (res['success'] == true) {
      final user = res['data']['user'];
      final details = res['data']['details'];
      final role = res['data']['role'];

      _currentUser = UserModel.fromJson(user, details, role);
      await AuthService.saveSession(user, details, role);
      try {
        FcmService.sendTokenToBackend(_currentUser!.id);
      } catch (_) {}
      notifyListeners();
      return true;
    } else {
      _errorMessage = res['message'] ?? 'Login gagal';
      notifyListeners();
      return false;
    }
  }

  Future<void> updateUser(Map<String, dynamic> userData, Map<String, dynamic>? detailsData, String roleStr) async {
    _currentUser = UserModel.fromJson(userData, detailsData, roleStr);
    await AuthService.saveSession(userData, detailsData, roleStr);
    notifyListeners();
  }

  Future<void> logout() async {
    await AuthService.clearSession();
    _currentUser = null;
    notifyListeners();
  }
}
