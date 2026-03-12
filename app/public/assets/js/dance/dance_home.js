(() => {
    const toast = document.getElementById('cartToast');
    let toastTimer = null;
    const defaultTitle = 'Ticket added to cart';
    const defaultSub = 'Click to open shopping cart';

    function showCartToast(customMessage) {
        if (!toast) return;
        var title = toast.querySelector('.block.font-semibold');
        var sub = toast.querySelector('.block.text-xs');
        if (customMessage && title && sub) {
            title.textContent = customMessage.title || defaultTitle;
            sub.textContent = customMessage.sub || defaultSub;
        }
        toast.classList.remove('hidden');
        if (toastTimer) window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toast.classList.add('hidden');
            if (title) title.textContent = defaultTitle;
            if (sub) sub.textContent = defaultSub;
        }, 3800);
    }

    if (toast) {
        toast.addEventListener('click', () => {
            if (window.HaarlemCart && typeof window.HaarlemCart.open === 'function') {
                window.HaarlemCart.open();
            }
            toast.classList.add('hidden');
        });
    }

    async function addTicketWithoutReload(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (!(submitBtn instanceof HTMLButtonElement)) return;

        const originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="cart-icon" aria-hidden="true"></span> Adding...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload || payload.ok !== true) {
                const redirect = payload && typeof payload.redirect === 'string' ? payload.redirect : '';
                if (redirect) {
                    window.location.href = redirect;
                    return;
                }
                const message =
                    payload && typeof payload.message === 'string'
                        ? payload.message
                        : 'Could not add ticket to cart.';
                window.alert(message);
                return;
            }

            if (window.HaarlemCart) {
                if (typeof window.HaarlemCart.update === 'function') {
                    window.HaarlemCart.update(payload.cart || null);
                }
                if (typeof window.HaarlemCart.open === 'function') {
                    window.HaarlemCart.open();
                }
            }

            showCartToast();
        } catch (_err) {
            window.alert('Network error while adding ticket. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml || 'ADD';
        }
    }

    // Same as Jazz: intercept ticket forms so we add to cart via fetch and show toast (no full page reload)
    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;
            if (!(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('ticket-form')) return;
            event.preventDefault();
            void addTicketWithoutReload(form);
        },
        true
    );

    // ADD buttons without event_id (default timetable): clickable, show “not yet available” toast
    document.querySelectorAll('.dance-add-placeholder').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showCartToast({ title: 'Ticket not yet available', sub: 'This item will be available for purchase soon.' });
        });
    });
})();
