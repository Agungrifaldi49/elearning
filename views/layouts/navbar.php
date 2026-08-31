<?php
$headerNotifs = ['unread_chat_count' => 0, 'unread_notif_count' => 0, 'unread_total' => 0, 'total_count' => 0, 'items' => []];
if (AuthHelper::check()) {
    require_once ROOT_PATH . 'models/CommunicationModel.php';
    $commModel = new CommunicationModel();
    $headerNotifs = $commModel->getUserHeaderNotifications(AuthHelper::user()['id']);
}
$unreadBadgeCount = (int)($headerNotifs['unread_total'] ?? 0);
?>
<nav class="navbar navbar-expand-lg app-navbar fixed-top px-3 px-lg-4">
    <div class="container-fluid p-0">
        <!-- Sidebar Toggle Mobile -->
        <button class="btn btn-light d-lg-none me-2" type="button" id="sidebarToggle">
            <i class="bi bi-list fs-4"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center gap-2 fw-bold text-primary" href="<?= BASE_URL ?>">
            <div class="bg-primary text-white rounded-3 p-1 px-2">
                <i class="bi bi-mortarboard-fill fs-5"></i>
            </div>
            <span class="d-none d-sm-inline"><?= APP_SHORT_NAME ?></span>
        </a>

        <div class="ms-auto d-flex align-items-center gap-2 gap-sm-3">
            <!-- Theme Toggle -->
            <button class="btn btn-sm btn-light rounded-circle" id="themeToggle" title="Ganti Tema">
                <i class="bi bi-moon-stars-fill"></i>
            </button>

            <!-- Notifications & Realtime Chat Dropdown (Realtime AJAX Polling Enabled) -->
            <div class="dropdown">
                <button class="btn btn-sm btn-light rounded-circle position-relative p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Pemberitahuan & Notifikasi">
                    <i class="bi bi-bell fs-5"></i>
                    <span id="navNotifBadgeSpan" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem; <?= $unreadBadgeCount > 0 ? '' : 'display:none;' ?>">
                        <?= $unreadBadgeCount ?>
                    </span>
                </button>
                <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-4 p-3 notif-dropdown-menu">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bell-fill text-primary me-1.5"></i>Notifikasi & Chat</h6>
                        <span id="navNotifHeaderRight">
                            <?php if ($unreadBadgeCount > 0): ?>
                                <a href="<?= BASE_URL ?>index.php?url=chat/markRead" class="btn btn-link btn-sm p-0 text-decoration-none text-primary fw-semibold" style="font-size: 0.75rem;">
                                    <i class="bi bi-check2-all me-1"></i>Tandai Dibaca
                                </a>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-muted rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">0 Belum Dibaca</span>
                            <?php endif; ?>
                        </span>
                    </div>
                    <hr class="dropdown-divider my-2">

                    <div id="navNotifItemsContainer">
                        <?php if (empty($headerNotifs['items'])): ?>
                            <div class="small text-muted py-3 text-center">
                                <i class="bi bi-check-all me-1 text-success fs-5 d-block mb-1"></i> Tidak ada notifikasi baru saat ini.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($headerNotifs['items'] as $item): ?>
                                    <a href="<?= $item['link'] ?>" class="list-group-item list-group-item-action border-0 rounded-3 p-2.5 mb-1.5 bg-light-hover">
                                        <div class="d-flex align-items-start gap-2.5">
                                            <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width:36px; height:36px;">
                                                <i class="bi <?= $item['icon'] ?> fs-6"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden" style="line-height: 1.25;">
                                                <div class="fw-bold small text-dark text-truncate"><?= htmlspecialchars($item['title']) ?></div>
                                                <div class="text-muted small text-truncate mt-1" style="font-size: 0.78rem;"><?= htmlspecialchars($item['desc']) ?></div>
                                                <div class="text-muted opacity-75" style="font-size: 0.68rem; margin-top: 3px;">
                                                    <i class="bi bi-clock me-1"></i><?= date('d M H:i', strtotime($item['time'])) ?>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr class="dropdown-divider my-2">
                    <div class="d-flex justify-content-between text-center small pt-1">
                        <a href="<?= BASE_URL ?>index.php?url=chat" class="text-decoration-none text-primary fw-semibold"><i class="bi bi-chat-dots me-1"></i>Buka Chat</a>
                        <a href="<?= BASE_URL ?>index.php?url=forum" class="text-decoration-none text-success fw-semibold"><i class="bi bi-chat-square-text me-1"></i>Buka Forum</a>
                    </div>
                </div>
            </div>

            <!-- Profile Dropdown -->
            <?php if (AuthHelper::check()): 
                $currUser = AuthHelper::user();
                $navAvFile = $currUser['avatar'] ?? '';
                $hasNavAvatar = false;
                $navAvatarUrl = '';
                if (!empty($navAvFile) && $navAvFile !== 'default_avatar.png') {
                    if (file_exists(ROOT_PATH . 'assets/uploads/profile/' . $navAvFile)) {
                        $hasNavAvatar = true;
                        $navAvatarUrl = BASE_URL . 'assets/uploads/profile/' . htmlspecialchars($navAvFile);
                    } elseif (file_exists(ROOT_PATH . 'assets/uploads/avatar/' . $navAvFile)) {
                        $hasNavAvatar = true;
                        $navAvatarUrl = BASE_URL . 'assets/uploads/avatar/' . htmlspecialchars($navAvFile);
                    }
                }
            ?>
                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-decoration-none dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown">
                        <?php if ($hasNavAvatar): ?>
                            <img src="<?= $navAvatarUrl ?>" alt="Avatar" class="rounded-circle object-fit-cover shadow-sm border border-2 border-primary" style="width: 38px; height: 38px;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px;">
                                <?= strtoupper(substr($currUser['full_name'], 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                        <div class="d-none d-md-block text-start" style="line-height: 1.2;">
                            <div class="fw-semibold small"><?= htmlspecialchars($currUser['full_name']) ?></div>
                            <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($currUser['role_name']) ?></small>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 mt-2">
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>index.php?url=chat"><i class="bi bi-chat-dots me-2 text-primary"></i> Chat Pesan Realtime</a></li>
                        <li><a class="dropdown-item py-2" href="<?= BASE_URL ?>index.php?url=forum"><i class="bi bi-chat-square-text me-2 text-success"></i> Forum Diskusi Pembelajaran</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item py-2 text-danger" href="<?= BASE_URL ?>logout.php"><i class="bi bi-box-arrow-right me-2"></i> Keluar / Logout</a></li>
                    </ul>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>
