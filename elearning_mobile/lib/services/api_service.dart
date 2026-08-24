import 'dart:convert';
import 'package:http/http.dart' as http;

class ApiService {
  // Primary Base URL pointing directly to api.php entry point
  static String baseUrl = 'https://smkmuthiaharapancicalengka.my.id/api.php?action=';
  
  // Fallback Base URL pointing to clean /api/ route or index.php?url=api/
  static String fallbackUrl = 'https://smkmuthiaharapancicalengka.my.id/index.php?url=api/';

  static Future<Map<String, dynamic>> post(String endpoint, Map<String, dynamic> body) async {
    try {
      final uri = Uri.parse('$baseUrl$endpoint');
      final response = await http.post(
        uri,
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );

      if (_isHtmlResponse(response.body)) {
        final fallbackUri = Uri.parse('$fallbackUrl$endpoint');
        final fallbackResponse = await http.post(
          fallbackUri,
          headers: {'Content-Type': 'application/json'},
          body: jsonEncode(body),
        );
        return _parseResponse(fallbackResponse);
      }

      return _parseResponse(response);
    } catch (e) {
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
      final response = await http.get(uri);

      if (_isHtmlResponse(response.body)) {
        var fallbackUri = Uri.parse('$fallbackUrl$endpoint');
        if (params != null && params.isNotEmpty) {
          final query = Map<String, String>.from(fallbackUri.queryParameters);
          query.addAll(params);
          fallbackUri = fallbackUri.replace(queryParameters: query);
        }
        final fallbackResponse = await http.get(fallbackUri);
        return _parseResponse(fallbackResponse);
      }

      return _parseResponse(response);
    } catch (e) {
      return {'success': false, 'message': 'Koneksi server gagal: $e'};
    }
  }

  static bool _isHtmlResponse(String body) {
    final trimmed = body.trim().toLowerCase();
    return trimmed.startsWith('<') || trimmed.startsWith('<!doctype');
  }

  static Map<String, dynamic> _parseResponse(http.Response response) {
    final body = response.body.trim();

    if (_isHtmlResponse(body)) {
      return {
        'success': false,
        'message': 'Server hosting mengembalikan respons HTML (bukan JSON).\n'
            'Silakan upload file "api.php", "controllers/ApiController.php", dan ".htaccess" '
            'ke hosting smkmuthiaharapancicalengka.my.id.'
      };
    }

    try {
      final decoded = jsonDecode(body);
      if (decoded is Map<String, dynamic>) {
        return decoded;
      }
      return {'success': false, 'message': 'Respon dari server tidak berbentuk JSON yang valid.'};
    } catch (e) {
      return {
        'success': false,
        'message': 'Gagal memproses data server: $e\nData yang diterima: ${body.length > 100 ? body.substring(0, 100) : body}'
      };
    }
  }
}
