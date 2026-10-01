<?php
/**
 * Single customer overview tab
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @property LLMS_Student    $student       Student / customer.
 * @property array           $metrics       Commerce metrics.
 * @property array           $orders_result Orders result array.
 * @property LLMS_Order|null $latest_order  Latest order.
 * @property string          $reporting_url Students reporting URL.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$courses     = $student->get_courses(
	array(
		'limit' => 1,
	)
);
$memberships = count( $student->get_membership_levels() );
?>
<div class="llms-reporting-tab-content">

	<section class="llms-reporting-tab-main llms-reporting-widgets">

		<header>
			<h3><?php esc_html_e( 'Customer overview', 'lifterlms' ); ?></h3>
		</header>

		<div class="llms-reporting-widgets-grid">
			<?php
			LLMS_Admin_Reporting::output_widget(
				array(
					'cols'      => 'd-1of3',
					'icon'      => 'money',
					'id'        => 'llms-customer-ltv',
					'data'      => $metrics['ltv'],
					'data_type' => 'monetary',
					'text'      => __( 'Lifetime value', 'lifterlms' ),
				)
			);

			LLMS_Admin_Reporting::output_widget(
				array(
					'cols' => 'd-1of3',
					'icon' => 'shopping-cart',
					'id'   => 'llms-customer-orders',
					'data' => $metrics['order_count'],
					'text' => __( 'Orders', 'lifterlms' ),
				)
			);

			LLMS_Admin_Reporting::output_widget(
				array(
					'cols'      => 'd-1of3',
					'icon'      => 'line-chart',
					'id'        => 'llms-customer-aov',
					'data'      => $metrics['aov'],
					'data_type' => 'monetary',
					'text'      => __( 'Average order value', 'lifterlms' ),
				)
			);

			LLMS_Admin_Reporting::output_widget(
				array(
					'cols'      => 'd-1of3',
					'icon'      => 'undo',
					'id'        => 'llms-customer-refunded',
					'data'      => $metrics['refunded'],
					'data_type' => 'monetary',
					'impact'    => 'negative',
					'text'      => __( 'Refunded', 'lifterlms' ),
				)
			);

			LLMS_Admin_Reporting::output_widget(
				array(
					'cols' => 'd-1of3',
					'icon' => 'refresh',
					'id'   => 'llms-customer-active-subs',
					'data' => $metrics['active_recurring_count'],
					'text' => __( 'Active subscriptions', 'lifterlms' ),
				)
			);

			LLMS_Admin_Reporting::output_widget(
				array(
					'cols'      => 'd-1of3',
					'icon'      => 'calendar',
					'id'        => 'llms-customer-first-order',
					'data'      => $metrics['first_order_date'] ? date_i18n( get_option( 'date_format' ), strtotime( $metrics['first_order_date'] ) ) : '&ndash;',
					'data_type' => 'text',
					'text'      => __( 'First order', 'lifterlms' ),
				)
			);

			LLMS_Admin_Reporting::output_widget(
				array(
					'cols'      => 'd-1of3',
					'icon'      => 'calendar',
					'id'        => 'llms-customer-last-order',
					'data'      => $metrics['last_order_date'] ? date_i18n( get_option( 'date_format' ), strtotime( $metrics['last_order_date'] ) ) : '&ndash;',
					'data_type' => 'text',
					'text'      => __( 'Last order', 'lifterlms' ),
				)
			);
			?>
		</div>

		<header>
			<h3><?php esc_html_e( 'Recent orders', 'lifterlms' ); ?></h3>
		</header>
		<?php llms_get_template( 'admin/customers/orders-table.php', array( 'orders_result' => $orders_result ) ); ?>

	</section>

	<aside class="llms-reporting-tab-side">

		<header>
			<h3><?php esc_html_e( 'Billing', 'lifterlms' ); ?></h3>
		</header>
		<?php if ( $latest_order ) : ?>
			<ul class="llms-customer-billing">
				<li>
					<strong><?php esc_html_e( 'Name', 'lifterlms' ); ?></strong><br>
					<?php echo esc_html( $latest_order->get_customer_name() ); ?>
				</li>
				<li>
					<strong><?php esc_html_e( 'Email', 'lifterlms' ); ?></strong><br>
					<a href="<?php echo esc_url( 'mailto:' . $latest_order->get( 'billing_email' ) ); ?>">
						<?php echo esc_html( $latest_order->get( 'billing_email' ) ); ?>
					</a>
				</li>
				<?php if ( $latest_order->get( 'billing_phone' ) ) : ?>
					<li>
						<strong><?php esc_html_e( 'Phone', 'lifterlms' ); ?></strong><br>
						<?php echo esc_html( $latest_order->get( 'billing_phone' ) ); ?>
					</li>
				<?php endif; ?>
				<li>
					<strong><?php esc_html_e( 'Address', 'lifterlms' ); ?></strong><br>
					<?php
					$address  = $latest_order->get( 'billing_address_1' );
					$address .= $latest_order->get( 'billing_address_2' ) ? ' ' . $latest_order->get( 'billing_address_2' ) : '';
					$address .= '<br>' . $latest_order->get( 'billing_city' ) . ', ' . $latest_order->get( 'billing_state' ) . ' ' . $latest_order->get( 'billing_zip' );
					$address .= ' ' . $latest_order->get( 'billing_country' );
					echo wp_kses_post( $address );
					?>
				</li>
			</ul>
		<?php else : ?>
			<p><?php esc_html_e( 'No billing information available.', 'lifterlms' ); ?></p>
		<?php endif; ?>

		<header>
			<h3><?php esc_html_e( 'Enrollments', 'lifterlms' ); ?></h3>
		</header>
		<ul class="llms-customer-enrollments">
			<li>
				<?php
				printf(
					/* translators: %d: number of course enrollments */
					esc_html( _n( '%d course', '%d courses', absint( $courses['found'] ), 'lifterlms' ) ),
					absint( $courses['found'] )
				);
				?>
			</li>
			<li>
				<?php
				printf(
					/* translators: %d: number of memberships */
					esc_html( _n( '%d membership', '%d memberships', $memberships, 'lifterlms' ) ),
					absint( $memberships )
				);
				?>
			</li>
			<li>
				<a href="<?php echo esc_url( $reporting_url ); ?>"><?php esc_html_e( 'View student report', 'lifterlms' ); ?></a>
			</li>
		</ul>

	</aside>

</div>
