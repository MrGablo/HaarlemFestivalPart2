(() => {
  const tabs = Array.from(document.querySelectorAll('[data-artist-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-artist-panel]'));

  if (tabs.length === 0 || panels.length === 0) return;

  function setActive(tabKey, pushUrl = true) {
    tabs.forEach(t => {
      const active = t.dataset.artistTab === tabKey;
      t.classList.toggle('is-active', active);
      t.classList.toggle('text-[#f7c600]', active);
      t.classList.toggle('underline', active);
      t.classList.toggle('underline-offset-[6px]', active);
    });

    panels.forEach(p => {
      const hidden = p.dataset.artistPanel !== tabKey;
      p.classList.toggle('is-hidden', hidden);
      p.classList.toggle('hidden', hidden);
    });

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