(function () {
  function ready(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback, { once: true });
    } else {
      callback();
    }
  }

  function toggleSolidHeader(hero) {
    var trigger = Math.max(64, hero.offsetHeight * 0.25);
    var body = document.body;

    function update() {
      if (window.scrollY > trigger) {
        body.classList.add('porto-hero-header-solid');
      } else {
        body.classList.remove('porto-hero-header-solid');
      }
    }

    update();
    window.addEventListener('scroll', update, { passive: true });
  }

  function setupSlider(hero) {
    var slides = Array.prototype.slice.call(hero.querySelectorAll('.porto-hero-banner__slide'));
    if (!slides.length) {
      return;
    }

    var dots = Array.prototype.slice.call(hero.querySelectorAll('[data-hero-nav]'));
    var prev = hero.querySelector('[data-hero-prev]');
    var next = hero.querySelector('[data-hero-next]');
    var autoplayDelay = parseInt(hero.getAttribute('data-hero-autoplay'), 10) || 0;
    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var index = 0;
    var autoplayTimer = null;

    function activate(target) {
      if (target === index || target < 0 || target >= slides.length) {
        return;
      }

      slides.forEach(function (slide, slideIndex) {
        var isActive = slideIndex === target;
        slide.classList.toggle('is-active', isActive);
        slide.setAttribute('aria-hidden', isActive ? 'false' : 'true');
      });

      dots.forEach(function (dot, dotIndex) {
        var isActive = dotIndex === target;
        dot.classList.toggle('is-active', isActive);
        if (isActive) {
          dot.setAttribute('aria-current', 'true');
        } else {
          dot.removeAttribute('aria-current');
        }
      });

      index = target;
    }

    function nextSlide() {
      var target = index + 1;
      if (target >= slides.length) {
        target = 0;
      }
      activate(target);
    }

    function prevSlide() {
      var target = index - 1;
      if (target < 0) {
        target = slides.length - 1;
      }
      activate(target);
    }

    function startAutoplay() {
      if (autoplayDelay < 1000 || prefersReducedMotion || slides.length < 2) {
        return;
      }
      stopAutoplay();
      autoplayTimer = window.setInterval(nextSlide, autoplayDelay);
    }

    function stopAutoplay() {
      if (autoplayTimer) {
        window.clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    }

    if (prev) {
      prev.addEventListener('click', function (event) {
        event.preventDefault();
        stopAutoplay();
        prevSlide();
      });
    }

    if (next) {
      next.addEventListener('click', function (event) {
        event.preventDefault();
        stopAutoplay();
        nextSlide();
      });
    }

    dots.forEach(function (dot) {
      dot.addEventListener('click', function (event) {
        event.preventDefault();
        var target = parseInt(dot.getAttribute('data-hero-nav'), 10);
        if (!Number.isNaN(target)) {
          stopAutoplay();
          activate(target);
        }
      });

      dot.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          dot.click();
        }
      });
    });

    hero.addEventListener('mouseenter', stopAutoplay);
    hero.addEventListener('mouseleave', startAutoplay);
    hero.addEventListener('focusin', stopAutoplay);
    hero.addEventListener('focusout', startAutoplay);

    activate(0);
    startAutoplay();
  }

  ready(function () {
    var hero = document.querySelector('.porto-hero-banner');
    if (!hero) {
      return;
    }

    toggleSolidHeader(hero);
    setupSlider(hero);
  });
})();
