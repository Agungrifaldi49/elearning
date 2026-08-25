import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import '../../providers/auth_provider.dart';

class KartuDigitalScreen extends StatelessWidget {
  const KartuDigitalScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = Provider.of<AuthProvider>(context);
    final user = auth.currentUser;
    final details = user?.details;
    final isSiswa = user?.isSiswa ?? true;
    final avatarUrl = user?.fullAvatarUrl ?? '';

    final nama = user?.fullName.isNotEmpty == true
        ? user!.fullName
        : (details?['nama_lengkap'] ?? 'Pengguna');
    final nomorId = isSiswa ? user?.nis ?? 'NIS-001' : user?.nip ?? 'NIP-001';
    final subTitle = isSiswa
        ? '${user?.namaKelas} • ${user?.namaJurusan}'
        : (details?['email'] ?? user?.email ?? 'Guru E-Learning');

    // Official Web E-Learning Payload Format: SMKMH-SISWA-{nis} or SMKMH-GURU-{nip}
    final qrPayload = isSiswa ? 'SMKMH-SISWA-$nomorId' : 'SMKMH-GURU-$nomorId';
    final qrImageUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${Uri.encodeComponent(qrPayload)}&color=0f172a&bgcolor=ffffff';

    return Scaffold(
      appBar: AppBar(
        title: Text(isSiswa ? 'Kartu Pelajar Digital' : 'Kartu Guru Digital'),
        backgroundColor: isSiswa ? Colors.indigo : Colors.teal,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24),
        child: Column(
          children: [
            // Virtual ID Card
            Card(
              elevation: 8,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(20),
              ),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(24),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(20),
                  gradient: LinearGradient(
                    colors: isSiswa
                        ? [Colors.indigo.shade800, Colors.blue.shade600]
                        : [Colors.teal.shade800, Colors.teal.shade500],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(4),
                              decoration: const BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                              ),
                              child: Image.asset(
                                'assets/logo/mhc_logo.png',
                                width: 24,
                                height: 24,
                                fit: BoxFit.contain,
                              ),
                            ),
                            const SizedBox(width: 8),
                            const Text(
                              'SMK MUTHIA HARAPAN',
                              style: TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 14,
                                letterSpacing: 1,
                              ),
                            ),
                          ],
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            isSiswa ? 'KARTU PELAJAR' : 'KARTU GURU',
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),

                    // Avatar Image or Fallback Circle
                    CircleAvatar(
                      radius: 44,
                      backgroundColor: Colors.white,
                      child: CircleAvatar(
                        radius: 41,
                        backgroundColor: Colors.grey.shade200,
                        backgroundImage: avatarUrl.isNotEmpty ? NetworkImage(avatarUrl) : null,
                        child: avatarUrl.isEmpty
                            ? Icon(
                                isSiswa ? Icons.person_rounded : Icons.person_pin_rounded,
                                size: 50,
                                color: isSiswa ? Colors.indigo : Colors.teal,
                              )
                            : null,
                      ),
                    ),
                    const SizedBox(height: 16),

                    Text(
                      nama,
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subTitle,
                      textAlign: TextAlign.center,
                      style: TextStyle(
                        color: Colors.white.withValues(alpha: 0.9),
                        fontSize: 13,
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text(
                            isSiswa ? 'NIS: $nomorId' : 'NIP: $nomorId',
                            style: TextStyle(
                              color: isSiswa ? Colors.indigo.shade900 : Colors.teal.shade900,
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                            ),
                          ),
                        ),
                        if (isSiswa && user?.nisn != '-') ...[
                          const SizedBox(width: 8),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 5),
                            decoration: BoxDecoration(
                              color: Colors.white.withValues(alpha: 0.2),
                              borderRadius: BorderRadius.circular(20),
                            ),
                            child: Text(
                              'NISN: ${user?.nisn}',
                              style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 12,
                              ),
                            ),
                          ),
                        ],
                      ],
                    ),
                    const SizedBox(height: 24),

                    // REAL WEB-COMPATIBLE QR CODE IMAGE
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 6)],
                      ),
                      child: Column(
                        children: [
                          Image.network(
                            qrImageUrl,
                            width: 140,
                            height: 140,
                            loadingBuilder: (context, child, loadingProgress) {
                              if (loadingProgress == null) return child;
                              return const SizedBox(
                                width: 140,
                                height: 140,
                                child: Center(child: CircularProgressIndicator(strokeWidth: 2)),
                              );
                            },
                            errorBuilder: (context, error, stackTrace) {
                              return Column(
                                children: [
                                  Icon(Icons.qr_code_2_rounded, size: 100, color: Colors.grey.shade900),
                                  Text(qrPayload, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                                ],
                              );
                            },
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Scan QR untuk Presensi Sekolah',
                            style: TextStyle(
                              fontSize: 11,
                              color: Colors.grey.shade700,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Text(
                            qrPayload,
                            style: TextStyle(
                              fontSize: 10,
                              color: Colors.indigo.shade700,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
