import 'dart:async';
import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/materi_model.dart';
import '../models/tugas_model.dart';
import '../models/quiz_model.dart';
import '../models/jadwal_model.dart';
import '../models/absensi_model.dart';
import '../models/nilai_model.dart';
import '../models/forum_model.dart';
import '../models/chat_model.dart';
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
  List<ForumModel> _forumTopicList = [];
  List<ChatContactModel> _chatContacts = [];
  final StreamController<List<ChatContactModel>> _chatContactsController = StreamController<List<ChatContactModel>>.broadcast();

  Set<int> _seenMateriIds = {};
  Set<int> _seenTugasIds = {};
  Set<int> _seenQuizIds = {};
  Set<int> _seenJadwalIds = {};
  Set<int> _seenForumIds = {};
  int _unreadChatCount = 0;

  bool get isLoading => _isLoading;
  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<JadwalModel> get jadwalList => _jadwalList;
  List<MateriModel> get materiList => _materiList;
  List<TugasModel> get tugasList => _tugasList;
  List<QuizModel> get quizList => _quizList;
  List<AbsensiModel> get absensiList => _absensiList;
  List<NilaiModel> get nilaiList => _nilaiList;
  List<ForumModel> get forumTopicList => _forumTopicList;
  List<ChatContactModel> get chatContacts => _chatContacts;
  Stream<List<ChatContactModel>> get chatContactsStream => _chatContactsController.stream;

  Set<int> get seenMateriIds => _seenMateriIds;
  Set<int> get seenTugasIds => _seenTugasIds;
  Set<int> get seenQuizIds => _seenQuizIds;
  Set<int> get seenJadwalIds => _seenJadwalIds;
  Set<int> get seenForumIds => _seenForumIds;
  int get unreadChatCount => _unreadChatCount;

  int get unreadMateriCount => _materiList.where((m) => !_seenMateriIds.contains(m.id)).length;
  int get unreadTugasCount => _tugasList.where((t) => !t.isSubmitted && !_seenTugasIds.contains(t.id)).length;
  int get unreadQuizCount => _quizList.where((q) => !q.isCompleted && !q.isSuspended && !q.isTerkunci && !q.isMaxAttemptsReached && !_seenQuizIds.contains(q.id)).length;
  int get unreadJadwalCount => _jadwalList.where((j) => !_seenJadwalIds.contains(j.id)).length;
  int get unreadForumCount => _forumTopicList.where((f) => !_seenForumIds.contains(f.id)).length;

  bool _hasClockedInToday = false;
  bool _hasClockedOutToday = false;
  bool _isAbsentToday = false;

  bool get isAbsentToday {
    if (_isAbsentToday) return true;
    final now = DateTime.now();
    final todayStr = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
    final todayRec = _absensiList.firstWhere(
      (a) => a.tanggal.contains(todayStr) || (a.waktuMasuk != null && a.waktuMasuk!.contains(todayStr)),
      orElse: () => AbsensiModel(id: 0, jadwalId: 0, siswaId: 0, tanggal: '', status: ''),
    );
    if (todayRec.id > 0) {
      final st = todayRec.status.toLowerCase();
      return st == 'izin' || st == 'sakit' || st == 'alpha' || st == 'alpa';
    }
    return false;
  }

  bool get hasClockedInToday {
    if (_hasClockedInToday) return true;
    final now = DateTime.now();
    final todayStr = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
    final todayRec = _absensiList.firstWhere(
      (a) => a.tanggal.contains(todayStr) || (a.waktuMasuk != null && a.waktuMasuk!.contains(todayStr)),
      orElse: () => AbsensiModel(id: 0, jadwalId: 0, siswaId: 0, tanggal: '', status: ''),
    );
    if (todayRec.id > 0) {
      final st = todayRec.status.toLowerCase();
      if (st == 'izin' || st == 'sakit' || st == 'alpha' || st == 'alpa') {
        return false;
      }
      return true;
    }
    return false;
  }

  bool get hasClockedOutToday {
    if (_hasClockedOutToday) return true;
    final now = DateTime.now();
    final todayStr = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
    final todayRec = _absensiList.firstWhere(
      (a) => a.tanggal.contains(todayStr) || (a.waktuMasuk != null && a.waktuMasuk!.contains(todayStr)),
      orElse: () => AbsensiModel(id: 0, jadwalId: 0, siswaId: 0, tanggal: '', status: ''),
    );
    if (todayRec.id > 0) {
      final st = todayRec.status.toLowerCase();
      if (st == 'izin' || st == 'sakit' || st == 'alpha' || st == 'alpa') {
        return false;
      }
      return todayRec.waktuPulang != null && todayRec.waktuPulang!.isNotEmpty;
    }
    return false;
  }

  Future<void> loadSeenState() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      _seenMateriIds = (prefs.getStringList('seen_materi_ids') ?? []).map((e) => int.tryParse(e) ?? 0).toSet();
      _seenTugasIds = (prefs.getStringList('seen_tugas_ids') ?? []).map((e) => int.tryParse(e) ?? 0).toSet();
      _seenQuizIds = (prefs.getStringList('seen_quiz_ids') ?? []).map((e) => int.tryParse(e) ?? 0).toSet();
      _seenJadwalIds = (prefs.getStringList('seen_jadwal_ids') ?? []).map((e) => int.tryParse(e) ?? 0).toSet();
      _seenForumIds = (prefs.getStringList('seen_forum_ids') ?? []).map((e) => int.tryParse(e) ?? 0).toSet();
      notifyListeners();
    } catch (_) {}
  }

  void markJadwalAsSeen(int id) async {
    if (!_seenJadwalIds.contains(id)) {
      _seenJadwalIds.add(id);
      notifyListeners();
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setStringList('seen_jadwal_ids', _seenJadwalIds.map((e) => e.toString()).toList());
      } catch (_) {}
    }
  }

  void markAllJadwalAsSeen() async {
    _seenJadwalIds.addAll(_jadwalList.map((j) => j.id));
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('seen_jadwal_ids', _seenJadwalIds.map((e) => e.toString()).toList());
    } catch (_) {}
  }

  void markForumAsSeen(int id) async {
    if (!_seenForumIds.contains(id)) {
      _seenForumIds.add(id);
      notifyListeners();
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setStringList('seen_forum_ids', _seenForumIds.map((e) => e.toString()).toList());
      } catch (_) {}
    }
  }

  void markAllForumAsSeen() async {
    _seenForumIds.addAll(_forumTopicList.map((f) => f.id));
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('seen_forum_ids', _seenForumIds.map((e) => e.toString()).toList());
    } catch (_) {}
  }

  void markMateriAsSeen(int materiId) async {
    if (!_seenMateriIds.contains(materiId)) {
      _seenMateriIds.add(materiId);
      notifyListeners();
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setStringList('seen_materi_ids', _seenMateriIds.map((e) => e.toString()).toList());
      } catch (_) {}
    }
  }

  void markTugasAsSeen(int tugasId) async {
    if (!_seenTugasIds.contains(tugasId)) {
      _seenTugasIds.add(tugasId);
      notifyListeners();
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setStringList('seen_tugas_ids', _seenTugasIds.map((e) => e.toString()).toList());
      } catch (_) {}
    }
  }

  void markQuizAsSeen(int quizId) async {
    if (!_seenQuizIds.contains(quizId)) {
      _seenQuizIds.add(quizId);
      notifyListeners();
      try {
        final prefs = await SharedPreferences.getInstance();
        await prefs.setStringList('seen_quiz_ids', _seenQuizIds.map((e) => e.toString()).toList());
      } catch (_) {}
    }
  }

  void markAllMateriAsSeen() async {
    _seenMateriIds.addAll(_materiList.map((m) => m.id));
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('seen_materi_ids', _seenMateriIds.map((e) => e.toString()).toList());
    } catch (_) {}
  }

  void markAllTugasAsSeen() async {
    _seenTugasIds.addAll(_tugasList.map((t) => t.id));
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('seen_tugas_ids', _seenTugasIds.map((e) => e.toString()).toList());
    } catch (_) {}
  }

  void markAllQuizAsSeen() async {
    _seenQuizIds.addAll(_quizList.map((q) => q.id));
    notifyListeners();
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.setStringList('seen_quiz_ids', _seenQuizIds.map((e) => e.toString()).toList());
    } catch (_) {}
  }

  Future<void> fetchDashboard(int userId) async {
    _isLoading = true;
    notifyListeners();

    final res = await ApiService.get('siswa/dashboard', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] != null) {
      _dashboardData = res['data'];
      final absToday = res['data']['absensi_today'];
      if (absToday is Map) {
        _hasClockedInToday = absToday['has_clocked_in'] == true;
        _hasClockedOutToday = absToday['has_clocked_out'] == true;
        _isAbsentToday = absToday['is_absent'] == true;
      }
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

  Future<Map<String, dynamic>?> fetchQuizReview(int userId, int quizId) async {
    final res = await ApiService.get('siswa/quiz_review', params: {
      'user_id': userId.toString(),
      'quiz_id': quizId.toString(),
    });
    if (res['success'] == true && res['data'] != null) {
      return res['data'];
    }
    return null;
  }

  Future<Map<String, dynamic>> submitQuiz(int userId, int quizId, Map<int, int> answers, {Map<int, String>? essayAnswers}) async {
    final Map<String, int> formattedAnswers = {};
    answers.forEach((key, value) {
      formattedAnswers[key.toString()] = value;
    });

    final Map<String, String> formattedEssayAnswers = {};
    if (essayAnswers != null) {
      essayAnswers.forEach((key, value) {
        if (value.trim().isNotEmpty) {
          formattedEssayAnswers[key.toString()] = value.trim();
        }
      });
    }

    final res = await ApiService.post('siswa/submit_quiz', {
      'user_id': userId,
      'quiz_id': quizId,
      'answers': formattedAnswers,
      'essay_answers': formattedEssayAnswers,
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

      final now = DateTime.now();
      final todayStr = "${now.year}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}";
      final todayRec = _absensiList.firstWhere(
        (a) => a.tanggal.contains(todayStr) || (a.waktuMasuk != null && a.waktuMasuk!.contains(todayStr)),
        orElse: () => AbsensiModel(id: 0, jadwalId: 0, siswaId: 0, tanggal: '', status: ''),
      );
      if (todayRec.id > 0) {
        final st = todayRec.status.toLowerCase();
        if (st == 'izin' || st == 'sakit' || st == 'alpha' || st == 'alpa') {
          _isAbsentToday = true;
          _hasClockedInToday = false;
          _hasClockedOutToday = false;
        } else {
          _hasClockedInToday = true;
          _isAbsentToday = false;
          if (todayRec.waktuPulang != null && todayRec.waktuPulang!.isNotEmpty) {
            _hasClockedOutToday = true;
          }
        }
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

  Timer? _realtimeTimer;

  void startRealtimeSync(int userId) {
    _realtimeTimer?.cancel();
    _realtimeTimer = Timer.periodic(const Duration(seconds: 3), (_) async {
      await fetchChatContactsSilent(userId);
      await fetchForumSilent(userId);
    });
  }

  void stopRealtimeSync() {
    _realtimeTimer?.cancel();
    _realtimeTimer = null;
  }

  Future<void> fetchMateriSilent(int userId) async {
    final res = await ApiService.get('siswa/materi', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => MateriModel.fromJson(e)).toList();
      if (list.length != _materiList.length) {
        _materiList = list;
        notifyListeners();
      }
    }
  }

  Future<void> fetchQuizSilent(int userId) async {
    final res = await ApiService.get('siswa/quiz', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => QuizModel.fromJson(e)).toList();
      if (list.length != _quizList.length) {
        _quizList = list;
        notifyListeners();
      }
    }
  }

  Future<void> fetchDashboardSilent(int userId) async {
    final res = await ApiService.get('siswa/dashboard', params: {'user_id': userId.toString()});
    if (res['success'] == true) {
      _dashboardData = res['data'];
    }
  }

  Future<void> fetchTugasSilent(int userId) async {
    final res = await ApiService.get('siswa/tugas', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => TugasModel.fromJson(e)).toList();
      if (list.length != _tugasList.length) {
        _tugasList = list;
        notifyListeners();
      }
    }
  }

  Future<void> fetchJadwalSilent(int userId) async {
    final res = await ApiService.get('siswa/jadwal', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => JadwalModel.fromJson(e)).toList();
      if (list.length != _jadwalList.length) {
        _jadwalList = list;
        notifyListeners();
      }
    }
  }

  Future<void> fetchForumSilent(int userId) async {
    final res = await ApiService.get('forum/list', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => ForumModel.fromJson(e)).toList();
      if (list.length != _forumTopicList.length) {
        _forumTopicList = list;
        notifyListeners();
      }
    }
  }

  Future<void> fetchChatContactsSilent(int userId) async {
    final res = await ApiService.get('chat/contacts', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => ChatContactModel.fromJson(e)).toList();
      _chatContacts = list;
      if (!_chatContactsController.isClosed) {
        _chatContactsController.add(list);
      }
      int totalUnread = 0;
      for (var c in list) {
        totalUnread += c.unreadCount;
      }
      if (_unreadChatCount != totalUnread) {
        _unreadChatCount = totalUnread;
        notifyListeners();
      }
    }
  }

  void markContactChatAsRead(int userId, int contactId) async {
    final index = _chatContacts.indexWhere((c) => c.id == contactId);
    if (index != -1) {
      final oldUnread = _chatContacts[index].unreadCount;
      if (oldUnread > 0) {
        _chatContacts[index] = ChatContactModel(
          id: _chatContacts[index].id,
          fullName: _chatContacts[index].fullName,
          avatar: _chatContacts[index].avatar,
          avatarUrl: _chatContacts[index].avatarUrl,
          roleName: _chatContacts[index].roleName,
          lastMessage: _chatContacts[index].lastMessage,
          lastTime: _chatContacts[index].lastTime,
          unreadCount: 0,
        );
        _unreadChatCount = _unreadChatCount - oldUnread;
        if (_unreadChatCount < 0) _unreadChatCount = 0;
        if (!_chatContactsController.isClosed) {
          _chatContactsController.add(_chatContacts);
        }
        notifyListeners();
      }
    }
    await ApiService.get('chat/messages', params: {
      'user_id': userId.toString(),
      'receiver_id': contactId.toString(),
    });
  }

  Future<void> fetchAbsensiSilent(int userId) async {
    final res = await ApiService.get('siswa/absensi', params: {'user_id': userId.toString()});
    if (res['success'] == true) {
      if (res['data'] is Map) {
        _absensiStats = res['data']['stats'];
        final list = res['data']['history'] as List? ?? [];
        _absensiList = list.map((e) => AbsensiModel.fromJson(e)).toList();
      } else if (res['data'] is List) {
        _absensiList = (res['data'] as List).map((e) => AbsensiModel.fromJson(e)).toList();
      }
      notifyListeners();
    }
  }
}
