<?php
/**
 * Plugin Name: Tilottama Custom
 * Plugin URI: https://example.com
 * Description: Custom functionality for WooCommerce site.
 * Version: 1.0.0
 * Author: Abhra Das
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: tilottama-custom
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Tilottama_Custom_Plugin {
    public function __construct() {
        // Add a custom checkout field example.
        add_action( 'woocommerce_after_order_notes', [ $this, 'add_custom_checkout_field' ] );
        add_action( 'woocommerce_checkout_create_order', [ $this, 'save_custom_checkout_field' ], 10, 2 );
        // Add GST badge to price HTML example.
        add_filter( 'woocommerce_get_price_html', [ $this, 'add_gst_badge_to_price_html' ], 10, 2 );
    }

    public function add_custom_checkout_field( $checkout ) {
        woocommerce_form_field( 'internal_note_code', [
            'type'        => 'text',
            'class'       => [ 'form-row-wide' ],
            'label'       => __( 'Internal Note Code', 'tilottama-custom' ),
            'placeholder' => __( 'Enter note code', 'tilottama-custom' ),
        ], $checkout->get_value( 'internal_note_code' ) );
    }

    public function save_custom_checkout_field( $order, $data ) {
        if ( isset( $_POST['internal_note_code'] ) ) {
            $order->update_meta_data( '_internal_note_code', sanitize_text_field( $_POST['internal_note_code'] ) );
        }
    }

    public function add_gst_badge_to_price_html( $price_html, $product ) {
        return $price_html . ' <small>(' . __( 'Incl. GST', 'tilottama-custom' ) . ')</small>';
    }
}

new Tilottama_Custom_Plugin();
