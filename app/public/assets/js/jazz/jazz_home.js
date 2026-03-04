(() => {
  const grid = document.getElementById('eventGrid');
  if (!grid) return;

  const cards = Array.from(grid.querySelectorAll('.event-card'));
  const hallBtns = Array.from(document.querySelectorAll('.hall-chip'));
  const dayBtns  = Array.from(document.querySelectorAll('.day-chip'));
  const showMoreBtn = document.getElementById('showMoreBtn');

  let activeHall = hallBtns[0]?.dataset.hall || 'Main Hall';
  let activeDay  = dayBtns[0]?.dataset.day || 'All Days';
  let expanded = false;

  const COLLAPSE_LIMIT = 4;

  function setActive(btns, clicked) {
    btns.forEach(b => b.classList.remove('is-active'));
    clicked.classList.add('is-active');
  }

  function matches(card) {
    const hall = card.dataset.hall;
    const day  = card.dataset.day;

    const hallOk = (hall === activeHall);
    const dayOk  = (activeDay === 'All Days') ? true : (day === activeDay);

    return hallOk && dayOk;
  }

  function apply() {
    const visible = [];

    cards.forEach(c => {
      const ok = matches(c);
      c.classList.toggle('is-hidden', !ok);
      if (ok) visible.push(c);
    });

    // collapse if not expanded
    if (!expanded && visible.length > COLLAPSE_LIMIT) {
      visible.forEach((c, i) => c.classList.toggle('is-hidden', i >= COLLAPSE_LIMIT));
      showMoreBtn?.classList.remove('is-hidden');
    } else {
      showMoreBtn?.classList.add('is-hidden');
    }
  }

  hallBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      activeHall = btn.dataset.hall;
      setActive(hallBtns, btn);
      expanded = false;
      apply();
    });
  });

  dayBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      activeDay = btn.dataset.day;
      setActive(dayBtns, btn);
      expanded = false;
      apply();
    });
  });

  showMoreBtn?.addEventListener('click', () => {
    expanded = true;
    apply();
    showMoreBtn.classList.add('is-hidden');
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