<?php
/**
 * Landing hero shortcode for Tilottama child theme.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! function_exists( 'tilo_landing_hero_register_assets' ) ) {
    /**
     * Register front-end assets for the hero component.
     */
    function tilo_landing_hero_register_assets() {
        static $registered = false;

        if ( $registered ) {
            return;
        }

        $registered = true;

        $theme_dir = get_stylesheet_directory();
        $theme_uri = get_stylesheet_directory_uri();

        wp_register_style(
            'tilo-hero-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css',
            array(),
            '10.3.1'
        );

        wp_register_script(
            'tilo-hero-swiper',
            'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js',
            array(),
            '10.3.1',
            true
        );

        $stylesheet = $theme_dir . '/landing-hero/styles.css';
        $script     = $theme_dir . '/landing-hero/app.js';

        wp_register_style(
            'tilo-hero-styles',
            $theme_uri . '/landing-hero/styles.css',
            array( 'tilo-hero-swiper' ),
            file_exists( $stylesheet ) ? filemtime( $stylesheet ) : null
        );

        wp_register_style(
            'tilo-hero-fonts',
            'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@500;600&family=Inter:wght@400;500&display=swap',
            array(),
            null
        );

        wp_register_script(
            'tilo-hero-app',
            $theme_uri . '/landing-hero/app.js',
            array( 'tilo-hero-swiper' ),
            file_exists( $script ) ? filemtime( $script ) : null,
            true
        );
    }
}

if ( ! function_exists( 'tilo_landing_hero_enqueue_assets' ) ) {
    /**
     * Enqueue the hero assets with inline defaults and localization data.
     *
     * @param array $slides Slides passed to the shortcode instance.
     */
    function tilo_landing_hero_enqueue_assets( $slides ) {
        static $inline_variables_added = false;
        static $instances              = array();

        tilo_landing_hero_register_assets();

        wp_enqueue_style( 'tilo-hero-swiper' );
        wp_enqueue_style( 'tilo-hero-fonts' );
        wp_enqueue_style( 'tilo-hero-styles' );
        wp_enqueue_script( 'tilo-hero-swiper' );
        wp_enqueue_script( 'tilo-hero-app' );

        if ( ! $inline_variables_added ) {
            $inline_variables_added = true;
            wp_add_inline_style(
                'tilo-hero-styles',
                '.tilo-hero{--tilo-bg:#F7EFEA;--tilo-card:#FFE8A8;--tilo-text:#1F1F1F;--tilo-muted:#6B6B6B;--tilo-white:#FFFFFF;--tilo-radius-xl:22px;--tilo-radius-lg:18px;--tilo-radius-md:14px;--tilo-shadow-sm:0 2px 6px rgba(0,0,0,.06);--tilo-shadow-md:0 10px 24px rgba(0,0,0,.08);}'
            );
        }

        $instances[] = array(
            'slides' => $slides,
        );

        wp_localize_script(
            'tilo-hero-app',
            'tiloHeroData',
            array(
                'instances' => $instances,
            )
        );
    }
}

if ( ! function_exists( 'tilo_landing_hero_shortcode' ) ) {
    /**
     * Render the landing hero shortcode.
     *
     * @param array $atts Shortcode attributes.
     *
     * @return string
     */
    function tilo_landing_hero_shortcode( $atts = array() ) {
        $defaults = array(
            'slides' => '[]',
        );

        $atts = shortcode_atts( $defaults, $atts, 'tilo_landing_hero' );

        $decoded = json_decode( html_entity_decode( $atts['slides'] ), true );
        $slides  = array();

        if ( is_array( $decoded ) ) {
            foreach ( $decoded as $slide ) {
                if ( empty( $slide['src'] ) ) {
                    continue;
                }

                $type = isset( $slide['type'] ) ? strtolower( $slide['type'] ) : 'image';

                if ( ! in_array( $type, array( 'image', 'video' ), true ) ) {
                    $type = 'image';
                }

                $slides[] = array(
                    'type'     => $type,
                    'src'      => esc_url_raw( $slide['src'] ),
                    'poster'   => ! empty( $slide['poster'] ) ? esc_url_raw( $slide['poster'] ) : '',
                    'caption'  => isset( $slide['caption'] ) ? sanitize_text_field( $slide['caption'] ) : '',
                    'alt'      => isset( $slide['alt'] ) ? sanitize_text_field( $slide['alt'] ) : '',
                );
            }
        }

        if ( empty( $slides ) ) {
            $slides = array(
                array(
                    'type'    => 'image',
                    'src'     => 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=900&q=80',
                    'caption' => 'Airy pastel sneaker',
                    'alt'     => 'Soft pastel sneaker on a color block background',
                ),
                array(
                    'type'    => 'image',
                    'src'     => 'https://images.unsplash.com/photo-1528701800489-20be3c6c51f4?auto=format&fit=crop&w=900&q=80',
                    'caption' => 'Studio close-up',
                    'alt'     => 'Minimalist sneaker photographed in studio lighting',
                ),
                array(
                    'type'    => 'video',
                    'src'     => 'https://cdn.coverr.co/videos/coverr-stylish-shoes-1080p.mp4',
                    'caption' => 'Sneaker in motion',
                    'poster'  => '',
                ),
            );
        }

        tilo_landing_hero_enqueue_assets( $slides );

        ob_start();
        ?>
        <section class="tilo-hero" aria-labelledby="tilo-hero-title" data-tilo-hero>
            <div class="tilo-hero__device" role="region" aria-label="Trending shoes carousel">
                <div class="tilo-hero__slider swiper" aria-live="polite">
                    <div class="swiper-wrapper">
                        <?php foreach ( $slides as $index => $slide ) : ?>
                            <article class="tilo-hero__slide swiper-slide" data-slide-index="<?php echo esc_attr( $index ); ?>" data-caption="<?php echo esc_attr( $slide['caption'] ); ?>" aria-roledescription="slide">
                                <?php if ( 'video' === $slide['type'] ) : ?>
                                    <video class="tilo-hero__video" preload="metadata" muted playsinline loop<?php echo ! empty( $slide['poster'] ) ? ' poster="' . esc_url( $slide['poster'] ) . '"' : ''; ?>>
                                        <source src="<?php echo esc_url( $slide['src'] ); ?>" type="video/mp4" />
                                        <?php esc_html_e( 'Your browser does not support the video tag.', 'tilottama-child' ); ?>
                                    </video>
                                <?php else : ?>
                                    <picture>
                                        <img src="<?php echo esc_url( $slide['src'] ); ?>" alt="<?php echo esc_attr( $slide['alt'] ?: $slide['caption'] ); ?>" loading="lazy" />
                                    </picture>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div class="tilo-hero__pagination swiper-pagination" aria-label="Slide pagination"></div>
                </div>
            </div>

            <div class="tilo-hero__card" aria-describedby="tilo-hero-progress">
                <h2 id="tilo-hero-title"><?php esc_html_e( 'Follow Latest Style Shoes to follow the trend going on', 'tilottama-child' ); ?></h2>
                <p><?php esc_html_e( 'Discover curated edits of the freshest footwear, styled to keep you ahead of every season and occasion.', 'tilottama-child' ); ?></p>
                <div id="tilo-hero-progress" class="tilo-hero__progress" role="img" aria-label="Carousel progress indicator"></div>
            </div>

            <div class="tilo-hero__action" role="group" aria-label="Carousel controls">
                <button class="tilo-hero__next" type="button">
                    <span class="tilo-hero__next-label"><?php esc_html_e( 'Next', 'tilottama-child' ); ?></span>
                    <span class="tilo-hero__next-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false" role="img" aria-hidden="true">
                            <path d="M12 4l1.41 1.41L9.83 9H20v2H9.83l3.58 3.59L12 16l-6-6 6-6z"></path>
                        </svg>
                    </span>
                </button>
            </div>
        </section>
        <?php
        return trim( ob_get_clean() );
    }
}

add_action( 'init', function () {
    add_shortcode( 'tilo_landing_hero', 'tilo_landing_hero_shortcode' );
} );
