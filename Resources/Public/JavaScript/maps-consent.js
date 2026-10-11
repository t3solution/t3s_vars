// Two-click privacy: load Google Maps iframe only after explicit click
document.addEventListener('click', (event) => {
  const button = event.target.closest('.t3s-contact__consent-btn');
  if (!button) {
    return;
  }
  const wrapper = button.closest('[data-t3s-map-src]');
  if (!wrapper) {
    return;
  }
  const iframe = document.createElement('iframe');
  iframe.src = wrapper.dataset.t3sMapSrc;
  iframe.title = wrapper.dataset.t3sMapTitle || 'Map';
  iframe.loading = 'lazy';
  iframe.referrerPolicy = 'no-referrer-when-downgrade';
  iframe.allowFullscreen = true;
  wrapper.replaceWith(iframe);
});
