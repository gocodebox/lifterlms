<?php
/**
 * Order information template part.
 *
 * @package LifterLMS/Templates
 *
 * @since 6.0.0
 * @version 6.0.0
 *
 * @var LLMS_Order                    $order   Current order object.
 * @var LLMS_Payment_Gateway|WP_Error $gateway Instance of the LLMS_Payment_Gateway extending class used for the payment.
 *                                             WP_Error if the gateway cannot be located, e.g. because it's no longer enabled.
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="order-primary">

	<table class="orders-table">
		<tbody>
			<tr>
				<th><?php _e( 'Status', 'lifterlms' ); ?></th>
				<td><?php echo $order->get_status_name(); ?></td>
			</tr>

			<tr>
				<th><?php _e( 'Access Plan', 'lifterlms' ); ?></th>
				<td><?php echo $order->get( 'plan_title' ); ?></td>
			</tr>

			<tr>
				<th><?php _e( 'Product', 'lifterlms' ); ?></th>
				<td>
				<?php if ( llms_get_post( $order->get( 'product_id' ) ) ) : ?>
					<a href="<?php echo get_permalink( $order->get( 'product_id' ) ); ?>"><?php echo $order->get( 'product_title' ); ?></a>
				<?php else : ?>
					<?php echo __( '[DELETED]', 'lifterlms' ) . ' ' . $order->get( 'product_title' ); ?>
				<?php endif; ?>
				</td>
			</tr>
			<?php if ( $order->has_trial() ) : ?>
				<?php if ( $order->has_coupon() && $order->get( 'coupon_amount_trial' ) ) : ?>
					<tr>
						<th><?php _e( 'Original Total', 'lifterlms' ); ?></th>
						<td><?php echo $order->get_price( 'trial_original_total' ); ?></td>
					</tr>

					<tr>
						<th><?php _e( 'Coupon Discount', 'lifterlms' ); ?></th>
						<td>
							<?php echo $order->get_coupon_amount( 'trial' ); ?>
							(<?php echo llms_price( $order->get_price( 'coupon_value_trial', array(), 'float' ) * - 1 ); ?>)
							[<code><?php echo $order->get( 'coupon_code' ); ?></code>]
						</td>
					</tr>
				<?php endif; ?>

				<tr>
					<th><?php _e( 'Trial Total', 'lifterlms' ); ?></th>
					<td>
						<?php echo $order->get_price( 'trial_total' ); ?>
						<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'trial_length' ), 'lifterlms' ), $order->get( 'trial_length' ), $order->get( 'trial_period' ) ); ?>
					</td>
				</tr>
			<?php endif; ?>

			<?php if ( $order->has_discount() ) : ?>
				<tr>
					<th><?php _e( 'Original Total', 'lifterlms' ); ?></th>
					<td><?php echo $order->get_price( 'original_total' ); ?></td>
				</tr>

				<?php if ( $order->has_sale() ) : ?>
					<tr>
						<th><?php _e( 'Sale Discount', 'lifterlms' ); ?></th>
						<td>
							<?php echo $order->get_price( 'sale_price' ); ?>
							(<?php echo llms_price( $order->get_price( 'sale_value', array(), 'float' ) * -1 ); ?>)
						</td>
					</tr>
				<?php endif; ?>

				<?php if ( $order->has_coupon() ) : ?>
					<tr>
						<th><?php _e( 'Coupon Discount', 'lifterlms' ); ?></th>
						<td>
							<?php echo $order->get_coupon_amount( 'regular' ); ?>
							(<?php echo llms_price( $order->get_price( 'coupon_value', array(), 'float' ) * - 1 ); ?>)
							[<code><?php echo $order->get( 'coupon_code' ); ?></code>]
						</td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>

			<tr>
				<th><?php _e( 'Total', 'lifterlms' ); ?></th>
				<td>
					<?php echo $order->get_price( 'total' ); ?>
					<?php if ( $order->is_recurring() ) : ?>
						<?php
						printf(
							// Translators: %1$d = the billing frequency; %2$s = the billing period.
							_n( // phpcs:ignore: WordPress.WP.I18n.MismatchedPlaceholders -- It's not an error.
								'Every %2$s', // phpcs:ignore: WordPress.WP.I18n.MissingSingularPlaceholder -- It works as expected despite the CS warning.
								'Every %1$d %2$ss',
								$order->get( 'billing_frequency' ),
								'lifterlms'
							),
							$order->get( 'billing_frequency' ),
							$order->get( 'billing_period' )
						);
						?>
						<?php if ( $order->get( 'billing_cycle' ) > 0 ) : ?>
							<?php printf( _n( 'for %1$d %2$s', 'for %1$d %2$ss', $order->get( 'billing_cycle' ), 'lifterlms' ), $order->get( 'billing_cycle' ), $order->get( 'billing_period' ) ); ?>
						<?php endif; ?>
					<?php else : ?>
						<?php _e( 'One-time', 'lifterlms' ); ?>
					<?php endif; ?>
				</td>
			</tr>

			<tr>
				<th><?php _e( 'Payment Method', 'lifterlms' ); ?></th>
				<td>
					<?php if ( is_wp_error( $gateway ) ) : ?>
						<?php echo $order->get( 'payment_gateway' ); ?>
					<?php else : ?>
						<?php echo $gateway->get_title(); ?>
					<?php endif; ?>
					<?php
						/**
						 * Action run immediately after the payment method is output within the view order information template.
						 *
						 * @since Unknown
						 *
						 * @param LLMS_Order $order Order object.
						 */
						do_action( 'lifterlms_view_order_after_payment_method', $order );
					?>
				</td>
			</tr>

			<tr>
				<th><?php _e( 'Start Date', 'lifterlms' ); ?></th>
				<td><?php echo $order->get_date( 'date', 'F j, Y' ); ?></td>
			</tr>
			<?php if ( $order->is_recurring() ) : ?>
				<tr>
					<th><?php _e( 'Last Payment Date', 'lifterlms' ); ?></th>
					<td><?php echo $order->get_last_transaction_date( 'llms-txn-succeeded', 'any', 'F j, Y' ); ?></td>
				</tr>

				<?php if ( 'llms-pending-cancel' !== $order->get( 'status' ) ) : ?>
					<tr>
						<th><?php _e( 'Next Payment Date', 'lifterlms' ); ?></th>
						<td>
							<?php if ( $order->has_scheduled_payment() ) : ?>
								<?php echo $order->get_next_payment_due_date( 'F j, Y' ); ?>
							<?php else : ?>
								&ndash;
							<?php endif; ?>
						</td>
					</tr>
				<?php endif; ?>
			<?php endif; ?>

			<?php if ( ! $order->is_recurring() || 'lifetime' !== $order->get( 'access_expiration' ) || 'llms-pending-cancel' === $order->get( 'status' ) ) : ?>
			<tr>
				<th><?php _e( 'Expiration Date', 'lifterlms' ); ?></th>
				<td><?php echo $order->get_access_expiration_date( 'F j, Y' ); ?></td>
			</tr>
			<?php endif; ?>

			<?php
				/**
				 * Action run before the closing of the `<tbody>` element on the view orders information table.
				 *
				 * @since Unknown
				 *
				 * @param LLMS_Order $order Order object.
				 */
				do_action( 'lifterlms_view_order_table_body', $order );
			?>
		</tbody>
	</table>
</section>
