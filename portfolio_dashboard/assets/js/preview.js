// assets/js/preview.js

document.addEventListener('DOMContentLoaded', function () {

    // ---- Dark / Light Mode (preview page) ----
    var html = document.documentElement;
    var toggleBtn  = document.getElementById('previewThemeToggle');
    var toggleIcon = document.getElementById('previewThemeIcon');

    function applyPreviewTheme(theme) {
        html.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        if (toggleIcon) {
            toggleIcon.className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-fill';
        }
        if (toggleBtn) {
            toggleBtn.setAttribute('aria-label', theme === 'dark' ? 'Switch to light mode' : 'Switch to dark mode');
        }
    }

    var currentTheme = html.getAttribute('data-theme') || 'light';
    applyPreviewTheme(currentTheme);

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            var next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            applyPreviewTheme(next);
        });
    }

    // ---- Section reveal on scroll ----
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

    // ---- Project Filtering ----
    var filterButtons = document.querySelectorAll('.filter-btn');
    var projectItems  = document.querySelectorAll('.project-item');

    filterButtons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var category = this.getAttribute('data-category');
            filterButtons.forEach(function (b) {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            projectItems.forEach(function (item) {
                var itemCategory = item.getAttribute('data-category');
                item.style.display = (category === '' || itemCategory === category) ? '' : 'none';
            });
        });
    });

    // ---- Contact Form — real AJAX submission ----
    var form        = document.getElementById('contactForm');
    var successMsg  = document.getElementById('formSuccess');
    var errorMsg    = document.getElementById('formError');
    var submitBtn   = document.getElementById('contactSubmitBtn');
    var btnText     = document.getElementById('contactBtnText');
    var btnSpinner  = document.getElementById('contactBtnSpinner');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var name    = document.getElementById('contact_name');
            var email   = document.getElementById('contact_email');
            var message = document.getElementById('contact_message');
            var isValid = true;

            // Client-side validation
            [name, message].forEach(function (field) {
                if (field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value.trim())) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            if (!isValid) {
                successMsg.style.display = 'none';
                errorMsg.style.display   = 'none';
                var firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
                return;
            }

            // Show spinner
            submitBtn.disabled    = true;
            btnText.textContent   = 'Sending...';
            btnSpinner.style.display = 'inline-block';
            successMsg.style.display = 'none';
            errorMsg.style.display   = 'none';

            // AJAX POST to contact_submit.php
            var formData = new FormData(form);
            fetch('contact_submit.php', {
                method: 'POST',
                body: formData
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                submitBtn.disabled       = false;
                btnText.textContent      = 'Send Message';
                btnSpinner.style.display = 'none';

                if (data.success) {
                    successMsg.textContent   = data.message;
                    successMsg.style.display = 'block';
                    errorMsg.style.display   = 'none';
                    form.reset();
                    successMsg.focus();
                } else {
                    errorMsg.textContent   = data.message;
                    errorMsg.style.display = 'block';
                    successMsg.style.display = 'none';
                    errorMsg.focus();
                }
            })
            .catch(function () {
                submitBtn.disabled       = false;
                btnText.textContent      = 'Send Message';
                btnSpinner.style.display = 'none';
                errorMsg.textContent     = 'Network error. Please try again.';
                errorMsg.style.display   = 'block';
                errorMsg.focus();
            });
        });
    }
});
