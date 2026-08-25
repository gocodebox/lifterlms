<?php
/**
 * Single customer admin template
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since [version]
 * @version [version]
 *
 * @property LLMS_Student $student       Student / customer.
 * @property array        $metrics       Commerce metrics.
 * @property string       $current_tab   Current sub-tab slug.
 * @property array        $orders_result Result from LLMS_Student::get_orders().
 * @property LLMS_Order|null $latest_order Latest order or null.
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}

$customer_tabs = array(
	'overview' => __( 'Overview', 'lifterlms' ),
	'orders'   => __( 'Orders', 'lifterlms' ),
);

$reporting_url = add_query_arg(
	array(
		'page'       => 'llms-reporting',
		'tab'        => 'students',
		'student_id' => $student->get_id(),
	),
	admin_url( 'admin.php' )
);
?>
<div class="wrap lifterlms llms-reporting llms-customers-wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'Customers', 'lifterlms' ); ?></h1>
	<hr class="wp-header-end">

	<section class="llms-reporting-tab llms-customer-single">

		<header class="llms-reporting-breadcrumbs">
			<a href="<?php echo esc_url( llms_get_customers_admin_url() ); ?>"><?php esc_html_e( 'Customers', 'lifterlms' ); ?></a>
			<a href="<?php echo esc_url( llms_get_customers_admin_url( $student->get_id() ) ); ?>"><?php echo esc_html( $student->get_name() ); ?></a>
		</header>

		<div class="llms-reporting-body">

			<header class="llms-reporting-header">
				<div class="llms-reporting-header-img">
					<?php echo wp_kses_post( $student->get_avatar( 64 ) ); ?>
				</div>
				<div class="llms-reporting-header-info">
					<h2>
						<a href="<?php echo esc_url( get_edit_user_link( $student->get_id() ) ); ?>">
							<?php echo esc_html( $student->get_name() ); ?>
						</a>
					</h2>
					<h5>
						<a href="<?php echo esc_url( 'mailto:' . $student->get( 'user_email' ) ); ?>">
							<?php echo esc_html( $student->get( 'user_email' ) ); ?>
						</a>
					</h5>
					<p class="llms-customer-header-links">
						<a href="<?php echo esc_url( get_edit_user_link( $student->get_id() ) ); ?>"><?php esc_html_e( 'Edit user', 'lifterlms' ); ?></a>
						&nbsp;|&nbsp;
						<a href="<?php echo esc_url( $reporting_url ); ?>"><?php esc_html_e( 'View student report', 'lifterlms' ); ?></a>
					</p>
				</div>
			</header>

			<nav class="llms-nav-tab-wrapper llms-nav-secondary">
				<ul class="llms-nav-items">
					<?php foreach ( $customer_tabs as $name => $label ) : ?>
						<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
							<a class="llms-nav-link" href="<?php echo esc_url( llms_get_customers_admin_url( $student->get_id(), array( 'stab' => $name ) ) ); ?>">
								<?php echo esc_html( $label ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>

			<section class="llms-reporting-stab">
				<?php
				llms_get_template(
					'admin/customers/' . $current_tab . '.php',
					array(
						'student'       => $student,
						'metrics'       => $metrics,
						'orders_result' => $orders_result,
						'latest_order'  => $latest_order,
						'reporting_url' => $reporting_url,
					)
				);
				?>
			</section>

		</div>
	</section>
</div>
