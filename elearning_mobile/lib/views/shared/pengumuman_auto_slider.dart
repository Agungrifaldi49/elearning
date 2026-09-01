import 'dart:async';
import 'package:flutter/material.dart';
import '../../theme/app_theme.dart';

class PengumumanAutoSlider extends StatefulWidget {
  final List<dynamic> pengumumanList;

  const PengumumanAutoSlider({super.key, required this.pengumumanList});

  @override
  State<PengumumanAutoSlider> createState() => _PengumumanAutoSliderState();
}

class _PengumumanAutoSliderState extends State<PengumumanAutoSlider> {
  late PageController _pageController;
  Timer? _timer;
  int _currentPage = 0;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: 0);
    _startAutoSlide();
  }

  @override
  void didUpdateWidget(covariant PengumumanAutoSlider oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.pengumumanList.length != widget.pengumumanList.length) {
      _startAutoSlide();
    }
  }

  void _startAutoSlide() {
    _timer?.cancel();
    if (widget.pengumumanList.length > 1) {
      _timer = Timer.periodic(const Duration(seconds: 4), (timer) {
        if (_pageController.hasClients) {
          _currentPage = (_currentPage + 1) % widget.pengumumanList.length;
          _pageController.animateToPage(
            _currentPage,
            duration: const Duration(milliseconds: 600),
            curve: Curves.easeInOut,
          );
        }
      });
    }
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  String? _getBannerUrl(Map p) {
    final raw = p['banner_url'] ?? p['banner'];
    if (raw == null || raw.toString().trim().isEmpty) return null;
    final str = raw.toString().trim();
    if (str.startsWith('http://') || str.startsWith('https://')) {
      return str;
    }
    return 'http://10.0.2.2/elearning/${str.replaceAll(RegExp(r'^\/'), '')}';
  }

  void _showDetailModal(BuildContext context, Map p, bool isDark) {
    final bannerUrl = _getBannerUrl(p);
    final judul = p['judul']?.toString() ?? 'Pengumuman';
    final isi = p['isi_pengumuman'] ?? p['isi'] ?? p['konten'] ?? '';
    final tanggal = p['created_at']?.toString() ?? '';
    final isPopup = (p['is_popup'] == 1 || p['is_popup'] == '1' || p['is_popup'] == true);

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return Container(
          constraints: BoxConstraints(
            maxHeight: MediaQuery.of(context).size.height * 0.85,
          ),
          decoration: BoxDecoration(
            color: isDark ? AppTheme.darkSurface : Colors.white,
            borderRadius: const BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                margin: const EdgeInsets.only(top: 12, bottom: 8),
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: Colors.grey.shade400,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      if (bannerUrl != null) ...[
                        ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: Image.network(
                            bannerUrl,
                            width: double.infinity,
                            fit: BoxFit.cover,
                            errorBuilder: (context, error, stackTrace) {
                              return const SizedBox.shrink();
                            },
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],
                      Row(
                        children: [
                          if (isPopup)
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              margin: const EdgeInsets.only(right: 8),
                              decoration: BoxDecoration(
                                color: Colors.red.shade100,
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: const Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(Icons.warning_amber_rounded, size: 14, color: Colors.red),
                                  SizedBox(width: 4),
                                  Text(
                                    'PENTING',
                                    style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 10),
                                  ),
                                ],
                              ),
                            ),
                          Expanded(
                            child: Text(
                              tanggal.isNotEmpty ? "Diterbitkan: $tanggal" : "Informasi Resmi",
                              style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Text(
                        judul,
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                      ),
                      const Divider(height: 24),
                      Text(
                        isi,
                        style: const TextStyle(fontSize: 14, height: 1.5),
                      ),
                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final isDark = Theme.of(context).brightness == Brightness.dark;

    if (widget.pengumumanList.isEmpty) {
      return Card(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Center(
            child: Text(
              'Belum ada pengumuman terbaru.',
              style: TextStyle(color: isDark ? Colors.grey.shade400 : Colors.grey.shade600),
            ),
          ),
        ),
      );
    }

    return Column(
      children: [
        SizedBox(
          height: 210,
          child: PageView.builder(
            controller: _pageController,
            onPageChanged: (index) {
              setState(() {
                _currentPage = index;
              });
            },
            itemCount: widget.pengumumanList.length,
            itemBuilder: (context, index) {
              final p = widget.pengumumanList[index] as Map;
              final bannerUrl = _getBannerUrl(p);
              final judul = p['judul']?.toString() ?? 'Pengumuman';
              final isi = p['isi_pengumuman'] ?? p['isi'] ?? p['konten'] ?? '';
              final isPopup = (p['is_popup'] == 1 || p['is_popup'] == '1' || p['is_popup'] == true);

              return GestureDetector(
                onTap: () => _showDetailModal(context, p, isDark),
                child: Container(
                  margin: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
                  decoration: BoxDecoration(
                    color: isDark ? AppTheme.darkSurface : Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(
                      color: isPopup ? Colors.red.shade300 : (isDark ? Colors.grey.shade800 : Colors.grey.shade200),
                      width: isPopup ? 1.5 : 1.0,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: isPopup ? Colors.red.withValues(alpha: 0.1) : Colors.black.withValues(alpha: 0.05),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      ),
                    ],
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (bannerUrl != null)
                          SizedBox(
                            height: 105,
                            width: double.infinity,
                            child: Stack(
                              children: [
                                Image.network(
                                  bannerUrl,
                                  height: 105,
                                  width: double.infinity,
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) {
                                    return Container(
                                      color: Colors.blue.shade50,
                                      child: const Center(
                                        child: Icon(Icons.image_not_supported_rounded, color: Colors.blue),
                                      ),
                                    );
                                  },
                                ),
                                if (isPopup)
                                  Positioned(
                                    top: 8,
                                    right: 8,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: Colors.red,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: const Text(
                                        'PENTING',
                                        style: TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.bold),
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                        Expanded(
                          child: Padding(
                            padding: const EdgeInsets.all(12),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  children: [
                                    if (bannerUrl == null) ...[
                                      Icon(
                                        isPopup ? Icons.campaign_rounded : Icons.info_outline_rounded,
                                        color: isPopup ? Colors.red : AppTheme.primaryColor,
                                        size: 18,
                                      ),
                                      const SizedBox(width: 6),
                                    ],
                                    Expanded(
                                      child: Text(
                                        judul,
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                                      ),
                                    ),
                                    if (bannerUrl == null && isPopup)
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: Colors.red.shade100,
                                          borderRadius: BorderRadius.circular(8),
                                        ),
                                        child: const Text(
                                          'PENTING',
                                          style: TextStyle(color: Colors.red, fontSize: 9, fontWeight: FontWeight.bold),
                                        ),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 4),
                                Expanded(
                                  child: Text(
                                    isi,
                                    maxLines: bannerUrl != null ? 2 : 3,
                                    overflow: TextOverflow.ellipsis,
                                    style: TextStyle(
                                      fontSize: 12,
                                      color: isDark ? Colors.grey.shade300 : Colors.grey.shade700,
                                    ),
                                  ),
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
            },
          ),
        ),

        // Indicator Dots
        if (widget.pengumumanList.length > 1) ...[
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              widget.pengumumanList.length,
              (index) => AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                height: 6,
                width: _currentPage == index ? 20 : 6,
                decoration: BoxDecoration(
                  color: _currentPage == index
                      ? AppTheme.primaryColor
                      : (isDark ? Colors.grey.shade700 : Colors.grey.shade300),
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
            ),
          ),
        ],
      ],
    );
  }
}
