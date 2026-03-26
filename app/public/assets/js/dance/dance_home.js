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

    // Ticket forms use /assets/js/cart/cart_add_ticket.js (header). Do not register a second
    // capture-phase submit listener here — it would POST twice per click.

    // ADD buttons without event_id (default timetable): clickable, show “not yet available” toast
    document.querySelectorAll('.dance-add-placeholder').forEach(function (btn) {
        btn.addEventListener('click', function () {
            showCartToast({
                title: 'Ticket not yet available',
                sub: 'This item will be available for purchase soon.',
            });
        });
    });
})();
