/**
 * Main Javascript for E-Learning SMK Muthia Harapan Cicalengka
 */
document.addEventListener('DOMContentLoaded', function () {
    // Dark Mode Toggle
    const themeToggleBtn = document.getElementById('themeToggle');
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', function () {
            const currentTheme = document.documentElement.getAttribute('data-bs-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });
    }

    // Apply Saved Theme
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-bs-theme', savedTheme);

    // Sidebar Toggle for Mobile / Offcanvas
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.app-sidebar');

    if (sidebarToggleBtn && sidebar) {
        sidebarToggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('show');
        });
    }

    // Close sidebar on click outside in mobile
    document.addEventListener('click', function (e) {
        if (window.innerWidth < 992 && sidebar && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !sidebarToggleBtn.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // DataTables Initialization (wrapped in responsive div)
    if (window.jQuery && $.fn.DataTable) {
        $.fn.dataTable.ext.errMode = 'none';
        $('.datatable').each(function() {
            var customLength = parseInt($(this).data('page-length')) || 20;
            $(this).DataTable({
                responsive: true,
                pageLength: customLength,
                lengthMenu: [
                    [10, 20, 25, 50, 100, -1],
                    [10, 20, 25, 50, 100, "Semua"]
                ],
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Lanjut",
                        previous: "Kembali"
                    },
                    zeroRecords: "Tidak ada data yang ditemukan"
                }
            });
        });
    }
});
