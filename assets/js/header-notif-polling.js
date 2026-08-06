/**
 * Header Real-Time Notification & Badge Polling JavaScript
 * Automatically updates notification badges in real-time across all pages without page refresh.
 *
 * Uses Hash Diffing & Incremental Patching to prevent Focus Loss & DOM Mutation Glitches.
 */
document.addEventListener('DOMContentLoaded', function () {
    const badgeSpan = document.getElementById('navNotifBadgeSpan');
    const headerRight = document.getElementById('navNotifHeaderRight');
    const itemsContainer = document.getElementById('navNotifItemsContainer');

    if (!badgeSpan) return;

    let lastNotifHash = '';

    function pollHeaderNotifications() {
        if (typeof BASE_URL === 'undefined') return;

        fetch(`${BASE_URL}index.php?url=chat/headerNotifications`)
            .then(res => res.json())
            .then(res => {
                if (res.status === 'success') {
                    const currentHash = JSON.stringify(res);

                    // HASH DIFFING: If notification data hasn't changed, DO NOT TOUCH DOM!
                    // This prevents Header DOM Mutations from causing Focus Loss or closing open chat menus.
                    if (currentHash === lastNotifHash) return;
                    lastNotifHash = currentHash;

                    const unreadTotal = parseInt(res.unread_total || 0, 10);

                    // Real-Time Update Badge Span
                    if (unreadTotal > 0) {
                        badgeSpan.textContent = unreadTotal;
                        badgeSpan.style.display = '';
                    } else {
                        badgeSpan.textContent = '0';
                        badgeSpan.style.display = 'none';
                    }

                    // Real-Time Update Header Right Action
                    if (headerRight) {
                        if (unreadTotal > 0) {
                            headerRight.innerHTML = `<a href="${BASE_URL}index.php?url=chat/markRead" class="btn btn-link btn-sm p-0 text-decoration-none text-primary" style="font-size: 0.75rem;"><i class="bi bi-check2-all me-1"></i>Tandai Dibaca</a>`;
                        } else {
                            headerRight.innerHTML = `<span class="badge bg-secondary rounded-pill">0 Belum Dibaca</span>`;
                        }
                    }

                    // Real-Time Update Dropdown Items List
                    if (itemsContainer && res.items) {
                        if (res.items.length === 0) {
                            itemsContainer.innerHTML = `
                                <div class="small text-muted py-3 text-center">
                                    <i class="bi bi-check-all me-1 text-success fs-5 d-block mb-1"></i> Tidak ada notifikasi baru saat ini.
                                </div>
                            `;
                        } else {
                            let html = '<div class="list-group list-group-flush">';
                            res.items.forEach(item => {
                                const dateFormatted = new Date(item.time).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
                                html += `
                                    <a href="${item.link}" class="list-group-item list-group-item-action border-0 rounded-3 p-2 mb-1">
                                        <div class="d-flex align-items-start gap-2">
                                            <div class="fs-5 mt-1">
                                                <i class="bi ${escapeHtml(item.icon)}"></i>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden" style="line-height: 1.25;">
                                                <div class="fw-bold small text-dark text-truncate">${escapeHtml(item.title)}</div>
                                                <div class="text-muted small text-truncate mt-1" style="font-size: 0.78rem;">${escapeHtml(item.desc)}</div>
                                                <div class="text-muted" style="font-size: 0.68rem; margin-top: 2px;">
                                                    <i class="bi bi-clock me-1"></i>${dateFormatted}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                `;
                            });
                            html += '</div>';
                            itemsContainer.innerHTML = html;
                        }
                    }
                }
            })
            .catch(err => console.error('Header notif polling error:', err));
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Run immediately and poll every 4 seconds in background with Hash Diffing
    pollHeaderNotifications();
    setInterval(pollHeaderNotifications, 4000);
});
