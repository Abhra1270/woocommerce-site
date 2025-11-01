<?php
add_action( 'wp_enqueue_scripts', function () {
    // Enqueue parent theme style.
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

    $child_dependencies = [ 'parent-style' ];

    if ( is_front_page() ) {
        wp_enqueue_style(
            'tilottama-hero-fonts',
            'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500&display=swap',
            [],
            null
        );

        wp_enqueue_style(
            'tilottama-hero-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css',
            [],
            '10.3.1'
        );

        $child_dependencies[] = 'tilottama-hero-fonts';
        $child_dependencies[] = 'tilottama-hero-swiper';
    }

    // Enqueue the child theme stylesheet with contextual dependencies.
    wp_enqueue_style( 'tilottama-child-style', get_stylesheet_directory_uri() . '/style.css', $child_dependencies, wp_get_theme()->get( 'Version' ) );

    if ( is_front_page() ) {
        wp_enqueue_script(
            'tilottama-hero-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js',
            [],
            '10.3.1',
            true
        );

        wp_register_script( 'tilottama-hero', false, [ 'tilottama-hero-swiper' ], null, true );
        wp_enqueue_script( 'tilottama-hero' );

        $hero_script = <<<'JS'
( function () {
    var initializeHero = function () {
        if ( typeof Swiper === 'undefined' ) {
            return;
        }

        var hero = document.querySelector( '.tilo-hero' );

        if ( ! hero ) {
            return;
        }

        var sliderEl = hero.querySelector( '.tilo-hero__slider' );
        var nextButton = hero.querySelector( '.tilo-hero__next' );
        var paginationEl = hero.querySelector( '.tilo-hero__pagination' );
        var progressEl = hero.querySelector( '.tilo-hero__progress' );

        if ( ! sliderEl ) {
            return;
        }

        var baseSlides = hero.querySelectorAll( '.swiper-slide[data-slide-index]' );

        if ( progressEl ) {
            progressEl.innerHTML = '';

            for ( var i = 0; i < baseSlides.length; i++ ) {
                var dot = document.createElement( 'span' );
                dot.setAttribute( 'aria-hidden', 'true' );
                progressEl.appendChild( dot );
            }
        }

        var updateProgress = function ( swiperInstance ) {
            if ( ! progressEl ) {
                return;
            }

            var dots = progressEl.querySelectorAll( 'span' );

            for ( var j = 0; j < dots.length; j++ ) {
                var isActive = dots.length > 0 && ( j === ( swiperInstance.realIndex % dots.length ) );
                dots[ j ].classList.toggle( 'is-active', isActive );
            }
        };

        var syncVideos = function ( swiperInstance ) {
            for ( var k = 0; k < swiperInstance.slides.length; k++ ) {
                var slide = swiperInstance.slides[ k ];
                var video = slide.querySelector( 'video' );

                if ( ! video ) {
                    continue;
                }

                if ( slide.classList.contains( 'swiper-slide-active' ) ) {
                    var playPromise = video.play();

                    if ( playPromise && typeof playPromise.catch === 'function' ) {
                        playPromise.catch( function () {
                            // Ignore autoplay restrictions.
                        } );
                    }
                } else {
                    video.pause();
                    video.currentTime = 0;
                }
            }
        };

        var swiper = new Swiper( sliderEl, {
            loop: true,
            speed: 600,
            spaceBetween: 16,
            grabCursor: true,
            resistanceRatio: 0.85,
            slidesPerView: 1,
            watchSlidesProgress: true,
            keyboard: {
                enabled: true,
                onlyInViewport: true
            },
            autoplay: {
                delay: 5000,
                disableOnInteraction: false,
                pauseOnMouseEnter: false
            },
            pagination: {
                el: paginationEl,
                clickable: true
            },
            on: {
                init: function ( swiperInstance ) {
                    updateProgress( swiperInstance );
                    syncVideos( swiperInstance );
                }
            }
        } );

        swiper.on( 'slideChangeTransitionEnd', function ( swiperInstance ) {
            updateProgress( swiperInstance );
            syncVideos( swiperInstance );
        } );

        var resumeTimer = null;

        var clearResumeTimer = function () {
            if ( resumeTimer ) {
                window.clearTimeout( resumeTimer );
                resumeTimer = null;
            }
        };

        var scheduleResume = function () {
            clearResumeTimer();

            resumeTimer = window.setTimeout( function () {
                if ( swiper.autoplay ) {
                    swiper.autoplay.start();
                }
            }, 8000 );
        };

        var stopAutoplay = function () {
            if ( swiper.autoplay && swiper.autoplay.running ) {
                swiper.autoplay.stop();
            }
        };

        var pauseAutoplay = function () {
            stopAutoplay();
            scheduleResume();
        };

        if ( nextButton ) {
            nextButton.addEventListener( 'click', function ( event ) {
                event.preventDefault();
                pauseAutoplay();
                swiper.slideNext( 600 );
            } );
        }

        var interactiveTargets = [ sliderEl, paginationEl, nextButton ];
        var interactionEvents = [ 'touchstart', 'pointerdown', 'focusin', 'keydown' ];

        for ( var targetIndex = 0; targetIndex < interactiveTargets.length; targetIndex++ ) {
            var target = interactiveTargets[ targetIndex ];

            if ( ! target ) {
                continue;
            }

            for ( var eventIndex = 0; eventIndex < interactionEvents.length; eventIndex++ ) {
                var eventName = interactionEvents[ eventIndex ];
                target.addEventListener( eventName, pauseAutoplay, eventName === 'touchstart' ? { passive: true } : false );
            }
        }

        hero.addEventListener( 'mouseenter', function () {
            stopAutoplay();
            clearResumeTimer();
        } );

        hero.addEventListener( 'mouseleave', function () {
            scheduleResume();
        } );

        document.addEventListener( 'visibilitychange', function () {
            if ( document.hidden ) {
                stopAutoplay();
                clearResumeTimer();

                for ( var s = 0; s < swiper.slides.length; s++ ) {
                    var slideWithVideo = swiper.slides[ s ];
                    var slideVideo = slideWithVideo.querySelector( 'video' );

                    if ( slideVideo ) {
                        slideVideo.pause();
                    }
                }
            } else {
                syncVideos( swiper );
                scheduleResume();
            }
        } );
    };

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initializeHero );
    } else {
        initializeHero();
    }
} )();
JS;

        wp_add_inline_script( 'tilottama-hero', $hero_script );
    }
} );
