<?php
/**
 * Front Page template for Tilottama Child theme.
 *
 * @package TilottamaChild
 */

global $post;

get_header();
?>

<main id="primary" class="tilottama-landing" tabindex="-1">
    <section class="tilo-hero" aria-labelledby="tilo-hero-title">
        <div class="tilo-hero__background">
            <div id="tilo-hero-slider" class="tilo-hero__slider swiper" aria-live="polite" role="region" aria-label="<?php esc_attr_e( 'Trending shoes carousel', 'tilottama-child' ); ?>">
                <div class="swiper-wrapper">
                    <article class="tilo-hero__slide swiper-slide" data-caption="Airy pastel sneaker" data-slide-index="0" aria-roledescription="slide">
                        <picture>
                            <img src="<?php echo esc_url( 'https://images.unsplash.com/photo-1523381210434-271e8be1f52b?auto=format&fit=crop&w=1200&q=80' ); ?>" alt="<?php esc_attr_e( 'Soft pastel sneaker on a color block background', 'tilottama-child' ); ?>" loading="lazy" />
                        </picture>
                    </article>
                    <article class="tilo-hero__slide swiper-slide" data-caption="Studio close-up" data-slide-index="1" aria-roledescription="slide">
                        <picture>
                            <img src="<?php echo esc_url( 'https://images.unsplash.com/photo-1528701800489-20be3c6c51f4?auto=format&fit=crop&w=1200&q=80' ); ?>" alt="<?php esc_attr_e( 'Minimalist sneaker photographed in studio lighting', 'tilottama-child' ); ?>" loading="lazy" />
                        </picture>
                    </article>
                    <article class="tilo-hero__slide swiper-slide" data-caption="Sneaker in motion" data-slide-index="2" aria-roledescription="slide">
                        <video class="tilo-hero__video" preload="metadata" muted playsinline loop>
                            <source src="<?php echo esc_url( 'https://cdn.coverr.co/videos/coverr-stylish-shoes-1080p.mp4' ); ?>" type="video/mp4" />
                            <?php esc_html_e( 'Your browser does not support the video tag.', 'tilottama-child' ); ?>
                        </video>
                    </article>
                </div>
            </div>
            <div class="tilo-hero__scrim" aria-hidden="true"></div>
        </div>

        <div class="tilo-hero__content">
            <div class="tilo-hero__upper">
                <section class="tilo-hero__card" aria-describedby="tilo-hero-progress">
                    <h1 id="tilo-hero-title"><?php esc_html_e( 'Follow Latest Style Shoes to follow the trend going on', 'tilottama-child' ); ?></h1>
                    <p><?php esc_html_e( 'Discover curated edits of the freshest footwear, styled to keep you ahead of every season and occasion.', 'tilottama-child' ); ?></p>
                    <div id="tilo-hero-progress" class="tilo-hero__progress" role="img" aria-label="<?php esc_attr_e( 'Carousel progress indicator', 'tilottama-child' ); ?>"></div>
                </section>

                <div class="tilo-hero__pagination swiper-pagination" aria-label="<?php esc_attr_e( 'Slide pagination', 'tilottama-child' ); ?>"></div>
            </div>

            <div class="tilo-hero__action" role="group" aria-label="<?php esc_attr_e( 'Carousel controls', 'tilottama-child' ); ?>">
                <button class="tilo-hero__next" type="button" aria-controls="tilo-hero-slider">
                    <span class="tilo-hero__next-label"><?php esc_html_e( 'Next', 'tilottama-child' ); ?></span>
                    <span class="tilo-hero__next-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" focusable="false" role="img" aria-hidden="true">
                            <path d="M9 5l7 7-7 7-1.41-1.41L13.17 12 7.59 6.41z" />
                        </svg>
                    </span>
                </button>
            </div>
        </div>
    </section>

    <section class="tilottama-highlights" aria-labelledby="popular-heading">
        <header class="tilottama-highlights__header">
            <div class="tilottama-highlights__eyebrow"><?php esc_html_e( 'Popular Shoes', 'tilottama-child' ); ?></div>
            <h2 id="popular-heading" class="tilottama-highlights__title"><?php esc_html_e( 'New Arrivals', 'tilottama-child' ); ?></h2>
            <p class="tilottama-highlights__subtitle"><?php esc_html_e( 'Fresh drops that pair perfectly with the season. Curated by our stylists, delivered to your doorstep.', 'tilottama-child' ); ?></p>
        </header>
        <?php
        $popular_products = [];

        if ( function_exists( 'wc_get_products' ) ) {
            $popular_products = wc_get_products(
                [
                    'status'  => 'publish',
                    'limit'   => 4,
                    'orderby' => 'date',
                    'order'   => 'DESC',
                ]
            );
        }

        if ( $popular_products ) :
            ?>
            <ul class="products tilottama-product-grid columns-4">
                <?php
                foreach ( $popular_products as $product ) {
                    if ( function_exists( 'wc_setup_product_data' ) ) {
                        wc_setup_product_data( $product );
                    }
                    wc_get_template_part( 'content', 'product' );
                }
                wp_reset_postdata();
                ?>
            </ul>
        <?php else : ?>
            <p class="tilottama-highlights__empty"><?php esc_html_e( 'Check back soon for our freshest releases.', 'tilottama-child' ); ?></p>
        <?php endif; ?>
    </section>
</main>

<?php
get_footer();
