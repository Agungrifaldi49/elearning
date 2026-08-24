import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class GuruScanQRScreen extends StatefulWidget {
  const GuruScanQRScreen({super.key});

  @override
  State<GuruScanQRScreen> createState() => _GuruScanQRScreenState();
}

class _GuruScanQRScreenState extends State<GuruScanQRScreen> {
  final MobileScannerController _scannerController = MobileScannerController(
    detectionSpeed: DetectionSpeed.normal,
    facing: CameraFacing.back,
    torchEnabled: false,
  );

  final _manualInputController = TextEditingController();
  bool _isProcessing = false;
  bool _isTorchOn = false;
  String _lastScannedCode = '';
  DateTime? _lastScanTime;
  Map<String, dynamic>? _lastScanResult;

  @override
  void dispose() {
    _scannerController.dispose();
    _manualInputController.dispose();
    super.dispose();
  }

  Future<void> _processScan(String rawPayload) async {
    final payload = rawPayload.trim();
    if (payload.isEmpty) return;

    final upperPayload = payload.toUpperCase();
    if (upperPayload.startsWith('SMKMH-GURU-') || upperPayload.startsWith('GURU-')) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('⚠️ Akses Ditolak: Halaman Scanner Presensi ini KHUSUS untuk Siswa! QR Code Guru tidak dapat di-scan di sini.'),
          backgroundColor: Colors.red,
          duration: Duration(seconds: 4),
        ),
      );
      return;
    }

    final now = DateTime.now();
    if (_lastScannedCode == payload &&
        _lastScanTime != null &&
        now.difference(_lastScanTime!).inSeconds < 3) {
      return; // Debounce duplicate scan
    }

    if (_isProcessing) return;

    setState(() {
      _isProcessing = true;
      _lastScannedCode = payload;
      _lastScanTime = now;
      _lastScanResult = null;
    });

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final res = await ApiService.post('guru/scan_qr', {
      'user_id': userId,
      'qr_code': payload,
      'identifier': payload,
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
        _manualInputController.clear();
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isSuccess = _lastScanResult?['success'] == true;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Kamera Scanner Presensi Siswa'),
        backgroundColor: Colors.teal,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: Icon(
              _isTorchOn ? Icons.flash_on : Icons.flash_off,
              color: _isTorchOn ? Colors.amber : Colors.white,
            ),
            onPressed: () {
              _scannerController.toggleTorch();
              setState(() {
                _isTorchOn = !_isTorchOn;
              });
            },
          ),
          IconButton(
            icon: const Icon(Icons.cameraswitch, color: Colors.white),
            onPressed: () => _scannerController.switchCamera(),
          ),
        ],
      ),
      body: Column(
        children: [
          // LIVE CAMERA SCANNER VIEWPORT
          Expanded(
            flex: 5,
            child: Stack(
              alignment: Alignment.center,
              children: [
                MobileScanner(
                  controller: _scannerController,
                  onDetect: (barcodeCapture) {
                    for (final barcode in barcodeCapture.barcodes) {
                      final val = barcode.rawValue;
                      if (val != null && val.isNotEmpty) {
                        _processScan(val);
                        break;
                      }
                    }
                  },
                ),

                // Viewfinder Target Overlay Frame
                Container(
                  width: 250,
                  height: 250,
                  decoration: BoxDecoration(
                    border: Border.all(
                      color: _isProcessing ? Colors.amber : Colors.tealAccent,
                      width: 3,
                    ),
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: (isSuccess ? Colors.teal : Colors.black).withValues(alpha: 0.3),
                        blurRadius: 10,
                      ),
                    ],
                  ),
                ),

                // Hint Label Banner
                Positioned(
                  bottom: 16,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                    decoration: BoxDecoration(
                      color: Colors.black.withValues(alpha: 0.65),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        _isProcessing
                            ? const SizedBox(
                                width: 14,
                                height: 14,
                                child: CircularProgressIndicator(color: Colors.amber, strokeWidth: 2),
                              )
                            : const Icon(Icons.qr_code_scanner, color: Colors.tealAccent, size: 18),
                        const SizedBox(width: 8),
                        Text(
                          _isProcessing ? 'Memproses Presensi...' : 'Arahkan kamera ke QR Code Kartu Pelajar',
                          style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),

          // REALTIME SCAN RESULT & MANUAL FALLBACK CONTAINER
          Expanded(
            flex: 4,
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  // Scan Result Feedback Card
                  if (_lastScanResult != null) ...[
                    Builder(
                      builder: (context) {
                        final rawData = _lastScanResult!['data'];
                        final dataMap = (rawData is Map<String, dynamic>) ? rawData : _lastScanResult!;
                        final namaSiswa = dataMap['nama'] ?? dataMap['nama_lengkap'] ?? dataMap['nama_siswa'] ?? '-';
                        final nisSiswa = dataMap['nis'] ?? dataMap['nisn'] ?? '-';
                        final kelasSiswa = dataMap['kelas'] ?? dataMap['nama_kelas'] ?? '-';
                        final jamMasukVal = dataMap['jam_masuk'] ?? dataMap['jam'] ?? dataMap['waktu_masuk'] ?? '-';
                        final jamPulangVal = dataMap['jam_pulang'] ?? dataMap['waktu_pulang'];
                        final statusKet = dataMap['status_keterangan'] ?? dataMap['status'] ?? (isSuccess ? 'Hadir Tepat Waktu' : 'Terkendala');

                        return Card(
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          color: isSuccess ? Colors.green.shade50 : Colors.red.shade50,
                          elevation: 4,
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    Icon(
                                      isSuccess ? Icons.check_circle_rounded : Icons.error_rounded,
                                      color: isSuccess ? Colors.green.shade800 : Colors.red.shade800,
                                      size: 28,
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(
                                        isSuccess ? 'Presensi Terekam!' : 'Presensi Terkendala',
                                        style: TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.bold,
                                          color: isSuccess ? Colors.green.shade900 : Colors.red.shade900,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  _lastScanResult!['message'] ?? '',
                                  style: TextStyle(
                                    fontSize: 13,
                                    color: isSuccess ? Colors.green.shade900 : Colors.red.shade900,
                                  ),
                                ),
                                const Divider(height: 16),
                                Text(
                                  "👤 Nama Siswa: $namaSiswa",
                                  style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  "💳 NIS / NISN: $nisSiswa • Rombel: $kelasSiswa",
                                  style: const TextStyle(fontSize: 13),
                                ),
                                const SizedBox(height: 4),
                                Row(
                                  children: [
                                    Text(
                                      "⏰ Jam Masuk: $jamMasukVal",
                                      style: const TextStyle(color: Colors.teal, fontWeight: FontWeight.bold, fontSize: 13),
                                    ),
                                    if (jamPulangVal != null) ...[
                                      const SizedBox(width: 12),
                                      Text(
                                        "🏠 Pulang: $jamPulangVal",
                                        style: const TextStyle(color: Colors.blue, fontWeight: FontWeight.bold, fontSize: 13),
                                      ),
                                    ],
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: isSuccess ? Colors.teal : Colors.red.shade700,
                                    borderRadius: BorderRadius.circular(8),
                                  ),
                                  child: Text(
                                    "Status: $statusKet",
                                    style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 11),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Manual Input Fallback Accordion
                  ExpansionTile(
                    initiallyExpanded: false,
                    leading: const Icon(Icons.keyboard, color: Colors.teal),
                    title: const Text('Input Manual NIS / Payload QR', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                    children: [
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                        child: Column(
                          children: [
                            TextField(
                              controller: _manualInputController,
                              decoration: const InputDecoration(
                                labelText: 'Ketik NIS / Kode QR (Contoh: SMKMH-SISWA-1001)',
                                border: OutlineInputBorder(),
                              ),
                              onSubmitted: (val) => _processScan(val),
                            ),
                            const SizedBox(height: 10),
                            SizedBox(
                              width: double.infinity,
                              child: ElevatedButton.icon(
                                onPressed: _isProcessing ? null : () => _processScan(_manualInputController.text),
                                icon: const Icon(Icons.check),
                                label: const Text('Rekam Presensi Manual'),
                                style: ElevatedButton.styleFrom(backgroundColor: Colors.teal, foregroundColor: Colors.white),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
