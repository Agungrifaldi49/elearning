<?php
/**
 * Communication Model (Forum, Chat AJAX, Pengumuman, Notifikasi)
 */
require_once ROOT_PATH . 'models/BaseModel.php';

class CommunicationModel extends BaseModel {

    public function __construct() {
        parent::__construct();
        $this->ensureChatColumnsExist();
        $this->ensureNotifikasiTable();
    }

    private function ensureChatColumnsExist() {
        try {
            $this->db->exec("ALTER TABLE chat ADD COLUMN IF NOT EXISTS is_edited TINYINT(1) DEFAULT 0");
            $this->db->exec("ALTER TABLE chat ADD COLUMN IF NOT EXISTS deleted_by_sender TINYINT(1) DEFAULT 0");
            $this->db->exec("ALTER TABLE chat ADD COLUMN IF NOT EXISTS deleted_by_receiver TINYINT(1) DEFAULT 0");
            $this->db->exec("ALTER TABLE chat ADD COLUMN IF NOT EXISTS is_deleted_everyone TINYINT(1) DEFAULT 0");
        } catch (Exception $e) {}
    }

    private function ensureNotifikasiTable() {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS notifikasi (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    title VARCHAR(255) NOT NULL,
                    message TEXT NOT NULL,
                    link VARCHAR(255) NULL,
                    is_read TINYINT(1) DEFAULT 0,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        } catch (Exception $e) {}
    }

    // --- PENGUMUMAN ---
    public function getPengumuman($role = 'all') {
        $stmt = $this->db->prepare("
            SELECT p.*, u.full_name as author
            FROM pengumuman p
            JOIN users u ON p.user_id = u.id
            WHERE p.target_role = 'all' OR p.target_role = ?
            ORDER BY p.id DESC
        ");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    public function getAllPengumuman() {
        return $this->db->query("
            SELECT p.*, u.full_name as author
            FROM pengumuman p
            LEFT JOIN users u ON p.user_id = u.id
            ORDER BY p.id DESC
        ")->fetchAll();
    }

    public function createPengumuman($userId, $judul, $isi, $target_role, $is_popup) {
        $stmt = $this->db->prepare("
            INSERT INTO pengumuman (user_id, judul, isi, target_role, is_popup)
            VALUES (?, ?, ?, ?, ?)
        ");
        return $stmt->execute([$userId, $judul, $isi, $target_role, $is_popup ? 1 : 0]);
    }

    public function updatePengumuman($id, $judul, $isi, $target_role, $is_popup) {
        $stmt = $this->db->prepare("
            UPDATE pengumuman 
            SET judul = ?, isi = ?, target_role = ?, is_popup = ?
            WHERE id = ?
        ");
        return $stmt->execute([$judul, $isi, $target_role, $is_popup ? 1 : 0, $id]);
    }

    public function deletePengumuman($id) {
        $stmt = $this->db->prepare("DELETE FROM pengumuman WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // --- FORUM DISKUSI ---
    public function getForumTopics() {
        return $this->db->query("
            SELECT f.*, u.full_name, u.avatar, r.name as role_name, m.nama_mapel,
            (SELECT COUNT(*) FROM komentar k WHERE k.forum_id = f.id) as total_replies
            FROM forum f
            JOIN users u ON f.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN mata_pelajaran m ON f.mapel_id = m.id
            ORDER BY f.id DESC
        ")->fetchAll();
    }

    public function getForumDetail($id) {
        $stmt = $this->db->prepare("
            SELECT f.*, u.full_name, u.avatar, r.name as role_name, m.nama_mapel
            FROM forum f
            JOIN users u ON f.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN mata_pelajaran m ON f.mapel_id = m.id
            WHERE f.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function createForumTopic($userId, $mapelId, $judul, $konten, $gambar) {
        $stmt = $this->db->prepare("INSERT INTO forum (user_id, mapel_id, judul, konten, gambar) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$userId, $mapelId ?: null, $judul, $konten, $gambar]);
    }

    public function getKomentar($forum_id) {
        $stmt = $this->db->prepare("
            SELECT k.*, u.full_name, u.avatar, r.name as role_name
            FROM komentar k
            JOIN users u ON k.user_id = u.id
            JOIN roles r ON u.role_id = r.id
            WHERE k.forum_id = ?
            ORDER BY k.id ASC
        ");
        $stmt->execute([$forum_id]);
        return $stmt->fetchAll();
    }

    public function addKomentar($forum_id, $userId, $komentar, $parent_id = null) {
        $stmt = $this->db->prepare("INSERT INTO komentar (forum_id, user_id, parent_id, komentar) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$forum_id, $userId, $parent_id ?: null, $komentar]);
    }

    // --- REALTIME CHAT (AJAX POLLING) ---
    public function getChatMessages($userId1, $userId2) {
        // Automatically purge chat messages older than 7 days for security and optimization
        try {
            $this->db->exec("DELETE FROM chat WHERE created_at < NOW() - INTERVAL 7 DAY");
        } catch (Exception $e) {}

        $stmt = $this->db->prepare("
            SELECT c.*, u.full_name as sender_name, u.avatar as sender_avatar
            FROM chat c
            JOIN users u ON c.sender_id = u.id
            WHERE (
                (c.sender_id = ? AND c.receiver_id = ? AND c.deleted_by_sender = 0) 
                OR 
                (c.sender_id = ? AND c.receiver_id = ? AND c.deleted_by_receiver = 0)
            )
            AND c.created_at >= NOW() - INTERVAL 7 DAY
            ORDER BY c.created_at ASC
        ");
        $stmt->execute([$userId1, $userId2, $userId2, $userId1]);

        $rows = $stmt->fetchAll();

        // Mark as read
        $stmtRead = $this->db->prepare("UPDATE chat SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        $stmtRead->execute([$userId2, $userId1]);

        return $rows;
    }

    public function sendChatMessage($senderId, $receiverId, $message) {
        require_once ROOT_PATH . 'helpers/ProfanityFilterHelper.php';
        $cleanMessage = ProfanityFilterHelper::filter($message);
        $stmt = $this->db->prepare("INSERT INTO chat (sender_id, receiver_id, message) VALUES (?, ?, ?)");
        return $stmt->execute([$senderId, $receiverId, $cleanMessage]);
    }

    public function editChatMessage($chatId, $userId, $newMessage) {
        require_once ROOT_PATH . 'helpers/ProfanityFilterHelper.php';
        $cleanMessage = ProfanityFilterHelper::filter($newMessage);
        $stmt = $this->db->prepare("
            UPDATE chat 
            SET message = ?, is_edited = 1 
            WHERE id = ? AND sender_id = ? AND is_deleted_everyone = 0
        ");
        return $stmt->execute([$cleanMessage, $chatId, $userId]);
    }

    public function deleteChatForMe($chatId, $userId) {
        $stmtCheck = $this->db->prepare("SELECT sender_id, receiver_id FROM chat WHERE id = ?");
        $stmtCheck->execute([$chatId]);
        $row = $stmtCheck->fetch();

        if (!$row) return false;

        if ($row['sender_id'] == $userId) {
            $stmt = $this->db->prepare("UPDATE chat SET deleted_by_sender = 1 WHERE id = ?");
            return $stmt->execute([$chatId]);
        } elseif ($row['receiver_id'] == $userId) {
            $stmt = $this->db->prepare("UPDATE chat SET deleted_by_receiver = 1 WHERE id = ?");
            return $stmt->execute([$chatId]);
        }
        return false;
    }

    public function deleteChatForEveryone($chatId, $userId) {
        $stmt = $this->db->prepare("
            UPDATE chat 
            SET is_deleted_everyone = 1 
            WHERE id = ? AND sender_id = ?
        ");
        return $stmt->execute([$chatId, $userId]);
    }

    public function clearChatHistory($currentUserId, $contactId) {
        $uid = (int)$currentUserId;
        $cid = (int)$contactId;

        $stmt1 = $this->db->prepare("UPDATE chat SET deleted_by_sender = 1 WHERE sender_id = ? AND receiver_id = ?");
        $stmt1->execute([$uid, $cid]);

        $stmt2 = $this->db->prepare("UPDATE chat SET deleted_by_receiver = 1 WHERE sender_id = ? AND receiver_id = ?");
        $stmt2->execute([$cid, $uid]);

        return true;
    }

    public function getChatContacts($currentUserId) {
        $uid = (int)$currentUserId;
        $stmt = $this->db->prepare("
            SELECT u.id, 
            COALESCE(s.nama_lengkap, g.nama_lengkap, u.full_name) as full_name,
            u.avatar, 
            u.role_id, 
            r.name as role_name,
            s.nis,
            g.nip,
            CASE 
                WHEN r.name = 'Siswa' THEN COALESCE(k.nama_kelas, '')
                ELSE ''
            END as nama_kelas,
            CASE 
                WHEN r.name = 'Guru' THEN COALESCE(
                    (SELECT GROUP_CONCAT(DISTINCT mp.nama_mapel SEPARATOR ', ') 
                     FROM jadwal j 
                     JOIN mata_pelajaran mp ON j.mapel_id = mp.id 
                     WHERE j.guru_id = g.id),
                    ''
                )
                ELSE ''
            END as mata_pelajaran,
            (SELECT 
                CASE 
                    WHEN is_deleted_everyone = 1 THEN '🚫 Pesan telah dihapus'
                    ELSE message 
                END
             FROM chat 
             WHERE ((sender_id = u.id AND receiver_id = ? AND deleted_by_receiver = 0) OR (sender_id = ? AND receiver_id = u.id AND deleted_by_sender = 0)) 
             ORDER BY created_at DESC LIMIT 1
            ) as last_message,
            (SELECT created_at 
             FROM chat 
             WHERE ((sender_id = u.id AND receiver_id = ? AND deleted_by_receiver = 0) OR (sender_id = ? AND receiver_id = u.id AND deleted_by_sender = 0)) 
             ORDER BY created_at DESC LIMIT 1
            ) as last_time,
            (SELECT COUNT(*) FROM chat WHERE sender_id = u.id AND receiver_id = ? AND is_read = 0 AND deleted_by_receiver = 0 AND is_deleted_everyone = 0) as unread_count
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN siswa s ON s.user_id = u.id
            LEFT JOIN kelas k ON s.kelas_id = k.id
            LEFT JOIN guru g ON g.user_id = u.id
            WHERE u.id != ? AND u.status = 'active'
            ORDER BY unread_count DESC, last_time DESC, full_name ASC
        ");
        $stmt->execute([$uid, $uid, $uid, $uid, $uid, $uid]);
        return $stmt->fetchAll();
    }

    // --- NOTIFIKASI ---
    public function getUnreadNotifications($userId) {
        $stmt = $this->db->prepare("SELECT * FROM notifikasi WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function sendNotification($userId, $title, $message, $link = null) {
        $this->ensureNotifikasiTable();
        $stmt = $this->db->prepare("INSERT INTO notifikasi (user_id, title, message, link) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$userId, $title, $message, $link]);
    }

    public function sendNotificationToClass($kelasId, $title, $message, $link = null) {
        $this->ensureNotifikasiTable();
        $stmt = $this->db->prepare("SELECT user_id FROM siswa WHERE kelas_id = ? AND user_id IS NOT NULL");
        $stmt->execute([(int)$kelasId]);
        $students = $stmt->fetchAll();
        
        $stmtNotif = $this->db->prepare("INSERT INTO notifikasi (user_id, title, message, link) VALUES (?, ?, ?, ?)");
        foreach ($students as $s) {
            $stmtNotif->execute([$s['user_id'], $title, $message, $link]);
        }
    }

    public function sendNotificationToTeacherByQuiz($quizId, $title, $message, $link = null) {
        $this->ensureNotifikasiTable();
        $stmt = $this->db->prepare("
            SELECT g.user_id 
            FROM quiz q 
            JOIN guru g ON q.guru_id = g.id 
            WHERE q.id = ?
        ");
        $stmt->execute([(int)$quizId]);
        $teacher = $stmt->fetch();
        if ($teacher && !empty($teacher['user_id'])) {
            $this->sendNotification($teacher['user_id'], $title, $message, $link);
        }
    }

    public function sendNotificationToTeacherByTugas($tugasId, $title, $message, $link = null) {
        $this->ensureNotifikasiTable();
        $stmt = $this->db->prepare("
            SELECT g.user_id 
            FROM tugas t 
            JOIN guru g ON t.guru_id = g.id 
            WHERE t.id = ?
        ");
        $stmt->execute([(int)$tugasId]);
        $teacher = $stmt->fetch();
        if ($teacher && !empty($teacher['user_id'])) {
            $this->sendNotification($teacher['user_id'], $title, $message, $link);
        }
    }

    public function sendNotificationToStudent($siswaId, $title, $message, $link = null) {
        $this->ensureNotifikasiTable();
        $stmt = $this->db->prepare("SELECT user_id FROM siswa WHERE id = ?");
        $stmt->execute([(int)$siswaId]);
        $student = $stmt->fetch();
        if ($student && !empty($student['user_id'])) {
            $this->sendNotification($student['user_id'], $title, $message, $link);
        }
    }

    public function getUserHeaderNotifications($userId) {
        $userId = (int)$userId;
        $items = [];
        $unreadChatCount = 0;

        try {
            $stmtChat = $this->db->prepare("
                SELECT c.id, c.sender_id, c.message, c.created_at, u.full_name as sender_name
                FROM chat c
                JOIN users u ON c.sender_id = u.id
                WHERE c.receiver_id = ? AND c.is_read = 0
                ORDER BY c.created_at DESC LIMIT 5
            ");
            $stmtChat->execute([$userId]);
            $chats = $stmtChat->fetchAll();
            $unreadChatCount = count($chats);

            foreach ($chats as $ch) {
                $items[] = [
                    'type' => 'chat',
                    'icon' => 'bi-chat-dots-fill text-primary',
                    'title' => 'Pesan Chat dari ' . $ch['sender_name'],
                    'desc' => substr($ch['message'], 0, 50) . (strlen($ch['message']) > 50 ? '...' : ''),
                    'time' => $ch['created_at'],
                    'link' => BASE_URL . 'index.php?url=chat&with=' . $ch['sender_id']
                ];
            }

            $stmtForum = $this->db->query("
                SELECT f.id, f.judul, f.created_at, u.full_name as author
                FROM forum f
                JOIN users u ON f.user_id = u.id
                WHERE f.user_id != {$userId}
                ORDER BY f.created_at DESC LIMIT 3
            ");
            $forumTopics = $stmtForum->fetchAll();
            foreach ($forumTopics as $ft) {
                $items[] = [
                    'type' => 'forum',
                    'icon' => 'bi-chat-square-quote-fill text-success',
                    'title' => 'Topik Forum: ' . $ft['author'],
                    'desc' => substr($ft['judul'], 0, 55) . (strlen($ft['judul']) > 55 ? '...' : ''),
                    'time' => $ft['created_at'],
                    'link' => BASE_URL . 'index.php?url=forum/detail&id=' . $ft['id']
                ];
            }

            $stmtNotif = $this->db->prepare("SELECT * FROM notifikasi WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT 3");
            $stmtNotif->execute([$userId]);
            $notifs = $stmtNotif->fetchAll();
            foreach ($notifs as $n) {
                $items[] = [
                    'type' => 'system',
                    'icon' => 'bi-info-circle-fill text-warning',
                    'title' => $n['title'],
                    'desc' => $n['message'],
                    'time' => $n['created_at'],
                    'link' => !empty($n['link']) ? BASE_URL . $n['link'] : '#'
                ];
            }

            usort($items, function($a, $b) {
                return strtotime($b['time']) <=> strtotime($a['time']);
            });

        } catch (Exception $e) {}

        $unreadNotifCount = count($notifs ?? []);
        $unreadTotal = $unreadChatCount + $unreadNotifCount;

        return [
            'unread_chat_count' => $unreadChatCount,
            'unread_notif_count' => $unreadNotifCount,
            'unread_total' => $unreadTotal,
            'total_count' => count($items),
            'items' => array_slice($items, 0, 6)
        ];
    }

    public function markAllNotificationsAsRead($userId) {
        $userId = (int)$userId;
        try {
            $stmtChat = $this->db->prepare("UPDATE chat SET is_read = 1 WHERE receiver_id = ?");
            $stmtChat->execute([$userId]);

            $stmtNotif = $this->db->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?");
            $stmtNotif->execute([$userId]);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
