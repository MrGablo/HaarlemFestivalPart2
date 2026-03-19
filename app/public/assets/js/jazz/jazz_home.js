(() => {
    const grid = document.getElementById('eventGrid');
    if (!grid) return;

    const cards = Array.from(grid.querySelectorAll('.event-card'));
    cards.forEach((card, index) => {
        card.dataset.originalIndex = String(index);
    });
    const hallBtns = Array.from(document.querySelectorAll('.hall-chip'));
    const dayBtns = Array.from(document.querySelectorAll('.day-chip'));

    const toggleMoreBtn = document.getElementById('toggleMoreBtn');
    const allEventsBtn = document.getElementById('allEventsBtn');

    const COLLAPSE_LIMIT = 4;
    const byDateValue = hallBtns[0]?.dataset.hall || 'By date';

    // Default: "By date" (first hall tab) + "All Days"
    let activeHall = byDateValue;
    let activeDay = dayBtns.find(b => b.dataset.day === 'all')?.dataset.day || dayBtns[0]?.dataset.day || 'all';
    let expanded = false;

    function setActive(btns, clicked) {
        btns.forEach((b) => {
            const activeClass = b.dataset.activeClass || '';
            const inactiveClass = b.dataset.inactiveClass || '';

            if (activeClass) {
                b.classList.remove(...activeClass.split(' '));
            }
            if (inactiveClass) {
                b.classList.add(...inactiveClass.split(' '));
            }
        });

        const clickedActiveClass = clicked.dataset.activeClass || '';
        const clickedInactiveClass = clicked.dataset.inactiveClass || '';
        if (clickedInactiveClass) {
            clicked.classList.remove(...clickedInactiveClass.split(' '));
        }
        if (clickedActiveClass) {
            clicked.classList.add(...clickedActiveClass.split(' '));
        }
    }

    function getBtnByData(btns, key, value) {
        return btns.find(b => b.dataset[key] === value) || null;
    }

    function matches(card) {
        const hall = card.dataset.hall;
        const day = card.dataset.day;
        const dayName = card.dataset.dayName;

        // "By date" means ALL halls (no hall filter)
        const hallOk = (activeHall === byDateValue) ? true : (hall === activeHall);

        // Match by exact date value, with day-name fallback for legacy content.
        const dayOk = (activeDay === 'all') ? true : (day === activeDay || dayName === activeDay);

        return hallOk && dayOk;
    }

    function cardStartTs(card) {
        const value = Number(card.dataset.startTs || 0);
        return Number.isFinite(value) ? value : 0;
    }

    function cardOriginalIndex(card) {
        const value = Number(card.dataset.originalIndex || 0);
        return Number.isFinite(value) ? value : 0;
    }

    function apply() {
        const visible = [];

        // 1) filter
        cards.forEach(c => {
            const ok = matches(c);
            c.classList.toggle('hidden', !ok);
            if (ok) visible.push(c);
        });

        // 2) order visible cards
        const ordered = visible.slice().sort((a, b) => {
            if (activeHall === byDateValue) {
                const tsDiff = cardStartTs(a) - cardStartTs(b);
                if (tsDiff !== 0) {
                    return tsDiff;
                }
            }

            return cardOriginalIndex(a) - cardOriginalIndex(b);
        });

        ordered.forEach((card) => {
            grid.appendChild(card);
        });

        // 3) collapse/expand
        if (!expanded && ordered.length > COLLAPSE_LIMIT) {
            ordered.forEach((c, i) => c.classList.toggle('hidden', i >= COLLAPSE_LIMIT));
            if (toggleMoreBtn) {
                toggleMoreBtn.classList.remove('hidden');
                toggleMoreBtn.textContent = 'Show more';
            }
        } else {
            if (toggleMoreBtn) {
                if (ordered.length > COLLAPSE_LIMIT) {
                    toggleMoreBtn.classList.remove('hidden');
                    toggleMoreBtn.textContent = expanded ? 'Show less' : 'Show more';
                } else {
                    toggleMoreBtn.classList.add('hidden');
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
            (activeHall === byDateValue) &&
            (activeDay === 'all');

        if (!alreadyFull) {
            // Reset filters to full schedule
            const byDateBtn = getBtnByData(hallBtns, 'hall', byDateValue) || hallBtns[0];
            if (byDateBtn) {
                activeHall = byDateBtn.dataset.hall;
                setActive(hallBtns, byDateBtn);
            } else {
                activeHall = byDateValue;
            }

            const allDaysBtn = getBtnByData(dayBtns, 'day', 'all') || dayBtns[0];
            if (allDaysBtn) {
                activeDay = allDaysBtn.dataset.day;
                setActive(dayBtns, allDaysBtn);
            } else {
                activeDay = 'all';
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