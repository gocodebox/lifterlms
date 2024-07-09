<?php
/**
 * Additional Filters used by various reporting screens
 *
 * @package LifterLMS/Templates/Admin
 *
 * @since Unknown
 * @since 7.6.2 Added `data-post-statuses` attribute to the course and membership filters.
 * @version 7.6.2
 */

defined( 'ABSPATH' ) || exit;
is_admin() || exit;
?>

<nav class="llms-nav-tab-wrapper llms-nav-style-filters" id="llms-date-quick-filters">
	<div class="llms-inside-wrap">
		<ul class="llms-nav-items">

			<li class="llms-nav-item<?php echo ( 'this-year' === $current_range ) ? ' llms-active' : ''; ?>">
				<a class="llms-nav-link" data-range="this-year" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=' . $current_tab . '&range=this-year' ) ); ?>"><?php esc_html_e( 'This Year', 'lifterlms' ); ?></a>
			</li>

			<li class="llms-nav-item<?php echo ( 'last-month' === $current_range ) ? ' llms-active' : ''; ?>">
				<a class="llms-nav-link" data-range="last-month" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=' . $current_tab . '&range=last-month' ) ); ?>"><?php esc_html_e( 'Last Month', 'lifterlms' ); ?></a>
			</li>

			<li class="llms-nav-item<?php echo ( 'this-month' === $current_range ) ? ' llms-active' : ''; ?>">
				<a class="llms-nav-link" data-range="this-month" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=' . $current_tab . '&range=this-month' ) ); ?>"><?php esc_html_e( 'This Month', 'lifterlms' ); ?></a>
			</li>

			<li class="llms-nav-item<?php echo ( 'last-7-days' === $current_range ) ? ' llms-active' : ''; ?>">
				<a class="llms-nav-link" data-range="last-7-days" href="<?php echo esc_url( admin_url( 'admin.php?page=llms-reporting&tab=' . $current_tab . '&range=last-7-days' ) ); ?>"><?php esc_html_e( 'Last 7 Days', 'lifterlms' ); ?></a>
			</li>


			<li class="llms-nav-item llms-analytics-form<?php echo ( 'custom' === $current_range ) ? ' llms-active' : ''; ?>">

				<label><?php esc_html_e( 'Custom', 'lifterlms' ); ?></label>
				<input type="text" name="date_start" class="llms-datepicker" placeholder="yyyy-mm-dd" value="<?php echo esc_html( $date_start ); ?>"> -
				<input type="text" name="date_end" class="llms-datepicker" placeholder="yyyy-mm-dd" value="<?php echo esc_html( $date_end ); ?>">

				<button class="llms-button-action small" id="llms-custom-date-submit" type="submit"><?php esc_html_e( 'Go', 'lifterlms' ); ?></button>
			</li>

			<li class="llms-nav-item llms-nav-item-right">
				<a class="llms-nav-link" href="#llms-toggle-filters"><span class="dashicons dashicons-filter"></span><?php esc_html_e( 'Toggle Filters', 'lifterlms' ); ?></a>
			</li>

		</ul>

	</div>

</nav>

<nav class="llms-analytics-filters"<?php echo ( $current_students || $current_courses || $current_memberships ) ? ' style="display:block;"' : ''; ?>>
	<div class="llms-inside-wrap">
		<ul class="llms-nav-items">
			<li class="llms-nav-item llms-analytics-form">

				<label><?php esc_html_e( 'Students', 'lifterlms' ); ?></label>

				<select id="llms-students-ids-filter" name="student_ids[]" multiple="multiple">
					<?php
					/**
					 * todo: do a better job on this loop for scalability...
					 */
					?>
					<?php foreach ( $current_students as $id ) : ?>
						<?php $s = get_user_by( 'id', $id ); ?>
						<option value="<?php echo esc_attr( $id ); ?>" selected="selected"><?php echo esc_html( $s->display_name ); ?> &lt;<?php echo esc_html( $s->user_email ); ?>&gt;</option>
					<?php endforeach; ?>

				</select>

			</li>
			<li class="llms-nav-item llms-analytics-form">
				<label><?php esc_html_e( 'Courses', 'lifterlms' ); ?></label>
				<select data-post-statuses="<?php echo esc_attr( implode( ',', array_keys( get_post_statuses() ) ) . ',future' ); ?>" class="llms-select2-post" data-placeholder="<?php esc_html_e( 'Filter by Course(s)', 'lifterlms' ); ?>" data-post-type="course" id="llms-course-ids-filter" name="course_ids[]" multiple="multiple">
					<?php foreach ( $current_courses as $course_id ) : ?>
						<option value="<?php echo esc_attr( $course_id ); ?>" selected><?php echo esc_html( get_the_title( $course_id ) ); ?>
							<?php
							printf(
								// Translators: %d = Course ID.
								esc_html__( '(ID# %d)', 'lifterlms' ),
								esc_html( $course_id )
							);
							?>
						</option>
					<?php endforeach; ?>
				</select>

			</li>

			<li class="llms-nav-item llms-analytics-form">

				<label><?php esc_html_e( 'Memberships', 'lifterlms' ); ?></label>

				<select data-post-statuses="<?php echo esc_attr( implode( ',', array_keys( get_post_statuses() ) ) . ',future' ); ?>" class="llms-select2-post" data-placeholder="<?php esc_html_e( 'Filter by Memberships(s)', 'lifterlms' ); ?>" data-post-type="llms_membership" id="llms-membership-ids-filter" name="membership_ids[]" multiple="multiple">
					<?php foreach ( $current_memberships as $membership_id ) : ?>
						<option value="<?php echo esc_attr( $membership_id ); ?>" selected><?php echo esc_html( get_the_title( $membership_id ) ); ?>
							<?php
							printf(
								// Translators: %d = Membership ID.
								esc_html__( '(ID# %d)', 'lifterlms' ),
								esc_html( $membership_id )
							);
							?>
						</option>
					<?php endforeach; ?>
				</select>

			</li>
		</ul>
		<p><button class="llms-button-primary small" type="submit"><?php esc_html_e( 'Apply Filters', 'lifterlms' ); ?></button></p>
	</div>
</nav>

<input type="hidden" name="range" value="<?php echo esc_attr( $current_range ); ?>">
<input type="hidden" name="tab" value="<?php echo esc_attr( $current_tab ); ?>">
<input type="hidden" name="page" value="llms-reporting">
