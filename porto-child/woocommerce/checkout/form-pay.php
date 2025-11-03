<?php
// wp-content/themes/porto-child/woocommerce/checkout/form-pay.php
defined( 'ABSPATH' ) || exit;

$child    = get_stylesheet_directory_uri();
$order_id = absint( get_query_var( 'order-pay' ) );
$order    = wc_get_order( $order_id );
if ( ! $order ) {
    wp_die( __( 'Invalid order.', 'woocommerce' ) );
}

status_header(200);
nocache_headers();
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Montserrat Font -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
  <?php wp_head(); ?>
  <style>
    /* full-page bg & font */
    html, body, .page-wrapper, .container {
      margin:0; padding:0; background:#FFEFD8!important;
      font-family:'Montserrat',sans-serif; width:100%!important; min-height:100vh;
    }
    body.woocommerce-order-pay { overflow-x:hidden; padding-bottom:100px; }

    /* hide parent theme wrappers */
    body.woocommerce-order-pay .header-wrapper,
    body.woocommerce-order-pay .footer-wrapper,
    body.woocommerce-order-pay #footer,
    body.woocommerce-order-pay .porto-block,
    body.woocommerce-order-pay .woo-page-header,
    body.woocommerce-order-pay .elementor-element {
      display:none!important;
    }

    /* container */
    .wrap {
      max-width:480px; margin:0 auto; padding:16px;
      background:#FFEFD8; border-radius:12px;
    }

    /* logo */
    .brand-logo {
      display:block; margin:0 auto 16px;
      max-width:200px; height:auto;
    }

    /* cart summary */
    .order-summary-card {
      background:#FFF4E6; border-radius:12px;
      box-shadow:0 4px 8px rgba(0,0,0,.1);
      margin-bottom:16px; overflow:hidden;
    }
    .order-summary-card .card-header {
      background:#FFF4E6; padding:16px; display:flex; align-items:center;
      border-bottom:1px solid rgba(0,0,0,.1);
    }
    .order-summary-card .card-header img {
      width:24px; height:24px; margin-right:8px;
    }
    .order-summary-card .card-header .title {
      font-size:18px; font-weight:600; color:#680527;
    }
    .order-summary-item {
      display:flex; align-items:center;
      padding:12px 16px; border-bottom:1px solid rgba(0,0,0,.1);
    }
    .order-summary-item:last-of-type { border-bottom:none; }
    .order-summary-item img.thumb,
    .order-summary-item img.truck {
      width:64px; height:64px; object-fit:cover;
      border-radius:8px; margin-right:12px;
    }
    .order-details .name {
      font-size:16px; font-weight:600; color:#333; margin-bottom:4px;
    }
    .order-details .variation,
    .order-details .qtyprice {
      font-size:14px; color:#777; margin-bottom:2px;
    }
    .order-total-footer {
      background:#680527; padding:12px 16px;
      display:flex; justify-content:space-between; align-items:center;
    }
    .order-total-footer .label,
    .order-total-footer .value {
      font-size:16px; font-weight:600; color:#fff;
    }

    /* address/shipping block */
    .address-form {
      background:#FFF4E6; border-radius:12px;
      padding:16px; margin-bottom:16px;
      box-shadow:0 4px 8px rgba(0,0,0,.1);
    }
    .address-form .address-header {
      display:flex; align-items:center; margin-bottom:12px;
    }
    .address-form .address-header img {
      width:24px; height:24px; margin-right:8px;
    }
    .address-form .address-header h3 {
      margin:0; font-size:18px; font-weight:600; color:#680527;
    }
    .address-form .form-row {
      margin-bottom:12px;
    }

    /* hide default place-order button */
    .woocommerce-checkout-payment .form-row.place-order {
      display:none!important;
    }

    /* Pay Now bar */
    .checkout-bar {
      position:fixed; bottom:0; left:0; right:0;
      background:#fff; border-top:1px solid #ddd;
      max-width:480px; width:100%; margin:0 auto;
      display:flex; justify-content:space-between; align-items:center;
      padding:12px 16px; box-shadow:0 -2px 4px rgba(0,0,0,.05);
    }
    .checkout-bar .total {
      font-size:16px; font-weight:600; color:#333;
    }
    #checkoutBtn {
      background:#680527; color:#fff; border:none;
      border-radius:8px; padding:10px 20px;
      font-size:16px; opacity:.5; cursor:not-allowed;
    }
    #checkoutBtn.enabled { opacity:1; cursor:pointer; }
  </style>
</head>
<body <?php body_class( 'woocommerce-order-pay' ); ?>>

  <div class="wrap">

    <!-- Logo -->
    <img src="<?php echo esc_url( $child . '/images/Tilattama%20Logo%20(1).jpg' ); ?>"
         alt="Tilattama Logo" class="brand-logo" />

    <form id="order_review"
          name="order_review"
          method="post"
          class="woocommerce-pay-form"
          action="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">

      <!-- Your Cart -->
      <div class="order-summary-card">
        <div class="card-header">
          <img src="<?php echo esc_url( $child . '/images/favicon%20maroon.ico' ); ?>" alt="Cart Icon">
          <div class="title"><?php _e( 'Your Cart', 'woocommerce' ); ?></div>
        </div>
        <?php foreach ( $order->get_items() as $item ) :
          $prod  = $item->get_product();
          $thumb = $prod && $prod->get_image_id()
                   ? wp_get_attachment_image_url( $prod->get_image_id(), 'thumbnail' )
                   : wc_placeholder_img_src();
          $var   = wc_get_formatted_variation( $item );
          $qp    = sprintf( 'qty – %s | price – %s',
                            $item->get_quantity(),
                            wc_price( $item->get_total() ) );
        ?>
          <div class="order-summary-item">
            <img src="<?php echo esc_url( $thumb ); ?>" class="thumb" alt="">
            <div class="order-details">
              <div class="name"><?php echo esc_html( $item->get_name() ); ?></div>
              <?php if ( $var ) : ?><div class="variation"><?php echo wp_kses_post( $var ); ?></div><?php endif; ?>
              <div class="qtyprice"><?php echo wp_kses_post( $qp ); ?></div>
            </div>
          </div>
        <?php endforeach; ?>

        <?php if ( $order->get_shipping_total() > 0 ) : ?>
          <div class="order-summary-item">
            <img src="<?php echo esc_url( $child . '/images/truck.png' ); ?>" class="truck" alt="Shipping Icon">
            <div class="order-details">
              <div class="name"><?php _e( 'Shipping', 'woocommerce' ); ?></div>
              <div class="qtyprice"><?php echo wc_price( $order->get_shipping_total() ); ?></div>
            </div>
          </div>
        <?php endif; ?>

        <div class="order-total-footer">
          <span class="label"><?php _e( 'Total', 'woocommerce' ); ?></span>
          <span class="value"><?php echo wc_price( $order->get_total() ); ?></span>
        </div>
      </div>

      <!-- Your Address (shipping) -->
      <div class="address-form">
        <div class="address-header">
          <img src="<?php echo esc_url( $child . '/images/favicon%20maroon.ico' ); ?>" alt="Address Icon">
          <h3><?php _e( 'Your Address', 'woocommerce' ); ?></h3>
        </div>
        <?php do_action( 'woocommerce_before_checkout_shipping_form', $order ); ?>
        <div class="woocommerce-shipping-fields__field-wrapper">
          <?php
  // grab all shipping fields…
  $fields = WC()->checkout()->get_checkout_fields( 'shipping' );

  // remove the extra address line
  unset( $fields['shipping_address_2'] );

  // relabel & re-placeholder the single address field
  if ( isset( $fields['shipping_address_1'] ) ) {
    $fields['shipping_address_1']['label']       = __( 'Address', 'woocommerce' );
    $fields['shipping_address_1']['placeholder'] = __( 'House no., street name, apartment/suite/unit, etc.', 'woocommerce' );
  }

  // render the remaining fields
  foreach ( $fields as $key => $field ) {
    $value = $order->get_meta( "_{$key}" );
    woocommerce_form_field( $key, $field, $value );
  }
?>
        </div>
        <!-- Email & Shipping Phone -->
        <div class="form-row form-row-wide">
          <label for="billing_email"><?php _e( 'Email', 'woocommerce' ); ?></label>
          <input type="email"
                 name="billing_email"
                 id="billing_email"
                 class="input-text"
                 placeholder="<?php esc_attr_e( 'Email address', 'woocommerce' ); ?>">
        </div>
        <div class="form-row form-row-wide">
          <label for="shipping_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
          <input type="tel"
                 name="shipping_phone"
                 id="shipping_phone"
                 class="input-text"
                 placeholder="<?php esc_attr_e( 'Phone number', 'woocommerce' ); ?>">
        </div>
        <?php do_action( 'woocommerce_after_checkout_shipping_form', $order ); ?>
      </div>

      <!-- payment methods & hidden place-order -->
      <?php if ( $order->needs_payment() ) : ?>
        <div class="woocommerce-checkout-payment">
          <?php
            do_action( 'woocommerce_review_order_before_payment' );
            foreach ( WC()->payment_gateways()->get_available_payment_gateways() as $gateway ) {
              wc_get_template( 'checkout/payment-method.php', [ 'gateway' => $gateway ] );
            }
            do_action( 'woocommerce_review_order_after_payment' );
          ?>
          <div class="form-row place-order">
            <?php wp_nonce_field( 'woocommerce-pay', 'woocommerce-pay-nonce' ); ?>
            <button type="submit"
                    name="woocommerce_pay"
                    id="place_order"
                    class="button alt">
              <?php esc_html_e( 'Pay for order', 'woocommerce' ); ?>
            </button>
          </div>
        </div>
      <?php endif; ?>

    </form>
  </div><!-- .wrap -->

  <!-- Pay Now bar -->
  <div class="checkout-bar">
    <div class="total"><?php echo wc_price( $order->get_total() ); ?></div>
    <button id="checkoutBtn"><?php esc_html_e( 'Pay Now', 'woocommerce' ); ?></button>
  </div>

  <?php wp_footer(); ?>
  <script>
  (function($){
    // auto‑select Razorpay
    var $rz = $('input[name="payment_method"].payment_method_razorpay');
    if ( $rz.length ) {
      $rz.prop('checked', true ).trigger('change');
    }

    // validate required fields
    var req = [
      'shipping_first_name','shipping_last_name','shipping_address_1',
      'shipping_city','shipping_state','shipping_postcode',
      'billing_email','shipping_phone'
    ],
    btn = $('#checkoutBtn'),
    nativeBtn = $('#place_order');

    function validate(){
      var ok = req.every(function(name){
        var el = $('[name="'+name+'"]');
        return el.length && el.val().trim() !== '';
      });
      btn.prop('disabled', !ok ).toggleClass('enabled', ok);
    }

    req.forEach(function(name){
      $('[name="'+name+'"]').on('input change', validate);
    });
    validate();

    btn.on('click', function(e){
      e.preventDefault();
      if ( ! btn.hasClass('enabled') ) {
        alert('<?php echo esc_js( __( 'Please complete all fields before proceeding.', 'woocommerce' ) ); ?>');
        return;
      }
      nativeBtn.trigger('click');
    });
  })(jQuery);
  </script>
</body>
</html>
