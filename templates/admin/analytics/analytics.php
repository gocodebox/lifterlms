<?php
/**
 * Analytics
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since    3.0.0
 * @version  3.24.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_admin() ) {
	exit;
}
?>

<div class="wrap lifterlms llms-analytics-wrap">

	<form action="<?php echo admin_url( 'admin.php' ); ?>" class="llms-analytics-nav" id="llms-analytics-filters-form" method="GET">

		<nav class="llms-nav-tab-wrapper">

			<ul class="llms-nav-items">
			<?php foreach ( $tabs as $name => $label ) : ?>

				<?php $current_tab_class = ( $current_tab == $name ) ? ' llms-active' : ''; ?>
				<li class="llms-nav-item<?php echo $current_tab_class; ?>"><a class="llms-nav-link" href="<?php echo admin_url( 'admin.php?page=llms-analytics&tab=' . $name ); ?>"><?php echo $label; ?></a>

			<?php endforeach; ?>
			</ul>

		</nav>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary" id="llms-date-quick-filters">

			<ul class="llms-nav-items">

				<li class="llms-nav-item<?php echo ( 'this-year' == $current_range ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" data-range="this-year" href="<?php echo admin_url( 'admin.php?page=llms-analytics&tab=' . $current_tab . '&range=this-year' ); ?>"><?php _e( 'This Year', 'lifterlms' ); ?></a>
				</li>

				<li class="llms-nav-item<?php echo ( 'last-month' == $current_range ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" data-range="last-month" href="<?php echo admin_url( 'admin.php?page=llms-analytics&tab=' . $current_tab . '&range=last-month' ); ?>"><?php _e( 'Last Month', 'lifterlms' ); ?></a>
				</li>

				<li class="llms-nav-item<?php echo ( 'this-month' == $current_range ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" data-range="this-month" href="<?php echo admin_url( 'admin.php?page=llms-analytics&tab=' . $current_tab . '&range=this-month' ); ?>"><?php _e( 'This Month', 'lifterlms' ); ?></a>
				</li>

				<li class="llms-nav-item<?php echo ( 'last-7-days' == $current_range ) ? ' llms-active' : ''; ?>">
					<a class="llms-nav-link" data-range="last-7-days" href="<?php echo admin_url( 'admin.php?page=llms-analytics&tab=' . $current_tab . '&range=last-7-days' ); ?>"><?php _e( 'Last 7 Days', 'lifterlms' ); ?></a>
				</li>


				<li class="llms-nav-item llms-analytics-form<?php echo ( 'custom' == $current_range ) ? ' llms-active' : ''; ?>">

					<label><?php _e( 'Custom', 'lifterlms' ); ?></label>
					<input type="text" name="date_start" class="llms-datepicker" placeholder="yyyy-mm-dd" value="<?php echo $date_start; ?>"> -
					<input type="text" name="date_end" class="llms-datepicker" placeholder="yyyy-mm-dd" value="<?php echo $date_end; ?>">

					<button class="button small" id="llms-custom-date-submit" type="submit"><?php _e( 'Go', 'lifterlms' ); ?></a>
				</li>

				<li class="llms-nav-item llms-nav-item-right">
					<a class="llms-nav-link" href="#llms-toggle-filters"><span class="dashicons dashicons-filter"></span><?php _e( 'Toggle Filters', 'lifterlms' ); ?></a>
				</li>

			</ul>

		</nav>

		<nav class="llms-nav-tab-wrapper llms-nav-secondary llms-analytics-filters"<?php echo ( $current_students || $current_courses || $current_memberships ) ? ' style="display:block;"' : ''; ?>>

			<ul class="llms-nav-items">
				<li class="llms-nav-item llms-analytics-form">

					<label><?php _e( 'Students', 'lifterlms' ); ?></label>

					<select id="llms-students-ids-filter" name="student_ids[]" multiple="multiple">
						<?php
						/**
						 * todo: do a better job on this loop for scalability...
						 */
						?>
						<?php foreach ( $current_students as $id ) : ?>
							<?php $s = get_user_by( 'id', $id ); ?>
							<option value="<?php echo $id; ?>" selected="selected"><?php echo $s->display_name; ?> &lt;<?php echo $s->user_email; ?>&gt;</option>
						<?php endforeach; ?>

					</select>

				</li>

				<li class="llms-nav-item llms-analytics-form">

					<label><?php _e( 'Courses', 'lifterlms' ); ?></label>

					<select class="llms-select2-post" data-placeholder="<?php _e( 'Filter by Course(s)', 'lifterlms' ); ?>" data-post-type="course" id="llms-course-ids-filter" name="course_ids[]" multiple="multiple">
						<?php foreach ( $current_courses as $course_id ) : ?>
							<option value="<?php echo $course_id; ?>" selected><?php echo get_the_title( $course_id ); ?> <?php printf( __( '(ID# %d)', 'lifterlms' ), $course_id ); ?></option>
						<?php endforeach; ?>
					</select>

				</li>

				<li class="llms-nav-item llms-analytics-form">

					<label><?php _e( 'Memberships', 'lifterlms' ); ?></label>

					<select class="llms-select2-post" data-placeholder="<?php _e( 'Filter by Memberships(s)', 'lifterlms' ); ?>" data-post-type="llms_membership" id="llms-membership-ids-filter" name="membership_ids[]" multiple="multiple">
						<?php foreach ( $current_memberships as $membership_id ) : ?>
							<option value="<?php echo $membership_id; ?>" selected><?php echo get_the_title( $membership_id ); ?> <?php printf( __( '(ID# %d)', 'lifterlms' ), $membership_id ); ?></option>
						<?php endforeach; ?>
					</select>

				</li>

				<li class="llms-nav-item llms-analytics-form">
					<button class="button" type="submit"><?php _e( 'Apply Filters', 'lifterlms' ); ?></a>
				</li>

			</ul>
		</nav>

		<input type="hidden" name="range" value="<?php echo $current_range; ?>">
		<input type="hidden" name="tab" value="<?php echo $current_tab; ?>">
		<input type="hidden" name="page" value="llms-analytics">

	</form>

	<h1 style="display:none;"></h1><!-- find a home for admin notices -->

	<div class="llms-options-page-contents">

		<?php foreach ( $widget_data as $row => $widgets ) : ?>
			<div class="llms-widget-row llms-widget-row-<?php $row; ?>">
			<?php foreach ( $widgets as $id => $opts ) : ?>

				<div class="llms-widget-<?php echo $opts['cols']; ?>">
					<div class="llms-widget is-loading" data-method="<?php echo $id; ?>" id="llms-widget-<?php echo $id; ?>">

						<p class="llms-label"><?php echo $opts['title']; ?></p>
						<h1><?php echo $opts['content']; ?></h1>

						<span class="spinner"></span>

						<i class="fa fa-info-circle llms-widget-info-toggle"></i>
						<div class="llms-widget-info">
							<p><?php echo $opts['info']; ?></p>
						</div>

					</div>
				</div>

			<?php endforeach; ?>
			</div>
		<?php endforeach; ?>

		<div class="llms-charts-wrapper" id="llms-charts-wrapper"></div>

	</div>

	<div id="llms-analytics-json" style="display:none;"><?php echo $json; ?></div>

</div>
