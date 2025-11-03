<?php
/**
 * Front page template with hero banner slider style.
 *
 * @package Porto Child
 */

defined( 'ABSPATH' ) || exit;

get_header();

global $porto_settings, $porto_layout;

$hero_enabled       = get_theme_mod( 'porto_child_hero_enable', true );
$hero_intro         = get_theme_mod( 'porto_child_hero_intro', __( 'New Arrival', 'porto-child' ) );
$hero_title         = get_theme_mod( 'porto_child_hero_title', __( 'Find Your New Favourite Sneakers', 'porto-child' ) );
$hero_description   = get_theme_mod( 'porto_child_hero_description', __( 'Step into a world of style with our revolutionary fashion destination — your ultimate stop for trendsetting looks and curated collections.', 'porto-child' ) );
$hero_button_text   = get_theme_mod( 'porto_child_hero_button_text', __( 'Shop Collection', 'porto-child' ) );
$hero_button_url    = get_theme_mod( 'porto_child_hero_button_url', '#content' );
$hero_image_id      = get_theme_mod( 'porto_child_hero_image' );
$hero_card_heading  = get_theme_mod( 'porto_child_hero_card_heading', __( "Follow Latest Style\nShoes to follow the trend going on", 'porto-child' ) );
$hero_card_desc     = get_theme_mod( 'porto_child_hero_card_description', __( 'Step into a world of style with our revolutionary fashion app — your ultimate destination for trendsetting looks and curated collections.', 'porto-child' ) );
$hero_card_cta_text = get_theme_mod( 'porto_child_hero_card_button_text', __( 'Next', 'porto-child' ) );
$hero_card_cta_url  = get_theme_mod( 'porto_child_hero_card_button_url', '#content' );

$hero_image_src = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';
$hero_image_alt = '';

if ( $hero_image_id ) {
        $hero_image_alt = get_post_meta( $hero_image_id, '_wp_attachment_image_alt', true );
}

if ( ! $hero_image_alt ) {
        $hero_image_alt = $hero_title ? wp_strip_all_tags( $hero_title ) : get_bloginfo( 'name', 'display' );
}

if ( $hero_enabled ) :
        ?>
<section class="home-hero" aria-label="<?php esc_attr_e( 'Featured promotion', 'porto-child' ); ?>">
        <span class="home-hero__bubble" aria-hidden="true"></span>
        <div class="container">
                <div class="home-hero__inner">
                        <div class="home-hero__copy">
                                <?php if ( $hero_intro ) : ?>
                                        <span class="home-hero__eyebrow"><?php echo esc_html( $hero_intro ); ?></span>
                                <?php endif; ?>

                                <?php if ( $hero_title ) : ?>
                                        <h1 class="home-hero__title"><?php echo nl2br( esc_html( $hero_title ) ); ?></h1>
                                <?php endif; ?>

                                <?php if ( $hero_description ) : ?>
                                        <p class="home-hero__summary"><?php echo wp_kses_post( $hero_description ); ?></p>
                                <?php endif; ?>

                                <?php if ( $hero_button_text && $hero_button_url ) : ?>
                                        <div class="home-hero__actions">
                                                <a class="home-hero__primary" href="<?php echo esc_url( $hero_button_url ); ?>">
                                                        <?php echo esc_html( $hero_button_text ); ?>
                                                </a>
                                        </div>
                                <?php endif; ?>
                        </div>

                        <div class="home-hero__device" role="group" aria-label="<?php esc_attr_e( 'Hero style preview', 'porto-child' ); ?>">
                                <span class="home-hero__device-frame" aria-hidden="true"></span>
                                <div class="home-hero__device-top">
                                        <?php if ( $hero_image_src ) : ?>
                                                <img class="home-hero__device-image" src="<?php echo esc_url( $hero_image_src ); ?>" alt="<?php echo esc_attr( $hero_image_alt ); ?>" loading="lazy" />
                                        <?php else : ?>
                                                <div class="home-hero__device-placeholder" aria-hidden="true"></div>
                                        <?php endif; ?>
                                </div>
                                <div class="home-hero__card" aria-live="polite">
                                        <?php if ( $hero_card_heading ) : ?>
                                                <h2 class="home-hero__card-title"><?php echo nl2br( esc_html( $hero_card_heading ) ); ?></h2>
                                        <?php endif; ?>

                                        <?php if ( $hero_card_desc ) : ?>
                                                <p class="home-hero__card-desc"><?php echo wp_kses_post( $hero_card_desc ); ?></p>
                                        <?php endif; ?>

                                        <?php if ( $hero_card_cta_text && $hero_card_cta_url ) : ?>
                                                <a class="home-hero__card-nav" href="<?php echo esc_url( $hero_card_cta_url ); ?>">
                                                        <span><?php echo esc_html( $hero_card_cta_text ); ?></span>
                                                        <svg class="home-hero__card-icon" width="20" height="20" viewBox="0 0 20 20" aria-hidden="true" focusable="false">
                                                                <path d="M5 10h10m-4-4 4 4-4 4" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75"></path>
                                                        </svg>
                                                </a>
                                        <?php endif; ?>
                                </div>
                        </div>
                </div>
        </div>
</section>
<?php
endif;

$featured_images     = porto_get_featured_images();
$show_featured_image = porto_get_meta_value( 'show_featured_image' );
?>
<div id="content" role="main" class="home-main">
        <?php while ( have_posts() ) : the_post(); ?>
                <article <?php post_class(); ?>>
                        <?php if ( isset( $show_featured_image ) && 'yes' === $show_featured_image && count( $featured_images ) && ! post_password_required() ) : ?>
                                <?php
                                $featured_images = porto_get_featured_images();
                                $image_count     = count( $featured_images );

                                if ( $image_count ) :
                                        ?>
                                        <div class="page-image<?php echo 1 === $image_count ? ' single' : ''; ?>">
                                                <div class="page-slideshow porto-carousel owl-carousel">
                                                        <?php foreach ( $featured_images as $featured_image ) :
                                                                $attachment = porto_get_attachment( $featured_image['attachment_id'] );
                                                                if ( $attachment ) :
                                                                        ?>
                                                                        <div>
                                                                                <div class="img-thumbnail">
                                                                                        <img class="owl-lazy img-responsive" width="<?php echo esc_attr( $attachment['width'] ); ?>" height="<?php echo esc_attr( $attachment['height'] ); ?>" data-src="<?php echo esc_url( $attachment['src'] ); ?>" alt="<?php echo esc_attr( $attachment['alt'] ); ?>" />
                                                                                        <?php if ( $porto_settings['page-zoom'] ) : ?>
                                                                                                <span class="zoom" data-src="<?php echo esc_attr( $attachment['src'] ); ?>" data-title="<?php echo esc_attr( $attachment['caption'] ); ?>"><i class="fas fa-search"></i></span>
                                                                                        <?php endif; ?>
                                                                                </div>
                                                                        </div>
                                                                <?php
                                                                endif;
                                                        endforeach;
                                                        ?>
                                                </div>
                                        </div>
                                        <?php
                                endif;
                                ?>
                        <?php endif; ?>

                        <?php
                        $microdata = porto_get_meta_value( 'page_microdata' );
                        if ( $porto_settings['rich-snippets'] && 'no' !== $microdata && ( 'yes' === $microdata || ( 'yes' !== $microdata && $porto_settings['page-microdata'] ) ) ) {
                                porto_render_rich_snippets( 'h2' );
                        }
                        ?>

                        <div class="page-content">
                                <?php
                                the_content();
                                wp_link_pages(
                                        array(
                                                'before'      => '<div class="pagination" role="navigation">',
                                                'after'       => '</div>',
                                                'link_before' => '<span>',
                                                'link_after'  => '</span>',
                                        )
                                );
                                ?>
                        </div>
                </article>

                <?php
                $share           = porto_get_meta_value( 'page_share' );
                $share_enabled   = $porto_settings['share-enable'] && 'no' !== $share && ( 'yes' === $share || ( 'yes' !== $share && $porto_settings['page-share'] ) ) && ( isset( $porto_settings['page-share-pos'] ) && ! $porto_settings['page-share-pos'] );
                $comment_enabled = $porto_settings['page-comment'] || comments_open();
                if ( $share_enabled || $comment_enabled ) :
                        ?>
                        <div class="<?php echo ( 'wide-left-sidebar' === $porto_layout || 'wide-right-sidebar' === $porto_layout || 'wide-both-sidebar' === $porto_layout ) ? 'm-t-lg m-b-xl' : ''; ?>">
                                <?php if ( $share_enabled ) : ?>
                                        <div class="page-share<?php echo 'widewidth' === $porto_layout ? ' container' : ''; ?>">
                                                <h3><i class="fas fa-share"></i><?php esc_html_e( 'Share this post', 'porto' ); ?></h3>
                                                <?php get_template_part( 'share' ); ?>
                                        </div>
                                <?php endif; ?>

                                <?php
                                if ( $comment_enabled ) {
                                        wp_reset_postdata();
                                        comments_template();
                                }
                                ?>
                        </div>
                <?php endif; ?>
        <?php endwhile; ?>
</div>

<?php
get_footer();
