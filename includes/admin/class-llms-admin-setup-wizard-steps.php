<?php
/**
 * LifterLM Setup Wizard Onboarding steps
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

class LLMS_Admin_Setup_Wizard_Steps {

	public static function get() {

		return array(

			'organization' => __( 'Organization', 'lifterlms' ),
			'content'      => __( 'Site Content', 'lifterlms' ),
			'ecommerce'    => __( 'Ecommerce', 'lifterlms' ),

			// 'intro'    => __( 'Welcome!', 'lifterlms' ),
			// 'pages'    => __( 'Page Setup', 'lifterlms' ),
			// 'payments' => __( 'Payments', 'lifterlms' ),
			// 'coupon'   => __( 'Coupon', 'lifterlms' ),
			// 'finish'   => __( 'Finish!', 'lifterlms' ),

		);

	}

	protected static function get_organization_fields() {

		return array(
			array(
				'classes'     => 'llms-select2',
				'description' => __( 'This will help us configure your site to get you started quickly', 'lifterlms' ),
				'id'          => 'llms_setup_location',
				'label'       => __( 'Where is your organization located?', 'lifterlms' ),
				'options'     => get_lifterlms_countries(),
				'type'        => 'select',
				'value'       => get_lifterlms_country(),
			),
			array(
				'id'          => 'llms_setup_for_client',
				'label'       => __( 'I am setting this site up for someone else', 'lifterlms' ),
				'type'        => 'checkbox',
				'value'       => 'yes',

			),
		);

	}

	public static function output( $step ) {

		$method = 'output_' . $step;
		// echo '<div class="llms-form-fields flush">';
		method_exists( __CLASS__, $method ) ? self::$method() : wp_die( __( 'Invalid action!', 'lifterlms' ) );
		// echo '</div>';

	}
	protected static function output_organization() {

		$data = LLMS_Admin_Setup_Wizard::get_data( array(
			'location'  => get_lifterlms_country(),
			'for_other' => 'no',
			'role'      => 'owner'
		) );
		?>

		<div class="llms-setup-row">
			<label for="llms-setup-location">
				<h2><?php _e( 'Where is your organization located?', 'lifterlms' ); ?></h2>
				<p><?php _e( 'This will help us configure your site to get you started quickly', 'lifterlms' ); ?></p>
			</label>
			<select class="llms-select2" id="llms-setup-location" name="llms_setup_data[location]">
				<?php foreach ( get_lifterlms_countries() as $code => $name ): ?>
					<option value="<?php echo esc_attr( $code ); ?>"<?php selected( $data['location'], $code ); ?>><?php echo esc_attr( $name ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="llms-setup-row">
			<input id="llms-setup-for-somene-else" name="llms_setup_data[for_other]" type="checkbox" value="yes"<?php echo checked( $data['for_other'], 'yes' ); ?>>
			<label for="llms-setup-for-somene-else">
				<strong><?php _e( 'I am setting this site up for someone else', 'lifterlms' ); ?></strong>
			</label>
		</div>


		<div class="llms-setup-row" id="llms-setup-role-options"<?php echo llms_parse_bool( $data['for_other'] ) ? '' : ' style="display:none;"'; ?>>

			<label for="llms-setup-role">
				<strong><?php _e( 'What is your primary role within your organization?', 'lifterlms' ); ?></strong>
				<p><?php _e( "We'll use this information to provide relevant recommendations during the setup of your site", 'lifterlms' ); ?></p>
			</label>
			<select class="llms-select2" id="llms-setup-role" name="llms_setup_data[role]" disabled>
				<option value="dev"<?php selected( $data['role'], 'dev' ); ?>><?php echo esc_attr_e( 'I am a coder or developer', 'lifterlms' ); ?></option>
				<option value="creator"<?php selected( $data['role'], 'creator' ); ?>><?php echo esc_attr_e( 'I create content', 'lifterlms' ); ?></option>
				<option value="assistant"<?php selected( $data['role'], 'assistant' ); ?>><?php echo esc_attr_e( 'I am an assistant', 'lifterlms' ); ?></option>
			</select>

		</div>

		<input type="hidden" id="llms-setup-role-default" name="llms_setup_data[role]" value="owner">
		<?php

	}

	protected static function output_content() {
		?>

		<h2><?php _e( 'What type of content will your site offer?', 'lifterlms' ); ?></h2>
		<p><?php _e( 'Choose any that apply', 'lifterlms' ); ?></p>

		<div class="llms-setup-row">
			<input id="llms-setup-content-courses" name="llms-setup-content[]" type="checkbox" value="courses">
			<label for="llms-setup-content-courses">
				<strong><?php _e( 'Courses', 'lifterlms' ); ?></strong>
				<p><?php _e( 'Structured learning journeys with text and image content, basic quizzes, and audio or video elements.', 'lifterlms' ); ?></p>
			</label>
		</div>

		<div class="llms-setup-row show-for-courses" style="display:none;">
			<input id="llms-setup-content-quizzes" name="llms-setup-content[]" type="checkbox" value="quizzes">
			<label for="llms-setup-content-quizzes">
				<strong><?php _e( 'Quizzes or tests', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'Create more powerful quizzes with Advanced Quizzes. %1$sLearn more%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

		<div class="llms-setup-row show-for-courses" style="display:none;">
			<input id="llms-setup-content-assignments" name="llms-setup-content[]" type="checkbox" value="assignments">
			<label for="llms-setup-content-assignments">
				<strong><?php _e( 'Assignments', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'Add task lists, uploads, and long-form text submissions to your courses. %1$sLearn more%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

		<div class="llms-setup-row show-for-courses" style="display:none;">
			<input id="llms-setup-content-videos" name="llms-setup-content[]" type="checkbox" value="videos">
			<label for="llms-setup-content-videos">
				<strong><?php _e( 'Video Courses', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'Require learners to complete videos to progress through courses, track their progress, and %1$smore%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

		<div class="llms-setup-row">
			<input id="llms-setup-content-memberships" name="llms-setup-content[]" type="checkbox" value="memberships">
			<label for="llms-setup-content-memberships">
				<strong><?php _e( 'Memberships', 'lifterlms' ); ?></strong>
				<p><?php _e( 'Create members-only content on your site, provide bulk-access to groups of courses, and more.', 'lifterlms' ); ?></p>
			</label>
		</div>

		<div class="llms-setup-row">
			<input id="llms-setup-content-groups" name="llms-setup-content[]" type="checkbox" value="groups">
			<label for="llms-setup-content-groups">
				<strong><?php _e( 'Groups', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'B2B training, small group learning, and bulk sales. %1$sLearn more%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

			<div class="llms-setup-row">
			<input id="llms-setup-content-social" name="llms-setup-content[]" type="checkbox" value="social">
			<label for="llms-setup-content-social">
				<strong><?php _e( 'Social Networking', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'Student profiles, discussion boards, and %1$smore%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

		<div class="llms-setup-row">
			<input id="llms-setup-content-areas" name="llms-setup-content[]" type="checkbox" value="areas">
			<label for="llms-setup-content-areas">
				<strong><?php _e( 'Private coaching', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'One on one coaching sessions and content. %1$sLearn more%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

		<?php
	}

	protected static function output_ecommerce() {
		?>

		<h2><?php _e( 'How will you sell and distribute your content?', 'lifterlms' ); ?></h2>
		<p><?php _e( 'Choose any that apply', 'lifterlms' ); ?></p>

		<div class="llms-setup-row">
			<input id="llms-setup-ecomm-digital" name="llms-setup-ecomm[]" type="checkbox" value="digital">
			<label for="llms-setup-ecomm-digital">
				<strong><?php _e( 'Online Sales', 'lifterlms' ); ?></strong>
			</label>
		</div>

		<div class="llms-setup-row show-for-courses" style="display:none;">
			<input id="llms-setup-content-quizzes" name="llms-setup-content[]" type="checkbox" value="quizzes">
			<label for="llms-setup-content-quizzes">
				<strong><?php _e( 'Quizzes or tests', 'lifterlms' ); ?></strong>
				<p><?php printf(
					__( 'Create more powerful quizzes with Advanced Quizzes. %1$sLearn more%2$s.', 'lifterlms' ),
					'<a href="#" target="_blank">',
					'</a>'
				); ?></p>
			</label>
		</div>

		<?php
	}
}

