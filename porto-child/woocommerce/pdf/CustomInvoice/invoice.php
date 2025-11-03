<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<?php
// Pull Cinzel & Figtree from Google Fonts
echo '<style type="text/css">
  @import url("https://fonts.googleapis.com/css2?family=Cinzel&display=swap");
  @import url("https://fonts.googleapis.com/css2?family=Figtree&display=swap");

  /* Default body text */
  body, table, ul, td, p {
    font-family: "Figtree", sans-serif !important;
  }
  /* Headings, table headers, anything marked .cinzel */
  .cinzel, th, h1, h2, h3, .heading {
    font-family: "Cinzel", serif !important;
  }
</style>';
?>

<?php echo $css; // plugin + your custom style.css ?>

<htmlpageheader name="invoiceHeader">

  <!-- 1) Centered, enlarged logo at the very top -->
  <div style="text-align:center; margin:20px 0 0;">
    <img
      src="https://tilattama.com/wp-content/uploads/2025/05/Tilattama-Logo.png"
      alt="Tilattama Logo"
      style="width:480px; height:auto; display:inline-block;"
    />
  </div>

  <!-- 2) Full-width top border, tiled horizontally -->
  <div style="
      width:100%;
      height:120px;
      margin:0;
      background: url('https://tilattama.com/wp-content/uploads/2025/05/ChatGPT-Image-May-3-2025-11_22_07-AM.png') repeat-x top center;
      background-size: auto 120px;
      display:block;
    "></div>

</htmlpageheader>
<sethtmlpageheader name="invoiceHeader" value="on" show-this-page="1" />

<!-- BILL TO & INVOICE HEADINGS -->
<table style="
    width:90%;
    margin:0 auto;
    color:#6A0A2A;
    font-size:17pt;
">
  <tr>
    <td style="vertical-align:top;">
      <div class="heading" style="
          font-size:22pt;
          color:#6A0A2A;
          font-weight:bold;
          text-transform:capitalize;
          margin-bottom:8px;
      ">Bill To</div>
      <?php echo esc_html( $this->order->get_formatted_billing_full_name() ); ?><br>
      <?php echo nl2br( esc_html( $this->order->get_formatted_billing_address() ) ); ?><br>
      <?php if ( $this->order->get_billing_phone() ) : ?>
        <?php echo esc_html( $this->order->get_billing_phone() ); ?><br>
      <?php endif; ?>
    </td>
    <td style="vertical-align:top; text-align:right;">
      <div class="heading" style="
          font-size:22pt;
          color:#6A0A2A;
          font-weight:bold;
          text-transform:capitalize;
          margin-bottom:8px;
      ">Invoice</div>
      Invoice No: <?php echo esc_html( $this->order->get_order_number() ); ?><br>
      <?php echo esc_html( $this->order->get_date_created()->date_i18n( 'd-M-Y, g:i A' ) ); ?>
    </td>
  </tr>
</table>

<!-- Bottom full-width border + centered flourish -->
<div style="
    width:100%;
    border-top:1px solid #6A0A2A;
    margin:20px 0;
    position:relative;
    padding-bottom:10px;
">
  <div style="
      position:absolute;
      top:-18px;
      left:50%;
      transform:translateX(-50%);
      background-color:#fff0d8;
      padding:0 12px;
  ">
    <img
      src="https://tilattama.com/wp-content/uploads/2025/05/Icon-maroon-for-favicon.png"
      alt="Flourish"
      style="width:34px; height:auto;"
    />
  </div>
</div>

<!-- ITEMS TABLE WITH SOFTENED EDGES & ONLY TOTAL ROW -->
<table style="
    width: 90%;
    margin: 0 auto 40px;
    border: 2px solid #6A0A2A;
    border-collapse: collapse;
    border-radius: 12px;
    overflow: hidden;
    font-size: 16pt;
">
  <thead>
    <tr style="background-color: #6A0A2A; color: #FFF;">
      <th class="cinzel" style="padding:16px; font-size:18pt; text-align:left; width:5%;">#</th>
      <th class="cinzel" style="padding:16px; font-size:18pt; text-align:left; width:55%;">Purchase</th>
      <th class="cinzel" style="padding:16px; font-size:18pt; text-align:center; width:10%;">HSN</th>
      <th class="cinzel" style="padding:16px; font-size:18pt; text-align:center; width:10%;">Qty</th>
      <th class="cinzel" style="padding:16px; font-size:18pt; text-align:right; width:20%;">Amount</th>
    </tr>
  </thead>
  <tbody>
    <?php
      $i = 1;
      foreach ( $this->order->get_items() as $item_id => $item ) :
        $product = $item->get_product();
        $color   = wc_get_order_item_meta( $item_id, 'Color', true );
        $hsn     = wc_get_order_item_meta( $item_id, 'HSN',   true );
    ?>
    <tr style="background-color:#FFF0D8;">
      <td style="padding:14px; border-bottom:1px solid #6A0A2A; text-align:center;"><?php echo $i++; ?></td>
      <td style="padding:14px; border-bottom:1px solid #6A0A2A;">
        <?php echo esc_html( $item->get_name() ); ?>
        <?php if ( $product && $product->get_sku() ) : ?>– <?php echo esc_html( $product->get_sku() ); ?><?php endif; ?>
        <?php if ( $color ) : ?>, Color: <?php echo esc_html( $color ); ?><?php endif; ?>
      </td>
      <td style="padding:14px; border-bottom:1px solid #6A0A2A; text-align:center;"><?php echo esc_html( $hsn ); ?></td>
      <td style="padding:14px; border-bottom:1px solid #6A0A2A; text-align:center;"><?php echo esc_html( $item->get_quantity() ); ?></td>
      <td style="padding:14px; border-bottom:1px solid #6A0A2A; text-align:right;"><?php echo wp_kses_post( wc_price( $item->get_total() ) ); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
  <tfoot>
    <tr style="
        font-family:'Cinzel', serif;
        font-size:18pt;
        font-weight:bold;
        color:#6A0A2A;
      ">
      <td colspan="4" style="padding:12px; text-align:right;">Total</td>
      <td style="padding:12px; text-align:right;"><?php echo wp_kses_post( wc_price( $this->order->get_total() ) ); ?></td>
    </tr>
  </tfoot>
</table>

<!-- TERMS & CONDITIONS HEADER -->
<div style="position: relative; width: 100%; margin: 40px 0;">
  <hr style="border: none; border-top: 1px solid #6A0A2A; margin: 0;">
  <div style="
      position: absolute;
      top: -0.7em;
      left: 50%;
      transform: translateX(-50%);
      background-color: #fff0d8;
      padding: 0 12px;
      font-family: 'Cinzel', serif;
      font-size: 18pt;
      font-weight: bold;
      text-transform: uppercase;
      color: #6A0A2A;
      white-space: nowrap;
    ">
    Terms and Conditions
  </div>
</div>

<ul style="
    margin: 0px 20 20px 20px;    /* added 20px top margin for padding before the list */
    padding: 10;
    list-style: disc;
    font-family: 'Figtree', sans-serif;
    font-size: 14pt;
    color: #6A0A2A;
">
  <li>Sarees &amp; Kurtis are exchangeable within 7 days of purchase</li>
  <li>Product tags must remain intact for any exchange</li>
  <li>Blouses &amp; Jewellery are not eligible for exchange or refund</li>
  <li>No refunds are provided post-purchase</li>
</ul>

</div>
</div>
<!-- Bottom full-width border + centered flourish -->
<div style="
    width:100%;
    border-top:1px solid #6A0A2A;
    margin:40px 0;
    position:fixed;
  
">
  <div style="
      position:absolute;
      top:-18px;
      left:50%;
      transform:translateX(-50%);
      background-color:#fff0d8;
      padding:0 12px;
  ">
    <img
      src="https://tilattama.com/wp-content/uploads/2025/05/Icon-maroon-for-favicon.png"
      alt="Flourish"
      style="width:34px; height:auto;"
    />
  </div>
</div>
</div><!-- END MAIN CONTENT -->

<!-- DEFINE A STATIC FOOTER AT THE BOTTOM -->
<htmlpagefooter name="docFooter">
  <div style="
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
      background-color: #fff0d8;
      padding: 0;
      margin: 0;
  ">
    
    <!-- company details -->
    <div style="
        text-align:center;
        font-size:10pt;
        font-family:'Figtree',sans-serif;
        color:#6A0A2A;
        padding:8px 0 12px;
        margin:0;
    ">
      Chandannagar, West Bengal – 712136, India · tilattamastudio@gmail.com<br>
      https://tilattama.co · +91 87774 81546 · GSTIN: 19AOFPC6139M1ZN
    </div>
  </div>
</htmlpagefooter>
<sethtmlpagefooter name="docFooter" value="on" show-this-page="1" />

<?php do_action( 'wpo_wcpdf_after_document', $this->get_type(), $this->order ); ?>