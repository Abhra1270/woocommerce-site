(function () {
  function onReady(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
      callback();
    }
  }

  function updateHeaderState() {
    if (!document.body.classList.contains('porto-hero-overlap')) {
      return;
    }

    var trigger = Math.max(48, window.innerHeight * 0.15);

    function toggle() {
      if (window.scrollY > trigger) {
        document.body.classList.add('porto-hero-header-solid');
      } else {
        document.body.classList.remove('porto-hero-header-solid');
      }
    }

    toggle();
    window.addEventListener('scroll', toggle, { passive: true });
    window.addEventListener('resize', function () {
      trigger = Math.max(48, window.innerHeight * 0.15);
      toggle();
    }, { passive: true });
  }

  onReady(updateHeaderState);
})();
