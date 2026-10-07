import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../providers/auth_provider.dart';
import '../../services/api_service.dart';

class SiswaPembayaranScreen extends StatefulWidget {
  const SiswaPembayaranScreen({super.key});

  @override
  State<SiswaPembayaranScreen> createState() => _SiswaPembayaranScreenState();
}

class _SiswaPembayaranScreenState extends State<SiswaPembayaranScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();

  bool _isLoading = true;
  String _errorMessage = '';

  Map<String, dynamic> _siswaInfo = {};
  Map<String, dynamic> _summary = {};
  List<dynamic> _unpaidBills = [];
  List<dynamic> _allBills = [];
  List<dynamic> _riwayat = [];
  Map<String, dynamic> _rekeningConfig = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
    _searchController.addListener(() {
      setState(() {});
    });
    _fetchPembayaranData();
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  bool _isTrue(dynamic val) {
    if (val == null) return false;
    if (val is bool) return val;
    if (val is num) return val != 0;
    final str = val.toString().toLowerCase().trim();
    return str == 'true' || str == '1' || str == 'yes';
  }

  String _formatRupiah(dynamic val) {
    if (val == null) return 'Rp 0';
    final num numVal = (val is num) ? val : (num.tryParse(val.toString()) ?? 0);
    final formatter = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);
    return formatter.format(numVal);
  }

  String _cleanText(String text) {
    if (text.isEmpty) return text;
    return text
        .replaceAll('&#039;', "'")
        .replaceAll('&quot;', '"')
        .replaceAll('&amp;', '&')
        .replaceAll('&lt;', '<')
        .replaceAll('&gt;', '>')
        .replaceAll('&nbsp;', ' ')
        .trim();
  }

  Future<void> _fetchPembayaranData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    final userId = user?.id ?? 0;

    final params = <String, String>{
      'user_id': userId.toString(),
    };

    final res = await ApiService.get('siswa/pembayaran', params: params);

    if (!mounted) return;

    final bool isSuccess = _isTrue(res['success']) || _isTrue(res['status']);
    final dynamic rawData = res['data'];

    if (isSuccess && rawData is Map) {
      final dataMap = Map<String, dynamic>.from(rawData);
      setState(() {
        _siswaInfo = (dataMap['siswa'] is Map) ? Map<String, dynamic>.from(dataMap['siswa']) : {};
        _summary = (dataMap['summary'] is Map) ? Map<String, dynamic>.from(dataMap['summary']) : {};
        _unpaidBills = (dataMap['unpaid_bills'] is List) ? dataMap['unpaid_bills'] : [];
        _allBills = (dataMap['all_bills'] is List) ? dataMap['all_bills'] : [];
        _riwayat = (dataMap['riwayat'] is List) ? dataMap['riwayat'] : [];
        _rekeningConfig = (dataMap['rekening_config'] is Map) ? Map<String, dynamic>.from(dataMap['rekening_config']) : {};
        _isLoading = false;
      });
    } else {
      setState(() {
        _errorMessage = res['message']?.toString() ?? 'Gagal memuat portal pembayaran.';
        _isLoading = false;
      });
    }
  }

  void _openWhatsApp(String phone) async {
    final cleanPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (cleanPhone.isEmpty) return;

    final url = 'https://wa.me/$cleanPhone';
    final uri = Uri.parse(url);
    try {
      if (await canLaunchUrl(uri)) {
        await launchUrl(uri, mode: LaunchMode.externalApplication);
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Tidak dapat membuka WhatsApp.')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }

  void _copyToClipboard(String text, String label) {
    Clipboard.setData(ClipboardData(text: text));
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('$label berhasil disalin ke clipboard!'),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 2),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
      ),
    );
  }

  void _showRekeningModal() {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final List bankAccounts = (_rekeningConfig['bank_accounts'] is List) ? _rekeningConfig['bank_accounts'] : [];
    final List prosedur = (_rekeningConfig['prosedur'] is List) ? _rekeningConfig['prosedur'] : [];
    final String kontak = _rekeningConfig['kontak_konfirmasi']?.toString() ?? '';
    final String catatan = _rekeningConfig['catatan_tambahan']?.toString() ?? '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.8,
          maxChildSize: 0.95,
          minChildSize: 0.5,
          expand: false,
          builder: (_, scrollController) {
            return SingleChildScrollView(
              controller: scrollController,
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 32),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 48,
                      height: 5,
                      decoration: BoxDecoration(
                        color: Colors.grey.withValues(alpha: 0.3),
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: const Color(0xFF0284C7).withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: const Icon(Icons.account_balance_rounded, color: Color(0xFF0284C7), size: 24),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              'Rekening & Prosedur Bayar',
                              style: GoogleFonts.outfit(
                                fontSize: 17,
                                fontWeight: FontWeight.bold,
                                color: isDark ? Colors.white : const Color(0xFF0F172A),
                              ),
                            ),
                            Text(
                              _rekeningConfig['nama_sekolah']?.toString() ?? 'SMK Muthia Harapan Cicalengka',
                              style: TextStyle(
                                fontSize: 12,
                                color: isDark ? Colors.white60 : Colors.grey.shade600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),
                  Text(
                    'Nomor Rekening Resmi Sekolah:',
                    style: GoogleFonts.outfit(
                      fontSize: 13,
                      fontWeight: FontWeight.bold,
                      color: isDark ? Colors.white70 : Colors.grey.shade800,
                    ),
                  ),
                  const SizedBox(height: 8),

                  // Bank Accounts List
                  if (bankAccounts.isEmpty)
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: Colors.grey.withValues(alpha: 0.2)),
                      ),
                      child: const Center(
                        child: Text('Informasi rekening sedang diperbarui oleh Bagian Keuangan TU.'),
                      ),
                    )
                  else
                    ...bankAccounts.map((acc) {
                      final bank = (acc['bank'] ?? 'BANK').toString().toUpperCase();
                      final norek = (acc['nomor_rekening'] ?? '').toString();
                      final an = (acc['atas_nama'] ?? 'SMK Muthia Harapan').toString();

                      return Container(
                        margin: const EdgeInsets.only(bottom: 10),
                        padding: const EdgeInsets.all(14),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: const Color(0xFF0284C7).withValues(alpha: 0.25),
                          ),
                        ),
                        child: Row(
                          children: [
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF0284C7).withValues(alpha: 0.15),
                                      borderRadius: BorderRadius.circular(6),
                                    ),
                                    child: Text(
                                      bank,
                                      style: GoogleFonts.outfit(
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold,
                                        color: const Color(0xFF0284C7),
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 6),
                                  SelectableText(
                                    norek,
                                    style: GoogleFonts.sourceCodePro(
                                      fontSize: 17,
                                      fontWeight: FontWeight.bold,
                                      color: isDark ? Colors.white : const Color(0xFF0F172A),
                                      letterSpacing: 1.2,
                                    ),
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    'a.n $an',
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark ? Colors.white60 : Colors.grey.shade600,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            ElevatedButton.icon(
                              style: ElevatedButton.styleFrom(
                                backgroundColor: const Color(0xFF0284C7),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              ),
                              icon: const Icon(Icons.copy_rounded, size: 14),
                              label: const Text('Salin', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                              onPressed: () => _copyToClipboard(norek.replaceAll(' ', ''), 'Nomor Rekening $bank'),
                            ),
                          ],
                        ),
                      );
                    }),

                  const SizedBox(height: 14),

                  // Prosedur Box
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF59E0B).withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFF59E0B).withValues(alpha: 0.3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            const Icon(Icons.info_outline_rounded, size: 18, color: Color(0xFFD97706)),
                            const SizedBox(width: 8),
                            Text(
                              'Prosedur Konfirmasi Pembayaran',
                              style: GoogleFonts.outfit(
                                fontSize: 13,
                                fontWeight: FontWeight.bold,
                                color: const Color(0xFFD97706),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        if (prosedur.isNotEmpty)
                          ...prosedur.asMap().entries.map((e) {
                            return Padding(
                              padding: const EdgeInsets.only(bottom: 4),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    '${e.key + 1}. ',
                                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12),
                                  ),
                                  Expanded(
                                    child: Text(
                                      e.value.toString(),
                                      style: TextStyle(
                                        fontSize: 12,
                                        color: isDark ? Colors.white70 : const Color(0xFF334155),
                                        height: 1.35,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          })
                        else
                          const Text(
                            'Silakan cantumkan NIS/NISN saat transfer dan konfirmasi bukti bayar ke TU.',
                            style: TextStyle(fontSize: 12),
                          ),

                        if (kontak.isNotEmpty) ...[
                          const SizedBox(height: 8),
                          Divider(color: Colors.amber.withValues(alpha: 0.3)),
                          const SizedBox(height: 4),
                          InkWell(
                            onTap: () => _openWhatsApp(kontak),
                            child: Row(
                              children: [
                                const Icon(Icons.chat_bubble_outline_rounded, size: 16, color: Color(0xFF10B981)),
                                const SizedBox(width: 6),
                                Expanded(
                                  child: Text(
                                    'WhatsApp Keuangan: $kontak',
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.bold,
                                      color: Color(0xFF10B981),
                                    ),
                                  ),
                                ),
                                const Icon(Icons.arrow_forward_ios_rounded, size: 12, color: Color(0xFF10B981)),
                              ],
                            ),
                          ),
                        ],
                      ],
                    ),
                  ),

                  if (catatan.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    Text(
                      '* $catatan',
                      style: TextStyle(
                        fontSize: 11,
                        fontStyle: FontStyle.italic,
                        color: isDark ? Colors.white38 : Colors.grey.shade500,
                      ),
                    ),
                  ],
                ],
              ),
            );
          },
        );
      },
    );
  }

  void _showSlipDialog(dynamic trx) {
    final isDark = Theme.of(context).brightness == Brightness.dark;
    final noTrx = trx['nomor_transaksi']?.toString() ?? '-';
    final namaTagihan = _cleanText((trx['nama_tagihan'] ?? 'Pembayaran SPP').toString());
    final jenis = (trx['jenis_pembayaran'] ?? 'SPP').toString();
    final periode = (trx['periode_bulan'] ?? '-').toString();
    final nominal = _formatRupiah(trx['nominal_bayar'] ?? 0);
    final tglBayar = trx['tanggal_bayar']?.toString() ?? '-';
    final metode = (trx['metode_pembayaran'] ?? 'Transfer Bank').toString();
    final channel = (trx['channel'] ?? 'Portal Keuangan SMK').toString();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        backgroundColor: isDark ? const Color(0xFF1E293B) : Colors.white,
        contentPadding: const EdgeInsets.all(20),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF10B981).withValues(alpha: 0.15),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.receipt_long_rounded, color: Color(0xFF10B981), size: 36),
            ),
            const SizedBox(height: 12),
            Text(
              'BUKTI PEMBAYARAN RESMI',
              style: GoogleFonts.outfit(
                fontSize: 14,
                fontWeight: FontWeight.bold,
                letterSpacing: 0.8,
                color: const Color(0xFF10B981),
              ),
            ),
            Text(
              'SMK Muthia Harapan Cicalengka',
              style: TextStyle(fontSize: 11, color: isDark ? Colors.white60 : Colors.grey.shade600),
            ),
            const SizedBox(height: 16),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.grey.withValues(alpha: 0.2)),
              ),
              child: Column(
                children: [
                  _buildSlipRow('No. Transaksi', noTrx, isMono: true),
                  _buildSlipRow('Uraian', namaTagihan),
                  _buildSlipRow('Kategori / Periode', '$jenis ($periode)'),
                  _buildSlipRow('Metode', metode),
                  _buildSlipRow('Channel', channel),
                  _buildSlipRow('Waktu Bayar', tglBayar),
                  const Divider(height: 16),
                  _buildSlipRow('Nominal Lunas', nominal, isHighlight: true),
                ],
              ),
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(ctx),
                    child: const Text('Tutup'),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: ElevatedButton.icon(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF0284C7),
                      foregroundColor: Colors.white,
                      elevation: 0,
                    ),
                    icon: const Icon(Icons.copy_rounded, size: 14),
                    label: const Text('Salin No Trx'),
                    onPressed: () {
                      _copyToClipboard(noTrx, 'Nomor Transaksi');
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSlipRow(String label, String value, {bool isMono = false, bool isHighlight = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
          const SizedBox(width: 8),
          Flexible(
            child: Text(
              value,
              textAlign: TextAlign.end,
              style: isHighlight
                  ? GoogleFonts.outfit(fontSize: 14, fontWeight: FontWeight.bold, color: const Color(0xFF10B981))
                  : isMono
                      ? GoogleFonts.sourceCodePro(fontSize: 11, fontWeight: FontWeight.bold)
                      : GoogleFonts.outfit(fontSize: 11.5, fontWeight: FontWeight.w600),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      backgroundColor: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F172A),
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        title: Text(
          'Portal Pembayaran & SPP',
          style: GoogleFonts.outfit(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Colors.white,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Segarkan Data',
            onPressed: _fetchPembayaranData,
          ),
          IconButton(
            icon: const Icon(Icons.account_balance_rounded),
            tooltip: 'Info Rekening',
            onPressed: _showRekeningModal,
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: Container(
            color: const Color(0xFF0F172A),
            child: TabBar(
              controller: _tabController,
              indicatorColor: const Color(0xFF38BDF8),
              indicatorWeight: 3.5,
              labelColor: Colors.white,
              unselectedLabelColor: Colors.white60,
              labelStyle: GoogleFonts.outfit(fontWeight: FontWeight.bold, fontSize: 13),
              tabs: [
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.hourglass_bottom_rounded, size: 16),
                      const SizedBox(width: 6),
                      Text('Tagihan (${_unpaidBills.length})'),
                    ],
                  ),
                ),
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.check_circle_outline_rounded, size: 16),
                      const SizedBox(width: 6),
                      Text('Riwayat (${_riwayat.length})'),
                    ],
                  ),
                ),
                Tab(
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(Icons.receipt_rounded, size: 16),
                      const SizedBox(width: 6),
                      Text('Semua (${_allBills.length})'),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
      body: _isLoading
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(),
                  SizedBox(height: 16),
                  Text('Memuat data administrasi pembayaran...'),
                ],
              ),
            )
          : _errorMessage.isNotEmpty
              ? _buildErrorState(isDark)
              : RefreshIndicator(
                  onRefresh: _fetchPembayaranData,
                  child: Column(
                    children: [
                      _buildHeroCard(isDark),
                      _buildKpiMetrics(isDark),
                      _buildSearchField(isDark),
                      Expanded(
                        child: TabBarView(
                          controller: _tabController,
                          children: [
                            _buildUnpaidBillsTab(isDark),
                            _buildRiwayatTab(isDark),
                            _buildAllBillsTab(isDark),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
    );
  }

  Widget _buildHeroCard(bool isDark) {
    final bool isBebas = _isTrue(_summary['is_bebas_keuangan']);
    final String statusLabel = _summary['status_label']?.toString() ?? (isBebas ? 'Bebas Keuangan (Lunas)' : 'Terdapat Tunggakan Aktif');
    final String namaSiswa = _siswaInfo['nama_lengkap']?.toString() ?? 'Siswa';
    final String nis = _siswaInfo['nis']?.toString() ?? '-';
    final String nisn = _siswaInfo['nisn']?.toString() ?? '-';
    final String kelas = _siswaInfo['nama_kelas']?.toString() ?? 'Rombel';
    final String jurusan = _siswaInfo['nama_jurusan']?.toString() ?? 'Kejuruan';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      decoration: const BoxDecoration(
        gradient: LinearGradient(
          colors: [
            Color(0xFF0F172A),
            Color(0xFF1E293B),
            Color(0xFF0369A1),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
                decoration: BoxDecoration(
                  color: (isBebas ? const Color(0xFF10B981) : const Color(0xFFF59E0B)).withValues(alpha: 0.25),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(
                    color: (isBebas ? const Color(0xFF10B981) : const Color(0xFFF59E0B)).withValues(alpha: 0.5),
                  ),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isBebas ? Icons.verified_user_rounded : Icons.pending_actions_rounded,
                      size: 13,
                      color: isBebas ? const Color(0xFFA7F3D0) : const Color(0xFFFDE68A),
                    ),
                    const SizedBox(width: 5),
                    Text(
                      statusLabel,
                      style: GoogleFonts.outfit(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: isBebas ? const Color(0xFFA7F3D0) : const Color(0xFFFDE68A),
                      ),
                    ),
                  ],
                ),
              ),
              InkWell(
                onTap: _showRekeningModal,
                borderRadius: BorderRadius.circular(16),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3.5),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.15),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.white30),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.account_balance_wallet_rounded, size: 12, color: Colors.amber),
                      const SizedBox(width: 4),
                      Text(
                        'Rekening Sekolah',
                        style: GoogleFonts.outfit(fontSize: 10.5, color: Colors.white, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            namaSiswa,
            style: GoogleFonts.outfit(
              fontSize: 16,
              fontWeight: FontWeight.bold,
              color: Colors.white,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          Text(
            'NIS: $nis • NISN: $nisn • $kelas • $jurusan',
            style: GoogleFonts.outfit(
              fontSize: 11,
              color: Colors.white70,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildKpiMetrics(bool isDark) {
    final totalTagihan = _formatRupiah(_summary['total_tagihan']);
    final totalTerbayar = _formatRupiah(_summary['total_terbayar']);
    final totalTunggakan = _formatRupiah(_summary['total_tunggakan']);
    final persenLunas = double.tryParse((_summary['persen_lunas'] ?? 0).toString()) ?? 0.0;
    final tunggakanVal = double.tryParse((_summary['total_tunggakan'] ?? 0).toString()) ?? 0.0;

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 4),
      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      child: Row(
        children: [
          // KPI 1: Tagihan
          Expanded(
            child: _buildMetricTile(
              label: 'Total Tagihan',
              amount: totalTagihan,
              sub: 'T.A ${(_summary['tahun_ajaran'] ?? '2025/2026')}',
              accentColor: const Color(0xFF0284C7),
              icon: Icons.receipt_long_rounded,
              isDark: isDark,
            ),
          ),
          const SizedBox(width: 8),
          // KPI 2: Terbayar
          Expanded(
            child: _buildMetricTile(
              label: 'Sudah Dibayar',
              amount: totalTerbayar,
              sub: '${persenLunas.toStringAsFixed(0)}% Lunas',
              accentColor: const Color(0xFF10B981),
              icon: Icons.check_circle_rounded,
              isDark: isDark,
            ),
          ),
          const SizedBox(width: 8),
          // KPI 3: Sisa Tunggakan
          Expanded(
            child: _buildMetricTile(
              label: 'Sisa Tagihan',
              amount: totalTunggakan,
              sub: tunggakanVal > 0 ? 'Tunggakan' : 'Lunas',
              accentColor: tunggakanVal > 0 ? const Color(0xFFEF4444) : Colors.grey,
              icon: Icons.hourglass_bottom_rounded,
              isDark: isDark,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildMetricTile({
    required String label,
    required String amount,
    required String sub,
    required Color accentColor,
    required IconData icon,
    required bool isDark,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.15 : 0.03),
            blurRadius: 4,
            offset: const Offset(0, 1.5),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Flexible(
                child: Text(
                  label,
                  style: TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w600,
                    color: isDark ? Colors.white54 : Colors.grey.shade600,
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Icon(icon, size: 13, color: accentColor),
            ],
          ),
          const SizedBox(height: 3),
          Text(
            amount,
            style: GoogleFonts.outfit(
              fontSize: 12.5,
              fontWeight: FontWeight.bold,
              color: accentColor,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          Text(
            sub,
            style: TextStyle(
              fontSize: 9,
              color: isDark ? Colors.white38 : Colors.grey.shade500,
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildSearchField(bool isDark) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 6, 16, 4),
      color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
      child: TextField(
        controller: _searchController,
        style: GoogleFonts.outfit(fontSize: 13),
        decoration: InputDecoration(
          isDense: true,
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          hintText: 'Cari tagihan SPP, jenis, kode, atau periode...',
          hintStyle: TextStyle(
            color: isDark ? Colors.white38 : Colors.grey.shade500,
            fontSize: 12.5,
          ),
          prefixIcon: const Icon(Icons.search_rounded, size: 18),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 16),
                  onPressed: () => _searchController.clear(),
                )
              : null,
          filled: true,
          fillColor: isDark ? const Color(0xFF1E293B) : Colors.white,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: BorderSide(color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.5),
          ),
        ),
      ),
    );
  }

  Widget _buildUnpaidBillsTab(bool isDark) {
    final query = _searchController.text.toLowerCase().trim();
    final filtered = _unpaidBills.where((b) {
      if (query.isEmpty) return true;
      final judul = (b['judul'] ?? '').toString().toLowerCase();
      final kode = (b['kode_tagihan'] ?? '').toString().toLowerCase();
      final jenis = (b['jenis_pembayaran'] ?? '').toString().toLowerCase();
      final periode = (b['periode_bulan'] ?? '').toString().toLowerCase();
      return judul.contains(query) || kode.contains(query) || jenis.contains(query) || periode.contains(query);
    }).toList();

    if (filtered.isEmpty) {
      return Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.check_circle_rounded, size: 54, color: Color(0xFF10B981)),
              ),
              const SizedBox(height: 16),
              Text(
                _unpaidBills.isEmpty
                    ? 'Alhamdulillah, Tidak Ada Tunggakan!'
                    : 'Tidak Ditemukan',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                _unpaidBills.isEmpty
                    ? 'Seluruh administrasi pembayaran SPP dan biaya pendidikan Anda telah tercatat lunas di sistem sekolah.'
                    : 'Tidak ada tagihan yang cocok dengan kata pencarian "$query".',
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 13,
                  color: isDark ? Colors.white60 : Colors.grey.shade600,
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 18),
              if (_unpaidBills.isEmpty)
                ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF0284C7),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                    elevation: 0,
                  ),
                  icon: const Icon(Icons.history_rounded, size: 18),
                  label: const Text('Lihat Riwayat Pembayaran'),
                  onPressed: () {
                    _tabController.animateTo(1);
                  },
                ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: filtered.length,
      itemBuilder: (ctx, idx) {
        final bill = filtered[idx];
        return _buildUnpaidBillCard(bill, isDark);
      },
    );
  }

  Widget _buildUnpaidBillCard(dynamic bill, bool isDark) {
    final String judul = _cleanText((bill['judul'] ?? 'Tagihan SPP').toString());
    final String kode = (bill['kode_tagihan'] ?? '-').toString();
    final String jenis = (bill['jenis_pembayaran'] ?? 'SPP').toString();
    final String keterangan = _cleanText((bill['keterangan'] ?? 'Iuran Sekolah').toString());
    final String nominalStr = _formatRupiah(bill['nominal'] ?? 0);
    final String sisaStr = _formatRupiah(bill['sisa_tagihan'] ?? 0);
    final String tempo = bill['tanggal_jatuh_tempo']?.toString() ?? '';
    final bool isDue = _isTrue(bill['is_due']);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDue
              ? const Color(0xFFEF4444).withValues(alpha: 0.5)
              : (isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0)),
          width: isDue ? 1.5 : 1,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.15 : 0.03),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: IntrinsicHeight(
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              // Left status stripe
              Container(
                width: 4.5,
                color: isDue ? const Color(0xFFEF4444) : const Color(0xFFF59E0B),
              ),
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Header Row: Jenis + Kode + Status Badge
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Expanded(
                            child: Wrap(
                              spacing: 6,
                              runSpacing: 4,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFF0284C7).withValues(alpha: 0.15),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: Text(
                                    jenis,
                                    style: GoogleFonts.outfit(
                                      fontSize: 10.5,
                                      fontWeight: FontWeight.bold,
                                      color: const Color(0xFF0284C7),
                                    ),
                                  ),
                                ),
                                Text(
                                  'Kode: $kode',
                                  style: GoogleFonts.sourceCodePro(
                                    fontSize: 10.5,
                                    color: isDark ? Colors.white54 : Colors.grey.shade600,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: (isDue ? const Color(0xFFEF4444) : const Color(0xFFF59E0B)).withValues(alpha: 0.15),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  isDue ? Icons.error_outline_rounded : Icons.schedule_rounded,
                                  size: 11,
                                  color: isDue ? const Color(0xFFEF4444) : const Color(0xFFD97706),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  isDue ? 'Lewat Tempo' : 'Belum Lunas',
                                  style: GoogleFonts.outfit(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: isDue ? const Color(0xFFEF4444) : const Color(0xFFD97706),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: 6),
                      Text(
                        judul,
                        style: GoogleFonts.outfit(
                          fontSize: 14.5,
                          fontWeight: FontWeight.bold,
                          color: isDark ? Colors.white : const Color(0xFF0F172A),
                        ),
                      ),
                      if (keterangan.isNotEmpty)
                        Text(
                          keterangan,
                          style: TextStyle(
                            fontSize: 11.5,
                            color: isDark ? Colors.white60 : Colors.grey.shade600,
                          ),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                        ),

                      const SizedBox(height: 10),

                      // Nominal Box
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                        decoration: BoxDecoration(
                          color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(
                            color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text('Nominal Tagihan', style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600)),
                                Text(nominalStr, style: GoogleFonts.outfit(fontSize: 13, fontWeight: FontWeight.bold)),
                              ],
                            ),
                            Column(
                              crossAxisAlignment: CrossAxisAlignment.end,
                              children: [
                                Text('Sisa Tagihan', style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600)),
                                Text(
                                  sisaStr,
                                  style: GoogleFonts.outfit(
                                    fontSize: 13,
                                    fontWeight: FontWeight.bold,
                                    color: const Color(0xFFEF4444),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 10),

                      // Bottom Action & Due Date
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.event_rounded, size: 13, color: Colors.grey),
                              const SizedBox(width: 4),
                              Text(
                                tempo.isNotEmpty ? 'Tempo: $tempo' : 'Tanpa batas tempo',
                                style: TextStyle(
                                  fontSize: 10.5,
                                  color: isDue ? Colors.redAccent : (isDark ? Colors.white60 : Colors.grey.shade600),
                                  fontWeight: isDue ? FontWeight.bold : FontWeight.normal,
                                ),
                              ),
                            ],
                          ),
                          ElevatedButton.icon(
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF0284C7),
                              foregroundColor: Colors.white,
                              elevation: 0,
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                            ),
                            icon: const Icon(Icons.account_balance_rounded, size: 13),
                            label: const Text('Cara Bayar', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                            onPressed: _showRekeningModal,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildRiwayatTab(bool isDark) {
    final query = _searchController.text.toLowerCase().trim();
    final filtered = _riwayat.where((r) {
      if (query.isEmpty) return true;
      final noTrx = (r['nomor_transaksi'] ?? '').toString().toLowerCase();
      final tagihan = (r['nama_tagihan'] ?? '').toString().toLowerCase();
      final metode = (r['metode_pembayaran'] ?? '').toString().toLowerCase();
      return noTrx.contains(query) || tagihan.contains(query) || metode.contains(query);
    }).toList();

    if (filtered.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.receipt_long_outlined, size: 54, color: Colors.grey),
              const SizedBox(height: 16),
              Text(
                'Belum Ada Riwayat Pembayaran',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                'Transaksi pembayaran yang telah Anda lunasi akan otomatis tercatat di sini.',
                textAlign: TextAlign.center,
                style: TextStyle(fontSize: 13, color: isDark ? Colors.white60 : Colors.grey.shade600),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: filtered.length,
      itemBuilder: (ctx, idx) {
        final trx = filtered[idx];
        return _buildRiwayatCard(trx, isDark);
      },
    );
  }

  Widget _buildRiwayatCard(dynamic trx, bool isDark) {
    final String noTrx = (trx['nomor_transaksi'] ?? '-').toString();
    final String namaTagihan = _cleanText((trx['nama_tagihan'] ?? 'Pembayaran SPP').toString());
    final String jenis = (trx['jenis_pembayaran'] ?? 'SPP').toString();
    final String nominalStr = _formatRupiah(trx['nominal_bayar'] ?? 0);
    final String tglBayar = trx['tanggal_bayar']?.toString() ?? '-';
    final String metode = (trx['metode_pembayaran'] ?? 'Transfer Bank').toString();

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isDark ? const Color(0xFF1E293B) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: isDark ? 0.15 : 0.03),
            blurRadius: 6,
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
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: isDark ? const Color(0xFF0F172A) : const Color(0xFFF1F5F9),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  noTrx,
                  style: GoogleFonts.sourceCodePro(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: isDark ? Colors.white70 : const Color(0xFF334155),
                  ),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(Icons.check_circle_rounded, size: 11, color: Color(0xFF10B981)),
                    SizedBox(width: 4),
                    Text(
                      'Lunas',
                      style: TextStyle(
                        fontSize: 10.5,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF10B981),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(
            namaTagihan,
            style: GoogleFonts.outfit(
              fontSize: 14.5,
              fontWeight: FontWeight.bold,
              color: isDark ? Colors.white : const Color(0xFF0F172A),
            ),
          ),
          Text(
            '$jenis • $metode',
            style: TextStyle(
              fontSize: 11.5,
              color: isDark ? Colors.white60 : Colors.grey.shade600,
            ),
          ),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Waktu Bayar', style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600)),
                  Text(
                    tglBayar,
                    style: TextStyle(fontSize: 11, color: isDark ? Colors.white70 : Colors.grey.shade800),
                  ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text('Nominal Terbayar', style: TextStyle(fontSize: 10, color: isDark ? Colors.white54 : Colors.grey.shade600)),
                  Text(
                    nominalStr,
                    style: GoogleFonts.outfit(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: const Color(0xFF10B981),
                    ),
                  ),
                ],
              ),
            ],
          ),
          const SizedBox(height: 8),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton.icon(
              style: TextButton.styleFrom(
                foregroundColor: const Color(0xFF0284C7),
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                minimumSize: Size.zero,
                tapTargetSize: MaterialTapTargetSize.shrinkWrap,
              ),
              icon: const Icon(Icons.receipt_rounded, size: 14),
              label: const Text('Lihat Bukti Bayar', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.bold)),
              onPressed: () => _showSlipDialog(trx),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAllBillsTab(bool isDark) {
    final query = _searchController.text.toLowerCase().trim();
    final filtered = _allBills.where((b) {
      if (query.isEmpty) return true;
      final judul = (b['judul'] ?? '').toString().toLowerCase();
      final kode = (b['kode_tagihan'] ?? '').toString().toLowerCase();
      final status = (b['status'] ?? '').toString().toLowerCase();
      return judul.contains(query) || kode.contains(query) || status.contains(query);
    }).toList();

    if (filtered.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.search_off_rounded, size: 54, color: Colors.grey),
              const SizedBox(height: 16),
              Text(
                'Tidak Ditemukan',
                style: GoogleFonts.outfit(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: isDark ? Colors.white : const Color(0xFF0F172A),
                ),
              ),
            ],
          ),
        ),
      );
    }

    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 8, 16, 24),
      itemCount: filtered.length,
      itemBuilder: (ctx, idx) {
        final b = filtered[idx];
        final String judul = _cleanText((b['judul'] ?? 'Tagihan').toString());
        final String kode = (b['kode_tagihan'] ?? '-').toString();
        final String status = (b['status'] ?? 'belum_lunas').toString();
        final String nominalStr = _formatRupiah(b['nominal'] ?? 0);
        final String sisaStr = _formatRupiah(b['sisa_tagihan'] ?? 0);
        final bool isLunas = status == 'lunas';

        return Container(
          margin: const EdgeInsets.only(bottom: 10),
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: isDark ? const Color(0xFF1E293B) : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: isDark ? const Color(0xFF334155) : const Color(0xFFE2E8F0),
            ),
          ),
          child: Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text(
                          kode,
                          style: GoogleFonts.sourceCodePro(
                            fontSize: 10.5,
                            fontWeight: FontWeight.bold,
                            color: Colors.grey,
                          ),
                        ),
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1.5),
                          decoration: BoxDecoration(
                            color: (isLunas ? const Color(0xFF10B981) : const Color(0xFFEF4444)).withValues(alpha: 0.15),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Text(
                            isLunas ? 'Lunas' : 'Belum Lunas',
                            style: TextStyle(
                              fontSize: 9.5,
                              fontWeight: FontWeight.bold,
                              color: isLunas ? const Color(0xFF10B981) : const Color(0xFFEF4444),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 3),
                    Text(
                      judul,
                      style: GoogleFonts.outfit(
                        fontSize: 13.5,
                        fontWeight: FontWeight.bold,
                        color: isDark ? Colors.white : const Color(0xFF0F172A),
                      ),
                    ),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  Text(
                    nominalStr,
                    style: GoogleFonts.outfit(fontSize: 12.5, fontWeight: FontWeight.bold),
                  ),
                  if (!isLunas)
                    Text(
                      'Sisa: $sisaStr',
                      style: const TextStyle(fontSize: 10, color: Colors.redAccent, fontWeight: FontWeight.bold),
                    ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildErrorState(bool isDark) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_rounded, size: 54, color: Colors.redAccent),
            const SizedBox(height: 16),
            Text(
              'Gagal Memuat Data Pembayaran',
              style: GoogleFonts.outfit(
                fontSize: 16,
                fontWeight: FontWeight.bold,
                color: isDark ? Colors.white : const Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 8),
            Text(
              _errorMessage,
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: isDark ? Colors.white60 : Colors.grey.shade600),
            ),
            const SizedBox(height: 18),
            ElevatedButton.icon(
              style: ElevatedButton.styleFrom(
                backgroundColor: const Color(0xFF0284C7),
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
              ),
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Lagi'),
              onPressed: _fetchPembayaranData,
            ),
          ],
        ),
      ),
    );
  }
}
