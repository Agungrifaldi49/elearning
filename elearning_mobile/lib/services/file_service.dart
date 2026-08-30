import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../theme/app_theme.dart';

class FileService {
  /// Opens URL/File either in In-App Browser or External Application
  static Future<void> openFileOrUrl(BuildContext context, String urlString, {bool preferInApp = true}) async {
    if (urlString.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tautan / berkas tidak tersedia.'), backgroundColor: Colors.red),
      );
      return;
    }

    final String cleanUrl = urlString.trim();
    final Uri uri = Uri.parse(cleanUrl);

    try {
      if (preferInApp) {
        // Try in-app browser first so user stays in the Flutter application
        final bool launchedInApp = await launchUrl(
          uri,
          mode: LaunchMode.inAppBrowserView,
        );
        if (launchedInApp) return;
      }

      // Fallback or external preference
      if (await canLaunchUrl(uri)) {
        await launchUrl(
          uri,
          mode: LaunchMode.externalApplication,
        );
      } else if (context.mounted) {
        _showFallbackDialog(context, cleanUrl);
      }
    } catch (e) {
      if (context.mounted) {
        _showFallbackDialog(context, cleanUrl);
      }
    }
  }

  /// Convert Google Drive URL to embed preview format
  static String getGoogleDriveEmbedUrl(String url) {
    if (url.isEmpty) return url;
    
    // File view
    final matchFile = RegExp(r'drive\.google\.com/file/d/([a-zA-Z0-9_-]+)').firstMatch(url);
    if (matchFile != null && matchFile.group(1) != null) {
      return 'https://drive.google.com/file/d/${matchFile.group(1)}/preview';
    }

    // Docs/Sheets/Slides
    final matchDoc = RegExp(r'docs\.google\.com/(document|spreadsheets|presentation|forms)/d/([a-zA-Z0-9_-]+)').firstMatch(url);
    if (matchDoc != null && matchDoc.group(1) != null && matchDoc.group(2) != null) {
      return 'https://docs.google.com/${matchDoc.group(1)}/d/${matchDoc.group(2)}/preview';
    }

    // Folders
    final matchFolder = RegExp(r'drive\.google\.com/drive/(?:u/\d+/)?folders/([a-zA-Z0-9_-]+)').firstMatch(url);
    if (matchFolder != null && matchFolder.group(1) != null) {
      return 'https://drive.google.com/embeddedfolderview?id=${matchFolder.group(1)}#list';
    }

    return url;
  }

  /// Show Full In-App Media / Image Preview Viewer Screen
  static void showInAppPreview(BuildContext context, String fileUrl, String title, {String studentName = 'Siswa'}) {
    final cleanUrl = fileUrl.trim();
    final ext = cleanUrl.split('.').last.toLowerCase().split('?').first;
    final isImage = ['jpg', 'jpeg', 'png', 'webp', 'gif'].contains(ext);
    final isDrive = cleanUrl.contains('drive.google.com') || cleanUrl.contains('docs.google.com');

    if (isImage) {
      // Open interactive zoomable image viewer screen inside app
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => _InAppImageViewerScreen(
            imageUrl: cleanUrl,
            title: title,
            studentName: studentName,
          ),
        ),
      );
    } else if (isDrive) {
      // Open Google Drive preview modal inside app
      _showDrivePreviewModal(context, cleanUrl, title, studentName);
    } else {
      // Open PDF / Office / Document via In-App Browser or Online Reader
      _showDocumentPreviewModal(context, cleanUrl, title, studentName, ext);
    }
  }

  static void _showDrivePreviewModal(BuildContext context, String driveUrl, String title, String studentName) {
    final embedUrl = getGoogleDriveEmbedUrl(driveUrl);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: Colors.green.shade100, borderRadius: BorderRadius.circular(12)),
                  child: Icon(Icons.add_to_drive_rounded, color: Colors.green.shade800, size: 26),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                      Text('Pengirim: $studentName', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    ],
                  ),
                ),
                IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close)),
              ],
            ),
            const SizedBox(height: 16),

            // Google Drive Access Notice Banner
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.amber.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.amber.shade300),
              ),
              child: Row(
                children: [
                  Icon(Icons.info_outline_rounded, color: Colors.amber.shade900, size: 20),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Catatan Akses Google Drive: Pastikan link diatur "Siapa saja yang memiliki link (Public)" agar terbaca.',
                      style: TextStyle(fontSize: 11, color: Colors.amber.shade900, fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),

            // Action Buttons
            ElevatedButton.icon(
              onPressed: () {
                Navigator.pop(context);
                openFileOrUrl(context, embedUrl, preferInApp: true);
              },
              icon: const Icon(Icons.open_in_browser_rounded),
              label: const Text('Pratinjau di In-App Browser (Dalam App)'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 10),

            OutlinedButton.icon(
              onPressed: () {
                Navigator.pop(context);
                openFileOrUrl(context, driveUrl, preferInApp: false);
              },
              icon: const Icon(Icons.launch_rounded),
              label: const Text('Buka di Aplikasi Google Drive HP'),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                side: BorderSide(color: Colors.green.shade700),
                foregroundColor: Colors.green.shade700,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
  }

  static void _showDocumentPreviewModal(BuildContext context, String fileUrl, String title, String studentName, String ext) {
    final googleDocsViewerUrl = 'https://docs.google.com/gview?embedded=true&url=${Uri.encodeComponent(fileUrl)}';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (context) => Container(
        padding: const EdgeInsets.all(20),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                margin: const EdgeInsets.only(bottom: 16),
                decoration: BoxDecoration(color: Colors.grey.shade300, borderRadius: BorderRadius.circular(10)),
              ),
            ),
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(color: Colors.blue.shade100, borderRadius: BorderRadius.circular(12)),
                  child: Icon(
                    ext == 'pdf' ? Icons.picture_as_pdf_rounded : Icons.description_rounded,
                    color: ext == 'pdf' ? Colors.red.shade700 : AppTheme.primaryColor,
                    size: 26,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15), maxLines: 1, overflow: TextOverflow.ellipsis),
                      Text('Format: ${ext.toUpperCase()} • Pengirim: $studentName', style: TextStyle(fontSize: 12, color: Colors.grey.shade600)),
                    ],
                  ),
                ),
                IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close)),
              ],
            ),
            const SizedBox(height: 20),

            ElevatedButton.icon(
              onPressed: () {
                Navigator.pop(context);
                openFileOrUrl(context, ext == 'pdf' ? fileUrl : googleDocsViewerUrl, preferInApp: true);
              },
              icon: const Icon(Icons.visibility_rounded),
              label: Text('Baca / Pratinjau ${ext.toUpperCase()} di Dalam App'),
              style: ElevatedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                backgroundColor: AppTheme.primaryColor,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 10),

            OutlinedButton.icon(
              onPressed: () {
                Navigator.pop(context);
                openFileOrUrl(context, fileUrl, preferInApp: false);
              },
              icon: const Icon(Icons.download_rounded),
              label: const Text('Unduh Berkas ke Memori HP'),
              style: OutlinedButton.styleFrom(
                minimumSize: const Size(double.infinity, 48),
                foregroundColor: Colors.black87,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              ),
            ),
            const SizedBox(height: 10),
          ],
        ),
      ),
    );
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

/// Interactive Pinch-to-Zoom Image Viewer Screen inside Flutter App
class _InAppImageViewerScreen extends StatelessWidget {
  final String imageUrl;
  final String title;
  final String studentName;

  const _InAppImageViewerScreen({
    required this.imageUrl,
    required this.title,
    required this.studentName,
  });

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold)),
            Text('Jawaban Siswa: $studentName', style: const TextStyle(fontSize: 11, color: Colors.grey)),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.open_in_browser_rounded),
            tooltip: 'Buka di Browser',
            onPressed: () => FileService.openFileOrUrl(context, imageUrl, preferInApp: false),
          ),
        ],
      ),
      body: Center(
        child: InteractiveViewer(
          panEnabled: true,
          boundaryMargin: const EdgeInsets.all(20),
          minScale: 0.5,
          maxScale: 4.0,
          child: Image.network(
            imageUrl,
            fit: BoxFit.contain,
            loadingBuilder: (context, child, loadingProgress) {
              if (loadingProgress == null) return child;
              return Center(
                child: CircularProgressIndicator(
                  value: loadingProgress.expectedTotalBytes != null
                      ? loadingProgress.cumulativeBytesLoaded / loadingProgress.expectedTotalBytes!
                      : null,
                  color: Colors.white,
                ),
              );
            },
            errorBuilder: (context, error, stackTrace) {
              return Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.broken_image_rounded, size: 64, color: Colors.grey),
                  const SizedBox(height: 12),
                  const Text('Gagal memuat gambar', style: TextStyle(color: Colors.white70)),
                  const SizedBox(height: 12),
                  ElevatedButton.icon(
                    onPressed: () => FileService.openFileOrUrl(context, imageUrl, preferInApp: false),
                    icon: const Icon(Icons.launch_rounded),
                    label: const Text('Buka Tautan Gambar'),
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }
}
