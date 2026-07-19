// assets/js/app.js - shared dashboard behavior (loading spinner)

document.addEventListener('DOMContentLoaded', function () {
    // Build a single reusable full-page loading overlay
    const overlay = document.createElement('div');
    overlay.id = 'globalLoader';
    overlay.style.cssText = 'display:none;position:fixed;inset:0;background:rgba(255,255,255,0.65);' +
        'z-index:2000;align-items:center;justify-content:center;';
    overlay.innerHTML = '<div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">' +
        '<span class="visually-hidden">Loading...</span></div>';
    document.body.appendChild(overlay);

    function showLoader() {
        overlay.style.display = 'flex';
    }

    // Show the spinner while a normal (non-AJAX) form submits.
    // Add data-no-loader to any <form> that shouldn't trigger this (e.g. a search box
    // where instant feedback isn't needed).
    document.querySelectorAll('form:not([data-no-loader])').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (form.checkValidity()) {
                showLoader();
            }
        });
    });

    // Show the spinner on normal page navigation via sidebar/topbar links
    // (skips in-page anchors, modal triggers, and links opening in a new tab).
    document.querySelectorAll('.sidebar-menu a, .topbar a').forEach(function (link) {
        link.addEventListener('click', function () {
            const href = link.getAttribute('href');
            if (href && !href.startsWith('#') && link.target !== '_blank') {
                showLoader();
            }
        });
    });

    // ---- Section reveal on scroll (Week 6) ----
    // Any element with class="fade-in-up" fades/slides into place once it enters
    // the viewport. Falls back to instantly visible if IntersectionObserver isn't
    // supported, or if the user has requested reduced motion.
    const revealTargets = document.querySelectorAll('.fade-in-up');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (revealTargets.length && 'IntersectionObserver' in window && !prefersReducedMotion) {
        const revealObserver = new IntersectionObserver(function (entries, observer) {
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
