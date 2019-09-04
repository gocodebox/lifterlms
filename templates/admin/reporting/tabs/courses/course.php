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
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=courses' ) ); ?>"><?php _e( 'Courses', 'lifterlms' ); ?></a>
		<?php do_action( 'llms_reporting_course_tab_breadcrumbs' ); ?>
	</header>

	<header class="llms-reporting-header">

		<?php if ( $img ) : ?>
			<div class="llms-reporting-header-img">
				<img src="<?php echo $img; ?>">
			</div>
		<?php endif; ?>
		<div class="llms-reporting-header-info">
			<h2><a href="<?php echo get_edit_post_link( $course->get( 'id' ) ); ?>"><?php echo $course->get( 'title' ); ?></a></h2>
		</div>

	</header>

	<nav class="llms-nav-tab-wrapper llms-nav-secondary">
		<ul class="llms-nav-items">
		<?php foreach ( $tabs as $name => $label ) : ?>
			<li class="llms-nav-item<?php echo ( $current_tab === $name ) ? ' llms-active' : ''; ?>">
				<a class="llms-nav-link" href="<?php echo LLMS_Admin_Reporting::get_stab_url( $name ); ?>">
					<?php echo $label; ?>
				</a>
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

</section>
