import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';

class AuthService {
  static const String _userKey = 'user_data';
  static const String _detailsKey = 'user_details';
  static const String _roleKey = 'user_role';
  static const String _onboardingKey = 'has_seen_onboarding';

  static Future<bool> hasSeenOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getBool(_onboardingKey) ?? false;
  }

  static Future<void> setHasSeenOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool(_onboardingKey, true);
  }

  static Future<void> saveSession(Map<String, dynamic> user, Map<String, dynamic>? details, String role) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_userKey, jsonEncode(user));
    if (details != null) {
      await prefs.setString(_detailsKey, jsonEncode(details));
    } else {
      await prefs.remove(_detailsKey);
    }
    await prefs.setString(_roleKey, role);
  }

  static Future<UserModel?> loadSession() async {
    final prefs = await SharedPreferences.getInstance();
    final userStr = prefs.getString(_userKey);
    final roleStr = prefs.getString(_roleKey);
    if (userStr == null || roleStr == null) return null;

    final userData = jsonDecode(userStr);
    final detailsStr = prefs.getString(_detailsKey);
    final detailsData = detailsStr != null ? jsonDecode(detailsStr) : null;

    return UserModel.fromJson(userData, detailsData, roleStr);
  }

  static Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_userKey);
    await prefs.remove(_detailsKey);
    await prefs.remove(_roleKey);
  }
}
