import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:geolocator/geolocator.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:image_picker/image_picker.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/guru_provider.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';

class GuruPresensiSelfieScreen extends StatefulWidget {
  const GuruPresensiSelfieScreen({super.key});

  @override
  State<GuruPresensiSelfieScreen> createState() => _GuruPresensiSelfieScreenState();
}

class _GuruPresensiSelfieScreenState extends State<GuruPresensiSelfieScreen> {
  bool _isLoading = true;
  bool _isSubmitting = false;

  // Geofence & Server Config
  String _schoolName = 'SMK Muthia Harapan Cicalengka';
  double _schoolLat = -6.984042;
  double _schoolLng = 107.838612;
  int _schoolRadius = 150;
  String _jamMasukBatas = '07:30';
  String _jamPulangMulai = '15:00';

  // Today's & History Data
  Map<String, dynamic>? _presensiHariIni;
  List<dynamic> _riwayatPresensi = [];
  Map<String, dynamic>? _guruProfile;

  // Live GPS State
  Position? _currentPosition;
  double? _distanceInMeters;
  bool _isInsideRadius = false;
  bool _isLocating = false;
  String? _gpsErrorMessage;
  StreamSubscription<Position>? _positionStreamSub;

  // Live Clock
  Timer? _clockTimer;
  String _liveTimeStr = '00:00:00';
  String _liveDateStr = '';

  // Selfie Camera State
  File? _selfieImageFile;
  String? _selfieBase64;
  final TextEditingController _keteranganController = TextEditingController();
  final ImagePicker _picker = ImagePicker();

  @override
  void initState() {
    super.initState();
    _startClock();
    _loadGeofenceInfo();
    _initGPSLocation();
  }

  @override
  void dispose() {
    _clockTimer?.cancel();
    _positionStreamSub?.cancel();
    _keteranganController.dispose();
    super.dispose();
  }

  String _formatIndonesianDate(DateTime dt) {
    const hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    const bulanList = [
      'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    final namaHari = hariList[dt.weekday - 1];
    final namaBulan = bulanList[dt.month - 1];
    return '$namaHari, ${dt.day} $namaBulan ${dt.year}';
  }

  String _formatWatermarkDateTime(DateTime dt) {
    const bulanList = [
      'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun',
      'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'
    ];
    final d = dt.day.toString().padLeft(2, '0');
    final m = bulanList[dt.month - 1];
    final y = dt.year;
    final hh = dt.hour.toString().padLeft(2, '0');
    final mm = dt.minute.toString().padLeft(2, '0');
    return '$d $m $y, $hh:$mm';
  }

  void _startClock() {
    _updateClock();
    _clockTimer = Timer.periodic(const Duration(seconds: 1), (_) => _updateClock());
  }

  void _updateClock() {
    final now = DateTime.now();
    if (mounted) {
      setState(() {
        _liveTimeStr = DateFormat('HH:mm:ss').format(now);
        _liveDateStr = _formatIndonesianDate(now);
      });
    }
  }

  Future<void> _loadGeofenceInfo() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isLoading = true);

    try {
      final res = await ApiService.get('guru/presensi_selfie_info', params: {
        'user_id': user.id.toString(),
      });

      if (res['success'] == true && res['data'] != null) {
        final data = res['data'];
        if (mounted) {
          setState(() {
            _schoolName = (data['lokasi_sekolah_nama'] ?? _schoolName).toString();
            _schoolLat = double.tryParse((data['lokasi_sekolah_lat'] ?? _schoolLat).toString()) ?? _schoolLat;
            _schoolLng = double.tryParse((data['lokasi_sekolah_lng'] ?? _schoolLng).toString()) ?? _schoolLng;
            _schoolRadius = int.tryParse((data['lokasi_sekolah_radius'] ?? _schoolRadius).toString()) ?? _schoolRadius;
            _jamMasukBatas = (data['presensi_jam_masuk_batas'] ?? _jamMasukBatas).toString();
            _jamPulangMulai = (data['presensi_jam_pulang_mulai'] ?? _jamPulangMulai).toString();

            if (data['presensi_hari_ini'] is Map) {
              _presensiHariIni = Map<String, dynamic>.from(data['presensi_hari_ini']);
            } else {
              _presensiHariIni = null;
            }

            if (data['riwayat_presensi'] is List) {
              _riwayatPresensi = data['riwayat_presensi'] as List;
            }

            if (data['guru'] is Map) {
              _guruProfile = Map<String, dynamic>.from(data['guru']);
            }
          });
        }
      }
    } catch (e) {
      debugPrint('Error loading geofence info: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _initGPSLocation() async {
    if (_isLocating) return;
    setState(() {
      _isLocating = true;
      _gpsErrorMessage = null;
    });

    try {
      // 1. Check if location service enabled
      bool serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        setState(() {
          _gpsErrorMessage = 'Layanan GPS (Lokasi) belum aktif pada perangkat Anda. Silakan aktifkan GPS.';
          _isLocating = false;
        });
        return;
      }

      // 2. Check and request permission
      LocationPermission permission = await Geolocator.checkPermission();
      if (permission == LocationPermission.denied) {
        permission = await Geolocator.requestPermission();
        if (permission == LocationPermission.denied) {
          setState(() {
            _gpsErrorMessage = 'Izin akses lokasi GPS ditolak oleh pengguna.';
            _isLocating = false;
          });
          return;
        }
      }

      if (permission == LocationPermission.deniedForever) {
        setState(() {
          _gpsErrorMessage = 'Izin lokasi ditolak secara permanen. Buka Pengaturan Aplikasi untuk mengaktifkan.';
          _isLocating = false;
        });
        return;
      }

      // 3. Get accurate current position
      final pos = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 12),
        ),
      );

      _updatePosition(pos);

      // 4. Listen to continuous position updates
      _positionStreamSub?.cancel();
      _positionStreamSub = Geolocator.getPositionStream(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          distanceFilter: 3,
        ),
      ).listen(_updatePosition);
    } catch (e) {
      debugPrint('GPS Error: $e');
      if (mounted) {
        setState(() {
          _gpsErrorMessage = 'Gagal mendeteksi koordinat GPS: $e';
        });
      }
    } finally {
      if (mounted) setState(() => _isLocating = false);
    }
  }

  void _updatePosition(Position pos) {
    if (!mounted) return;

    final distance = Geolocator.distanceBetween(
      pos.latitude,
      pos.longitude,
      _schoolLat,
      _schoolLng,
    );

    setState(() {
      _currentPosition = pos;
      _distanceInMeters = distance;
      _isInsideRadius = distance <= _schoolRadius;
      _gpsErrorMessage = null;
    });
  }

  Future<void> _takeSelfie() async {
    try {
      final XFile? photo = await _picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.front,
        maxWidth: 1024,
        maxHeight: 1024,
        imageQuality: 85,
      );

      if (photo != null) {
        final bytes = await File(photo.path).readAsBytes();
        final base64Img = 'data:image/jpeg;base64,${base64Encode(bytes)}';

        setState(() {
          _selfieImageFile = File(photo.path);
          _selfieBase64 = base64Img;
        });

        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Foto selfie wajah berhasil diambil!'),
            backgroundColor: Color(0xFF10B981),
            duration: Duration(seconds: 2),
          ),
        );
      }
    } catch (e) {
      debugPrint('Selfie error: $e');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal membuka kamera: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  Future<void> _submitAttendance(String jenis) async {
    if (_selfieBase64 == null) {
      _showWarningDialog(
        title: 'Foto Selfie Diperlukan',
        message: 'Harap ambil foto selfie wajah Anda terlebih dahulu menggunakan tombol kamera.',
      );
      return;
    }

    if (_currentPosition == null) {
      _showWarningDialog(
        title: 'Koordinat GPS Belum Terkunci',
        message: 'Pastikan sinyal GPS aktif dan akurat sebelum mengirim presensi.',
      );
      return;
    }

    if (!_isInsideRadius) {
      _showWarningDialog(
        title: 'Di Luar Radius Sekolah!',
        message: 'Jarak Anda saat ini ${_distanceInMeters?.round() ?? 0} meter dari sekolah. Batas toleransi maksimal adalah $_schoolRadius meter.\n\nPresensi hanya dapat dilakukan di lingkungan sekolah.',
        isDanger: true,
      );
      return;
    }

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user == null) return;

    setState(() => _isSubmitting = true);

    try {
      final body = {
        'user_id': user.id,
        'jenis': jenis,
        'latitude': _currentPosition!.latitude,
        'longitude': _currentPosition!.longitude,
        'image_base64': _selfieBase64,
        'keterangan': _keteranganController.text.trim(),
      };

      final res = await ApiService.post('guru/submit_presensi_selfie', body);

      if (res['success'] == true) {
        if (!mounted) return;
        // Refresh provider dashboard
        Provider.of<GuruProvider>(context, listen: false).fetchDashboard(user.id);

        if (mounted) {
          showDialog(
            context: context,
            barrierDismissible: false,
            builder: (ctx) => AlertDialog(
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
              contentPadding: const EdgeInsets.all(24),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 64,
                    height: 64,
                    decoration: const BoxDecoration(
                      shape: BoxShape.circle,
                      color: Color(0xFFD1FAE5),
                    ),
                    child: const Icon(Icons.check_circle_rounded, color: Color(0xFF10B981), size: 40),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Presensi Berhasil!',
                    style: GoogleFonts.outfit(fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    res['message'] ?? 'Presensi ${jenis.toUpperCase()} berhasil disimpan.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.grey.shade700, fontSize: 13),
                  ),
                  const SizedBox(height: 20),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _selfieImageFile = null;
                        _selfieBase64 = null;
                        _keteranganController.clear();
                        _loadGeofenceInfo();
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF10B981),
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                        padding: const EdgeInsets.symmetric(vertical: 12),
                      ),
                      child: const Text('OK, Mengerti', style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ),
                ],
              ),
            ),
          );
        }
      } else {
        _showWarningDialog(
          title: 'Presensi Ditolak',
          message: res['message'] ?? 'Terjadi kendala saat validasi presensi.',
          isDanger: true,
        );
      }
    } catch (e) {
      _showWarningDialog(
        title: 'Kesalahan Sistem',
        message: 'Gagal terhubung ke server: $e',
        isDanger: true,
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showWarningDialog({required String title, required String message, bool isDanger = false}) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.all(24),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isDanger ? const Color(0xFFFEE2E2) : const Color(0xFFFEF3C7),
              ),
              child: Icon(
                isDanger ? Icons.error_outline_rounded : Icons.warning_amber_rounded,
                color: isDanger ? const Color(0xFFEF4444) : const Color(0xFFF59E0B),
                size: 34,
              ),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: GoogleFonts.outfit(fontSize: 18, fontWeight: FontWeight.bold),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.grey.shade700, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 20),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                style: ElevatedButton.styleFrom(
                  backgroundColor: isDanger ? const Color(0xFFEF4444) : const Color(0xFFF59E0B),
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: const Text('Tutup'),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _previewPhotoDialog(String imageUrl, String title) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        clipBehavior: Clip.antiAlias,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              color: Colors.grey.shade900,
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      title,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: Colors.white, size: 20),
                    onPressed: () => Navigator.pop(ctx),
                  ),
                ],
              ),
            ),
            InteractiveViewer(
              child: Image.network(
                imageUrl,
                fit: BoxFit.contain,
                loadingBuilder: (context, child, progress) {
                  if (progress == null) return child;
                  return const SizedBox(
                    height: 250,
                    child: Center(child: CircularProgressIndicator()),
                  );
                },
                errorBuilder: (_, __, ___) => const SizedBox(
                  height: 200,
                  child: Center(child: Text('Gagal memuat foto')),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  bool get _sudahMasuk {
    final w = _presensiHariIni?['waktu_masuk']?.toString() ?? '';
    return w.isNotEmpty && w != '0000-00-00 00:00:00';
  }

  bool get _sudahPulang {
    final w = _presensiHariIni?['waktu_pulang']?.toString() ?? '';
    return w.isNotEmpty && w != '0000-00-00 00:00:00';
  }

  String get _jamMasukDisplay {
    if (!_sudahMasuk) return '-';
    final raw = _presensiHariIni!['waktu_masuk'].toString();
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('HH:mm').format(dt);
    } catch (_) {
      return raw.length >= 5 ? raw.substring(0, 5) : raw;
    }
  }

  String get _jamPulangDisplay {
    if (!_sudahPulang) return '-';
    final raw = _presensiHariIni!['waktu_pulang'].toString();
    try {
      final dt = DateTime.parse(raw);
      return DateFormat('HH:mm').format(dt);
    } catch (_) {
      return raw.length >= 5 ? raw.substring(0, 5) : raw;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      appBar: AppBar(
        title: Text(
          'Presensi Selfie Guru',
          style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 18),
        ),
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan Data & GPS',
            onPressed: () {
              _loadGeofenceInfo();
              _initGPSLocation();
            },
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: () async {
                await _loadGeofenceInfo();
                await _initGPSLocation();
              },
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // 1. Digital Real-Time Clock & Header Card
                    _buildClockHeaderCard(isDark),

                    const SizedBox(height: 16),

                    // 2. Today's Status Cards (Masuk & Pulang)
                    _buildTodayStatusCards(isDark),

                    const SizedBox(height: 16),

                    // 3. Live Geofencing Radar & GPS Status Card
                    _buildGeofenceRadarCard(isDark),

                    const SizedBox(height: 16),

                    // 4. Camera Selfie Viewfinder & Photo Preview Card
                    _buildCameraSelfieCard(isDark),

                    const SizedBox(height: 24),

                    // 5. Recent History
                    _buildHistorySection(isDark),

                    const SizedBox(height: 32),
                  ],
                ),
              ),
            ),
    );
  }

  // Widget 1: Digital Real-Time Clock & Header Card
  Widget _buildClockHeaderCard(bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: AppTheme.guruGradient,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF1E3A8A).withValues(alpha: 0.35),
            blurRadius: 14,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _liveDateStr.isNotEmpty ? _liveDateStr : 'Presensi Kehadiran Guru',
                    style: const TextStyle(color: Colors.white70, fontSize: 12),
                  ),
                  const SizedBox(height: 4),
                  Row(
                    children: [
                      Text(
                        _liveTimeStr,
                        style: GoogleFonts.outfit(
                          fontSize: 28,
                          fontWeight: FontWeight.w800,
                          color: Colors.white,
                          letterSpacing: 1.2,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text('WIB', style: TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.bold)),
                      ),
                    ],
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.15),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.camera_enhance_rounded, color: Colors.white, size: 28),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
            decoration: BoxDecoration(
              color: Colors.black.withValues(alpha: 0.25),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.school_rounded, color: Colors.white70, size: 14),
                const SizedBox(width: 6),
                Flexible(
                  child: Text(
                    _schoolName,
                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.w500),
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // Widget 2: Today's Status Cards
  Widget _buildTodayStatusCards(bool isDark) {
    return Row(
      children: [
        // Masuk Card
        Expanded(
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: _sudahMasuk ? const Color(0xFF10B981).withValues(alpha: 0.4) : Colors.grey.withValues(alpha: 0.2),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: _sudahMasuk ? const Color(0xFFD1FAE5) : const Color(0xFFFEF3C7),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        _sudahMasuk ? 'Masuk' : 'Belum Masuk',
                        style: TextStyle(
                          color: _sudahMasuk ? const Color(0xFF065F46) : const Color(0xFF92400E),
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    if (_sudahMasuk && _presensiHariIni?['foto_masuk'] != null)
                      GestureDetector(
                        onTap: () => _previewPhotoDialog(
                          ApiService.getFileUrl(_presensiHariIni!['foto_masuk']),
                          'Selfie Masuk ($_jamMasukDisplay WIB)',
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: Image.network(
                            ApiService.getFileUrl(_presensiHariIni!['foto_masuk']),
                            width: 28,
                            height: 28,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Icon(Icons.image, size: 20),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  _sudahMasuk ? '$_jamMasukDisplay WIB' : '--:--',
                  style: GoogleFonts.outfit(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: _sudahMasuk ? const Color(0xFF10B981) : Colors.grey,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  _sudahMasuk
                      ? 'Status: ${_presensiHariIni?['status'] ?? 'Hadir'}'
                      : 'Batas: $_jamMasukBatas WIB',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
        ),

        const SizedBox(width: 12),

        // Pulang Card
        Expanded(
          child: Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: _sudahPulang ? const Color(0xFF3B82F6).withValues(alpha: 0.4) : Colors.grey.withValues(alpha: 0.2),
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: _sudahPulang ? const Color(0xFFDBEAFE) : const Color(0xFFFEF3C7),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        _sudahPulang ? 'Pulang' : 'Belum Pulang',
                        style: TextStyle(
                          color: _sudahPulang ? const Color(0xFF1E40AF) : const Color(0xFF92400E),
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                    if (_sudahPulang && _presensiHariIni?['foto_pulang'] != null)
                      GestureDetector(
                        onTap: () => _previewPhotoDialog(
                          ApiService.getFileUrl(_presensiHariIni!['foto_pulang']),
                          'Selfie Pulang ($_jamPulangDisplay WIB)',
                        ),
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(6),
                          child: Image.network(
                            ApiService.getFileUrl(_presensiHariIni!['foto_pulang']),
                            width: 28,
                            height: 28,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Icon(Icons.image, size: 20),
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  _sudahPulang ? '$_jamPulangDisplay WIB' : '--:--',
                  style: GoogleFonts.outfit(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: _sudahPulang ? const Color(0xFF3B82F6) : Colors.grey,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  _sudahPulang
                      ? 'Check-out selesai'
                      : 'Buka: $_jamPulangMulai WIB',
                  style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // Widget 3: Live Geofencing Radar & GPS Status Card
  Widget _buildGeofenceRadarCard(bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: _isInsideRadius
              ? const Color(0xFF10B981).withValues(alpha: 0.35)
              : const Color(0xFFEF4444).withValues(alpha: 0.35),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    width: 12,
                    height: 12,
                    decoration: BoxDecoration(
                      shape: BoxShape.circle,
                      color: _isInsideRadius ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                      boxShadow: [
                        BoxShadow(
                          color: (_isInsideRadius ? const Color(0xFF10B981) : const Color(0xFFEF4444)).withValues(alpha: 0.5),
                          blurRadius: 6,
                          spreadRadius: 2,
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    'Status Radius Geofencing',
                    style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                ],
              ),
              InkWell(
                onTap: _isLocating ? null : _initGPSLocation,
                borderRadius: BorderRadius.circular(20),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  child: Row(
                    children: [
                      if (_isLocating)
                        const SizedBox(
                          width: 12,
                          height: 12,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      else
                        const Icon(Icons.refresh, size: 14, color: Color(0xFF4F46E5)),
                      const SizedBox(width: 4),
                      Text(
                        _isLocating ? 'Mencari...' : 'Cek GPS',
                        style: const TextStyle(color: Color(0xFF4F46E5), fontSize: 11, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),

          if (_gpsErrorMessage != null) ...[
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFFEE2E2),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(Icons.location_off, color: Color(0xFFDC2626), size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _gpsErrorMessage!,
                      style: const TextStyle(color: Color(0xFFDC2626), fontSize: 11),
                    ),
                  ),
                ],
              ),
            ),
          ] else if (_currentPosition != null && _distanceInMeters != null) ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: _isInsideRadius
                    ? const Color(0xFF10B981).withValues(alpha: 0.1)
                    : const Color(0xFFEF4444).withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Row(
                children: [
                  Icon(
                    _isInsideRadius ? Icons.check_circle_rounded : Icons.cancel_rounded,
                    color: _isInsideRadius ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                    size: 26,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          _isInsideRadius
                              ? 'Lokasi Sesuai (Di Dalam Radius Sekolah)'
                              : 'Di Luar Radius Presensi Sekolah',
                          style: TextStyle(
                            color: _isInsideRadius ? const Color(0xFF065F46) : const Color(0xFF991B1B),
                            fontWeight: FontWeight.bold,
                            fontSize: 12,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          'Jarak saat ini: ${_distanceInMeters!.round()} meter (Batas: $_schoolRadius meter)',
                          style: TextStyle(
                            color: _isInsideRadius ? const Color(0xFF047857) : const Color(0xFFB91C1C),
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 8),

            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  'GPS: ${_currentPosition!.latitude.toStringAsFixed(6)}, ${_currentPosition!.longitude.toStringAsFixed(6)}',
                  style: TextStyle(fontSize: 10, color: Colors.grey.shade500, fontFamily: 'monospace'),
                ),
                Text(
                  'Akurasi: ±${_currentPosition!.accuracy.round()}m',
                  style: TextStyle(fontSize: 10, color: Colors.grey.shade500),
                ),
              ],
            ),
          ] else ...[
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 8),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    SizedBox(width: 14, height: 14, child: CircularProgressIndicator(strokeWidth: 2)),
                    SizedBox(width: 8),
                    Text('Menghubungkan ke satelit GPS...', style: TextStyle(fontSize: 11, color: Colors.grey)),
                  ],
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  // Widget 4: Camera Selfie Viewfinder & Photo Preview Card
  Widget _buildCameraSelfieCard(bool isDark) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Foto Selfie Wajah',
                style: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              if (_selfieImageFile != null)
                TextButton.icon(
                  onPressed: _takeSelfie,
                  icon: const Icon(Icons.replay_rounded, size: 16),
                  label: const Text('Foto Ulang', style: TextStyle(fontSize: 12)),
                  style: TextButton.styleFrom(
                    foregroundColor: const Color(0xFFEF4444),
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                  ),
                ),
            ],
          ),

          const SizedBox(height: 12),

          // Camera Viewfinder / Preview Frame
          Center(
            child: Container(
              width: double.infinity,
              height: 240,
              decoration: BoxDecoration(
                color: const Color(0xFF0F172A),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: _selfieImageFile != null ? const Color(0xFF10B981) : Colors.grey.shade700,
                  width: 2,
                ),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: _selfieImageFile != null
                    ? Stack(
                        fit: StackFit.expand,
                        children: [
                          Image.file(
                            _selfieImageFile!,
                            fit: BoxFit.cover,
                          ),
                          // Watermark Overlay on Preview
                          Positioned(
                            bottom: 0,
                            left: 0,
                            right: 0,
                            child: Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              color: Colors.black.withValues(alpha: 0.65),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    _guruProfile?['nama_lengkap'] ?? 'Guru MHC',
                                    style: const TextStyle(color: Colors.white, fontSize: 11, fontWeight: FontWeight.bold),
                                  ),
                                  Text(
                                    '${_formatWatermarkDateTime(DateTime.now())} WIB | Jarak: ${_distanceInMeters?.round() ?? 0}m',
                                    style: const TextStyle(color: Colors.white70, fontSize: 9),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      )
                    : Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          // Biometric Oval Guide Shape
                          Container(
                            width: 100,
                            height: 130,
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(50),
                              border: Border.all(
                                color: const Color(0xFF10B981).withValues(alpha: 0.7),
                                width: 2,
                                style: BorderStyle.solid,
                              ),
                            ),
                            child: const Icon(Icons.person_rounded, size: 48, color: Colors.white30),
                          ),
                          const SizedBox(height: 10),
                          const Text(
                            'Posisikan wajah Anda di dalam bingkai',
                            style: TextStyle(color: Colors.white70, fontSize: 12),
                          ),
                        ],
                      ),
              ),
            ),
          ),

          const SizedBox(height: 14),

          // Tombol Buka Kamera
          if (_selfieImageFile == null) ...[
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _takeSelfie,
                icon: const Icon(Icons.camera_alt_rounded),
                label: const Text('Ambil Foto Selfie Wajah', style: TextStyle(fontWeight: FontWeight.bold)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF4F46E5),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 13),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
              ),
            ),
          ],

          const SizedBox(height: 14),

          // Input Keterangan Opsional
          TextField(
            controller: _keteranganController,
            decoration: InputDecoration(
              hintText: 'Keterangan tambahan (opsional, misal: Tugas Luar)',
              hintStyle: TextStyle(fontSize: 12, color: Colors.grey.shade500),
              prefixIcon: const Icon(Icons.edit_note, size: 20),
              filled: true,
              fillColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey.shade300),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(color: Colors.grey.withValues(alpha: 0.2)),
              ),
            ),
            style: const TextStyle(fontSize: 12),
          ),

          const SizedBox(height: 18),

          // Action Buttons: Presensi Masuk & Presensi Pulang
          Row(
            children: [
              // Presensi Masuk
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: (_sudahMasuk || _isSubmitting)
                      ? null
                      : () => _submitAttendance('masuk'),
                  icon: _isSubmitting
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.login_rounded, size: 18),
                  label: Text(_sudahMasuk ? 'Sudah Masuk' : 'Presensi Masuk', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF10B981),
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: Colors.grey.shade300,
                    disabledForegroundColor: Colors.grey.shade600,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),

              const SizedBox(width: 10),

              // Presensi Pulang
              Expanded(
                child: ElevatedButton.icon(
                  onPressed: (!_sudahMasuk || _sudahPulang || _isSubmitting)
                      ? null
                      : () => _submitAttendance('pulang'),
                  icon: _isSubmitting
                      ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.logout_rounded, size: 18),
                  label: Text(_sudahPulang ? 'Sudah Pulang' : 'Presensi Pulang', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF3B82F6),
                    foregroundColor: Colors.white,
                    disabledBackgroundColor: Colors.grey.shade300,
                    disabledForegroundColor: Colors.grey.shade600,
                    padding: const EdgeInsets.symmetric(vertical: 13),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // Widget 5: Recent Attendance History
  Widget _buildHistorySection(bool isDark) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              'Riwayat Presensi Mandiri',
              style: GoogleFonts.outfit(fontSize: 16, fontWeight: FontWeight.bold),
            ),
            Text(
              '${_riwayatPresensi.length} Catatan',
              style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
            ),
          ],
        ),
        const SizedBox(height: 12),
        if (_riwayatPresensi.isEmpty)
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(24),
            decoration: BoxDecoration(
              color: isDark ? const Color(0xFF1E293B) : Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Column(
              children: [
                Icon(Icons.history_rounded, size: 36, color: Colors.grey),
                SizedBox(height: 8),
                Text('Belum ada riwayat presensi selfie.', style: TextStyle(color: Colors.grey, fontSize: 12)),
              ],
            ),
          )
        else
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _riwayatPresensi.length > 10 ? 10 : _riwayatPresensi.length,
            separatorBuilder: (_, __) => const SizedBox(height: 10),
            itemBuilder: (context, index) {
              final item = _riwayatPresensi[index] as Map;
              final tgl = (item['tanggal'] ?? '').toString();
              final waktuMasuk = (item['waktu_masuk'] ?? '').toString();
              final waktuPulang = (item['waktu_pulang'] ?? '').toString();
              final status = (item['status'] ?? 'Hadir').toString();
              final jarakMasuk = item['jarak_masuk_meter'];
              final fotoMasuk = item['foto_masuk']?.toString();
              final fotoPulang = item['foto_pulang']?.toString();

              String jamIn = '-';
              if (waktuMasuk.isNotEmpty && waktuMasuk != '0000-00-00 00:00:00') {
                try {
                  jamIn = DateFormat('HH:mm').format(DateTime.parse(waktuMasuk));
                } catch (_) {
                  jamIn = waktuMasuk.length >= 5 ? waktuMasuk.substring(0, 5) : waktuMasuk;
                }
              }

              String jamOut = '-';
              if (waktuPulang.isNotEmpty && waktuPulang != '0000-00-00 00:00:00') {
                try {
                  jamOut = DateFormat('HH:mm').format(DateTime.parse(waktuPulang));
                } catch (_) {
                  jamOut = waktuPulang.length >= 5 ? waktuPulang.substring(0, 5) : waktuPulang;
                }
              }

              Color statusColor = const Color(0xFF10B981);
              if (status == 'Terlambat') statusColor = const Color(0xFFF59E0B);
              if (status == 'Alpa' || status == 'Alpha') statusColor = const Color(0xFFEF4444);

              return Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF1E293B) : Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.03),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    // Date Badge
                    Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        color: statusColor.withValues(alpha: 0.12),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            tgl.length >= 10 ? tgl.substring(8, 10) : '',
                            style: TextStyle(fontWeight: FontWeight.bold, color: statusColor, fontSize: 14),
                          ),
                          Text(
                            tgl.length >= 7 ? _getMonthName(tgl.substring(5, 7)) : '',
                            style: TextStyle(color: statusColor, fontSize: 9),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(width: 12),

                    // Info
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                decoration: BoxDecoration(
                                  color: statusColor.withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(6),
                                ),
                                child: Text(
                                  status,
                                  style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold),
                                ),
                              ),
                              if (jarakMasuk != null) ...[
                                const SizedBox(width: 6),
                                Text(
                                  '• ${jarakMasuk}m dari sekolah',
                                  style: TextStyle(color: Colors.grey.shade500, fontSize: 10),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 4),
                          Row(
                            children: [
                              const Icon(Icons.arrow_downward_rounded, size: 12, color: Color(0xFF10B981)),
                              Text(' Masuk: $jamIn WIB', style: const TextStyle(fontSize: 11)),
                              const SizedBox(width: 8),
                              const Icon(Icons.arrow_upward_rounded, size: 12, color: Color(0xFF3B82F6)),
                              Text(' Pulang: $jamOut WIB', style: const TextStyle(fontSize: 11)),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // Selfie Thumbnails
                    Row(
                      children: [
                        if (fotoMasuk != null && fotoMasuk.isNotEmpty)
                          GestureDetector(
                            onTap: () => _previewPhotoDialog(
                              ApiService.getFileUrl(fotoMasuk),
                              'Selfie Masuk ($tgl)',
                            ),
                            child: Padding(
                              padding: const EdgeInsets.only(left: 4),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: Image.network(
                                  ApiService.getFileUrl(fotoMasuk),
                                  width: 34,
                                  height: 34,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const Icon(Icons.image, size: 24),
                                ),
                              ),
                            ),
                          ),
                        if (fotoPulang != null && fotoPulang.isNotEmpty)
                          GestureDetector(
                            onTap: () => _previewPhotoDialog(
                              ApiService.getFileUrl(fotoPulang),
                              'Selfie Pulang ($tgl)',
                            ),
                            child: Padding(
                              padding: const EdgeInsets.only(left: 4),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(8),
                                child: Image.network(
                                  ApiService.getFileUrl(fotoPulang),
                                  width: 34,
                                  height: 34,
                                  fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => const Icon(Icons.image, size: 24),
                                ),
                              ),
                            ),
                          ),
                      ],
                    ),
                  ],
                ),
              );
            },
          ),
      ],
    );
  }

  String _getMonthName(String mm) {
    const months = {
      '01': 'Jan',
      '02': 'Feb',
      '03': 'Mar',
      '04': 'Apr',
      '05': 'Mei',
      '06': 'Jun',
      '07': 'Jul',
      '08': 'Agt',
      '09': 'Sep',
      '10': 'Okt',
      '11': 'Nov',
      '12': 'Des',
    };
    return months[mm] ?? mm;
  }
}
