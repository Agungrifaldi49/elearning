<?php
/**
 * Absensi Model
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class AbsensiModel extends BaseModel {

    public function recordAttendance($jadwal_id, $siswa_id, $tanggal, $status, $keterangan = '') {
        $qrCode = "ATT_" . $jadwal_id . "_" . $siswa_id . "_" . date('Ymd');
        $stmtExist = $this->db->prepare("SELECT id FROM absensi WHERE jadwal_id = ? AND siswa_id = ? AND tanggal = ?");
        $stmtExist->execute([$jadwal_id, $siswa_id, $tanggal]);
        $exist = $stmtExist->fetch();

        if ($exist) {
            $stmt = $this->db->prepare("UPDATE absensi SET status = ?, keterangan = ? WHERE id = ?");
            return $stmt->execute([$status, $keterangan, $exist['id']]);
        } else {
            $stmt = $this->db->prepare("INSERT INTO absensi (jadwal_id, siswa_id, tanggal, status, qr_code, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
            return $stmt->execute([$jadwal_id, $siswa_id, $tanggal, $status, $qrCode, $keterangan]);
        }
    }

    public function getRecap($jadwal_id, $tanggal) {
        $kelasId = null;
        $mapelId = null;
        $guruId = null;

        $stmtJ = $this->db->prepare("SELECT kelas_id, mapel_id, guru_id FROM jadwal WHERE id = ?");
        $stmtJ->execute([$jadwal_id]);
        $jData = $stmtJ->fetch();

        if ($jData) {
            $kelasId = $jData['kelas_id'];
            $mapelId = $jData['mapel_id'];
            $guruId = $jData['guru_id'];
        } else {
            // Check in mapel_enrollment_keys
            $stmtK = $this->db->prepare("SELECT mapel_id, guru_id, kelas_id FROM mapel_enrollment_keys WHERE id = ?");
            $stmtK->execute([$jadwal_id]);
            $kData = $stmtK->fetch();
            if ($kData) {
                $kelasId = $kData['kelas_id'];
                $mapelId = $kData['mapel_id'];
                $guruId = $kData['guru_id'];
            }
        }

        if ($kelasId) {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, a.status, a.keterangan, a.created_at
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.jadwal_id = ? AND a.tanggal = ?
                WHERE s.kelas_id = ?
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$jadwal_id, $tanggal, $kelasId]);
            return $stmt->fetchAll();
        } elseif ($mapelId && $guruId) {
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, a.status, a.keterangan, a.created_at
                FROM siswa s
                JOIN siswa_mapel_enrollment sme ON s.id = sme.siswa_id AND sme.mapel_id = ? AND sme.guru_id = ?
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.jadwal_id = ? AND a.tanggal = ?
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$mapelId, $guruId, $jadwal_id, $tanggal]);
            return $stmt->fetchAll();
        } else {
            // Fallback to active class students if no schedule linked
            $stmt = $this->db->prepare("
                SELECT s.id as siswa_id, s.nama_lengkap, s.nis, s.nisn, a.status, a.keterangan, a.created_at
                FROM siswa s
                LEFT JOIN absensi a ON s.id = a.siswa_id AND a.jadwal_id = ? AND a.tanggal = ?
                WHERE s.kelas_id IS NOT NULL
                ORDER BY s.nama_lengkap ASC
            ");
            $stmt->execute([$jadwal_id, $tanggal]);
            return $stmt->fetchAll();
        }
    }
}
