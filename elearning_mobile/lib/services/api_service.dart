import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  // Production Base URL pointing directly to api.php entry point
  static const String defaultOnlineUrl = 'https://smkmuthiaharapancicalengka.my.id/api.php?action=';
  static String baseUrl = defaultOnlineUrl;

  static Future<void> initBaseUrl() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      final savedUrl = prefs.getString('custom_api_base_url');
      if (savedUrl != null && savedUrl.isNotEmpty) {
        baseUrl = savedUrl;
      }
    } catch (_) {}
  }

  static Future<void> setBaseUrl(String newUrl) async {
    baseUrl = newUrl;
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setString('custom_api_base_url', newUrl);
    } catch (_) {}
  }

  /// Extracts root web server URL from configured [baseUrl]
  static String get serverRootUrl {
    String url = baseUrl;
    int apiIdx = url.indexOf('api.php');
    if (apiIdx != -1) {
      url = url.substring(0, apiIdx);
    } else {
      int queryIdx = url.indexOf('?');
      if (queryIdx != -1) {
        url = url.substring(0, queryIdx);
      }
    }
    if (!url.endsWith('/')) {
      url = '$url/';
    }
    return url;
  }

  /// Converts relative asset/upload path to absolute server URL matching active server environment
  static String getFileUrl(String? path) {
    if (path == null || path.trim().isEmpty) return '';
    final trimmed = path.trim();
    if (trimmed.startsWith('http://') || trimmed.startsWith('https://')) {
      return trimmed;
    }
    final cleanPath = trimmed.startsWith('/') ? trimmed.substring(1) : trimmed;
    return '$serverRootUrl$cleanPath';
  }

  static Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> body) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };
      final bodyString = jsonEncode(body);

      // Mask sensitive fields in log
      final maskedBody = Map<String, dynamic>.from(body);
      if (maskedBody.containsKey('password')) {
        maskedBody['password'] = '***';
      }

      debugPrint('=== API REQUEST (POST) ===');
      debugPrint('URL: $uri');
      debugPrint('Headers: $headers');
      debugPrint('Body: $maskedBody');

      final response = await http.post(
        uri,
        headers: headers,
        body: bodyString,
      ).timeout(const Duration(seconds: 8));

      return _handleResponse(response, uri.toString());
    } on TimeoutException {
      debugPrint('=== API TIMEOUT ERROR ===');
      return {
        'success': false,
        'message': 'Koneksi ke server timeout (8 detik).\nServer tidak merespons. Periksa koneksi internet atau ganti URL server.'
      };
    } on SocketException catch (e) {
      debugPrint('=== API SOCKET ERROR ===\n$e');
      return {
        'success': false,
        'message': 'Koneksi jaringan terputus (SocketException).\nServer tidak dapat dijangkau dari perangkat Anda.'
      };
    } catch (e) {
      debugPrint('=== API REQUEST ERROR ===\n$e');
      return {'success': false, 'message': 'Koneksi server gagal: $e'};
    }
  }

  static Future<Map<String, dynamic>> get(String endpoint, {Map<String, String>? params}) async {
    try {
      var uri = Uri.parse('$baseUrl$endpoint');
      if (params != null && params.isNotEmpty) {
        final query = Map<String, String>.from(uri.queryParameters);
        query.addAll(params);
        uri = uri.replace(queryParameters: query);
      }

      final headers = {
        'Accept': 'application/json',
      };

      debugPrint('=== API REQUEST (GET) ===');
      debugPrint('URL: $uri');
      debugPrint('Headers: $headers');

      final response = await http.get(uri, headers: headers).timeout(const Duration(seconds: 8));

      return _handleResponse(response, uri.toString());
    } on TimeoutException {
      debugPrint('=== API TIMEOUT ERROR ===');
      return {
        'success': false,
        'message': 'Koneksi ke server timeout (8 detik).'
      };
    } on SocketException catch (e) {
      debugPrint('=== API SOCKET ERROR ===\n$e');
      return {
        'success': false,
        'message': 'Gagal terhubung ke jaringan server.'
      };
    } catch (e) {
      debugPrint('=== API REQUEST ERROR ===\n$e');
      return {'success': false, 'message': 'Koneksi server gagal: $e'};
    }
  }

  static Map<String, dynamic> _handleResponse(http.Response response, String url) {
    final statusCode = response.statusCode;
    final headers = response.headers;
    final rawBody = response.body;
    final trimmedBody = rawBody.trim();
    final contentType = headers['content-type'] ?? headers['Content-Type'] ?? '';

    debugPrint('=== API RESPONSE ===');
    debugPrint('URL: $url');
    debugPrint('StatusCode: $statusCode');
    debugPrint('Headers: $headers');
    debugPrint('ContentType: $contentType');
    debugPrint('BodyLength: ${rawBody.length}');
    debugPrint('BodyContent: $trimmedBody');

    // 1. Validation for empty body
    if (trimmedBody.isEmpty) {
      return {
        'success': false,
        'message': 'Server mengembalikan response kosong.\n(StatusCode: $statusCode)'
      };
    }

    // 2. Validation for non-JSON / HTML response
    if (trimmedBody.startsWith('<') || trimmedBody.toLowerCase().startsWith('<!doctype')) {
      final snippet = trimmedBody.length > 200 ? trimmedBody.substring(0, 200) : trimmedBody;
      return {
        'success': false,
        'message': 'Server tidak mengembalikan JSON.\nSnippet: $snippet'
      };
    }

    // 3. Attempt JSON decoding safely
    try {
      final decoded = jsonDecode(trimmedBody);
      if (decoded is Map<String, dynamic>) {
        return decoded;
      }
      return {
        'success': false,
        'message': 'Respon dari server tidak berbentuk JSON Map yang valid.'
      };
    } catch (e) {
      final snippet = trimmedBody.length > 150 ? trimmedBody.substring(0, 150) : trimmedBody;
      return {
        'success': false,
        'message': 'Gagal dekode JSON: $e\nResponse Mentah: $snippet'
      };
    }
  }
}
