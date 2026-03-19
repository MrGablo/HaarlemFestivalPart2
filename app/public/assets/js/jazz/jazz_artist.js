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

  const tabs = Array.from(document.querySelectorAll('[data-artist-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-artist-panel]'));

  if (tabs.length === 0 || panels.length === 0) return;

  function setActive(tabKey, pushUrl = true) {
    tabs.forEach((t) => {
      const isActive = t.dataset.artistTab === tabKey;
      const activeClass = t.dataset.activeClass || '';
      const inactiveClass = t.dataset.inactiveClass || '';

      if (activeClass) {
        t.classList.remove(...activeClass.split(' '));
      }
      if (inactiveClass) {
        t.classList.remove(...inactiveClass.split(' '));
      }

      if (isActive && activeClass) {
        t.classList.add(...activeClass.split(' '));
      }
      if (!isActive && inactiveClass) {
        t.classList.add(...inactiveClass.split(' '));
      }
    });

    panels.forEach(p => p.classList.toggle('hidden', p.dataset.artistPanel !== tabKey));

    if (pushUrl) {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tabKey);
      window.history.pushState({ tab: tabKey }, '', url.toString());
    }
  }

  tabs.forEach(t => {
    t.addEventListener('click', (e) => {
      e.preventDefault();
      setActive(t.dataset.artistTab, true);
    });
  });

  window.addEventListener('popstate', () => {
    const url = new URL(window.location.href);
    const tab = url.searchParams.get('tab') || 'events';
    setActive(tab, false);
  });
})();