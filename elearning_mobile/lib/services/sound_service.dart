import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/services.dart';
import 'package:flutter_tts/flutter_tts.dart';

class SoundService {
  static final AudioPlayer _audioPlayer = AudioPlayer();
  static final FlutterTts _flutterTts = FlutterTts();
  static bool _isTtsInitialized = false;

  static Future<void> _initTts() async {
    if (_isTtsInitialized) return;
    try {
      await _flutterTts.setLanguage("id-ID");
      await _flutterTts.setSpeechRate(0.5);
      await _flutterTts.setVolume(1.0);
      await _flutterTts.setPitch(1.0);
      _isTtsInitialized = true;
    } catch (_) {}
  }

  // Play Crisp Success Beep Chime + Tactile Haptic Vibration
  static Future<void> playSuccessBeep() async {
    try {
      HapticFeedback.mediumImpact();
      SystemSound.play(SystemSoundType.click);

      await _audioPlayer.stop();
      await _audioPlayer.play(UrlSource('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3'));
    } catch (_) {
      HapticFeedback.mediumImpact();
    }
  }

  // Play Warning Error Beep + Tactile Error Vibration
  static Future<void> playErrorBeep() async {
    try {
      HapticFeedback.vibrate();

      await _audioPlayer.stop();
      await _audioPlayer.play(UrlSource('https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3'));
    } catch (_) {
      HapticFeedback.vibrate();
    }
  }

  // Indonesian TTS Voice Announcement for Entry Attendance (Presensi Masuk)
  static Future<void> speakPresensiMasuk({
    required String nama,
    required bool isLate,
  }) async {
    await _initTts();
    await playSuccessBeep();

    final hour = DateTime.now().hour;
    String greeting = "Selamat pagi";
    if (hour >= 11 && hour < 15) {
      greeting = "Selamat siang";
    } else if (hour >= 15) {
      greeting = "Selamat sore";
    }

    final statusStr = isLate ? "terlambat. Mohon tingkatkan kedisiplinan." : "tepat waktu.";
    final text = "$greeting, siswa atas nama $nama hadir, statusnya $statusStr";

    try {
      await Future.delayed(const Duration(milliseconds: 400));
      await _flutterTts.speak(text);
    } catch (_) {}
  }

  // Indonesian TTS Voice Announcement for Exit Attendance (Presensi Pulang)
  static Future<void> speakPresensiPulang({
    required String nama,
  }) async {
    await _initTts();
    await playSuccessBeep();

    final text = "Siswa atas nama $nama sudah meninggalkan sekolah. Hati-hati di jalan, utamakan keselamatan bukan kecepatan.";

    try {
      await Future.delayed(const Duration(milliseconds: 400));
      await _flutterTts.speak(text);
    } catch (_) {}
  }

  // Indonesian TTS Voice Announcement for Already Attended Complete (Presensi Sudah Lengkap)
  static Future<void> speakPresensiLengkap({
    required String nama,
  }) async {
    await _initTts();
    await playErrorBeep();

    final text = "Peringatan! Siswa atas nama $nama presensinya sudah lengkap hari ini.";

    try {
      await Future.delayed(const Duration(milliseconds: 300));
      await _flutterTts.speak(text);
    } catch (_) {}
  }

  // Indonesian TTS Voice Announcement for Holiday / No Schedule (Hari Libur / Tidak Ada Jadwal)
  static Future<void> speakHariLibur({String? message}) async {
    await _initTts();
    await playErrorBeep();

    final text = message != null && message.isNotEmpty
        ? "Peringatan! $message"
        : "Peringatan! Tidak ada jadwal presensi hari ini. Hari ini adalah hari libur sekolah.";

    try {
      await Future.delayed(const Duration(milliseconds: 300));
      await _flutterTts.speak(text);
    } catch (_) {}
  }

  // Indonesian TTS Voice Announcement for Warning/Error
  static Future<void> speakErrorAnnouncement(String text) async {
    await _initTts();
    await playErrorBeep();

    try {
      await Future.delayed(const Duration(milliseconds: 300));
      await _flutterTts.speak("Peringatan! $text");
    } catch (_) {}
  }
}
