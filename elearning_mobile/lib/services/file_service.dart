import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

class FileService {
  static Future<void> openFileOrUrl(BuildContext context, String urlString) async {
    if (urlString.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tautan / berkas tidak tersedia.'), backgroundColor: Colors.red),
      );
      return;
    }

    try {
      final Uri uri = Uri.parse(urlString.trim());
      if (await canLaunchUrl(uri)) {
        await launchUrl(
          uri,
          mode: LaunchMode.externalApplication,
        );
      } else {
        // Fallback: try launching with inAppBrowserView
        final bool launched = await launchUrl(
          uri,
          mode: LaunchMode.inAppBrowserView,
        );
        if (!launched && context.mounted) {
          _showFallbackDialog(context, urlString);
        }
      }
    } catch (e) {
      if (context.mounted) {
        _showFallbackDialog(context, urlString);
      }
    }
  }

  static void _showFallbackDialog(BuildContext context, String urlString) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Tautan Berkas File'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Gagal membuka otomatis di HP. Gunakan tautan di bawah ini:'),
            const SizedBox(height: 8),
            SelectableText(
              urlString,
              style: const TextStyle(color: Colors.blue, decoration: TextDecoration.underline, fontSize: 13),
            ),
          ],
        ),
        actions: [
          ElevatedButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }
}
