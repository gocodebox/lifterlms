<?php
/**
 * Builder lesson model view
 *
 * @since 3.16.0
 * @since 3.30.3 Fixed spelling errors.
 * @since 7.2.0 Added lesson id.
 * @version 7.2.0
 */
?>
<script type="text/html" id="tmpl-llms-lesson-template">

	<span class="llms-drag-utility drag-lesson"></span>

	<header class="llms-builder-header">
		<h3 class="llms-headline">
			<span class="llms-input" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<div class="llms-action-icons">

			<div class="llms-action-icons-left">

				<# if ( data.get_edit_post_link() ) { #>
					<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'Open WordPress lesson editor', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}" target="_blank">
						<span class="fa fa-pencil-square-o"></span>
						<?php esc_html_e( 'Edit', 'lifterlms' ); ?>
					</a>
				<# } #>

				<# if ( ! data.has_temp_id() ) { #>
					<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'View lesson', 'lifterlms' ); ?>" href="{{{ data.get( 'permalink' ) }}}" target="_blank">
						<span class="fa fa-external-link"></span>
						<?php esc_html_e( 'View', 'lifterlms' ); ?>
					</a>
				<# } #>

				<# if ( ! data.has_temp_id() ) { #>
					<a class="llms-action-icon detach--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach-model">
						<span class="fa fa-chain-broken"></span>
						<?php esc_html_e( 'Detach', 'lifterlms' ); ?>
					</a>
				<# } #>

				<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
					<a class="llms-action-icon trash--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Trash Lesson', 'lifterlms' ); ?>" href="#llms-trash-model">
						<span class="fa fa-trash"></span>
						<?php esc_html_e( 'Trash', 'lifterlms' ); ?>
					</a>
				<?php endif; ?>

			</div>

			<div class="llms-action-icons-right">

				<button id="llms-section-change" class="llms-action-icon section-prev tip--top-right" data-tip="<?php esc_attr_e( 'Move to previous section', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Move to previous section', 'lifterlms' ); ?>">
					<span class="fa fa-arrow-circle-o-up"></span>
				</button>

				<button id="llms-section-change" class="llms-action-icon section-next tip--top-right" data-tip="<?php esc_attr_e( 'Move to next section', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Move to next section', 'lifterlms' ); ?>">
					<span class="fa fa-arrow-circle-o-down"></span>
				</button>

				<button id="llms-shift" class="llms-action-icon shift-up--lesson tip--top-right" data-tip="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>">
					<span class="fa fa-chevron-up"></span>
				</button>

				<button id="llms-shift" class="llms-action-icon shift-down--lesson tip--top-right" data-tip="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" aria-label="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>">
					<span class="fa fa-chevron-down"></span>
				</button>

			</div>

		</div>

	</header>

	<ul class="llms-info-list">

		<li class="llms-info-item">
			<?php esc_html_e( 'ID:', 'lifterlms' ); ?> {{{ data.get( 'id' ) }}}
		</li>

		<?php
		$icons = array(

			'settings'    => array(
				'action'           => 'edit-lesson',
				'active_condition' => 'true',
				'tip'              => '',
				'tip_active'       => esc_attr__( 'Edit Lesson settings', 'lifterlms' ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-cog"></i>' . __( 'Settings', 'lifterlms' ),
			),

			'assignment'  => array(
				'action'           => 'edit-assignment',
				'active_condition' => "'yes' === data.get( 'assignment_enabled' )",
				'tip'              => esc_attr__( 'Add an assignment', 'lifterlms' ),
				'tip_active'       => sprintf( esc_attr__( 'Edit Assignment: %s', 'lifterlms' ), "{{{ _.isEmpty( data.get( 'assignment' ) ) ? '' : data.get( 'assignment' ).get( 'title' ) }}}" ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-check-square-o"></i>' . __( 'Assignment', 'lifterlms' ),
			),

			'quiz'        => array(
				'action'           => 'edit-quiz',
				'active_condition' => "'yes' === data.get( 'quiz_enabled' )",
				'tip'              => esc_attr__( 'Add a quiz', 'lifterlms' ),
				'tip_active'       => sprintf( esc_attr__( 'Edit Quiz: %s', 'lifterlms' ), "{{{ ( 'yes' === data.get( 'quiz_enabled' ) ) ? data.get( 'quiz' ).get( 'title' ) : '' }}}" ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-question-circle"></i>' . __( 'Quiz', 'lifterlms' ),
			),

			'video'       => array(
				'action'           => 'edit-lesson',
				'active_condition' => "data.get( 'video_embed' )",
				'tip'              => esc_attr__( 'No video', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Has video', 'lifterlms' ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-play-circle"></i>' . __( 'Video', 'lifterlms' ),
			),

			'audio'       => array(
				'action'           => false,
				'active_condition' => "data.get( 'audio_embed' )",
				'tip'              => esc_attr__( 'No audio', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Has audio', 'lifterlms' ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-volume-up"></i>' . __( 'Audio', 'lifterlms' ),
			),

			'free'        => array(
				'action'           => false,
				'active_condition' => "'yes' === data.get( 'free_lesson' )",
				'tip'              => esc_attr__( 'Enrolled students only', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Free Lesson', 'lifterlms' ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-unlock"></i>' . __( 'Free Lesson', 'lifterlms' ),
			),

			'prereq'      => array(
				'action'           => false,
				'active_condition' => "'yes' === data.get( 'has_prerequisite' )",
				'tip'              => esc_attr__( 'No prerequisite', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Prerequisite Enabled', 'lifterlms' ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-link"></i>' . __( 'Prerequisite Enabled', 'lifterlms' ),
			),

			'drip_method' => array(
				'action'           => false,
				'active_condition' => "data.get( 'drip_method' )",
				'tip'              => esc_attr__( 'Drip disabled', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Drip Enabled', 'lifterlms' ),
				'icon'             => '',
				'icon_active'      => '<i class="fa fa-calendar"></i>' . __( 'Drip Enabled', 'lifterlms' ),
			),

		);

		foreach ( $icons as $icon ) :
			?>

			<?php // Hide the whole icon area if there is no icon set, and the active condition is not met. ?>
			<?php if ( ! $icon['icon'] ) : ?>
				<# if ( <?php echo $icon['active_condition']; ?> ) { #>
			<?php endif; ?>
				<li class="llms-info-item tip--top-right<# if ( <?php echo $icon['active_condition']; ?> ) { print( ' active') } #>"
					data-tip="<?php echo $icon['tip']; ?>"
					data-tip-active="<?php echo $icon['tip_active']; ?>">
					<?php if ( $icon['action'] ) : ?>
						<?php printf( '<button class="llms-action-icon %1$s" id="#llms-action--%1$s">', $icon['action'] ); ?>
					<?php endif; ?>
					<# if ( <?php echo $icon['active_condition']; ?> ) { #>
						<?php echo $icon['icon_active']; ?>
					<# } else { #>
						<?php echo $icon['icon']; ?>
					<# } #>
					<?php if ( $icon['action'] ) : ?>
					</button>
					<?php endif; ?>
				</li>
			<?php if ( ! $icon['icon'] ) : ?>
				<# } #>
			<?php endif; ?>

		<?php endforeach; ?>

	</ul>

</script>
