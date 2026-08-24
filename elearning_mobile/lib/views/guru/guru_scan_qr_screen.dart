import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class GuruScanQRScreen extends StatefulWidget {
  const GuruScanQRScreen({super.key});

  @override
  State<GuruScanQRScreen> createState() => _GuruScanQRScreenState();
}

class _GuruScanQRScreenState extends State<GuruScanQRScreen> {
  final _qrInputController = TextEditingController();
  bool _isProcessing = false;
  Map<String, dynamic>? _lastScanResult;

  @override
  void dispose() {
    _qrInputController.dispose();
    super.dispose();
  }

  Future<void> _processScan([String? customPayload]) async {
    final qr = (customPayload ?? _qrInputController.text).trim();
    if (qr.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Kode QR / NIS / NISN tidak boleh kosong!'), backgroundColor: Colors.orange),
      );
      return;
    }

    setState(() {
      _isProcessing = true;
      _lastScanResult = null;
    });

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final res = await ApiService.post('guru/scan_qr?user_id=$userId', {
      'qr_code': qr,
      'identifier': qr,
    });

    if (mounted) {
      setState(() {
        _isProcessing = false;
        _lastScanResult = res;
      });

      final success = res['success'] == true;
      final msg = res['message'] ?? (success ? 'Presensi Berhasil!' : 'Gagal memproses QR');

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(msg),
          backgroundColor: success ? Colors.teal : Colors.red,
          duration: const Duration(seconds: 4),
        ),
      );

      if (success) {
        _qrInputController.clear();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final data = _lastScanResult?['data'];
    final isSuccess = _lastScanResult?['success'] == true;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Scan QR Code Presensi Siswa'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Scanner Header Box
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [Colors.teal.shade800, Colors.teal.shade500],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(20),
                boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 10, offset: Offset(0, 4))],
              ),
              child: Column(
                children: [
                  const Icon(Icons.qr_code_scanner_rounded, size: 80, color: Colors.white),
                  const SizedBox(height: 12),
                  const Text(
                    'Terminal Scanner Presensi Siswa',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Pindai QR Code Kartu Pelajar Digital (SMKMH-SISWA-xxx) atau ketik NIS secara manual.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.white.withValues(alpha: 0.9), fontSize: 12),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 20),

            // Input Form Card
            Card(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
              elevation: 4,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Input / Pindai Payload QR Code:',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                    ),
                    const SizedBox(height: 10),
                    TextField(
                      controller: _qrInputController,
                      decoration: InputDecoration(
                        labelText: 'Kode QR / NIS (Contoh: SMKMH-SISWA-1001)',
                        border: const OutlineInputBorder(),
                        prefixIcon: const Icon(Icons.qr_code, color: Colors.teal),
                        suffixIcon: IconButton(
                          icon: const Icon(Icons.clear),
                          onPressed: () => _qrInputController.clear(),
                        ),
                      ),
                      onSubmitted: (val) => _processScan(val),
                    ),
                    const SizedBox(height: 14),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: ElevatedButton.icon(
                        onPressed: _isProcessing ? null : () => _processScan(),
                        icon: _isProcessing
                            ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                            : const Icon(Icons.check_circle_rounded),
                        label: const Text('Proses & Catat Presensi', style: TextStyle(fontSize: 16)),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: Colors.teal,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            const SizedBox(height: 20),

            // Scan Result Card (Realtime Web-Compatible Feedback)
            if (_lastScanResult != null) ...[
              Card(
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                color: isSuccess ? Colors.green.shade50 : Colors.red.shade50,
                elevation: 4,
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Icon(
                            isSuccess ? Icons.check_circle_rounded : Icons.error_rounded,
                            color: isSuccess ? Colors.green.shade800 : Colors.red.shade800,
                            size: 32,
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: Text(
                              isSuccess ? 'Presensi Berhasil Terekam!' : 'Presensi Gagal / Terkendala',
                              style: TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.bold,
                                color: isSuccess ? Colors.green.shade900 : Colors.red.shade900,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 12),
                      Text(
                        _lastScanResult!['message'] ?? '',
                        style: TextStyle(
                          fontSize: 14,
                          color: isSuccess ? Colors.green.shade900 : Colors.red.shade900,
                        ),
                      ),
                      if (data != null && data is Map<String, dynamic>) ...[
                        const Divider(height: 24),
                        Text("Nama Siswa: ${data['nama'] ?? '-'}", style: const TextStyle(fontWeight: FontWeight.bold)),
                        Text("NIS / NIP: ${data['nis'] ?? '-'}"),
                        Text("Rombel Kelas: ${data['kelas'] ?? '-'}"),
                        if (data['jam_masuk'] != null) Text("Jam Masuk: ${data['jam_masuk']}", style: const TextStyle(color: Colors.green, fontWeight: FontWeight.bold)),
                        if (data['jam_pulang'] != null) Text("Jam Pulang: ${data['jam_pulang']}", style: const TextStyle(color: Colors.blue, fontWeight: FontWeight.bold)),
                        if (data['status_keterangan'] != null)
                          Container(
                            margin: const EdgeInsets.only(top: 8),
                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: data['is_late'] == true ? Colors.orange : Colors.green,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              "Status: ${data['status_keterangan']}",
                              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                            ),
                          ),
                      ],
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
