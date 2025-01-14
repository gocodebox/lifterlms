<?php
/**
 * Single Student View
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since 3.2.0
 * @version 3.15.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>
<section class="llms-reporting-tab llms-reporting-student">

	<header class="llms-reporting-breadcrumbs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting' ) ); ?>"><?php esc_html_e( 'Students', 'lifterlms' ); ?></a>
		<?php do_action( 'llms_reporting_student_tab_breadcrumbs' ); ?>
	</header>

	<div class="llms-reporting-body">

		<header class="llms-reporting-header">

			<div class="llms-reporting-header-img">
				<?php echo wp_kses_post( $student->get_avatar( 64 ) ); ?>
			</div>
			<div class="llms-reporting-header-info">
				<h2><a href="<?php echo esc_url( get_edit_user_link( $student->get_id() ) ); ?>"><?php echo esc_html( $student->get_name() ); ?></a></h2>
				<h5><a href="<?php echo esc_url( 'mailto:' . $student->get( 'user_email' ) ); ?>"><?php echo esc_html( $student->get( 'user_email' ) ); ?></a></h5>
			</div>

		</header>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary">
			<ul class="llms-nav-items">
			<?php foreach ( $tabs as $name => $label ) : ?>
				<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" href="<?php echo esc_url( LLMS_Admin_Reporting::get_stab_url( $name ) ); ?>">
						<?php echo wp_kses_post( $label ); ?>
					</a>
				</li>
			<?php endforeach; ?>
			</ul>
		</nav>

		<section class="llms-reporting-stab">
			<?php
			llms_get_template(
				'admin/reporting/tabs/students/' . $current_tab . '.php',
				array(
					'student' => $student,
				)
			);
			?>
		</section>

	</div>

</section>
