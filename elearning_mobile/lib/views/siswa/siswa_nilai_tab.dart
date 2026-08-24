import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';
import '../../providers/siswa_provider.dart';
import '../../theme/app_theme.dart';

class SiswaNilaiTab extends StatefulWidget {
  const SiswaNilaiTab({super.key});

  @override
  State<SiswaNilaiTab> createState() => _SiswaNilaiTabState();
}

class _SiswaNilaiTabState extends State<SiswaNilaiTab> {
  @override
  void initState() {
    super.initState();
    _loadNilai();
  }

  void _loadNilai() {
    final user = Provider.of<AuthProvider>(context, listen: false).currentUser;
    if (user != null) {
      Provider.of<SiswaProvider>(context, listen: false).fetchNilai(user.id);
    }
  }

  @override
  Widget build(BuildContext context) {
    final siswaProvider = Provider.of<SiswaProvider>(context);
    final nilaiList = siswaProvider.nilaiList;

    double sumNilai = 0;
    for (var n in nilaiList) {
      sumNilai += n.nilaiAkhir;
    }
    final avg = nilaiList.isNotEmpty ? (sumNilai / nilaiList.length) : 0.0;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Rekap Nilai & Raport Siswa'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Average Card
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: AppTheme.primaryGradient,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Rata-Rata Nilai Akhir', style: TextStyle(color: Colors.white70, fontSize: 13)),
                      const SizedBox(height: 4),
                      Text(
                        avg.toStringAsFixed(1),
                        style: const TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold),
                      ),
                    ],
                  ),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(Icons.workspace_premium, color: Colors.white, size: 32),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 20),
            const Text('📊 Nilai Per Mata Pelajaran', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),

            Expanded(
              child: siswaProvider.isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : nilaiList.isEmpty
                      ? const Center(child: Text('Belum ada rekap nilai tersedia.'))
                      : ListView.builder(
                          itemCount: nilaiList.length,
                          itemBuilder: (context, index) {
                            final n = nilaiList[index];
                            return Card(
                              margin: const EdgeInsets.only(bottom: 12),
                              child: Padding(
                                padding: const EdgeInsets.all(16),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                      children: [
                                        Text(
                                          n.namaMapel,
                                          style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: AppTheme.secondaryColor.withOpacity(0.15),
                                            borderRadius: BorderRadius.circular(8),
                                          ),
                                          child: Text(
                                            "Predikat ${n.predikat}",
                                            style: const TextStyle(
                                              color: AppTheme.secondaryColor,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                    const Divider(height: 20),
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                                      children: [
                                        _buildSubScore('Tugas', n.nilaiTugas),
                                        _buildSubScore('Quiz', n.nilaiQuiz),
                                        _buildSubScore('UTS', n.nilaiUts),
                                        _buildSubScore('UAS', n.nilaiUas),
                                        _buildSubScore('Akhir', n.nilaiAkhir, isBold: true),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSubScore(String label, double val, {bool isBold = false}) {
    return Column(
      children: [
        Text(label, style: const TextStyle(fontSize: 11, color: Colors.grey)),
        const SizedBox(height: 2),
        Text(
          val.toStringAsFixed(0),
          style: TextStyle(
            fontSize: isBold ? 16 : 14,
            fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
            color: isBold ? AppTheme.primaryColor : null,
          ),
        ),
      ],
    );
  }
}
