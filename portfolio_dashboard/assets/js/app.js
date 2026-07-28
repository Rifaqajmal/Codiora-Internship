// assets/js/app.js - shared dashboard behavior

document.addEventListener('DOMContentLoaded', function () {

    // ---- Dark / Light Mode ----
    var html = document.documentElement;
    var toggleBtn = document.getElementById('themeToggle');
    var themeIcon = document.getElementById('themeIcon');

    function applyTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        if (themeIcon) {
            themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        }
    }

    // Initialise icon to match current theme (anti-flash script already set the attribute)
    var currentTheme = html.getAttribute('data-theme') || 'light';
    applyTheme(currentTheme);

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyTheme(next);
        });
    }

    // ---- Global loading spinner ----
    var overlay = document.createElement('div');
    overlay.id = 'globalLoader';
    overlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(255,255,255,0.65);' +
        'z-index:2000;align-items:center;justify-content:center;';
    overlay.innerHTML = '<div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">' +
        '<span class="visually-hidden">Loading...</span></div>';
    document.body.appendChild(overlay);

    function showLoader() {
        overlay.style.display = 'flex';
    }

    document.querySelectorAll('form:not([data-no-loader])').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.checkValidity()) showLoader();
        });
    });

    document.querySelectorAll('.sidebar-menu a, .topbar a').forEach(function (link) {
        link.addEventListener('click', function () {
            var href = link.getAttribute('href');
            if (href && !href.startsWith('#') && link.target !== '_blank') showLoader();
        });
    });

    // ---- Section reveal on scroll (Week 6) ----
    var revealTargets = document.querySelectorAll('.fade-in-up');
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (revealTargets.length && 'IntersectionObserver' in window && !prefersReducedMotion) {
        var revealObserver = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        revealTargets.forEach(function (el) { revealObserver.observe(el); });
    } else {
        revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
    }
});
