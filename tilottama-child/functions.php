<?php
add_action( 'wp_enqueue_scripts', function () {
    // Enqueue parent theme style
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    // Enqueue child theme style
    wp_enqueue_style( 'tilottama-child-style', get_stylesheet_directory_uri() . '/style.css', [ 'parent-style' ], wp_get_theme()->get( 'Version' ) );
} );

require_once get_stylesheet_directory() . '/tilo-landing-hero.php';
