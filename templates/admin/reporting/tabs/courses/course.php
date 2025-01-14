<?php
/**
 * Single Course View
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @version Unknown
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
$img = $course->get_image( array( 64, 64 ) );
?>
<section class="llms-reporting-tab llms-reporting-course">

	<header class="llms-reporting-breadcrumbs">
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=courses' ) ); ?>"><?php esc_html_e( 'Courses', 'lifterlms' ); ?></a>
		<?php do_action( 'llms_reporting_course_tab_breadcrumbs' ); ?>
	</header>

	<div class="llms-reporting-body">

		<header class="llms-reporting-header">

			<?php if ( $img ) : ?>
				<div class="llms-reporting-header-img">
					<img src="<?php echo esc_url( $img ); ?>">
				</div>
			<?php endif; ?>
			<div class="llms-reporting-header-info">
				<h2><a href="<?php echo esc_url( get_edit_post_link( $course->get( 'id' ) ) ); ?>"><?php echo esc_html( $course->get( 'title' ) ); ?></a></h2>
			</div>

		</header>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary">
			<ul class="llms-nav-items">
			<?php foreach ( $tabs as $name => $label ) : ?>
				<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" href="<?php echo esc_url( LLMS_Admin_Reporting::get_stab_url( $name ) ); ?>">
						<?php echo wp_kses_post( $label ); ?>
					</a></li>
			<?php endforeach; ?>
			</ul>
		</nav>

		<section class="llms-gb-tab">
			<?php
			llms_get_template(
				'admin/reporting/tabs/courses/' . $current_tab . '.php',
				array(
					'course' => $course,
				)
			);
			?>
		</section>

	</div>

</section>
