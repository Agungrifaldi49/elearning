import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class SertifikatScreen extends StatefulWidget {
  const SertifikatScreen({super.key});

  @override
  State<SertifikatScreen> createState() => _SertifikatScreenState();
}

class _SertifikatScreenState extends State<SertifikatScreen> {
  bool _isLoading = true;
  String _errorMessage = '';
  Map<String, dynamic> _certData = {};
  bool _showScoreBreakdown = false;

  @override
  void initState() {
    super.initState();
    _fetchCertificateData();
  }

  Future<void> _fetchCertificateData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
      final userId = user?.id ?? 0;

      final res = await ApiService.get('siswa/sertifikat?user_id=$userId');
      if (!mounted) return;

      if (res['success'] == true && res['data'] is Map<String, dynamic>) {
        setState(() {
          _certData = res['data'];
          _isLoading = false;
        });
      } else {
        setState(() {
          _errorMessage = res['message']?.toString() ?? 'Gagal memuat data sertifikat';
          _isLoading = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = 'Terjadi kesalahan jaringan: $e';
          _isLoading = false;
        });
      }
    }
  }

  Future<void> _openWebCertificate() async {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;
    final webUrl = '${ApiService.serverRootUrl}index.php?url=siswa/sertifikat&user_id=$userId';
    
    final uri = Uri.parse(webUrl);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (mounted) {
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Buka di browser: $webUrl'),
              action: SnackBarAction(
                label: 'Salin',
                textColor: Colors.amber,
                onPressed: () {
                  Clipboard.setData(ClipboardData(text: webUrl));
                },
              ),
            ),
          );
        }
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal membuka halaman cetak sertifikat'),
            backgroundColor: Colors.red,
          ),
        );
      }
    }
  }

  void _copyCertNumber(String certNumber) {
    Clipboard.setData(ClipboardData(text: certNumber));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
            const SizedBox(width: 8),
            Text('No. Sertifikat disalin: $certNumber'),
          ],
        ),
        backgroundColor: const Color(0xFF0F172A),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _showQrDialog(String qrUrl, String qrData, String studentName) {
    showDialog(
      context: context,
      builder: (ctx) => Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.amber.shade50,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.qr_code_2_rounded, size: 40, color: Color(0xFFD97706)),
              ),
              const SizedBox(height: 16),
              Text(
                'Verifikasi QR Sertifikat',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                studentName,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF475569),
                ),
              ),
              const SizedBox(height: 20),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFF0056D3), width: 2),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x0F000000),
                      blurRadius: 10,
                      offset: Offset(0, 4),
                    ),
                  ],
                ),
                child: Image.network(
                  qrUrl,
                  width: 180,
                  height: 180,
                  fit: BoxFit.contain,
                  errorBuilder: (context, error, stackTrace) => Container(
                    width: 180,
                    height: 180,
                    color: Colors.grey.shade100,
                    alignment: Alignment.center,
                    child: const Icon(Icons.qr_code_rounded, size: 80, color: Colors.grey),
                  ),
                ),
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  qrData,
                  style: GoogleFonts.robotoMono(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF0056D3),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Scan QR ini untuk memverifikasi keaslian penerbitan sertifikat digital secara online.',
                textAlign: TextAlign.center,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 12,
                  color: const Color(0xFF64748B),
                ),
              ),
              const SizedBox(height: 20),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => Navigator.pop(ctx),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0F172A),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    padding: const EdgeInsets.symmetric(vertical: 12),
                  ),
                  child: const Text('Tutup', style: TextStyle(fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Color _getGradeColor(String? grade) {
    final g = (grade ?? '').toUpperCase().trim();
    if (g.startsWith('A')) return const Color(0xFF16A34A); // Green
    if (g.startsWith('B')) return const Color(0xFF2563EB); // Blue
    if (g.startsWith('C')) return const Color(0xFFD97706); // Amber
    if (g.startsWith('D')) return const Color(0xFFDC2626); // Red
    return const Color(0xFF64748B);
  }

  @override
  Widget build(BuildContext context) {
    final user = Provider.of<AuthProvider>(context).currentUser;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final siswa = (_certData['siswa'] as Map<String, dynamic>?) ?? {};
    final school = (_certData['school'] as Map<String, dynamic>?) ?? {};
    final certInfo = (_certData['cert_info'] as Map<String, dynamic>?) ?? {};
    final stats = (_certData['stats'] as Map<String, dynamic>?) ?? {};

    final studentName = siswa['nama_lengkap']?.toString().isNotEmpty == true
        ? siswa['nama_lengkap'].toString()
        : (user?.fullName ?? 'Siswa SMK Muthia Harapan');
    final nis = siswa['nis']?.toString().isNotEmpty == true ? siswa['nis'].toString() : '-';
    final nisn = siswa['nisn']?.toString().isNotEmpty == true ? siswa['nisn'].toString() : '-';
    final namaKelas = siswa['nama_kelas']?.toString().isNotEmpty == true ? siswa['nama_kelas'].toString() : '-';
    final namaJurusan = siswa['nama_jurusan']?.toString().isNotEmpty == true ? siswa['nama_jurusan'].toString() : '-';

    final schoolName = school['nama_sekolah']?.toString() ?? 'SMK MUTHIA HARAPAN CICALENGKA';
    final schoolAlamat = school['alamat']?.toString() ?? 'Jl. Raya Cicalengka, Kab. Bandung, Jawa Barat';
    final kepalaSekolah = school['kepala_sekolah']?.toString() ?? 'H. ASEP SAEPULLOH, S. Ag';
    final logoUrl = school['logo_url']?.toString();

    final certTitle = certInfo['title']?.toString() ?? 'SERTIFIKAT PENGHARGAAN KELULUSAN DIGITAL';
    final nomorSertifikat = certInfo['nomor_sertifikat']?.toString() ?? 'SMKMH/SERT/2026/0001';
    final tanggalTerbit = certInfo['tanggal_terbit']?.toString() ?? '07 Oktober 2026';
    final statement = certInfo['statement']?.toString() ??
        'Telah berhasil menyelesaikan seluruh Program Pembelajaran Digital E-Learning Semester Ganjil Tahun Pelajaran 2025/2026 dengan perolehan hasil yang memuaskan serta menunjukkan kedisiplinan dan semangat belajar yang luar biasa.';
    final qrUrl = certInfo['qr_url']?.toString() ??
        'https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=SMKMH-CERT-$nisn&color=0056d3&bgcolor=ffffff';
    final qrData = certInfo['qr_data']?.toString() ?? 'SMKMH-CERT-$nisn';

    final predikatStr = stats['predikat']?.toString() ?? 'Belum Ada Data';
    final presensiLog = stats['presensi_log']?.toString() ?? 'Belum Ada Data';
    final evaluasiLms = stats['evaluasi_lms']?.toString() ?? 'Belum Ada Nilai';
    final predikatGrade = stats['predikat_grade']?.toString() ?? 'D';
    final gradeColor = _getGradeColor(predikatGrade);

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: Text(
          'Sertifikat Digital',
          style: GoogleFonts.plusJakartaSans(
            fontWeight: FontWeight.bold,
            fontSize: 18,
            color: isDark ? Colors.white : const Color(0xFF0F172A),
          ),
        ),
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        foregroundColor: isDark ? Colors.white : const Color(0xFF0F172A),
        elevation: 0.5,
        actions: [
          IconButton(
            tooltip: 'Segarkan Data',
            icon: const Icon(Icons.refresh_rounded),
            onPressed: _fetchCertificateData,
          ),
          IconButton(
            tooltip: 'Cetak / Unduh PDF',
            icon: const Icon(Icons.print_rounded),
            onPressed: _openWebCertificate,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Color(0xFFD97706)),
                  SizedBox(height: 16),
                  Text(
                    'Menyiapkan Sertifikat Digital Resmi...',
                    style: TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF64748B)),
                  ),
                ],
              ),
            )
          : _errorMessage.isNotEmpty
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.error_outline_rounded, size: 64, color: Colors.red.shade400),
                        const SizedBox(height: 16),
                        Text(
                          'Gagal Memuat Sertifikat',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          _errorMessage,
                          textAlign: TextAlign.center,
                          style: const TextStyle(color: Color(0xFF64748B)),
                        ),
                        const SizedBox(height: 20),
                        ElevatedButton.icon(
                          onPressed: _fetchCertificateData,
                          icon: const Icon(Icons.refresh_rounded),
                          label: const Text('Coba Lagi'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFFD97706),
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                      ],
                    ),
                  ),
                )
              : RefreshIndicator(
                  color: const Color(0xFFD97706),
                  onRefresh: _fetchCertificateData,
                  child: SingleChildScrollView(
                    physics: const AlwaysScrollableScrollPhysics(),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        // 1. Hero Luxury Banner
                        _buildHeroBanner(schoolName),
                        const SizedBox(height: 20),

                        // 2. Main Luxury Certificate Paper Frame
                        _buildCertificatePaper(
                          context: context,
                          studentName: studentName,
                          nis: nis,
                          nisn: nisn,
                          namaKelas: namaKelas,
                          namaJurusan: namaJurusan,
                          schoolName: schoolName,
                          schoolAlamat: schoolAlamat,
                          kepalaSekolah: kepalaSekolah,
                          logoUrl: logoUrl,
                          certTitle: certTitle,
                          statement: statement,
                          nomorSertifikat: nomorSertifikat,
                          tanggalTerbit: tanggalTerbit,
                          predikatStr: predikatStr,
                          presensiLog: presensiLog,
                          evaluasiLms: evaluasiLms,
                          predikatGrade: predikatGrade,
                          gradeColor: gradeColor,
                          qrUrl: qrUrl,
                          qrData: qrData,
                          stats: stats,
                        ),
                        const SizedBox(height: 20),

                        // 3. Quick Action Buttons
                        _buildActionRow(nomorSertifikat, qrUrl, qrData, studentName),
                        const SizedBox(height: 30),
                      ],
                    ),
                  ),
                ),
    );
  }

  // Hero Amber Gradient Banner matching web .sertifikat-hero-banner
  Widget _buildHeroBanner(String schoolName) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF0F172A), Color(0xFF78350F), Color(0xFFD97706)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [
          BoxShadow(
            color: Color(0x47D97706),
            blurRadius: 20,
            offset: Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: const [
                    BoxShadow(
                      color: Color(0x33000000),
                      blurRadius: 8,
                      offset: Offset(0, 4),
                    ),
                  ],
                ),
                child: const Icon(
                  Icons.workspace_premium_rounded,
                  color: Colors.white,
                  size: 30,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Sertifikat Kelulusan & Prestasi',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                        letterSpacing: -0.2,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Terverifikasi resmi penerbitan otomatis dari $schoolName',
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                        color: const Color(0xFFFEF3C7),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          InkWell(
            onTap: _openWebCertificate,
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFF59E0B),
                borderRadius: BorderRadius.circular(12),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x26000000),
                    blurRadius: 6,
                    offset: Offset(0, 2),
                  ),
                ],
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.print_rounded, size: 18, color: Color(0xFF0F172A)),
                  const SizedBox(width: 8),
                  Text(
                    'Cetak / Simpan PDF Sertifikat',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF0F172A),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  // The actual Luxury Certificate Frame
  Widget _buildCertificatePaper({
    required BuildContext context,
    required String studentName,
    required String nis,
    required String nisn,
    required String namaKelas,
    required String namaJurusan,
    required String schoolName,
    required String schoolAlamat,
    required String kepalaSekolah,
    required String? logoUrl,
    required String certTitle,
    required String statement,
    required String nomorSertifikat,
    required String tanggalTerbit,
    required String predikatStr,
    required String presensiLog,
    required String evaluasiLms,
    required String predikatGrade,
    required Color gradeColor,
    required String qrUrl,
    required String qrData,
    required Map<String, dynamic> stats,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFD97706), width: 3.5),
        boxShadow: const [
          BoxShadow(
            color: Color(0x140F172A),
            blurRadius: 30,
            offset: Offset(0, 10),
          ),
        ],
      ),
      child: Container(
        margin: const EdgeInsets.all(6),
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(color: const Color(0xFFFEF3C7), width: 1.5),
        ),
        child: Column(
          children: [
            // School Header
            _buildSchoolHeader(schoolName, schoolAlamat, logoUrl),
            const SizedBox(height: 12),

            // Ornamental Divider
            Row(
              children: [
                Expanded(child: Divider(color: Colors.amber.shade400, thickness: 1)),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8),
                  child: Icon(Icons.stars_rounded, color: Colors.amber.shade700, size: 18),
                ),
                Expanded(child: Divider(color: Colors.amber.shade400, thickness: 1)),
              ],
            ),
            const SizedBox(height: 12),

            // Certificate Ribbon Badge
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 7),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                  colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(30),
                boxShadow: const [
                  BoxShadow(
                    color: Color(0x4DD97706),
                    blurRadius: 8,
                    offset: Offset(0, 3),
                  ),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.workspace_premium_rounded, size: 16, color: Colors.white),
                  const SizedBox(width: 6),
                  Flexible(
                    child: Text(
                      certTitle,
                      textAlign: TextAlign.center,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                        letterSpacing: 0.8,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),

            // Diberikan Kepada
            Text(
              'Dengan bangga diberikan kepada:',
              style: GoogleFonts.plusJakartaSans(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: const Color(0xFF64748B),
              ),
            ),
            const SizedBox(height: 8),

            // Student Full Name in Playfair Display (Prestigious Serif)
            Text(
              studentName,
              textAlign: TextAlign.center,
              style: GoogleFonts.playfairDisplay(
                fontSize: 24,
                fontWeight: FontWeight.w800,
                color: const Color(0xFF0F172A),
                letterSpacing: -0.5,
              ),
            ),
            const SizedBox(height: 12),

            // Metadata Badges (NIS / NISN & Kelas / Jurusan)
            Wrap(
              alignment: WrapAlignment.center,
              spacing: 8,
              runSpacing: 8,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF8FAFC),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFE2E8F0)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.badge_rounded, size: 14, color: Color(0xFF0284C7)),
                      const SizedBox(width: 6),
                      Text(
                        'NIS: $nis  |  NISN: $nisn',
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF1E293B),
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: const Color(0xFFEFF6FF),
                    borderRadius: BorderRadius.circular(20),
                    border: Border.all(color: const Color(0xFFBFDBFE)),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.school_rounded, size: 14, color: Color(0xFF2563EB)),
                      const SizedBox(width: 6),
                      Text(
                        '$namaKelas — $namaJurusan',
                        style: GoogleFonts.plusJakartaSans(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w600,
                          color: const Color(0xFF1D4ED8),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),

            // Formal Statement Quote Box
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Text(
                '"$statement"',
                textAlign: TextAlign.center,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 12.5,
                  height: 1.55,
                  fontStyle: FontStyle.italic,
                  color: const Color(0xFF475569),
                ),
              ),
            ),
            const SizedBox(height: 18),

            // 3 Real Performance Metrics Cards
            _buildRealStatsRow(
              predikatStr: predikatStr,
              presensiLog: presensiLog,
              evaluasiLms: evaluasiLms,
              predikatGrade: predikatGrade,
              gradeColor: gradeColor,
            ),
            const SizedBox(height: 14),

            // Detailed Score Breakdown Accordion
            _buildScoreBreakdown(stats),
            const SizedBox(height: 16),

            // Divider before Legal Verification
            Divider(color: Colors.grey.shade200, thickness: 1),
            const SizedBox(height: 12),

            // Legal Verification Section (Nomor, QR, Signature)
            _buildLegalVerification(
              nomorSertifikat: nomorSertifikat,
              tanggalTerbit: tanggalTerbit,
              kepalaSekolah: kepalaSekolah,
              qrUrl: qrUrl,
              qrData: qrData,
              studentName: studentName,
            ),
          ],
        ),
      ),
    );
  }

  // School Header & Logo
  Widget _buildSchoolHeader(String schoolName, String schoolAlamat, String? logoUrl) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        if (logoUrl != null && logoUrl.isNotEmpty)
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: Image.network(
              logoUrl,
              width: 48,
              height: 48,
              fit: BoxFit.contain,
              errorBuilder: (_, __, ___) => _buildFallbackLogo(),
            ),
          )
        else
          _buildFallbackLogo(),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                schoolName.toUpperCase(),
                style: GoogleFonts.outfit(
                  fontSize: 13.5,
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF0F172A),
                  letterSpacing: 0.5,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                schoolAlamat,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 10,
                  color: const Color(0xFF64748B),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildFallbackLogo() {
    return Container(
      width: 46,
      height: 46,
      decoration: BoxDecoration(
        color: const Color(0xFF0284C7),
        borderRadius: BorderRadius.circular(12),
      ),
      child: const Icon(Icons.school_rounded, color: Colors.white, size: 26),
    );
  }

  // 3 Real Performance Metrics Cards
  Widget _buildRealStatsRow({
    required String predikatStr,
    required String presensiLog,
    required String evaluasiLms,
    required String predikatGrade,
    required Color gradeColor,
  }) {
    return Column(
      children: [
        Row(
          children: [
            // Stat 1: Predikat
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  children: [
                    Text(
                      predikatStr,
                      textAlign: TextAlign.center,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: gradeColor,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(Icons.star_rounded, size: 13, color: Colors.amber.shade700),
                        const SizedBox(width: 4),
                        Text(
                          'Predikat Belajar',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF64748B),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(width: 8),

            // Stat 2: Kehadiran
            Expanded(
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  children: [
                    Text(
                      presensiLog,
                      textAlign: TextAlign.center,
                      style: GoogleFonts.plusJakartaSans(
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                        color: const Color(0xFF16A34A),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.event_available_rounded, size: 13, color: Color(0xFF16A34A)),
                        const SizedBox(width: 4),
                        Text(
                          'Kehadiran KBM',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                            color: const Color(0xFF64748B),
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
        const SizedBox(height: 8),

        // Stat 3: Rata-Rata Evaluasi LMS
        Container(
          width: double.infinity,
          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 12),
          decoration: BoxDecoration(
            color: const Color(0x66FEF3C7),
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFFDE68A)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: Colors.amber.shade100,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.military_tech_rounded, size: 18, color: Color(0xFFD97706)),
              ),
              const SizedBox(width: 10),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    evaluasiLms,
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF92400E),
                    ),
                  ),
                  Text(
                    'Rata-Rata Evaluasi LMS Riil (Komposit)',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w600,
                      color: const Color(0xFFB45309),
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ],
    );
  }

  // Detailed Score Breakdown Accordion
  Widget _buildScoreBreakdown(Map<String, dynamic> stats) {
    final avgTugas = stats['avg_tugas'] != null ? stats['avg_tugas'].toString() : '-';
    final avgQuiz = stats['avg_quiz'] != null ? stats['avg_quiz'].toString() : '-';
    final avgRapor = stats['avg_rapor'] != null ? stats['avg_rapor'].toString() : '-';
    final isTuntas = stats['is_tuntas'] == true || stats['is_tuntas'] == 1;

    return Container(
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        children: [
          InkWell(
            onTap: () {
              setState(() {
                _showScoreBreakdown = !_showScoreBreakdown;
              });
            },
            borderRadius: BorderRadius.circular(14),
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              child: Row(
                children: [
                  const Icon(Icons.analytics_rounded, size: 16, color: Color(0xFFD97706)),
                  const SizedBox(width: 8),
                  Text(
                    'Rincian Komposisi Penilaian',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF334155),
                    ),
                  ),
                  const Spacer(),
                  Icon(
                    _showScoreBreakdown ? Icons.keyboard_arrow_up_rounded : Icons.keyboard_arrow_down_rounded,
                    size: 18,
                    color: const Color(0xFF64748B),
                  ),
                ],
              ),
            ),
          ),
          if (_showScoreBreakdown)
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 12),
              child: Column(
                children: [
                  const Divider(height: 1),
                  const SizedBox(height: 10),
                  _buildScoreRow('Bobot Tugas (25%)', avgTugas, Icons.assignment_outlined),
                  const SizedBox(height: 6),
                  _buildScoreRow('Bobot Kuis & CBT (35%)', avgQuiz, Icons.quiz_outlined),
                  const SizedBox(height: 6),
                  _buildScoreRow('Bobot E-Rapor (40%)', avgRapor, Icons.menu_book_rounded),
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: isTuntas ? const Color(0xFFDCFCE7) : const Color(0xFFFEE2E2),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          isTuntas ? Icons.check_circle_rounded : Icons.info_outline_rounded,
                          size: 14,
                          color: isTuntas ? const Color(0xFF16A34A) : const Color(0xFFDC2626),
                        ),
                        const SizedBox(width: 6),
                        Text(
                          isTuntas ? 'Status: TUNTAS (Memenuhi Standar KKM 75)' : 'Status: Perlu Bimbingan Tambahan',
                          style: GoogleFonts.plusJakartaSans(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: isTuntas ? const Color(0xFF15803D) : const Color(0xFFB91C1C),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildScoreRow(String label, String value, IconData icon) {
    return Row(
      children: [
        Icon(icon, size: 14, color: const Color(0xFF64748B)),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            label,
            style: GoogleFonts.plusJakartaSans(
              fontSize: 11.5,
              color: const Color(0xFF475569),
            ),
          ),
        ),
        Text(
          value,
          style: GoogleFonts.plusJakartaSans(
            fontSize: 12,
            fontWeight: FontWeight.bold,
            color: const Color(0xFF0F172A),
          ),
        ),
      ],
    );
  }

  // Legal Verification Section: Nomor Registrasi, QR Code, Signature
  Widget _buildLegalVerification({
    required String nomorSertifikat,
    required String tanggalTerbit,
    required String kepalaSekolah,
    required String qrUrl,
    required String qrData,
    required String studentName,
  }) {
    return Column(
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Left: Certificate Registration Number
            Expanded(
              flex: 3,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Nomor Registrasi Sertifikat:',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 10,
                      color: const Color(0xFF64748B),
                    ),
                  ),
                  const SizedBox(height: 2),
                  GestureDetector(
                    onTap: () => _copyCertNumber(nomorSertifikat),
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFEFF6FF),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFBFDBFE)),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Flexible(
                            child: Text(
                              nomorSertifikat,
                              style: GoogleFonts.robotoMono(
                                fontSize: 10.5,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFF0056D3),
                              ),
                            ),
                          ),
                          const SizedBox(width: 4),
                          const Icon(Icons.copy_rounded, size: 12, color: Color(0xFF0056D3)),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    'Diterbitkan: $tanggalTerbit',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 10,
                      color: const Color(0xFF64748B),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 10),

            // Center: QR Code Image
            GestureDetector(
              onTap: () => _showQrDialog(qrUrl, qrData, studentName),
              child: Column(
                children: [
                  Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFF0056D3), width: 1.5),
                    ),
                    child: Image.network(
                      qrUrl,
                      width: 58,
                      height: 58,
                      fit: BoxFit.contain,
                      errorBuilder: (_, __, ___) => const Icon(Icons.qr_code_2_rounded, size: 48, color: Colors.blue),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    'Pindai QR',
                    style: GoogleFonts.plusJakartaSans(
                      fontSize: 9,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF0056D3),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 16),

        // Signature of Kepala Sekolah
        Container(
          width: double.infinity,
          alignment: Alignment.centerRight,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                'Cicalengka, $tanggalTerbit',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 11,
                  color: const Color(0xFF64748B),
                ),
              ),
              const SizedBox(height: 28),
              Container(
                decoration: const BoxDecoration(
                  border: Border(bottom: BorderSide(color: Color(0xFF0F172A), width: 1.2)),
                ),
                padding: const EdgeInsets.only(bottom: 2),
                child: Text(
                  kepalaSekolah,
                  style: GoogleFonts.plusJakartaSans(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: const Color(0xFF0F172A),
                  ),
                ),
              ),
              const SizedBox(height: 2),
              Text(
                'Kepala Sekolah Pengesah',
                style: GoogleFonts.plusJakartaSans(
                  fontSize: 10.5,
                  color: const Color(0xFF64748B),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  // Quick Action Buttons Row
  Widget _buildActionRow(String nomorSertifikat, String qrUrl, String qrData, String studentName) {
    return Row(
      children: [
        Expanded(
          child: ElevatedButton.icon(
            onPressed: _openWebCertificate,
            icon: const Icon(Icons.download_rounded, size: 18),
            label: const Text('Unduh PDF'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFD97706),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 13),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
              elevation: 2,
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: OutlinedButton.icon(
            onPressed: () => _showQrDialog(qrUrl, qrData, studentName),
            icon: const Icon(Icons.qr_code_scanner_rounded, size: 18),
            label: const Text('Verifikasi QR'),
            style: OutlinedButton.styleFrom(
              foregroundColor: const Color(0xFF0F172A),
              side: const BorderSide(color: Color(0xFFCBD5E1)),
              padding: const EdgeInsets.symmetric(vertical: 13),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            ),
          ),
        ),
      ],
    );
  }
}
