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
$hero_image_id = get_theme_mod( 'porto_child_hero_image' );

$hero_image_src = $hero_image_id ? wp_get_attachment_image_url( $hero_image_id, 'full' ) : '';

if ( $hero_enabled ) :
        $hero_classes = array( 'home-hero' );

        if ( $hero_image_src ) {
                $hero_classes[] = 'home-hero--has-image';
        } else {
                $hero_classes[] = 'home-hero--no-image';
        }

        $hero_background_style = $hero_image_src ? sprintf( ' style="background-image: url(%s);"', esc_url( $hero_image_src ) ) : '';
        ?>
<section class="<?php echo esc_attr( implode( ' ', $hero_classes ) ); ?>"<?php echo $hero_background_style; ?> aria-label="<?php esc_attr_e( 'Featured promotion', 'porto-child' ); ?>">
        <div class="home-hero__overlay">
                <div class="container">
                        <div class="home-hero__content">
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
