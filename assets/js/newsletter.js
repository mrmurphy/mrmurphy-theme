/**
 * Newsletter Signup - In-page status states
 *
 * Intercepts the subscribe form submit and fires the WordPress.com
 * memberships GET via a no-cors fetch, so the reader stays on the page
 * while we render our own success/error states inline. Without JS the
 * form still works via native navigation to the confirmation page.
 */

(function () {
    'use strict';

    function renderStatus(form, type, text) {
        let status = form.querySelector('.newsletter-signup__status');

        if (!status) {
            status = document.createElement('div');
            status.hidden = true;
            status.setAttribute('role', 'status');
            form.appendChild(status);
        }

        status.hidden = false;
        status.className = 'newsletter-signup__status newsletter-signup__status--' + type;
        status.textContent = text;
    }

    function setBusy(form, busy) {
        const button = form.querySelector('.newsletter-signup__submit');
        if (!button) {
            return;
        }
        button.disabled = busy;
        button.setAttribute('aria-busy', busy ? 'true' : 'false');
    }

    function init(form) {
        if (form.dataset.newsletterBound) {
            return;
        }
        form.dataset.newsletterBound = 'true';

        const input = form.querySelector('input[type="email"]');

        form.addEventListener('submit', function (event) {
            // No fetch support: let the native form navigation handle it.
            if (typeof window.fetch !== 'function') {
                return;
            }

            event.preventDefault();

            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }

            setBusy(form, true);

            const params = new URLSearchParams(new FormData(form)).toString();
            const url = form.action + '?' + params;

            fetch(url, { mode: 'no-cors' })
                .then(function () {
                    renderStatus(
                        form,
                        'success',
                        'Check your inbox! We sent a confirmation link to ' + input.value + '.'
                    );
                    form.reset();
                })
                .catch(function () {
                    renderStatus(
                        form,
                        'error',
                        'Something went wrong sending that. Please try again.'
                    );
                })
                .finally(function () {
                    setBusy(form, false);
                });
        });
    }

    document.querySelectorAll('.newsletter-signup').forEach(init);
})();
