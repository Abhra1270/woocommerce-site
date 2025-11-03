<?php
/**
 * Order tracking
 *
 * @version 2.2.0
 */

defined( 'ABSPATH' ) || exit;

?>
<p class="order-info">
	<?php
	echo wp_kses_post(
		apply_filters(
			'woocommerce_order_tracking_status',
			sprintf(
				/* translators: 1: order number 2: order date 3: order status */
				__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
				'<mark class="order-number">' . $order->get_order_number() . '</mark>',
				'<mark class="order-date">' . wc_format_datetime( $order->get_date_created() ) . '</mark>',
				'<mark class="order-status">' . wc_get_order_status_name( $order->get_status() ) . '</mark>'
			)
		)
	);
	?>
</p>

<?php

$notes = $order->get_customer_order_notes();

if ( $notes ) :
	?>
	<div class="featured-box align-left">
		<div class="box-content">
			<h2><?php esc_html_e( 'Order Updates', 'porto' ); ?></h2>
			<ol class="commentlist notes">
				<?php foreach ( $notes as $note ) : ?>
				<li class="comment note">
					<div class="comment_container">
						<div class="comment-text">
							<p class="meta"><?php echo date_i18n( esc_html__( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); ?></p>
							<div class="description">
								<?php echo function_exists( 'porto_shortcode_format_content' ) ? porto_shortcode_format_content( $note->comment_content ) : wp_kses_post( $note->comment_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</div>
							<div class="clear"></div>
						</div>
						<div class="clear"></div>
					</div>
				</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>
