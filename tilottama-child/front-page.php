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
    <section class="tilottama-hero">
        <div class="tilottama-hero__content">
            <span class="tilottama-hero__eyebrow"><?php esc_html_e( 'Pic 1', 'tilottama-child' ); ?></span>
            <h1 class="tilottama-hero__title"><?php esc_html_e( 'Follow Latest Style Shoes to follow the trend going on', 'tilottama-child' ); ?></h1>
            <p class="tilottama-hero__description"><?php esc_html_e( 'Step into leading silhouettes with our revolutionary fashion lab and curated sneaker rotation for trendsetting looks and curated collections.', 'tilottama-child' ); ?></p>
            <?php
            $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );
            ?>
            <div class="tilottama-hero__cta">
                <a class="tilottama-hero__button" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'Shop now', 'tilottama-child' ); ?></a>
                <button class="tilottama-hero__secondary" type="button"><?php esc_html_e( 'Next', 'tilottama-child' ); ?></button>
            </div>
        </div>
        <div class="tilottama-hero__visual" aria-hidden="true">
            <div class="tilottama-hero__halo"></div>
            <img class="tilottama-hero__image" src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/images/hero-sneaker.svg' ); ?>" alt="" loading="lazy" />
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
