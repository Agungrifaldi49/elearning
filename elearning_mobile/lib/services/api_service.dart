import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiService {
  // Production Base URL pointing directly to api.php entry point
  static String baseUrl = 'https://smkmuthiaharapancicalengka.my.id/api.php?action=';

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
      );

      return _handleResponse(response, uri.toString());
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

      final response = await http.get(uri, headers: headers);

      return _handleResponse(response, uri.toString());
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
