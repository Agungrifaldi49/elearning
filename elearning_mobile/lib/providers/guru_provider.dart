import 'dart:async';
import 'package:flutter/material.dart';
import '../models/materi_model.dart';
import '../models/tugas_model.dart';
import '../models/quiz_model.dart';
import '../models/jadwal_model.dart';
import '../models/forum_model.dart';
import '../models/chat_model.dart';
import '../services/api_service.dart';

class GuruProvider with ChangeNotifier {
  bool _isLoading = false;
  Map<String, dynamic>? _dashboardData;
  List<JadwalModel> _jadwalList = [];
  List<MateriModel> _materiList = [];
  List<TugasModel> _tugasList = [];
  List<QuizModel> _quizList = [];
  List<ForumModel> _forumTopicList = [];
  List<ChatContactModel> _chatContacts = [];
  final StreamController<List<ChatContactModel>> _chatContactsController = StreamController<List<ChatContactModel>>.broadcast();

  final Set<int> _seenForumIds = {};
  int _unreadChatCount = 0;

  bool get isLoading => _isLoading;
  Map<String, dynamic>? get dashboardData => _dashboardData;
  List<JadwalModel> get jadwalList => _jadwalList;
  List<MateriModel> get materiList => _materiList;
  List<TugasModel> get tugasList => _tugasList;
  List<QuizModel> get quizList => _quizList;
  List<ForumModel> get forumTopicList => _forumTopicList;
  List<ChatContactModel> get chatContacts => _chatContacts;
  Stream<List<ChatContactModel>> get chatContactsStream => _chatContactsController.stream;

  int get unreadChatCount => _unreadChatCount;
  int get unreadForumCount => _forumTopicList.where((f) => !_seenForumIds.contains(f.id)).length;

  bool _hasClockedInToday = false;
  bool _hasClockedOutToday = false;
  bool get hasClockedInToday => _hasClockedInToday;
  bool get hasClockedOutToday => _hasClockedOutToday;

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
    if (res['success'] == true && res['data'] != null) {
      if (res['data'] is Map && res['data']['students'] is List) {
        return res['data']['students'];
      } else if (res['data'] is List) {
        return res['data'];
      }
    }
    return [];
  }

  Future<Map<String, dynamic>> fetchAbsensiData(
    int userId, {
    int jadwalId = 0,
    int kelasId = 0,
    int mapelId = 0,
    String? tanggal,
  }) async {
    final Map<String, String> params = {
      'user_id': userId.toString(),
    };
    if (jadwalId > 0) params['jadwal_id'] = jadwalId.toString();
    if (kelasId > 0) params['kelas_id'] = kelasId.toString();
    if (mapelId > 0) params['mapel_id'] = mapelId.toString();
    if (tanggal != null && tanggal.isNotEmpty) params['tanggal'] = tanggal;

    final res = await ApiService.get('guru/absensi', params: params);
    if (res['success'] == true && res['data'] != null) {
      if (res['data'] is Map) {
        return Map<String, dynamic>.from(res['data']);
      } else if (res['data'] is List) {
        return {
          'jadwal_list': [],
          'selected_jadwal_id': 0,
          'tanggal': tanggal ?? '',
          'mapel_list': [],
          'classes': [],
          'students': res['data'],
        };
      }
    }
    return {
      'jadwal_list': [],
      'selected_jadwal_id': 0,
      'tanggal': tanggal ?? '',
      'mapel_list': [],
      'classes': [],
      'students': []
    };
  }

  Future<bool> saveAbsensi(
    int userId,
    int jadwalId,
    String tanggal,
    Map<int, String> records,
    Map<int, String> keterangan,
  ) async {
    final Map<String, String> formattedRecords = {};
    records.forEach((key, value) {
      formattedRecords[key.toString()] = value;
    });

    final Map<String, String> formattedKeterangan = {};
    keterangan.forEach((key, value) {
      if (value.trim().isNotEmpty) {
        formattedKeterangan[key.toString()] = value.trim();
      }
    });

    final res = await ApiService.post('guru/absensi', {
      'user_id': userId,
      'jadwal_id': jadwalId,
      'tanggal': tanggal,
      'records': formattedRecords,
      'keterangan': formattedKeterangan,
    });
    return res['success'] == true;
  }

  Future<Map<String, dynamic>> fetchRecapAbsensiData(
    int userId, {
    int kelasId = 0,
    int mapelId = 0,
    String? bulan,
    String? tahun,
    String? tanggal,
    String? search,
  }) async {
    final Map<String, String> params = {
      'user_id': userId.toString(),
    };
    if (kelasId > 0) params['kelas_id'] = kelasId.toString();
    if (mapelId > 0) params['mapel_id'] = mapelId.toString();
    if (bulan != null && bulan.isNotEmpty) params['bulan'] = bulan;
    if (tahun != null && tahun.isNotEmpty) params['tahun'] = tahun;
    if (tanggal != null && tanggal.isNotEmpty) params['tanggal'] = tanggal;
    if (search != null && search.isNotEmpty) params['search'] = search;

    final res = await ApiService.get('guru/recap_absensi', params: params);
    if (res['success'] == true && res['data'] != null) {
      if (res['data'] is Map) {
        return Map<String, dynamic>.from(res['data']);
      } else if (res['data'] is List) {
        return {
          'summary': {
            'total_records': (res['data'] as List).length,
            'hadir': 0,
            'izin': 0,
            'sakit': 0,
            'alpa': 0,
          },
          'classes': [],
          'records': res['data'],
        };
      }
    }
    return {
      'summary': {'total_records': 0, 'hadir': 0, 'izin': 0, 'sakit': 0, 'alpa': 0},
      'classes': [],
      'records': [],
    };
  }

  Future<Map<String, dynamic>> fetchKeyMapelData(int userId) async {
    final res = await ApiService.get('guru/key_mapel', params: {
      'user_id': userId.toString(),
    });
    if (res['success'] == true && res['data'] is Map) {
      return Map<String, dynamic>.from(res['data']);
    }
    return {'keys': [], 'mapel_list': [], 'classes': []};
  }

  Future<bool> updateKeyMapel(int userId, int mapelId, String key, {int? kelasId}) async {
    final Map<String, dynamic> body = {
      'user_id': userId,
      'mapel_id': mapelId,
      'enrollment_key': key,
    };
    if (kelasId != null && kelasId > 0) {
      body['kelas_id'] = kelasId;
    }

    final res = await ApiService.post('guru/key_mapel', body);
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

  Future<void> fetchQuizSilent(int userId) async {
    final res = await ApiService.get('guru/quiz', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => QuizModel.fromJson(e)).toList();
      if (list.length != _quizList.length) {
        _quizList = list;
        notifyListeners();
      }
    }
  }

  Future<void> fetchSusulanRequestsSilent(int userId) async {
    final res = await ApiService.get('guru/susulan_requests', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      _susulanList = res['data'];
    }
  }

  Future<void> fetchDashboardSilent(int userId) async {
    final res = await ApiService.get('guru/dashboard', params: {'user_id': userId.toString()});
    if (res['success'] == true) {
      _dashboardData = res['data'];
    }
  }

  Future<void> fetchTugasSilent(int userId) async {
    final res = await ApiService.get('guru/tugas', params: {'user_id': userId.toString()});
    if (res['success'] == true && res['data'] is List) {
      final list = (res['data'] as List).map((e) => TugasModel.fromJson(e)).toList();
      if (list.length != _tugasList.length) {
        _tugasList = list;
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

  void markForumAsSeen(int forumId) async {
    _seenForumIds.add(forumId);
    notifyListeners();
  }

  Future<Map<String, dynamic>> fetchEnrolledStudents(
    int userId, {
    int? mapelId,
    int? kelasId,
    String? search,
  }) async {
    final params = <String, String>{
      'user_id': userId.toString(),
    };
    if (mapelId != null && mapelId > 0) {
      params['mapel_id'] = mapelId.toString();
    }
    if (kelasId != null && kelasId > 0) {
      params['kelas_id'] = kelasId.toString();
    }
    if (search != null && search.isNotEmpty) {
      params['search'] = search;
    }

    final res = await ApiService.get('guru/siswa_enrolled', params: params);
    if (res['success'] == true && res['data'] is Map) {
      return Map<String, dynamic>.from(res['data']);
    }
    return {
      'total_enrolled': 0,
      'keys': [],
      'classes': [],
      'students': [],
    };
  }

  Future<Map<String, dynamic>> fetchInputAbsensiData(
    int userId, {
    int? mapelId,
    String? tanggal,
  }) async {
    final params = <String, String>{
      'user_id': userId.toString(),
    };
    if (mapelId != null && mapelId > 0) {
      params['mapel_id'] = mapelId.toString();
    }
    if (tanggal != null && tanggal.isNotEmpty) {
      params['tanggal'] = tanggal;
    }

    final res = await ApiService.get('guru/input_absensi', params: params);
    if (res['success'] == true && res['data'] is Map) {
      return Map<String, dynamic>.from(res['data']);
    }
    return {
      'mapel_list': [],
      'selected_mapel_id': 0,
      'tanggal': tanggal ?? DateTime.now().toString().substring(0, 10),
      'students': [],
    };
  }

  Future<bool> saveManualAttendance(
    int userId,
    int mapelId,
    String tanggal,
    Map<int, String> absensiMap, {
    Map<int, String>? keteranganMap,
  }) async {
    final Map<String, dynamic> body = {
      'user_id': userId,
      'mapel_id': mapelId,
      'tanggal': tanggal,
      'absensi': absensiMap,
    };
    if (keteranganMap != null && keteranganMap.isNotEmpty) {
      body['keterangan'] = keteranganMap;
    }

    final res = await ApiService.post('guru/input_absensi', body);
    return res['success'] == true;
  }
}
