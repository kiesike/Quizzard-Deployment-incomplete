import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class AuthService {
  static const String baseUrl = 'http://172.21.22.155:8000/api';

  // ─── LOGIN ───────────────────────────────────────────────
  static Future<Map<String, dynamic>> login(
      String email, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$baseUrl/login'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'email': email, 'password': password}),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('token', data['token']);
        await prefs.setString('role', data['user']['role']);
        await prefs.setString('name', data['user']['name']);
        await prefs.setInt('user_id', data['user']['id']);
        return {'success': true, 'data': data};
      } else {
        return {'success': false, 'message': data['message']};
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Cannot connect to server. Please check your connection.'
      };
    }
  }

  // ─── LOGOUT ──────────────────────────────────────────────
  static Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }

  // ─── GETTERS ─────────────────────────────────────────────
  static Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('token');
  }

  static Future<String?> getRole() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('role');
  }

  static Future<String?> getName() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('name');
  }

  static Future<bool> isLoggedIn() async {
    final token = await getToken();
    return token != null;
  }

  // ─── AUTHENTICATED GET REQUEST ───────────────────────────
  static Future<Map<String, dynamic>> authGet(String endpoint) async {
    try {
      final token = await getToken();
      final response = await http.get(
        Uri.parse('$baseUrl$endpoint'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200) {
        return {'success': true, 'data': data};
      } else {
        return {'success': false, 'message': data['message']};
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Cannot connect to server. Please check your connection.'
      };
    }
  }

  // ─── AUTHENTICATED POST REQUEST ──────────────────────────
  static Future<Map<String, dynamic>> authPost(
      String endpoint, Map<String, dynamic> body) async {
    try {
      final token = await getToken();
      final response = await http.post(
        Uri.parse('$baseUrl$endpoint'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode(body),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 || response.statusCode == 201) {
        return {'success': true, 'data': data};
      } else {
        return {'success': false, 'message': data['message']};
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Cannot connect to server. Please check your connection.'
      };
    }
  }


// ─── AUTHENTICATED PATCH REQUEST ─────────────────────────
  static Future<Map<String, dynamic>> authPatch(
      String endpoint, Map<String, dynamic> body) async {
    try {
      final token = await getToken();
      final response = await http.patch(
        Uri.parse('$baseUrl$endpoint'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
        body: jsonEncode(body),
      );
      final data = jsonDecode(response.body);
      if (response.statusCode >= 200 && response.statusCode < 300) {
        return {'success': true, 'data': data['data'], 'message': data['message'] ?? ''};
      } else {
        return {'success': false, 'message': data['message']};
      }
    } catch (e) {
      return {
        'success': false,
        'message': 'Cannot connect to server. Please check your connection.'
      };
    }
  }
  // ============================================================
  // ALSO ensure clearToken() exists in AuthService.
  // If it doesn't exist, add:
  // ============================================================

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }
}