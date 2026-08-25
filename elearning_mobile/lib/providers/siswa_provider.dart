import 'package:flutter/material.dart';
import '../models/materi_model.dart';
import '../models/tugas_model.dart';
import '../models/quiz_model.dart';
import '../models/jadwal_model.dart';
import '../models/absensi_model.dart';
import '../models/nilai_model.dart';
import '../services/api_service.dart';

class SiswaProvider with ChangeNotifier {
  bool _isLoading = false;
  Map<String, dynamic>? _dashboardData;
  List<JadwalModel> _jadwalList = [];
  List<MateriModel> _materiList = [];
  List<TugasModel> _tugasList = [];
  List<QuizModel> _quizList = [];
  List<AbsensiModel> _absensiList = [];
  List<NilaiModel> _nilaiList = [];

  bool get isLoading => _isLoading;
  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<JadwalModel> get jadwalList => _jadwalList;
  List<MateriModel> get materiList => _materiList;
  List<TugasModel> get tugasList => _tugasList;
  List<QuizModel> get quizList => _quizList;
  List<AbsensiModel> get absensiList => _absensiList;
  List<NilaiModel> get nilaiList => _nilaiList;

  Future<void> fetchDashboard(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/dashboard', params: {'user_id': userId.toString()});
    if (res['success'] == true) {
      _dashboardData = res['data'];
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchJadwal(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/jadwal', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _jadwalList = (res['data'] as List).map((e) => JadwalModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchMateri(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/materi', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _materiList = (res['data'] as List).map((e) => MateriModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<void> fetchTugas(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/tugas', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _tugasList = (res['data'] as List).map((e) => TugasModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> submitTugas(int userId, int tugasId, String catatan, String filePath) async {
    final res = await ApiService.post('siswa/submit_tugas', {
      'user_id': userId,
      'tugas_id': tugasId,
      'catatan_siswa': catatan,
      'file_path': filePath,
    });
    if (res['success'] == true) {
      await fetchTugas(userId);
      return true;
    }
    return false;
  }

  Future<void> fetchQuiz(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/quiz', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _quizList = (res['data'] as List).map((e) => QuizModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<Map<String, dynamic>?> fetchQuizDetail(int userId, int quizId) async {
    final res = await ApiService.get('siswa/quiz_detail', params: {
      'user_id': userId.toString(),
      'quiz_id': quizId.toString(),
    });
    if (res['success'] == true) {
      return res['data'];
    }
    return null;
  }

  Future<Map<String, dynamic>> submitQuiz(int userId, int quizId, Map<int, int> answers) async {
    final Map<String, int> formattedAnswers = {};
    answers.forEach((key, value) {
      formattedAnswers[key.toString()] = value;
    });

    final res = await ApiService.post('siswa/submit_quiz', {
      'user_id': userId,
      'quiz_id': quizId,
      'answers': formattedAnswers,
    });

    if (res['success'] == true) {
      await fetchQuiz(userId);
    }
    return res;
  }

  Future<Map<String, dynamic>> recordQuizViolation(int userId, int quizId) async {
    final res = await ApiService.post('siswa/record_violation', {
      'user_id': userId,
      'quiz_id': quizId,
    });
    return res;
  }

  Future<bool> requestQuizSusulan(int userId, int quizId, String catatan) async {
    final res = await ApiService.post('siswa/request_susulan', {
      'user_id': userId,
      'quiz_id': quizId,
      'catatan': catatan,
    });
    if (res['success'] == true) {
      await fetchQuiz(userId);
      return true;
    }
    return false;
  }

  Map<String, dynamic>? _absensiStats;
  Map<String, dynamic>? get absensiStats => _absensiStats;

  Future<void> fetchAbsensi(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/absensi', params: {'user_id': userId.toString()});
    if (res['success'] == true) {
      if (res['data'] is Map) {
        _absensiStats = res['data']['stats'];
        final list = res['data']['history'] as List? ?? [];
        _absensiList = list.map((e) => AbsensiModel.fromJson(e)).toList();
      } else if (res['data'] is List) {
        _absensiList = (res['data'] as List).map((e) => AbsensiModel.fromJson(e)).toList();
      }
    }
    _isLoading = false;
    notifyListeners();
  }

  Future<bool> checkinAbsensi(int userId, int jadwalId, String status) async {
    final res = await ApiService.post('siswa/checkin_absensi', {
      'user_id': userId,
      'jadwal_id': jadwalId,
      'status': status,
    });
    if (res['success'] == true) {
      await fetchAbsensi(userId);
      return true;
    }
    return false;
  }

  Future<void> fetchNilai(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/nilai', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _nilaiList = (res['data'] as List).map((e) => NilaiModel.fromJson(e)).toList();
    }
    _isLoading = false;
    notifyListeners();
  }
}
