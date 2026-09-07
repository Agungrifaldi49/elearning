import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'api_service.dart';
import '../views/shared/notifications_screen.dart';
import '../views/shared/live_class_screen.dart';
import '../views/siswa/siswa_chat_screen.dart';
import '../views/siswa/siswa_forum_screen.dart';
import '../views/siswa/siswa_main_screen.dart';
import '../views/guru/guru_main_screen.dart';

@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp();
    if (kDebugMode) {
      print("Handling a background message: ${message.messageId}");
    }
  } catch (e) {
    if (kDebugMode) {
      print("Background FCM handler error: $e");
    }
  }
}

class FcmService {
  static final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
  static final FlutterLocalNotificationsPlugin _localNotificationsPlugin =
      FlutterLocalNotificationsPlugin();

  static Future<void> initialize() async {
    try {
      // 1. Initialize Firebase App
      await Firebase.initializeApp();

      // 2. Set background message handler
      FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

      // 3. Setup Local Notification for Foreground messages & Click handler
      const AndroidInitializationSettings initializationSettingsAndroid =
          AndroidInitializationSettings('@mipmap/ic_launcher');
      const InitializationSettings initializationSettings =
          InitializationSettings(android: initializationSettingsAndroid);

      await _localNotificationsPlugin.initialize(
        initializationSettings,
        onDidReceiveNotificationResponse: (NotificationResponse response) {
          if (response.payload != null && response.payload!.isNotEmpty) {
            try {
              final Map<String, dynamic> data = jsonDecode(response.payload!);
              handleNotificationNavigation(data);
            } catch (e) {
              if (kDebugMode) print("Error parsing notification payload: $e");
            }
          }
        },
      );

      const AndroidNotificationChannel channel = AndroidNotificationChannel(
        'high_importance_channel',
        'High Importance Notifications',
        description: 'This channel is used for important notifications.',
        importance: Importance.high,
      );

      await _localNotificationsPlugin
          .resolvePlatformSpecificImplementation<
              AndroidFlutterLocalNotificationsPlugin>()
          ?.createNotificationChannel(channel);

      // 4. Request Notification Permission & Foreground Options
      NotificationSettings settings =
          await FirebaseMessaging.instance.requestPermission(
        alert: true,
        badge: true,
        sound: true,
      );

      await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
        alert: true,
        badge: true,
        sound: true,
      );

      if (kDebugMode) {
        print('User granted notification permission: ${settings.authorizationStatus}');
      }

      // 5. Listen for Foreground Messages
      FirebaseMessaging.onMessage.listen((RemoteMessage message) {
        RemoteNotification? notification = message.notification;
        AndroidNotification? android = message.notification?.android;

        if (notification != null && android != null) {
          _localNotificationsPlugin.show(
            notification.hashCode,
            notification.title,
            notification.body,
            NotificationDetails(
              android: AndroidNotificationDetails(
                channel.id,
                channel.name,
                channelDescription: channel.description,
                icon: '@mipmap/ic_launcher',
                importance: Importance.max,
                priority: Priority.high,
              ),
            ),
            payload: jsonEncode(message.data),
          );
        }
      });

      // 6. Handle Background/Terminated Notification Tap Clicks
      FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
        if (kDebugMode) {
          print("Notification clicked from background: ${message.data}");
        }
        handleNotificationNavigation(message.data);
      });

      // 7. Handle Initial Terminated App Clicks
      RemoteMessage? initialMessage =
          await FirebaseMessaging.instance.getInitialMessage();
      if (initialMessage != null) {
        if (kDebugMode) {
          print("Initial notification message: ${initialMessage.data}");
        }
        Future.delayed(const Duration(milliseconds: 1000), () {
          handleNotificationNavigation(initialMessage.data);
        });
      }

    } catch (e) {
      if (kDebugMode) {
        print("Error initializing FCM Service: $e");
      }
    }
  }

  /// Navigate user to corresponding screen when notification is clicked
  static void handleNotificationNavigation(Map<String, dynamic> data) {
    final BuildContext? context = navigatorKey.currentContext;
    if (context == null) return;

    final String type = (data['type'] ?? data['notification_type'] ?? '').toString().toLowerCase();
    final int targetId = int.tryParse((data['id'] ?? data['target_id'] ?? data['sender_id'] ?? '0').toString()) ?? 0;

    switch (type) {
      case 'chat':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SiswaChatScreen()),
        );
        break;

      case 'forum':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SiswaForumScreen()),
        );
        break;

      case 'absensi':
        // Route to Siswa Main Screen tab 3 (Absensi) or Notifications
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SiswaMainScreen(initialIndex: 3)),
        );
        break;

      case 'jadwal':
        // Route to Siswa Main Screen tab 1 (Jadwal)
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const SiswaMainScreen(initialIndex: 1)),
        );
        break;

      case 'live_class':
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const LiveClassScreen()),
        );
        break;

      case 'pengumuman':
      default:
        Navigator.push(
          context,
          MaterialPageRoute(builder: (_) => const NotificationsScreen()),
        );
        break;
    }
  }

  /// Sends FCM device token to backend MySQL database after user logs in
  static Future<void> sendTokenToBackend(int userId) async {
    try {
      String? fcmToken = await FirebaseMessaging.instance.getToken();
      if (fcmToken == null || fcmToken.isEmpty) return;

      if (kDebugMode) {
        print("FCM Device Token: $fcmToken");
      }

      await ApiService.post('save_fcm_token', {
        'user_id': userId,
        'fcm_token': fcmToken,
      });

      // Also trigger check reminders in background when token is registered
      await ApiService.get('check_reminders');
    } catch (e) {
      if (kDebugMode) {
        print("Error sending FCM Token: $e");
      }
    }
  }
}
