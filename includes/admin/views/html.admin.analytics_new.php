<div class="wrap lifterlms">

	<h1 class="llms-page-header"><?php _e( 'Analytics', 'lifterlms' ); ?></h1>

	<h2 class="llms-nav-tab-wrapper">
		<?php foreach ( $tabs as $name => $label ) : ?>

			<?php $current_tab_class = ( $current_tab == $name ) ? ' llms-nav-tab-active' : ''; ?>
			<a class="llms-button-primary llms-nav-tab-settings<?php echo $current_tab_class; ?>" href="<?php echo admin_url( 'admin.php?page=llms-analytics-new&tab=' . $name ); ?>"><?php echo $label; ?></a>

		<?php endforeach; ?>
	</h2>

	<h2 class="llms-nav-tab-wrapper llms-skin--light">

		<a class="llms-button-secondary llms-nav-tab-filters<?php echo ( $current_range == 'this-year' ) ? ' llms-nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=llms-analytics-new&tab=' . $current_tab . '&range=this-year' ); ?>"><?php _e( 'This Year', 'lifterlms' ); ?></a>
		<a class="llms-button-secondary llms-nav-tab-filters<?php echo ( $current_range == 'last-month' ) ? ' llms-nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=llms-analytics-new&tab=' . $current_tab . '&range=last-month' ); ?>"><?php _e( 'Last Month', 'lifterlms' ); ?></a>
		<a class="llms-button-secondary llms-nav-tab-filters<?php echo ( $current_range == 'this-month' ) ? ' llms-nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=llms-analytics-new&tab=' . $current_tab . '&range=this-month' ); ?>"><?php _e( 'This Month', 'lifterlms' ); ?></a>
		<a class="llms-button-secondary llms-nav-tab-filters<?php echo ( $current_range == 'last-7-days' ) ? ' llms-nav-tab-active' : ''; ?>" href="<?php echo admin_url( 'admin.php?page=llms-analytics-new&tab=' . $current_tab . '&range=last-7-days' ); ?>"><?php _e( 'Last 7 Days', 'lifterlms' ); ?></a>

		<form action="<?php echo admin_url( 'admin.php' ); ?>" class="llms-button-secondary llms-nav-tab-filters custom-date-filters<?php echo ( $current_range == 'custom' ) ? ' llms-nav-tab-active' : ''; ?>" method="GET">
			<label><?php _e( 'Custom', 'lifterlms' ); ?></label>
			<input type="text" name="date_start" class="llms-datepicker" placeholder="yyyy-mm-dd" value="<?php echo $date_start; ?>"> -
			<input type="text" name="date_end" class="llms-datepicker" placeholder="yyyy-mm-dd" value="<?php echo $date_end; ?>">
			<button class="llms-button-secondary" type="submit"><?php _e( 'Go', 'lifterlms' ); ?></button>

			<input type="hidden" name="range" value="custom">
			<input type="hidden" name="page" value="llms-analytics-new">
			<input type="hidden" name="tab" value="<?php echo $current_tab; ?>">
			<input type="hidden" name="course_ids" value="<?php echo implode( ',', $current_courses ); ?>">
			<input type="hidden" name="membership_ids" value="<?php echo implode( ',', $current_memberships ); ?>">
			<input type="hidden" name="student_ids" value="<?php echo implode( ',', $current_students ); ?>">
		</form>

	</h2>

	<h2 class="llms-nav-tab-wrapper llms-skin--light">

		<form action="<?php echo admin_url( 'admin.php' ); ?>" class="llms-button-secondary llms-nav-tab-filters full-width llms-filter-students" method="GET">

			<label><?php _e( 'Students', 'lifterlms' ); ?></label>

			<select id="llms-students-ids-filter" name="student_ids[]" multiple="multiple">
				<?php
				/**
				 * todo: do a better job on this loop for scalability...
				 */
				?>
				<?php foreach( $current_students as $id ) : ?>
					<?php $s = get_user_by( 'id', $id ); ?>
					<option value="<?php echo $id; ?>" selected="selected"><?php echo $s->display_name; ?> &lt;<?php echo $s->user_email; ?>&gt;</option>
				<?php endforeach; ?>

			</select>

			<button class="llms-button-secondary" type="submit"><?php _e( 'Go', 'lifterlms' ); ?></button>

			<input type="hidden" name="range" value="<?php echo $current_range; ?>">
			<input type="hidden" name="page" value="llms-analytics-new">
			<input type="hidden" name="tab" value="<?php echo $current_tab; ?>">
			<input type="hidden" name="course_ids" value="<?php echo implode( ',', $current_courses ); ?>">
			<input type="hidden" name="membership_ids" value="<?php echo implode( ',', $current_memberships ); ?>">
		</form>

		<form action="<?php echo admin_url( 'admin.php' ); ?>" class="llms-button-secondary llms-nav-tab-filters full-width llms-filter-courses" method="GET">

			<label><?php _e( 'Courses', 'lifterlms' ); ?></label>

			<select class="llms-select2-post" data-placeholder="<?php _e( 'Filter by Course(s)', 'lifterlms' ); ?>" data-post-type="course" id="llms-course-ids-filter" name="course_ids[]" multiple="multiple">
				<?php foreach( $current_courses as $course_id ): ?>
					<option value="<?php echo $course_id; ?>" selected><?php echo get_the_title( $course_id ); ?> <?php printf( __( '(ID# %d)', 'lifterlms' ), $course_id ); ?></option>
				<?php endforeach; ?>
			</select>

			<button class="llms-button-secondary" type="submit"><?php _e( 'Go', 'lifterlms' ); ?></button>

			<input type="hidden" name="range" value="<?php echo $current_range; ?>">
			<input type="hidden" name="page" value="llms-analytics-new">
			<input type="hidden" name="tab" value="<?php echo $current_tab; ?>">
			<input type="hidden" name="membership_ids" value="<?php echo implode( ',', $current_memberships ); ?>">
			<input type="hidden" name="student_ids" value="<?php echo implode( ',', $current_students ); ?>">
		</form>

		<form action="<?php echo admin_url( 'admin.php' ); ?>" class="llms-button-secondary llms-nav-tab-filters full-width llms-filter-memberships" method="GET">

			<label><?php _e( 'Memberships', 'lifterlms' ); ?></label>

			<select class="llms-select2-post" data-placeholder="<?php _e( 'Filter by Memberships(s)', 'lifterlms' ); ?>" data-post-type="llms_membership" id="llms-membership-ids-filter" name="membership_ids[]" multiple="multiple">
				<?php foreach( $current_memberships as $membership_id ): ?>
					<option value="<?php echo $membership_id; ?>" selected><?php echo get_the_title( $membership_id ); ?> <?php printf( __( '(ID# %d)', 'lifterlms' ), $membership_id ); ?></option>
				<?php endforeach; ?>
			</select>

			<button class="llms-button-secondary" type="submit"><?php _e( 'Go', 'lifterlms' ); ?></button>

			<input type="hidden" name="range" value="<?php echo $current_range; ?>">
			<input type="hidden" name="page" value="llms-analytics-new">
			<input type="hidden" name="tab" value="<?php echo $current_tab; ?>">
			<input type="hidden" name="course_ids" value="<?php echo implode( ',', $current_courses ); ?>">
			<input type="hidden" name="student_ids" value="<?php echo implode( ',', $current_students ); ?>">
		</form>

	</h2>

	<div class="llms-options-page-contents">

		<?php foreach( $widget_data as $row => $widgets ) : ?>
			<div class="llms-widget-row llms-widget-row-<?php $row; ?>">
			<?php foreach( $widgets as $id => $opts ) : ?>

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
