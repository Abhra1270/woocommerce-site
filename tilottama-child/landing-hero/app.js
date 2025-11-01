(function () {
  'use strict';

  if (typeof Swiper === 'undefined') {
    console.warn('Swiper is required for the Tilo hero carousel.');
    return;
  }

  const parseInstances = () => {
    const data = window.tiloHeroData;
    if (!data) {
      return [];
    }

    if (Array.isArray(data.instances)) {
      return data.instances;
    }

    if (Array.isArray(data.slides)) {
      return [data];
    }

    return [];
  };

  const dataInstances = parseInstances();

  const renderProgress = (progressEl, count) => {
    if (!progressEl) {
      return;
    }

    progressEl.innerHTML = '';

    if (!count) {
      return;
    }

    for (let index = 0; index < count; index += 1) {
      const dot = document.createElement('span');
      dot.setAttribute('aria-hidden', 'true');
      progressEl.appendChild(dot);
    }
  };

  const updateProgressActive = (progressEl, activeIndex) => {
    if (!progressEl) {
      return;
    }

    const dots = progressEl.querySelectorAll('span');
    dots.forEach((dot, index) => {
      dot.classList.toggle('is-active', index === activeIndex);
    });
  };

  document.querySelectorAll('.tilo-hero').forEach((heroEl, instanceIndex) => {
    const sliderEl = heroEl.querySelector('.tilo-hero__slider');
    const nextButton = heroEl.querySelector('.tilo-hero__next');
    const paginationEl = heroEl.querySelector('.tilo-hero__pagination');
    const progressEl = heroEl.querySelector('.tilo-hero__progress');

    if (!sliderEl || !nextButton || !paginationEl) {
      return;
    }

    const uniqueSlides = sliderEl.querySelectorAll('.swiper-slide[data-slide-index]');
    const instanceData = dataInstances[instanceIndex] || dataInstances[0] || null;
    const slidesCount = uniqueSlides.length || (Array.isArray(instanceData?.slides) ? instanceData.slides.length : 0);

    renderProgress(progressEl, slidesCount);

    const handleVideos = (swiperInstance) => {
      swiperInstance.slides.forEach((slideEl) => {
        const video = slideEl.querySelector('video');
        if (!video) {
          return;
        }

        if (slideEl.classList.contains('swiper-slide-active')) {
          const playPromise = video.play();
          if (playPromise && typeof playPromise.catch === 'function') {
            playPromise.catch(() => {
              /* ignore autoplay restrictions */
            });
          }
        } else {
          video.pause();
          video.currentTime = 0;
        }
      });
    };

    const swiper = new Swiper(sliderEl, {
      loop: true,
      speed: 600,
      spaceBetween: 16,
      grabCursor: true,
      touchReleaseOnEdges: true,
      resistanceRatio: 0.85,
      slidesPerView: 1,
      watchSlidesProgress: true,
      keyboard: {
        enabled: true,
        onlyInViewport: true,
      },
      autoplay: {
        delay: 5000,
        disableOnInteraction: false,
        pauseOnMouseEnter: false,
      },
      pagination: {
        el: paginationEl,
        clickable: true,
      },
      on: {
        init(swiperInstance) {
          updateProgressActive(progressEl, swiperInstance.realIndex);
          handleVideos(swiperInstance);
        },
      },
    });

    swiper.on('slideChangeTransitionEnd', (swiperInstance) => {
      const dotCount = progressEl ? progressEl.childElementCount : 0;
      if (dotCount) {
        updateProgressActive(progressEl, swiperInstance.realIndex % dotCount);
      } else {
        updateProgressActive(progressEl, swiperInstance.realIndex);
      }
      handleVideos(swiperInstance);
    });

    let resumeTimer = null;

    const clearResumeTimer = () => {
      if (resumeTimer) {
        window.clearTimeout(resumeTimer);
        resumeTimer = null;
      }
    };

    const scheduleResume = () => {
      clearResumeTimer();
      resumeTimer = window.setTimeout(() => {
        if (swiper.autoplay) {
          swiper.autoplay.start();
        }
      }, 8000);
    };

    const stopAutoplay = () => {
      if (swiper.autoplay && swiper.autoplay.running) {
        swiper.autoplay.stop();
      }
    };

    const pauseAutoplay = () => {
      stopAutoplay();
      scheduleResume();
    };

    heroEl.addEventListener('mouseenter', () => {
      stopAutoplay();
      clearResumeTimer();
    });

    heroEl.addEventListener('mouseleave', () => {
      scheduleResume();
    });

    const interactionEvents = ['touchstart', 'pointerdown', 'focusin', 'keydown'];
    interactionEvents.forEach((eventName) => {
      const options = eventName === 'touchstart' ? { passive: true } : false;
      sliderEl.addEventListener(eventName, pauseAutoplay, options);
      nextButton.addEventListener(eventName, pauseAutoplay, options);
      if (paginationEl) {
        paginationEl.addEventListener(eventName, pauseAutoplay, options);
      }
    });

    nextButton.addEventListener('click', (event) => {
      event.preventDefault();
      pauseAutoplay();
      swiper.slideNext(600);
    });

    if (paginationEl) {
      paginationEl.addEventListener(
        'click',
        (event) => {
          if (event.target && event.target.classList.contains('swiper-pagination-bullet')) {
            pauseAutoplay();
          }
        },
        true
      );
    }

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stopAutoplay();
        clearResumeTimer();
        swiper.slides.forEach((slideEl) => {
          const video = slideEl.querySelector('video');
          if (video) {
            video.pause();
          }
        });
      } else {
        handleVideos(swiper);
        scheduleResume();
      }
    });
  });
})();
