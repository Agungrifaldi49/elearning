import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/services.dart';

class SoundService {
  static final AudioPlayer _audioPlayer = AudioPlayer();

  // Play Crisp Success Beep Chime + Tactile Haptic Vibration
  static Future<void> playSuccessBeep() async {
    try {
      // Trigger Haptic Feedback
      HapticFeedback.mediumImpact();
      SystemSound.play(SystemSoundType.click);

      // Play audio chime URL (Fast high-res success beep)
      await _audioPlayer.stop();
      await _audioPlayer.play(UrlSource('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3'));
    } catch (_) {
      // Fallback to system haptic if audio stream is offline
      HapticFeedback.mediumImpact();
    }
  }

  // Play Warning Error Beep + Tactile Error Vibration
  static Future<void> playErrorBeep() async {
    try {
      // Trigger Haptic Error Vibration
      HapticFeedback.vibrate();

      // Play warning tone audio
      await _audioPlayer.stop();
      await _audioPlayer.play(UrlSource('https://assets.mixkit.co/active_storage/sfx/2874/2874-preview.mp3'));
    } catch (_) {
      HapticFeedback.vibrate();
    }
  }
}
