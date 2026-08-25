import 'package:flutter/material.dart';
import '../models/materi_model.dart';
import '../models/tugas_model.dart';
import '../models/quiz_model.dart';
import '../models/jadwal_model.dart';
import '../services/api_service.dart';

class GuruProvider with ChangeNotifier {
  bool _isLoading = false;
  Map<String, dynamic>? _dashboardData;
  List<JadwalModel> _jadwalList = [];
  List<MateriModel> _materiList = [];
  List<TugasModel> _tugasList = [];
  List<QuizModel> _quizList = [];

  bool get isLoading => _isLoading;
  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<JadwalModel> get jadwalList => _jadwalList;
  List<MateriModel> get materiList => _materiList;
  List<TugasModel> get tugasList => _tugasList;
  List<QuizModel> get quizList => _quizList;

  Future<void> fetchDashboard(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('guru/dashboard', params: {'user_id': userId.toString()});
    if (res['success'] == true) {
      _dashboardData = res['data'];
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchJadwal(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('guru/jadwal', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _jadwalList = (res['data'] as List).map((e) => JadwalModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchMateri(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('guru/materi', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _materiList = (res['data'] as List).map((e) => MateriModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createMateri(int userId, String judul, String deskripsi, int mapelId, int kelasId, String jenisFile, String youtubeUrl) async {
    final res = await ApiService.post('guru/materi', {
      'user_id': userId,
      'judul': judul,
      'deskripsi': deskripsi,
      'mapel_id': mapelId,
      'kelas_id': kelasId,
      'jenis_file': jenisFile,
      'youtube_url': youtubeUrl,
    });
    if (res['success'] == true) {
      await fetchMateri(userId);
      return true;
    }
    return false;
  }

  Future<void> fetchTugas(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('guru/tugas', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _tugasList = (res['data'] as List).map((e) => TugasModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createTugas(int userId, String judul, String deskripsi, int mapelId, int kelasId, String deadline) async {
    final res = await ApiService.post('guru/tugas', {
      'user_id': userId,
      'action': 'create',
      'judul': judul,
      'deskripsi': deskripsi,
      'mapel_id': mapelId,
      'kelas_id': kelasId,
      'deadline': deadline,
    });
    if (res['success'] == true) {
      await fetchTugas(userId);
      return true;
    }
    return false;
  }

  Future<List<dynamic>> fetchSubmissions(int userId, int tugasId) async {
    final res = await ApiService.get('guru/submissions', params: {
      'user_id': userId.toString(),
      'tugas_id': tugasId.toString(),
    });
    if (res['success'] == true && res['data'] is List) {
      return res['data'];
    }
    return [];
  }

  Future<bool> gradeSubmission(int userId, int submissionId, double nilai, String komentar) async {
    final res = await ApiService.post('guru/tugas', {
      'user_id': userId,
      'action': 'grade',
      'submission_id': submissionId,
      'nilai': nilai,
      'komentar_guru': komentar,
    });
    return res['success'] == true;
  }

  Future<void> fetchQuiz(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('guru/quiz', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _quizList = (res['data'] as List).map((e) => QuizModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> createQuiz(int userId, String judul, String deskripsi, int mapelId, int kelasId, int durasi) async {
    final res = await ApiService.post('guru/quiz', {
      'user_id': userId,
      'judul': judul,
      'deskripsi': deskripsi,
      'mapel_id': mapelId,
      'kelas_id': kelasId,
      'durasi_menit': durasi,
    });
    if (res['success'] == true) {
      await fetchQuiz(userId);
      return true;
    }
    return false;
  }

  Future<List<dynamic>> fetchStudentsForAbsensi(int userId, int kelasId) async {
    final res = await ApiService.get('guru/absensi', params: {
      'user_id': userId.toString(),
      'kelas_id': kelasId.toString(),
    });
    if (res['success'] == true && res['data'] is List) {
      return res['data'];
    }
    return [];
  }

  Future<bool> saveAbsensi(int userId, int jadwalId, Map<int, String> records) async {
    final Map<String, String> formattedRecords = {};
    records.forEach((key, value) {
      formattedRecords[key.toString()] = value;
    });

    final res = await ApiService.post('guru/absensi', {
      'user_id': userId,
      'jadwal_id': jadwalId,
      'records': formattedRecords,
    });
    return res['success'] == true;
  }

  List<dynamic> _susulanList = [];
  List<dynamic> get susulanList => _susulanList;

  Future<void> fetchSusulanRequests(int userId) async {
    final res = await ApiService.get('guru/susulan_requests', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _susulanList = res['data'];
      notifyListeners();
    }
  }

  Future<bool> approveSusulanRequest(int userId, int requestId) async {
    final res = await ApiService.post('guru/approve_susulan', {
      'user_id': userId,
      'request_id': requestId,
    });
    if (res['success'] == true) {
      await fetchSusulanRequests(userId);
      await fetchQuiz(userId);
      return true;
    }
    return false;
  }

  Future<bool> rejectSusulanRequest(int userId, int requestId) async {
    final res = await ApiService.post('guru/reject_susulan', {
      'user_id': userId,
      'request_id': requestId,
    });
    if (res['success'] == true) {
      await fetchSusulanRequests(userId);
      await fetchQuiz(userId);
      return true;
    }
    return false;
  }
}
