(() => {
    const toast = document.getElementById('cartToast');
    let toastTimer = null;

    function showCartToast() {
        if (!toast) return;

        toast.classList.remove('hidden');
        if (toastTimer) {
            window.clearTimeout(toastTimer);
        }

        toastTimer = window.setTimeout(() => {
            toast.classList.add('hidden');
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
        if (!(submitBtn instanceof HTMLButtonElement)) {
            return;
        }

        const originalLabel = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Adding...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });

            const payload = await response.json().catch(() => null);

            if (!response.ok || !payload || payload.ok !== true) {
                const redirect = payload && typeof payload.redirect === 'string' ? payload.redirect : '';
                if (redirect) {
                    window.location.href = redirect;
                    return;
                }

                const message = payload && typeof payload.message === 'string'
                    ? payload.message
                    : 'Could not add ticket to cart.';
                window.alert(message);
                return;
            }

            if (window.HaarlemCart && typeof window.HaarlemCart.update === 'function') {
                window.HaarlemCart.update(payload.cart || null);
            }

            showCartToast();
        } catch (_error) {
            window.alert('Network error while adding ticket. Please try again.');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = originalLabel || 'Ticket';
        }
    }

    // Capture submit at document level to guarantee no full page navigation.
    document.addEventListener('submit', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLFormElement)) {
            return;
        }

        if (!target.classList.contains('ticket-form')) {
            return;
        }

        event.preventDefault();
        void addTicketWithoutReload(target);
    }, true);

    const grid = document.getElementById('eventGrid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.event-card'));
    const hallBtns = Array.from(document.querySelectorAll('.hall-chip'));
    const dayBtns = Array.from(document.querySelectorAll('.day-chip'));

    const toggleMoreBtn = document.getElementById('toggleMoreBtn');
    const allEventsBtn = document.getElementById('allEventsBtn');

    const COLLAPSE_LIMIT = 4;

    // Default: "By date" (first hall tab) + "All Days"
    let activeHall = hallBtns[0]?.dataset.hall || 'By date';
    let activeDay = dayBtns.find(b => b.dataset.day === 'All Days')?.dataset.day || 'All Days';
    let expanded = false;

    function setActive(btns, clicked) {
        btns.forEach(b => b.classList.remove('is-active'));
        clicked.classList.add('is-active');
    }

    function getBtnByData(btns, key, value) {
        return btns.find(b => b.dataset[key] === value) || null;
    }

    function matches(card) {
        const hall = card.dataset.hall;
        const day = card.dataset.day;

        // "By date" means ALL halls (no hall filter)
        const hallOk = (activeHall === 'By date') ? true : (hall === activeHall);

        const dayOk = (activeDay === 'All Days') ? true : (day === activeDay);

        return hallOk && dayOk;
    }

    function apply() {
        const visible = [];

        // 1) filter
        cards.forEach(c => {
            const ok = matches(c);
            c.classList.toggle('is-hidden', !ok);
            if (ok) visible.push(c);
        });

        // 2) collapse/expand
        if (!expanded && visible.length > COLLAPSE_LIMIT) {
            visible.forEach((c, i) => c.classList.toggle('is-hidden', i >= COLLAPSE_LIMIT));
            if (toggleMoreBtn) {
                toggleMoreBtn.classList.remove('is-hidden');
                toggleMoreBtn.textContent = 'Show more';
            }
        } else {
            if (toggleMoreBtn) {
                if (visible.length > COLLAPSE_LIMIT) {
                    toggleMoreBtn.classList.remove('is-hidden');
                    toggleMoreBtn.textContent = expanded ? 'Show less' : 'Show more';
                } else {
                    toggleMoreBtn.classList.add('is-hidden');
                }
            }
        }
    }

    // hall click
    hallBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            activeHall = btn.dataset.hall;
            setActive(hallBtns, btn);
            expanded = false;
            apply();
        });
    });

    // day click
    dayBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            activeDay = btn.dataset.day;
            setActive(dayBtns, btn);
            expanded = false;
            apply();
        });
    });

    // show more / show less toggle
    toggleMoreBtn?.addEventListener('click', () => {
        expanded = !expanded;
        apply();
    });

    // All Events: reset to By date + All Days + expanded
    allEventsBtn?.addEventListener('click', () => {
        const alreadyFull =
            (activeHall === 'By date') &&
            (activeDay === 'All Days');

        if (!alreadyFull) {
            // Reset filters to full schedule
            const byDateBtn = getBtnByData(hallBtns, 'hall', 'By date') || hallBtns[0];
            if (byDateBtn) {
                activeHall = byDateBtn.dataset.hall;
                setActive(hallBtns, byDateBtn);
            } else {
                activeHall = 'By date';
            }

            const allDaysBtn = getBtnByData(dayBtns, 'day', 'All Days') || dayBtns[0];
            if (allDaysBtn) {
                activeDay = allDaysBtn.dataset.day;
                setActive(dayBtns, allDaysBtn);
            } else {
                activeDay = 'All Days';
            }
        }

        // Always expand after clicking All Events
        expanded = true;
        apply();

        document.getElementById('schedule')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });

    // Smooth scroll (Buy ticket -> Day Ticket Pass)
    document.querySelectorAll('[data-scroll-target]').forEach(el => {
        el.addEventListener('click', () => {
            const sel = el.getAttribute('data-scroll-target');
            const target = sel ? document.querySelector(sel) : null;
            if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    apply();
})();