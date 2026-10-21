/* Shared admin UI contract. Page scripts retain their business behavior. */
(() => {
  const registry = JSON.parse(document.getElementById('kdrk-ui-icons')?.textContent || '{}');
  const icon = (name, size = 16) => `<svg class="ui-icon" viewBox="0 0 24 24" width="${size}" height="${size}" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${registry[name] || registry.info || ''}</svg>`;
  const enhance = (root = document) => {
    root.querySelectorAll('[data-ui-icon]').forEach(control => {
      if (!control.querySelector('svg')) control.insertAdjacentHTML('afterbegin', icon(control.dataset.uiIcon));
    });
    root.querySelectorAll('.save-bar .btn-primary').forEach(control => {
      if (!control.querySelector('svg')) control.insertAdjacentHTML('afterbegin', icon('check'));
    });
    root.querySelectorAll('.btn-icon[aria-label]').forEach(control => {
      if (!control.title) control.title = control.getAttribute('aria-label');
    });
  };
  // Kart/Tablo tercihi çerezde durur; sunucu (admin_list_view) sayfayı ilk açılışta doğru görünümle üretir.
  const rememberView = (key, view) => {
    if (!/^[a-z0-9-]+$/.test(key) || !['tablo', 'kart'].includes(view)) return;
    document.cookie = `kdrk_view_${key}=${view}; path=/yonetim; max-age=31536000; SameSite=Lax${location.protocol === 'https:' ? '; Secure' : ''}`;
  };
  document.addEventListener('click', event => {
    const link = event.target.closest('[data-view-switch] a[data-view]');
    if (!link || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
    const control = link.closest('[data-view-switch]');
    const key = control.dataset.viewSwitch;
    const lists = document.querySelectorAll(`[data-view-key="${key}"]`);
    if (!lists.length) return;
    event.preventDefault();
    const view = link.dataset.view;
    lists.forEach(list => { list.dataset.listView = view; });
    control.querySelectorAll('a[data-view]').forEach(option => {
      if (option === link) option.setAttribute('aria-current', 'true'); else option.removeAttribute('aria-current');
    });
    rememberView(key, view);
    const url = new URL(location.href);
    if (url.searchParams.has('gorunum')) { url.searchParams.delete('gorunum'); history.replaceState(null, '', url); }
  });
  window.KdrkUI = Object.freeze({ icon, enhance, rememberView });
  enhance();
})();
