/**
 * Site Yönetim Sistemi - Ana JavaScript Dosyası
 */

(function() {
    'use strict';

    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('sidebarToggle');
    const STORAGE_KEY = 'site_yonetim_sidebar_collapsed';

    function setCollapsed(isCollapsed) {
        if (isCollapsed) {
            sidebar.classList.add('collapsed');
            if (toggleBtn) toggleBtn.classList.add('collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            if (toggleBtn) toggleBtn.classList.remove('collapsed');
        }
        try {
            localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');
        } catch (e) {
            // localStorage desteklenmiyorsa sessizce devam et
        }
    }

    function init() {
        let isCollapsed = false;
        try {
            isCollapsed = localStorage.getItem(STORAGE_KEY) === '1';
        } catch (e) {
            isCollapsed = false;
        }
        setCollapsed(isCollapsed);

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                const currentlyCollapsed = sidebar.classList.contains('collapsed');
                setCollapsed(!currentlyCollapsed);
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
