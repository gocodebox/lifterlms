<?php
/**
 * Builder lesson model view
 *
 * @since 3.16.0
 * @since 3.30.3 Fixed spelling errors.
 * @version 3.30.3
 */
?>
<script type="text/html" id="tmpl-llms-lesson-template">

	<span class="llms-drag-utility drag-lesson"></span>

	<header class="llms-builder-header">
		<h3 class="llms-headline">
			<?php echo get_post_type_object( 'lesson' )->labels->singular_name; ?> {{{ data.get( 'order' ) }}}:
			<span class="llms-input" contenteditable="true" data-attribute="title" data-original-content="{{{ data.get( 'title' ) }}}" data-required="required">{{{ data.get( 'title' ) }}}</span>
		</h3>

		<div class="llms-action-icons">

			<# if ( data.get_edit_post_link() ) { #>
				<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'Open WordPress lesson editor', 'lifterlms' ); ?>" href="{{{ data.get_edit_post_link() }}}" target="_blank">
					<span class="fa fa-wordpress"></span>
				</a>
			<# } #>

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon tip--top-right" data-tip="<?php esc_attr_e( 'View lesson', 'lifterlms' ); ?>" href="{{{ data.get( 'permalink' ) }}}" target="_blank">
					<span class="fa fa-external-link"></span>
				</a>
			<# } #>

			<a class="llms-action-icon shift-up--lesson tip--top-right" data-tip="<?php esc_attr_e( 'Shift up', 'lifterlms' ); ?>" href="#llms-shift">
				<span class="fa fa-caret-square-o-up"></span>
			</a>

			<a class="llms-action-icon shift-down--lesson tip--top-right" data-tip="<?php esc_attr_e( 'Shift down', 'lifterlms' ); ?>" href="#llms-shift">
				<span class="fa fa-caret-square-o-down"></span>
			</a>

			<a class="llms-action-icon section-prev tip--top-right" data-tip="<?php esc_attr_e( 'Move to previous section', 'lifterlms' ); ?>" href="#llms-section-change">
				<span class="fa fa-arrow-circle-o-up"></span>
			</a>

			<a class="llms-action-icon section-next tip--top-right" data-tip="<?php esc_attr_e( 'Move to next section', 'lifterlms' ); ?>" href="#llms-section-change">
				<span class="fa fa-arrow-circle-o-down"></span>
			</a>

			<# if ( ! data.has_temp_id() ) { #>
				<a class="llms-action-icon detach--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Detach Lesson', 'lifterlms' ); ?>" href="#llms-detach-model">
					<span class="fa fa-chain-broken"></span>
				</a>
			<# } #>

			<?php if ( current_user_can( 'delete_course', $course_id ) ) : ?>
				<a class="llms-action-icon trash--lesson danger tip--top-right" data-tip="<?php esc_attr_e( 'Trash Lesson', 'lifterlms' ); ?>" href="#llms-trash-model">
					<span class="fa fa-trash"></span>
				</a>
			<?php endif; ?>

		</div>

	</header>

	<ul class="llms-info-list">

		<?php
		$icons = array(

			'settings'    => array(
				'action'           => 'edit-lesson',
				'active_condition' => 'false',
				'tip'              => esc_attr__( 'Edit Lesson settings', 'lifterlms' ),
				'tip_active'       => '',
				'icon'             => '<i class="fa fa-cog"></i>',
				'icon_active'      => '',
			),

			'assignment'  => array(
				'action'           => 'edit-assignment',
				'active_condition' => "'yes' === data.get( 'assignment_enabled' )",
				'tip'              => esc_attr__( 'Add an assignment', 'lifterlms' ),
				'tip_active'       => sprintf( esc_attr__( 'Edit Assignment: %s', 'lifterlms' ), "{{{ _.isEmpty( data.get( 'assignment' ) ) ? '' : data.get( 'assignment' ).get( 'title' ) }}}" ),
				'icon'             => '<i class="fa fa-check-square-o"></i>',
				'icon_active'      => '<i class="fa fa-check-square-o"></i>',
			),

			'quiz'        => array(
				'action'           => 'edit-quiz',
				'active_condition' => "'yes' === data.get( 'quiz_enabled' )",
				'tip'              => esc_attr__( 'Add a quiz', 'lifterlms' ),
				'tip_active'       => sprintf( esc_attr__( 'Edit Quiz: %s', 'lifterlms' ), "{{{ ( 'yes' === data.get( 'quiz_enabled' ) ) ? data.get( 'quiz' ).get( 'title' ) : '' }}}" ),
				'icon'             => '<i class="fa fa-question-circle"></i>',
				'icon_active'      => '<i class="fa fa-question-circle"></i>',
			),

			'content'     => array(
				'action'           => false,
				'active_condition' => "data.get( 'content' )",
				'tip'              => esc_attr__( 'No content', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Has content', 'lifterlms' ),
				'icon'             => '<i class="fa fa-file-text-o"></i>',
				'icon_active'      => '<i class="fa fa-file-text-o"></i>',
			),

			'video'       => array(
				'action'           => false,
				'active_condition' => "data.get( 'video_embed' )",
				'tip'              => esc_attr__( 'No video', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Has video', 'lifterlms' ),
				'icon'             => '<i class="fa fa-play-circle"></i>',
				'icon_active'      => '<i class="fa fa-play-circle"></i>',
			),

			'audio'       => array(
				'action'           => false,
				'active_condition' => "data.get( 'audio_embed' )",
				'tip'              => esc_attr__( 'No audio', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Has audio', 'lifterlms' ),
				'icon'             => '<i class="fa fa-volume-off"></i>',
				'icon_active'      => '<i class="fa fa-volume-up"></i>',
			),

			'free'        => array(
				'action'           => false,
				'active_condition' => "'yes' === data.get( 'free_lesson' )",
				'tip'              => esc_attr__( 'Enrolled students only', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Free Lesson', 'lifterlms' ),
				'icon'             => '<i class="fa fa-lock"></i>',
				'icon_active'      => '<i class="fa fa-unlock"></i>',
			),

			'prereq'      => array(
				'action'           => false,
				'active_condition' => "'yes' === data.get( 'has_prerequisite' )",
				'tip'              => esc_attr__( 'No prerequisite', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Prerequisite Enabled', 'lifterlms' ),
				'icon'             => '<i class="fa fa-chain-broken"></i>',
				'icon_active'      => '<i class="fa fa-link"></i>',
			),

			'drip_method' => array(
				'action'           => false,
				'active_condition' => "data.get( 'drip_method' )",
				'tip'              => esc_attr__( 'Drip disabled', 'lifterlms' ),
				'tip_active'       => esc_attr__( 'Drip Enabled', 'lifterlms' ),
				'icon'             => '<i class="fa fa-calendar"></i>',
				'icon_active'      => '<i class="fa fa-calendar"></i>',
			),

		);

		foreach ( $icons as $icon ) :
			?>

			<li class="llms-info-item tip--top-right<# if ( <?php echo $icon['active_condition']; ?> ) { print( ' active') } #>"
				data-tip="<?php echo $icon['tip']; ?>"
				data-tip-active="<?php echo $icon['tip_active']; ?>">
				<?php if ( $icon['action'] ) : ?>
					<?php printf( '<a class="llms-action-icon %1$s" href="#llms-action--%1$s">', $icon['action'] ); ?>
				<?php endif; ?>
				<# if ( <?php echo $icon['active_condition']; ?> ) { #>
					<?php echo $icon['icon_active']; ?>
				<# } else { #>
					<?php echo $icon['icon']; ?>
				<# } #>
				<?php if ( $icon['action'] ) : ?>
					</a>
				<?php endif; ?>
			</li>

		<?php endforeach; ?>

	</ul>

</script>
