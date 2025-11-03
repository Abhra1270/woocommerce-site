<?php
// … your existing code …

/**
 * 1) Ensure our custom slug table exists, on every init.
 */
add_action( 'init', function() {
    global $wpdb;
    $table   = $wpdb->prefix . 'payment_slugs';
    $charset = $wpdb->get_charset_collate();

    // Create if missing
    if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table}'" ) !== $table ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $sql = "
        CREATE TABLE {$table} (
          slug VARCHAR(64) NOT NULL,
          url  TEXT        NOT NULL,
          PRIMARY KEY (slug)
        ) {$charset};
        ";
        dbDelta( $sql );
        error_log("[SlugTable] Created or updated table {$table}");
    }
});

/**
 * 2) On every new/REST/admin order, generate & store slug → URL mapping
 */
add_action( 'woocommerce_new_order',        'ps_store_slug_mapping', 20, 1 );
add_action( 'save_post_shop_order',         'ps_store_slug_mapping', 20, 2 );
add_action( 'woocommerce_rest_insert_shop_order', function( $order, $req, $creating ) {
    if ( $creating ) ps_store_slug_mapping( $order->get_id() );
}, 20, 3 );

function ps_store_slug_mapping( $order_id ) {
    if ( ! $order_id ) return;

    global $wpdb;
    $table = $wpdb->prefix . 'payment_slugs';

    // Generate the slug (base36 of order ID)
    $slug = base_convert( $order_id, 10, 36 );

    // Build the exact WooCommerce pay-for-order URL
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        error_log("[SlugMap] Order not found for ID {$order_id}");
        return;
    }
    $url = wc_get_checkout_url()
         . 'order-pay/' . $order_id
         . '/?pay_for_order=true&key=' . rawurlencode( $order->get_order_key() );

    // Upsert into our slug table
    $result = $wpdb->query( $wpdb->prepare(
        "REPLACE INTO {$table} (slug, url) VALUES (%s, %s)",
        $slug, $url
    ) );

    if ( false === $result ) {
        error_log("[SlugMap] FAILED to write slug {$slug} → {$url}");
    } else {
        error_log("[SlugMap] Stored slug {$slug} → {$url} (order {$order_id})");
        // Also save slug as order meta for webhook
        update_post_meta( $order_id, 'payment_slug', $slug );
    }
}

/**
 * 3) On front-end, catch /{slug} paths and immediately redirect
 */
add_action( 'template_redirect', function() {
    if ( is_admin() || ( defined('REST_REQUEST') && REST_REQUEST ) ) {
        return;
    }

    // Grab the clean path
    $path = trim( parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH ), '/' );
    if ( ! preg_match( '/^[0-9a-zA-Z]+$/', $path ) ) {
        return;
    }

    global $wpdb;
    $table = $wpdb->prefix . 'payment_slugs';

    // Ultra-fast indexed lookup
    $url = $wpdb->get_var( $wpdb->prepare(
        "SELECT url FROM {$table} WHERE slug = %s LIMIT 1",
        $path
    ) );

    if ( ! $url ) {
        error_log("[SlugRedirect] No URL found for slug '{$path}'");
        return;  // let WP serve 404
    }

    error_log("[SlugRedirect] Redirecting slug '{$path}' → {$url}");
    wp_safe_redirect( $url, 302 );
    exit;
} );

/**
 * 4) Inject full short URL into WooCommerce webhook payload
 */
add_filter( 'woocommerce_webhook_payload', function( $payload, $resource, $resource_id ) {
    if ( 'order' === $resource ) {
        $slug = get_post_meta( $resource_id, 'payment_slug', true );
        if ( $slug ) {
            $full = home_url( "/{$slug}" );
            $payload['meta_data'][] = [
                'id'    => null,
                'key'   => 'payment_slug',
                'value' => $full,
            ];
            error_log("[Webhook] order {$resource_id} → payment_slug {$full}");
        }
    }
    return $payload;
}, 10, 3 );

/**
 * Make the front-page header float above the hero banner.
 */
add_filter( 'body_class', function( $classes ) {
    if ( is_front_page() && ! is_admin() ) {
        $classes[] = 'porto-hero-overlap';
    }

    return $classes;
} );

/**
 * Output a hero slider before the main content on the front page.
 */
add_action( 'porto_before_content', 'porto_child_output_front_hero', 5 );

function porto_child_output_front_hero() {
    if ( ! is_front_page() || is_admin() ) {
        return;
    }

    $front_id = get_queried_object_id();
    $featured = '';

    if ( $front_id && has_post_thumbnail( $front_id ) ) {
        $featured = get_the_post_thumbnail_url( $front_id, 'full' );
    }

    if ( ! $featured ) {
        $fallback = get_stylesheet_directory() . '/images/hero-fallback.jpg';
        if ( file_exists( $fallback ) ) {
            $featured = get_stylesheet_directory_uri() . '/images/hero-fallback.jpg';
        }
    }

    $shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' );

    $default_slides = [
        [
            'eyebrow'         => __( 'Limited Release', 'porto-child' ),
            'title'           => __( 'Run the city in feather-light comfort', 'porto-child' ),
            'description'     => __( 'Ultra-responsive cushioning, breathable mesh uppers, and reflective accents make the Velocity X your go-to sneaker for sunrise sessions and late-night laps.', 'porto-child' ),
            'primary_label'   => __( 'Shop the men’s drop', 'porto-child' ),
            'primary_url'     => $shop_url ?: home_url( '/' ),
            'secondary_label' => __( 'View lookbook', 'porto-child' ),
            'secondary_url'   => home_url( '/lookbook/' ),
            'image'           => $featured,
        ],
        [
            'eyebrow'         => __( 'Everyday essential', 'porto-child' ),
            'title'           => __( 'Support that keeps pace with you', 'porto-child' ),
            'description'     => __( 'A sculpted midsole hugs your arch while the grippy outsole keeps you grounded on rainy commutes and weekend long runs alike.', 'porto-child' ),
            'primary_label'   => __( 'Shop women’s sneakers', 'porto-child' ),
            'primary_url'     => $shop_url ? trailingslashit( $shop_url ) . '#women' : home_url( '/women/' ),
            'secondary_label' => __( 'Find your fit', 'porto-child' ),
            'secondary_url'   => home_url( '/size-guide/' ),
            'image'           => $featured,
        ],
        [
            'eyebrow'         => __( 'Performance engineered', 'porto-child' ),
            'title'           => __( 'Built for miles, styled for anywhere', 'porto-child' ),
            'description'     => __( 'From tempo training to post-run coffee, the Velocity collection pairs technical materials with a bold silhouette inspired by sport heritage.', 'porto-child' ),
            'primary_label'   => __( 'Customize yours', 'porto-child' ),
            'primary_url'     => home_url( '/customizer/' ),
            'secondary_label' => __( 'Explore accessories', 'porto-child' ),
            'secondary_url'   => home_url( '/gear/' ),
            'image'           => $featured,
        ],
    ];

    $slides = apply_filters( 'porto_child_hero_slides', $default_slides );
    $slides = array_values( array_filter( $slides, 'is_array' ) );

    if ( empty( $slides ) ) {
        return;
    }

    $autoplay = apply_filters( 'porto_child_hero_autoplay', 7000 );
    $autoplay = absint( $autoplay );

    echo '<section class="porto-hero-banner" data-hero-autoplay="' . esc_attr( $autoplay ) . '">';
    echo '<div class="porto-hero-banner__slides">';

    foreach ( $slides as $index => $slide ) {
        $classes = 'porto-hero-banner__slide';
        if ( 0 === $index ) {
            $classes .= ' is-active';
        }

        $image = isset( $slide['image'] ) && $slide['image'] ? $slide['image'] : $featured;
        $style = $image ? ' style="--porto-hero-image:url(' . esc_url( $image ) . ');"' : '';

        $title = isset( $slide['title'] ) ? wp_strip_all_tags( $slide['title'] ) : '';

        echo '<article class="' . esc_attr( $classes ) . '" role="group" aria-roledescription="slide" aria-label="' . esc_attr( $title ) . '" aria-hidden="' . ( 0 === $index ? 'false' : 'true' ) . '" data-hero-index="' . esc_attr( $index ) . '"' . $style . '>';
        echo '<div class="porto-hero-banner__media" aria-hidden="true"></div>';
        echo '<div class="porto-hero-banner__inner container">';
        echo '<div class="porto-hero-banner__copy">';

        if ( ! empty( $slide['eyebrow'] ) ) {
            echo '<p class="porto-hero-banner__eyebrow">' . esc_html( $slide['eyebrow'] ) . '</p>';
        }

        if ( $title ) {
            echo '<h1 class="porto-hero-banner__title">' . esc_html( $title ) . '</h1>';
        }

        if ( ! empty( $slide['description'] ) ) {
            echo '<p class="porto-hero-banner__description">' . esc_html( $slide['description'] ) . '</p>';
        }

        $has_primary   = ! empty( $slide['primary_label'] ) && ! empty( $slide['primary_url'] );
        $has_secondary = ! empty( $slide['secondary_label'] ) && ! empty( $slide['secondary_url'] );

        if ( $has_primary || $has_secondary ) {
            echo '<div class="porto-hero-banner__actions">';

            if ( $has_primary ) {
                echo '<a class="porto-hero-banner__btn porto-hero-banner__btn--primary" href="' . esc_url( $slide['primary_url'] ) . '">' . esc_html( $slide['primary_label'] ) . '</a>';
            }

            if ( $has_secondary ) {
                echo '<a class="porto-hero-banner__btn porto-hero-banner__btn--ghost" href="' . esc_url( $slide['secondary_url'] ) . '">' . esc_html( $slide['secondary_label'] ) . '</a>';
            }

            echo '</div>';
        }

        echo '</div>'; // .porto-hero-banner__copy
        echo '</div>'; // .porto-hero-banner__inner
        echo '</article>';
    }

    echo '</div>'; // .porto-hero-banner__slides

    if ( count( $slides ) > 1 ) {
        echo '<div class="porto-hero-banner__controls">';
        echo '<button type="button" class="porto-hero-banner__control porto-hero-banner__control--prev" aria-label="' . esc_attr__( 'Previous slide', 'porto-child' ) . '" data-hero-prev></button>';
        echo '<button type="button" class="porto-hero-banner__control porto-hero-banner__control--next" aria-label="' . esc_attr__( 'Next slide', 'porto-child' ) . '" data-hero-next></button>';
        echo '</div>';

        echo '<div class="porto-hero-banner__dots" role="tablist" aria-label="' . esc_attr__( 'Hero slides', 'porto-child' ) . '">';

        foreach ( $slides as $index => $slide ) {
            $title = isset( $slide['title'] ) ? wp_strip_all_tags( $slide['title'] ) : __( 'Slide', 'porto-child' );
            $is_active = 0 === $index;
            echo '<button type="button" class="porto-hero-banner__dot' . ( $is_active ? ' is-active' : '' ) . '" data-hero-nav="' . esc_attr( $index ) . '" aria-label="' . esc_attr( $title ) . '"' . ( $is_active ? ' aria-current="true"' : '' ) . '></button>';
        }

        echo '</div>';
    }

    echo '</section>';
}

/**
 * Enqueue front-page hero assets.
 */
add_action( 'wp_enqueue_scripts', 'porto_child_enqueue_hero_assets', 1100 );

function porto_child_enqueue_hero_assets() {
    if ( ! is_front_page() || is_admin() ) {
        return;
    }

    wp_enqueue_script(
        'porto-child-hero-slider',
        get_stylesheet_directory_uri() . '/js/hero-slider.js',
        array(),
        '1.0.0',
        true
    );
}



// wp-content/themes/porto-child/functions.php

// 1) Enqueue your child-theme CSS
add_action( 'wp_enqueue_scripts', 'porto_child_enqueue_styles', 1001 );
function porto_child_enqueue_styles() {
    wp_deregister_style( 'styles-child' );
    wp_register_style(   'styles-child',
                         get_stylesheet_directory_uri() . '/style.css' );
    wp_enqueue_style(    'styles-child' );

    if ( is_rtl() ) {
        wp_deregister_style( 'styles-child-rtl' );
        wp_register_style(   'styles-child-rtl',
                             get_stylesheet_directory_uri() . '/style_rtl.css' );
        wp_enqueue_style(    'styles-child-rtl' );
    }
}

// 2) Treat the “order-pay” endpoint as checkout so Razorpay JS loads
add_filter( 'woocommerce_is_checkout', function( $is_checkout ) {
    return is_wc_endpoint_url( 'order-pay' ) ? true : $is_checkout;
}, 20 );

// 3) Save shipping fields + email before payment, mirror into billing
add_action( 'woocommerce_before_pay_action', 'porto_child_save_pay_for_order_fields', 10, 1 );
function porto_child_save_pay_for_order_fields( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Map form fields → order setter methods
    $map = [
        'shipping_first_name' => 'set_shipping_first_name',
        'shipping_last_name'  => 'set_shipping_last_name',
        'shipping_address_1'  => 'set_shipping_address_1',
        'shipping_address_2'  => 'set_shipping_address_2',
        'shipping_city'       => 'set_shipping_city',
        'shipping_state'      => 'set_shipping_state',
        'shipping_postcode'   => 'set_shipping_postcode',
        'shipping_country'    => 'set_shipping_country',
        'shipping_phone'      => 'set_shipping_phone',
        'billing_email'       => 'set_billing_email',
    ];

    foreach ( $map as $field => $setter ) {
        if ( isset( $_REQUEST[ $field ] ) ) {
            $value = sanitize_text_field( wp_unslash( $_REQUEST[ $field ] ) );
            $order->{$setter}( $value );
        }
    }

    // Mirror all shipping → billing fields
    $order->set_billing_first_name(  $order->get_shipping_first_name() );
    $order->set_billing_last_name(   $order->get_shipping_last_name() );
    $order->set_billing_address_1(   $order->get_shipping_address_1() );
    $order->set_billing_address_2(   $order->get_shipping_address_2() );
    $order->set_billing_city(        $order->get_shipping_city() );
    $order->set_billing_state(       $order->get_shipping_state() );
    $order->set_billing_postcode(    $order->get_shipping_postcode() );
    $order->set_billing_country(     $order->get_shipping_country() );
    $order->save();
}

// 4) On order → processing, queue a Delhivery push via Action Scheduler
add_action( 'woocommerce_order_status_processing', 'porto_child_queue_delhivery_push', 10, 1 );
function porto_child_queue_delhivery_push( $order_id ) {
    $order = wc_get_order( $order_id );
    if ( ! $order || 'razorpay' !== $order->get_payment_method() ) {
        return;
    }

    if ( function_exists( 'as_schedule_single_action' ) ) {
        as_schedule_single_action(
            time() + 60,
            'porto_child_send_to_delhivery',
            [ 'order_id' => $order_id ],
            'delhivery'
        );
    } else {
        porto_child_send_to_delhivery( [ 'order_id' => $order_id ] );
    }
}

// 5) The actual Delhivery push handler
add_action( 'porto_child_send_to_delhivery', 'porto_child_send_to_delhivery' );
function porto_child_send_to_delhivery( $args ) {
    $order_id = absint( $args['order_id'] ?? 0 );
    $order    = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    $items = [];
    $total_qty = 0;
    foreach ( $order->get_items() as $item ) {
        $product = $item->get_product();
        $items[] = [
            'sku'       => $product ? $product->get_sku() : '',
            'quantity'  => $item->get_quantity(),
            'price'     => (float) $item->get_total(),
        ];
        $total_qty += $item->get_quantity();
    }

    $address_parts = array_filter([
        $order->get_shipping_address_1(),
        $order->get_shipping_address_2(),
        $order->get_shipping_city(),
        $order->get_shipping_state(),
        $order->get_shipping_postcode(),
        $order->get_shipping_country(),
    ]);
    $full_address = implode( ', ', $address_parts );

    $manifest = [
        'pickup_location' => 'Tilattama Studio De elegance',
        'shipments'       => [
            [
                'order'         => $order->get_order_number(),
                'payment_mode'  => 'Prepaid',
                'products_desc' => implode( ', ', wp_list_pluck( $order->get_items(), 'name' ) ),
                'total_amount'  => (float) $order->get_total(),
                'source'        => 'WooCommerce',
                'quantity'      => $total_qty,
                'name'          => trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ),
                'phone'         => $order->get_shipping_phone(),
                'add'           => $full_address,
                'city'          => $order->get_shipping_city(),
                'state'         => $order->get_shipping_state(),
                'pin'           => $order->get_shipping_postcode(),
                'country'       => $order->get_shipping_country(),
            ],
        ],
    ];

    $body = 'format=json&data=' . rawurlencode( wp_json_encode( $manifest ) );
    $response = wp_remote_post(
        'https://track.delhivery.com/api/cmu/create.json',
        [
            'headers' => [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Authorization' => 'Token db1e287b7dcd48cd5e0342c0e9cd7672f27d5469',
            ],
            'body'    => $body,
            'timeout' => 20,
        ]
    );

    $code = wp_remote_retrieve_response_code( $response );
    if ( is_wp_error( $response ) || 200 !== $code ) {
        error_log( sprintf(
            'Delhivery push FAILED for order %d: %s',
            $order_id,
            is_wp_error( $response )
                ? $response->get_error_message()
                : wp_remote_retrieve_response_message( $response )
        ) );
        if ( function_exists( 'as_schedule_single_action' ) ) {
            as_schedule_single_action(
                time() + 300,
                'porto_child_send_to_delhivery',
                [ 'order_id' => $order_id ],
                'delhivery'
            );
        }
    }
}



// 1) Remove “email” requirement & hide it:
add_filter( 'woocommerce_checkout_fields', 'tsl_hide_pay_for_order_email', 20, 1 );
function tsl_hide_pay_for_order_email( $fields ) {
    if ( is_wc_endpoint_url( 'order-pay' ) ) {
        $order_id = absint( get_query_var( 'order-pay' ) );
        if ( $order = wc_get_order( $order_id ) ) {
            // Prefill with the order’s billing email
            $fields['billing']['billing_email']['default'] = $order->get_billing_email();
        }
        // Make it hidden and not required
        $fields['billing']['billing_email']['required'] = false;
        $fields['billing']['billing_email']['type']     = 'hidden';
        $fields['billing']['billing_email']['label']    = false;
        $fields['billing']['billing_email']['class'][]  = 'tsl-hidden';
    }
    return $fields;
}

// 2) Hide the whole “Billing details” wrapper on pay page:
add_action( 'wp_head', 'tsl_hide_pay_for_order_billing_section' );
function tsl_hide_pay_for_order_billing_section() {
    if ( is_wc_endpoint_url( 'order-pay' ) ) {
        echo "<style>
            /* hide billing box */
            .woocommerce-billing-fields { display: none !important; }
            /* (optional) collapse title too */
            .woocommerce-billing-fields__field-wrapper h3 { display: none !important; }
        </style>";
    }
}
// functions.php in your porto-child theme

add_filter( 'wpo_wcpdf_mpdf_config', 'tilattama_register_pdf_fonts' );
function tilattama_register_pdf_fonts( $config ) {

  // register Cinzel Decorative
  $config['font_data']['CinzelDecorative'] = [
    'R' => get_stylesheet_directory() . '/woocommerce/pdf/fonts/CinzelDecorative-Regular.ttf',
    'B' => get_stylesheet_directory() . '/woocommerce/pdf/fonts/CinzelDecorative-Regular.ttf',
  ];

  // (optional) register Figtree if you want your body text in Figtree
  $config['font_data']['Figtree'] = [
    'R' => get_stylesheet_directory() . '/woocommerce/pdf/fonts/Figtree-Regular.ttf',
    'B' => get_stylesheet_directory() . '/woocommerce/pdf/fonts/Figtree-Bold.ttf',
  ];

  return $config;
}



// 1) Hook into the Processing‐status transition
add_action( 'woocommerce_order_status_processing', 'tilattama_send_whatsapp_invoice', 10, 1 );

function tilattama_send_whatsapp_invoice( $order_id ) {
    // 2) Load the order and customer data
    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    // Normalize phone to include country code
    $phone = $order->get_billing_phone();
    if ( ! preg_match( '/^\+/', trim( $phone ) ) ) {
        $phone = '+91' . preg_replace( '/\D+/', '', $phone );
    }

    $customer_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
    $total_amount  = $order->get_total();

    // 3) Get the PDF URL from WooCommerce PDF Invoices & Packing Slips
    $pdf_url = '';
    if ( class_exists( '\WPO\WCPDF\WCPDF' ) ) {
        $doc = \WPO\WCPDF\WCPDF()->export->get_document( 'invoice', $order );
        if ( $doc ) {
            $pdf_url = $doc->get_pdf_url();
        }
    }
    if ( ! $pdf_url ) {
        error_log( "Invoice PDF not found for order {$order_id}" );
        return;
    }

    // 4) Build the WhatsApp Cloud API payload
    $phone_number_id        = '580937845092536';           // your Phone Number ID
    $whatsapp_endpoint      = "https://graph.facebook.com/v16.0/{$phone_number_id}/messages";
    $permanent_access_token = 'EAANmhnIVI0sBOZBVZAIYaBaQfITsBQMH2xk0i6ORJJuu28Rd27hfliypagI8WUGteZAZC8Nwgjpjm1QfOtpeX9r2Pmx6dZBM00XGwVqCFwZBrdTWZAZCAgT1ydbpEY69zjey5c98LP4kRJC0EI4ZBCbmcF8iqMZAPYjZABhAbdiaRJtxKT3AzZBM53J4udn4WmiB1c7KGAZDZD';              // your long-lived token

    $payload = [
        'messaging_product' => 'whatsapp',
        'to'                => $phone,
        'type'              => 'template',
        'template'          => [
            'name'       => 'utility_bill',
            'language'   => [ 'code' => 'en' ],
            'components' => [
                [
                    'type'       => 'header',
                    'parameters' => [
                        [
                            'type'     => 'document',
                            'document' => [
                                'link'     => $pdf_url,
                                'filename' => "Invoice-{$order_id}.pdf",
                            ],
                        ],
                    ],
                ],
                [
                    'type'       => 'body',
                    'parameters' => [
                        [ 'type' => 'text', 'text' => (string) $total_amount ],
                        [ 'type' => 'text', 'text' => $customer_name ],
                    ],
                ],
            ],
        ],
    ];

    // 5) Send it with WPHTTP
    $args = [
        'headers' => [
            'Authorization' => "Bearer {$permanent_access_token}",
            'Content-Type'  => 'application/json',
        ],
        'body'    => wp_json_encode( $payload ),
        'timeout' => 20,
    ];

    $response = wp_remote_post( $whatsapp_endpoint, $args );
    if ( is_wp_error( $response ) ) {
        error_log( "WhatsApp API error: " . $response->get_error_message() );
    } else {
        error_log( "WhatsApp response: " . wp_remote_retrieve_body( $response ) );
    }
}
