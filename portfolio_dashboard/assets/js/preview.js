// assets/js/preview.js

document.addEventListener('DOMContentLoaded', function () {

    // ---- Section reveal on scroll (Week 6) ----
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

    // ---- Project Filtering (no page reload) ----
    const filterButtons = document.querySelectorAll('.filter-btn');
    const projectItems = document.querySelectorAll('.project-item');

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const category = this.getAttribute('data-category');

            filterButtons.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');

            projectItems.forEach(item => {
                const itemCategory = item.getAttribute('data-category');
                if (category === '' || itemCategory === category) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Keyboard accessibility: Enter/Space already trigger click on <button> natively.
    });

    // ---- Contact Form Validation ----
    const form = document.getElementById('contactForm');
    const successMsg = document.getElementById('formSuccess');

    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const name = document.getElementById('contact_name');
            const email = document.getElementById('contact_email');
            const message = document.getElementById('contact_message');

            let isValid = true;

            // Required field checks
            [name, message].forEach(field => {
                if (field.value.trim() === '') {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            });

            // Email validation (format check)
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value.trim())) {
                email.classList.add('is-invalid');
                isValid = false;
            } else {
                email.classList.remove('is-invalid');
            }

            if (isValid) {
                successMsg.style.display = 'block';
                form.reset();
                // Move focus to success message for screen reader users
                successMsg.setAttribute('tabindex', '-1');
                successMsg.focus();
            } else {
                successMsg.style.display = 'none';
                // Focus the first invalid field
                const firstInvalid = form.querySelector('.is-invalid');
                if (firstInvalid) firstInvalid.focus();
            }
        });
    }
});
