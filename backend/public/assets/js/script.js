// Konfirmasi sebelum menghapus data
const SIDEBAR_OPEN_KEY = 'rtex-sidebar-open';

document.addEventListener('click', function (e) {
    const menuToggle = e.target.closest('[data-sidebar-toggle]');
    if (menuToggle) {
        const isOpen = isMobileSidebar()
            ? !document.querySelector('.sidebar').classList.contains('is-open')
            : document.querySelector('.sidebar').classList.contains('is-collapsed');
        setSidebarOpen(isOpen);
    }

    if (e.target.closest('[data-sidebar-close]') || e.target.closest('[data-sidebar-backdrop]')) {
        closeSidebar();
    }

    const el = e.target.closest('[data-confirm]');
    if (el) {
        const pesan = el.getAttribute('data-confirm') || 'Yakin ingin melanjutkan?';
        if (!confirm(pesan)) {
            e.preventDefault();
        }
    }
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isMobileSidebar()) {
        closeSidebar();
    }
});

function isMobileSidebar() {
    return window.matchMedia('(max-width: 767.98px)').matches;
}

function setSidebarOpen(isOpen) {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (!sidebar || !menuToggle || !backdrop) {
        return;
    }

    if (isMobileSidebar()) {
        sidebar.classList.toggle('is-open', isOpen);
        sidebar.classList.remove('is-collapsed');
        backdrop.hidden = !isOpen;
    } else {
        sidebar.classList.toggle('is-collapsed', !isOpen);
        sidebar.classList.remove('is-open');
        backdrop.hidden = true;
        localStorage.setItem(SIDEBAR_OPEN_KEY, String(isOpen));
    }

    menuToggle.setAttribute('aria-expanded', String(isOpen));
    menuToggle.querySelector('[data-sidebar-toggle-label]').textContent = isOpen ? 'Tutup menu' : 'Buka menu';
}

function closeSidebar() {
    setSidebarOpen(false);
}

function syncSidebarWithViewport() {
    const sidebar = document.querySelector('.sidebar');
    const menuToggle = document.querySelector('[data-sidebar-toggle]');
    const backdrop = document.querySelector('[data-sidebar-backdrop]');

    if (!sidebar || !menuToggle || !backdrop) {
        return;
    }

    const isMobile = isMobileSidebar();
    const isOpen = !isMobile && localStorage.getItem(SIDEBAR_OPEN_KEY) === 'true';

    sidebar.classList.remove('is-open');
    sidebar.classList.toggle('is-collapsed', !isOpen && !isMobile);
    backdrop.hidden = true;
    menuToggle.setAttribute('aria-expanded', String(isOpen));
    menuToggle.querySelector('[data-sidebar-toggle-label]').textContent = isOpen ? 'Tutup menu' : 'Buka menu';
}

syncSidebarWithViewport();
window.addEventListener('resize', syncSidebarWithViewport);

// Auto-hide alert flash message setelah beberapa detik
document.addEventListener('DOMContentLoaded', function () {
    const alertBox = document.querySelector('.topbar + .alert, .alert.mb-4');
    if (alertBox) {
        setTimeout(() => {
            alertBox.style.transition = 'opacity 0.4s ease';
            alertBox.style.opacity = '0';
            setTimeout(() => alertBox.remove(), 400);
        }, 4500);
    }
});

// Preview nama file upload bukti transfer
function previewFileName(input, targetId) {
    const target = document.getElementById(targetId);
    if (target && input.files && input.files[0]) {
        target.textContent = '📎 ' + input.files[0].name;
    }
}

// ==========================================
// GANTI TAMPILAN TERANG / GELAP (DARK MODE)
// ==========================================
function updateThemeIcon(theme) {
    const icon = document.getElementById('themeToggleIcon');
    if (icon) {
        icon.textContent = theme === 'dark' ? '☀️' : '🌙';
    }
    const btn = document.getElementById('themeToggleBtn');
    if (btn) {
        btn.setAttribute('title', theme === 'dark' ? 'Ganti ke Mode Terang' : 'Ganti ke Mode Gelap');
    }
}

// Inisialisasi ikon saat DOM siap
document.addEventListener('DOMContentLoaded', function () {
    const currentTheme = document.documentElement.getAttribute('data-theme') || localStorage.getItem('rtex_theme') || 'light';
    updateThemeIcon(currentTheme);
});

// Event listener klik tombol toggle mode terang/gelap
document.addEventListener('click', function (e) {
    const btn = e.target.closest('#themeToggleBtn');
    if (btn) {
        const currentTheme = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('rtex_theme', newTheme);
        updateThemeIcon(newTheme);
    }
});
