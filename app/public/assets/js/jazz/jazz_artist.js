(() => {
  const tabs = Array.from(document.querySelectorAll('[data-artist-tab]'));
  const panels = Array.from(document.querySelectorAll('[data-artist-panel]'));

  if (tabs.length === 0 || panels.length === 0) return;

  function setActive(tabKey, pushUrl = true) {
    tabs.forEach(t => t.classList.toggle('is-active', t.dataset.artistTab === tabKey));
    panels.forEach(p => p.classList.toggle('is-hidden', p.dataset.artistPanel !== tabKey));

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