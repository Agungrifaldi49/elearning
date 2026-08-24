import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class GuruScanQRScreen extends StatefulWidget {
  const GuruScanQRScreen({super.key});

  @override
  State<GuruScanQRScreen> createState() => _GuruScanQRScreenState();
}

class _GuruScanQRScreenState extends State<GuruScanQRScreen> {
  final _qrInputController = TextEditingController();
  bool _isProcessing = false;

  @override
  void dispose() {
    _qrInputController.dispose();
    super.dispose();
  }

  Future<void> _processScan() async {
    final qr = _qrInputController.text.trim();
    if (qr.isEmpty) return;

    setState(() => _isProcessing = true);
    final res = await ApiService.post('guru/scan_qr', {
      'qr_code': qr,
      'jadwal_id': 1,
    });

    if (mounted) {
      setState(() => _isProcessing = false);
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Presensi QR Berhasil!'), backgroundColor: Colors.teal),
        );
        _qrInputController.clear();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(res['message'] ?? 'Gagal memproses QR'), backgroundColor: Colors.red),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR Code Presensi Siswa'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(
                color: Colors.teal.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.teal.withValues(alpha: 0.3)),
              ),
              child: const Column(
                children: [
                  Icon(Icons.qr_code_scanner_rounded, size: 90, color: Colors.teal),
                  SizedBox(height: 12),
                  Text(
                    'Pindai QR Code Kartu Pelajar Siswa',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 24),
            TextField(
              controller: _qrInputController,
              decoration: const InputDecoration(
                labelText: 'Input Manual NIS / Hasil Scan QR (Contoh: SISWA-1001)',
                border: OutlineInputBorder(),
                prefixIcon: Icon(Icons.qr_code),
              ),
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 48,
              child: ElevatedButton.icon(
                onPressed: _isProcessing ? null : _processScan,
                icon: _isProcessing
                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                    : const Icon(Icons.check_circle),
                label: const Text('Proses Presensi Siswa'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
