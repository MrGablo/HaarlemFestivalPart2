(function () {
    var toastTimer = null;

    function getToast() {
        return document.getElementById('cartToast');
    }

    function showCartToast() {
        var toast = getToast();
        if (!toast) {
            return;
        }

        toast.classList.remove('hidden');
        if (toastTimer) {
            window.clearTimeout(toastTimer);
        }

        toastTimer = window.setTimeout(function () {
            toast.classList.add('hidden');
        }, 3800);
    }

    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!(target instanceof Element)) {
            return;
        }

        var toast = target.closest('#cartToast');
        if (!(toast instanceof HTMLElement)) {
            return;
        }

            if (window.HaarlemCart && typeof window.HaarlemCart.open === 'function') {
                window.HaarlemCart.open();
            }
            toast.classList.add('hidden');
    });

    function addTicketWithoutReload(form) {
        var submitBtn = form.querySelector('button[type="submit"]');
        if (!(submitBtn instanceof HTMLButtonElement)) {
            return;
        }

        var originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Adding...';

        fetch(form.action, {
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
                        : 'Could not add ticket to cart.';
                    window.alert(message);
                    return;
                }

                if (window.HaarlemCart && typeof window.HaarlemCart.update === 'function') {
                    window.HaarlemCart.update(payload.cart || null);
                }

                showCartToast();

                if (document.body && document.body.classList.contains('dance-open-cart-on-add')
                    && window.HaarlemCart && typeof window.HaarlemCart.open === 'function') {
                    window.HaarlemCart.open();
                }
            })
            .catch(function () {
                window.alert('Network error while adding ticket. Please try again.');
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            });
    }

    // Capture submit at document level to guarantee no full page navigation.
    document.addEventListener('submit', function (event) {
        var target = event.target;
        if (!(target instanceof HTMLFormElement)) {
            return;
        }

        if (!target.classList.contains('ticket-form')) {
            return;
        }

        event.preventDefault();
        addTicketWithoutReload(target);
    }, true);
})();