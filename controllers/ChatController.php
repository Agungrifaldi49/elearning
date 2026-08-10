<?php
/**
 * Chat Controller (AJAX Real-Time Polling)
 */
require_once ROOT_PATH . 'helpers/AuthHelper.php';
require_once ROOT_PATH . 'helpers/Security.php';
require_once ROOT_PATH . 'models/CommunicationModel.php';

class ChatController {

    public function index() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();
        $db = Database::getConnection();

        $contacts = $commModel->getChatContacts($user['id']);
        foreach ($contacts as &$c) {
            $c['avatar_url'] = $this->formatAvatarUrl($c['avatar'] ?? '');
        }
        unset($c);

        $classList = $db->query("SELECT id, nama_kelas FROM kelas ORDER BY tingkat ASC, nama_kelas ASC")->fetchAll();
        $mapelList = $db->query("SELECT id, nama_mapel FROM mata_pelajaran ORDER BY nama_mapel ASC")->fetchAll();

        $activeContactId = (int)($_GET['with'] ?? ($contacts[0]['id'] ?? 0));
        $activeContactInfo = null;
        $messages = [];

        if ($activeContactId > 0) {
            $messages = $commModel->getChatMessages($user['id'], $activeContactId);
            foreach ($contacts as $cnt) {
                if ($cnt['id'] == $activeContactId) {
                    $activeContactInfo = $cnt;
                    break;
                }
            }
        }

        require_once ROOT_PATH . 'views/chat/index.php';
    }

    private function formatAvatarUrl($avFile) {
        if (!empty($avFile) && $avFile !== 'default_avatar.png') {
            if (file_exists(ROOT_PATH . 'assets/uploads/profile/' . $avFile)) {
                return BASE_URL . 'assets/uploads/profile/' . htmlspecialchars($avFile);
            } elseif (file_exists(ROOT_PATH . 'assets/uploads/avatar/' . $avFile)) {
                return BASE_URL . 'assets/uploads/avatar/' . htmlspecialchars($avFile);
            }
        }
        return '';
    }

    private function normalizeMessages($messages, $currentUserId) {
        require_once ROOT_PATH . 'helpers/ProfanityFilterHelper.php';
        $result = [];
        foreach ($messages as $msg) {
            $avFile = (string)($msg['sender_avatar'] ?? '');
            $rawMsg = (string)($msg['message'] ?? '');
            $cleanMsg = ProfanityFilterHelper::filter($rawMsg);

            $result[] = [
                'id' => (int)$msg['id'],
                'sender_id' => (int)$msg['sender_id'],
                'receiver_id' => (int)$msg['receiver_id'],
                'message' => $cleanMsg,
                'is_read' => (int)($msg['is_read'] ?? 0),
                'is_edited' => (int)($msg['is_edited'] ?? 0),
                'deleted_by_sender' => (int)($msg['deleted_by_sender'] ?? 0),
                'deleted_by_receiver' => (int)($msg['deleted_by_receiver'] ?? 0),
                'is_deleted_everyone' => (int)($msg['is_deleted_everyone'] ?? 0),
                'created_at' => $msg['created_at'],
                'sender_name' => (string)($msg['sender_name'] ?? ''),
                'sender_avatar' => $avFile,
                'sender_avatar_url' => $this->formatAvatarUrl($avFile),
                'is_me' => ((int)$msg['sender_id'] === (int)$currentUserId)
            ];
        }
        return $result;
    }

    public function fetch() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        $user = AuthHelper::user();
        $contactId = (int)($_GET['with'] ?? 0);

        if ($contactId > 0) {
            $commModel = new CommunicationModel();
            $messages = $commModel->getChatMessages($user['id'], $contactId);
            $contacts = $commModel->getChatContacts($user['id']);

            foreach ($contacts as &$c) {
                $c['avatar_url'] = $this->formatAvatarUrl($c['avatar'] ?? '');
            }
            unset($c);

            $normalizedMessages = $this->normalizeMessages($messages, $user['id']);

            echo json_encode([
                'status' => 'success',
                'current_user_id' => (int)$user['id'],
                'data' => $normalizedMessages,
                'contacts' => $contacts
            ]);
        } else {
            echo json_encode(['status' => 'error', 'data' => [], 'contacts' => []]);
        }
        exit();
    }

    public function send() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        if (!Security::verifyCsrfToken()) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
            exit();
        }

        $user = AuthHelper::user();
        $receiverId = (int)($_POST['receiver_id'] ?? 0);
        $message = Security::sanitize($_POST['message'] ?? '');

        if ($receiverId > 0 && !empty($message)) {
            $commModel = new CommunicationModel();
            $commModel->sendChatMessage($user['id'], $receiverId, $message);
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
        }
        exit();
    }

    public function markRead() {
        AuthHelper::requireLogin();
        $user = AuthHelper::user();
        $commModel = new CommunicationModel();
        $commModel->markAllNotificationsAsRead($user['id']);

        $referer = $_SERVER['HTTP_REFERER'] ?? BASE_URL;
        header('Location: ' . $referer);
        exit();
    }

    public function headerNotifications() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        $user = AuthHelper::user();
        if (!$user) {
            echo json_encode(['status' => 'error', 'unread_total' => 0, 'items' => []]);
            exit();
        }

        $commModel = new CommunicationModel();
        $notifs = $commModel->getUserHeaderNotifications($user['id']);

        echo json_encode([
            'status' => 'success',
            'unread_total' => (int)($notifs['unread_total'] ?? 0),
            'unread_chat_count' => (int)($notifs['unread_chat_count'] ?? 0),
            'unread_notif_count' => (int)($notifs['unread_notif_count'] ?? 0),
            'items' => $notifs['items'] ?? []
        ]);
        exit();
    }

    public function edit() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        if (!Security::verifyCsrfToken()) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
            exit();
        }

        $user = AuthHelper::user();
        $chatId = (int)($_POST['chat_id'] ?? 0);
        $message = Security::sanitize($_POST['message'] ?? '');

        if ($chatId > 0 && !empty($message)) {
            $commModel = new CommunicationModel();
            $success = $commModel->editChatMessage($chatId, $user['id'], $message);
            if ($success) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah pesan']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
        }
        exit();
    }

    public function deleteForMe() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        if (!Security::verifyCsrfToken()) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
            exit();
        }

        $user = AuthHelper::user();
        $chatId = (int)($_POST['chat_id'] ?? 0);

        if ($chatId > 0) {
            $commModel = new CommunicationModel();
            $success = $commModel->deleteChatForMe($chatId, $user['id']);
            if ($success) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pesan']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pesan tidak valid']);
        }
        exit();
    }

    public function deleteForEveryone() {
        AuthHelper::requireLogin();
        header('Content-Type: application/json');

        if (!Security::verifyCsrfToken()) {
            echo json_encode(['status' => 'error', 'message' => 'CSRF Token Invalid']);
            exit();
        }

        $user = AuthHelper::user();
        $chatId = (int)($_POST['chat_id'] ?? 0);

        if ($chatId > 0) {
            $commModel = new CommunicationModel();
            $success = $commModel->deleteChatForEveryone($chatId, $user['id']);
            if ($success) {
                echo json_encode(['status' => 'success']);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus pesan untuk semua orang']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Pesan tidak valid']);
        }
        exit();
    }
}
