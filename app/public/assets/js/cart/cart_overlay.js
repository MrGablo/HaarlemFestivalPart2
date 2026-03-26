(function () {
    var toggleBtn = document.getElementById('cartToggleBtn');
    var overlay = document.getElementById('cartOverlay');
    var backdrop = document.getElementById('cartOverlayBackdrop');
    var closeBtn = document.getElementById('cartCloseBtn');
    var cartBadge = document.getElementById('cartBadge');
    var cartBody = document.getElementById('cartOverlayBody');
    var cartTotalValue = document.getElementById('cartTotalValue');
    var cartActionFlash = document.getElementById('cartActionFlash');
    var cartActionFlashTimer = null;

    if (!toggleBtn || !overlay || !backdrop || !closeBtn || !cartBody || !cartTotalValue || !cartBadge) {
        return;
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderCartEmpty(message) {
        return '<p class="mt-2 text-[0.95rem] text-[#2f2f2f]">' + escapeHtml(message) + '</p>';
    }

    function renderCartItem(item) {
        var title = escapeHtml(item.title || 'Event');
        var location = escapeHtml(item.location || '');
        var passDateLabel = escapeHtml(item.passDateLabel || '');
        var quantity = Number(item.quantity || 0);
        var unitPriceLabel = escapeHtml(item.unitPriceLabel || Number(item.unitPrice || 0).toFixed(2));
        var orderItemId = Number(item.orderItemId || 0);

        return [
            '<article class="mb-[10px] rounded-xl border border-[#ececec] bg-white px-3 py-[10px] text-[#171717]">',
                '<h3 class="m-0 text-[0.98rem] font-extrabold text-[#0f0f0f]">' + title + '</h3>',
                '<p class="my-[6px] mb-[10px] text-[0.9rem] text-[#2d2d2d]">' + location + '</p>',
                (passDateLabel !== ''
                    ? '<p class="my-[6px] mb-[10px] text-[0.9rem] font-semibold text-[#2d2d2d]">Date: ' + passDateLabel + '</p>'
                    : ''),
                '<div class="flex items-center justify-between gap-[10px]">',
                    '<div class="inline-flex items-center gap-2">',
                        '<span class="font-bold text-[#171717]">Qty: ' + quantity + ' x EUR ' + unitPriceLabel + '</span>',
                        '<div class="inline-flex items-center gap-[6px]" data-cart-qty-controls data-order-item-id="' + orderItemId + '" data-quantity="' + quantity + '">',
                            '<button type="button" class="h-[30px] w-[30px] rounded-lg border border-[#2f80ed] bg-[#2f80ed] font-bold text-white transition-colors duration-200 hover:bg-[#1d6ed8] disabled:cursor-wait disabled:opacity-60" data-cart-qty-button data-direction="decrease" aria-label="Decrease quantity for ' + title + '">-</button>',
                            '<button type="button" class="h-[30px] w-[30px] rounded-lg border border-[#2f80ed] bg-[#2f80ed] font-bold text-white transition-colors duration-200 hover:bg-[#1d6ed8] disabled:cursor-wait disabled:opacity-60" data-cart-qty-button data-direction="increase" aria-label="Increase quantity for ' + title + '">+</button>',
                        '</div>',
                    '</div>',
                    '<div class="flex items-center gap-2">',
                        '<form method="POST" action="/order/item/remove">',
                            '<input type="hidden" name="order_item_id" value="' + orderItemId + '">',
                            '<button type="submit" class="cursor-pointer rounded-lg border border-[#9f9f9f] bg-[#f3f3f3] px-[10px] py-[6px] font-bold text-[#111] transition-colors duration-200 hover:bg-[#e8e8e8] disabled:cursor-wait disabled:opacity-60">Remove</button>',
                        '</form>',
                    '</div>',
                '</div>',
            '</article>'
        ].join('');
    }

    function updateCartUI(cart) {
        if (!cart) {
            return;
        }

        var count = Number(cart.itemCount || 0);
        var totalLabel = typeof cart.totalLabel === 'string' ? cart.totalLabel : Number(cart.total || 0).toFixed(2);
        var items = Array.isArray(cart.items) ? cart.items : [];

        cartBadge.textContent = String(count);
        cartTotalValue.textContent = 'EUR ' + totalLabel;

        if (cartBody.dataset.loggedIn !== '1') {
            return;
        }

        if (items.length === 0) {
            cartBody.innerHTML = renderCartEmpty('Your cart is empty.');
            return;
        }

        cartBody.innerHTML = items.map(renderCartItem).join('');
    }

    function showCartActionFlash(message) {
        if (!cartActionFlash || !message) {
            return;
        }

        cartActionFlash.textContent = String(message);
        cartActionFlash.classList.remove('hidden');

        if (cartActionFlashTimer) {
            window.clearTimeout(cartActionFlashTimer);
        }

        cartActionFlashTimer = window.setTimeout(function () {
            cartActionFlash.classList.add('hidden');
        }, 2600);
    }

    function setOpen(isOpen) {
        overlay.classList.toggle('translate-x-full', !isOpen);
        backdrop.classList.toggle('hidden', !isOpen);
        overlay.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        backdrop.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    }

    window.HaarlemCart = {
        update: updateCartUI,
        open: function () { setOpen(true); },
        close: function () { setOpen(false); }
    };

    toggleBtn.addEventListener('click', function () {
        var isOpen = toggleBtn.getAttribute('aria-expanded') === 'true';
        setOpen(!isOpen);
    });

    closeBtn.addEventListener('click', function () {
        setOpen(false);
    });

    backdrop.addEventListener('click', function () {
        setOpen(false);
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });

    document.addEventListener('submit', function (event) {
        var form = event.target;
        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        var action = form.getAttribute('action') || '';
        if (action !== '/order/item/remove') {
            return;
        }

        event.preventDefault();

        var submitBtn = form.querySelector('button[type="submit"]');
        if (!(submitBtn instanceof HTMLButtonElement)) {
            return;
        }

        var originalLabel = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Removing...';

        fetch(action, {
            method: 'POST',
            body: new FormData(form),
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().catch(function () { return null; }).then(function (payload) {
                    return { response: response, payload: payload };
                });
            })
            .then(function (result) {
                var response = result.response;
                var payload = result.payload;

                if (!response.ok || !payload || payload.ok !== true) {
                    var redirect = payload && typeof payload.redirect === 'string' ? payload.redirect : '';
                    if (redirect) {
                        window.location.href = redirect;
                        return;
                    }

                    var message = payload && typeof payload.message === 'string'
                        ? payload.message
                        : 'Could not remove cart item.';
                    window.alert(message);
                    return;
                }

                updateCartUI(payload.cart || null);
                setOpen(true);
                showCartActionFlash(payload.message || 'Item removed from cart.');
            })
            .catch(function () {
                window.alert('Network error while removing from cart. Please try again.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = originalLabel || 'Remove';
            });
    }, true);

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        var button = target.closest('[data-cart-qty-button]');
        if (!(button instanceof HTMLButtonElement)) {
            return;
        }

        event.preventDefault();

        var controls = button.closest('[data-cart-qty-controls]');
        if (!(controls instanceof HTMLElement)) {
            return;
        }

        var orderItemId = Number(controls.dataset.orderItemId || 0);
        var currentQuantity = Number(controls.dataset.quantity || 0);
        var direction = button.getAttribute('data-direction') || '';
        var delta = direction === 'increase' ? 1 : (direction === 'decrease' ? -1 : 0);

        if (orderItemId <= 0 || currentQuantity <= 0 || delta === 0) {
            return;
        }

        var nextQuantity = Math.min(99, Math.max(1, currentQuantity + delta));
        if (nextQuantity === currentQuantity) {
            return;
        }

        var qtyButtons = controls.querySelectorAll('[data-cart-qty-button]');
        qtyButtons.forEach(function (qtyButton) {
            qtyButton.disabled = true;
        });

        var body = new FormData();
        body.append('order_item_id', String(orderItemId));
        body.append('quantity', String(nextQuantity));

        fetch('/order/item/quantity', {
            method: 'POST',
            body: body,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().catch(function () { return null; }).then(function (payload) {
                    return { response: response, payload: payload };
                });
            })
            .then(function (result) {
                var response = result.response;
                var payload = result.payload;

                if (!response.ok || !payload || payload.ok !== true) {
                    var redirect = payload && typeof payload.redirect === 'string' ? payload.redirect : '';
                    if (redirect) {
                        window.location.href = redirect;
                        return;
                    }

                    var message = payload && typeof payload.message === 'string'
                        ? payload.message
                        : 'Could not update cart quantity.';
                    window.alert(message);
                    return;
                }

                updateCartUI(payload.cart || null);
                setOpen(true);
            })
            .catch(function () {
                window.alert('Network error while updating quantity. Please try again.');
            })
            .finally(function () {
                qtyButtons.forEach(function (qtyButton) {
                    qtyButton.disabled = false;
                });
            });
    }, true);
})();
