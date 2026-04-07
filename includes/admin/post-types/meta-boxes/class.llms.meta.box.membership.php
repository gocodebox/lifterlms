<?php
/**
 * Membership Settings meta box
 *
 * @package LifterLMS/Admin/PostTypes/MetaBoxes/Classes
 *
 * @since 1.0.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Membership Settings meta box class
 *
 * @since 1.0.0
 * @since 3.30.3 Fixed spelling errors; removed duplicate array keys.
 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
 * @since 3.36.0 Allow some fields to store values with quotes.
 * @since 3.36.3 In the `save() method Added logic to correctly sanitize fields of type
 *              'multi' (array) and 'shortcode' (preventing quotes encode).
 *               Also align the method return type to the parent `save()` method.
 */
class LLMS_Meta_Box_Membership extends LLMS_Admin_Metabox {

	/**
	 * This function allows extending classes to configure required class properties
	 * $this->id, $this->title, and $this->screens should be configured in this function.
	 *
	 * @return void
	 * @since  3.0.0
	 */
	public function configure() {

		$this->id       = 'lifterlms-membership';
		$this->title    = __( 'Membership Settings', 'lifterlms' );
		$this->screens  = array(
			'llms_membership',
		);
		$this->priority = 'high';
	}

	/**
	 * Get array of data to pass to the auto enrollment courses table.
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Removed sorting by title.
	 * @since 3.30.3 Fixed spelling errors.
	 *
	 * @param obj $membership instance of LLMS_Membership for the current post.
	 * @return array
	 */
	private function get_content_table( $membership ) {

		$data   = array();
		$data[] = array(
			'',
			'<br>' . __( 'No automatic enrollment courses found. Add a course below.', 'lifterlms' ) . '<br><br>',
			'',
		);

		foreach ( $membership->get_auto_enroll_courses() as $course_id ) {

			$course = new LLMS_Course( $course_id );

			$title = $course->get( 'title' );

			$data[] = array(

				'<span class="dashicons dashicons-menu llms-drag-handle ui-sortable-handle"></span>',
				'<a href="' . get_edit_post_link( $course->get( 'id' ) ) . '">' . $title . ' (ID#' . $course_id . ')</a>',
				'<a class="llms-button-danger small" data-id="' . $course_id . '" href="#llms-course-remove" style="float:right;">' . __( 'Remove course', 'lifterlms' ) . '</a>
				 <a class="llms-button-secondary small" data-id="' . $course_id . '" href="#llms-course-bulk-enroll" style="float:right;margin-right:5px;">' . __( 'Enroll All Members', 'lifterlms' ) . '</a>',

			);

		}

		return apply_filters( 'llms_membership_get_content_table_data', $data, $membership );
	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox.
	 * The function must return an array which can be consumed by the "output" function.
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Removed empty field settings. Modified settings to accommodate sortable auto-enrollment table.
	 * @since 3.30.3 Removed duplicate array keys.
	 * @since 3.36.0 Allow some fields to store values with quotes.
	 *
	 * @return array
	 */
	public function get_fields() {

		global $post;

		$membership = new LLMS_Membership( $this->post );

		$redirect_options = array();
		$redirect_page_id = $membership->get( 'redirect_page_id' );
		if ( $redirect_page_id ) {
			$redirect_options[] = array(
				'key'   => $redirect_page_id,
				'title' => get_the_title( $redirect_page_id ) . '(ID#' . $redirect_page_id . ')',
			);
		}

		$sales_page_content_type = 'none';
		if ( $post && 'auto-draft' !== $post->post_status && $post->post_excerpt ) {
			$sales_page_content_type = 'content';
		}

		$instructor_defaults = llms_get_instructors_defaults();

		return array(
			array(
				'title'  => __( 'Sales Page', 'lifterlms' ),
				'fields' => array(
					array(
						'allow_null'    => false,
						'class'         => 'llms-select2',
						'desc'          => __( 'Customize the content displayed to visitors and students who are not enrolled in the membership.', 'lifterlms' ),
						'desc_class'    => 'd-3of4 t-3of4 m-1of2',
						'default'       => $sales_page_content_type,
						'id'            => $this->prefix . 'sales_page_content_type',
						'is_controller' => true,
						'label'         => __( 'Sales Page Content', 'lifterlms' ),
						'type'          => 'select',
						'value'         => llms_get_sales_page_types(),
					),
					array(
						'controller'       => '#' . $this->prefix . 'sales_page_content_type',
						'controller_value' => 'content',
						'desc'             => __( 'This content will only be shown to visitors who are not enrolled in this membership.', 'lifterlms' ),
						'id'               => '',
						'label'            => __( 'Sales Page Custom Content', 'lifterlms' ),
						'type'             => 'post-excerpt',
					),
					array(
						'controller'       => '#' . $this->prefix . 'sales_page_content_type',
						'controller_value' => 'page',
						'data_attributes'  => array(
							'post-type'   => 'page',
							'placeholder' => __( 'Select a page', 'lifterlms' ),
						),
						'class'            => 'llms-select2-post',
						'id'               => $this->prefix . 'sales_page_content_page_id',
						'type'             => 'select',
						'label'            => __( 'Select a Page', 'lifterlms' ),
						'value'            => $membership->get( 'sales_page_content_page_id' ) ? llms_make_select2_post_array( array( $membership->get( 'sales_page_content_page_id' ) ) ) : array(),
					),
					array(
						'controller'       => '#' . $this->prefix . 'sales_page_content_type',
						'controller_value' => 'url',
						'type'             => 'text',
						'label'            => __( 'Sales Page Redirect URL', 'lifterlms' ),
						'id'               => $this->prefix . 'sales_page_content_url',
						'class'            => 'input-full',
						'value'            => '',
						'desc_class'       => 'd-all',
						'group'            => 'top',
					),

				),
			),

			array(
				'title'  => __( 'General', 'lifterlms' ),
				'fields' => array(
					array(
						'type'  => 'text',
						'label' => __( 'Featured Video', 'lifterlms' ),
						'desc'  => sprintf( __( 'Paste the url for a Wistia, Vimeo or Youtube video or a hosted video file. For a full list of supported providers see %s.', 'lifterlms' ), '<a href="https://wordpress.org/documentation/article/embeds/#list-of-sites-you-can-embed-from" target="_blank">WordPress oEmbeds</a>' ),
						'id'    => $this->prefix . 'video_embed',
						'class' => 'code input-full',
					),
					array(
						'desc'       => __( 'When enabled, the featured video will be displayed on the membership tile in addition to the membership page.', 'lifterlms' ),
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'id'         => $this->prefix . 'tile_featured_video',
						'label'      => __( 'Display Featured Video on Membership Tile', 'lifterlms' ),
						'type'       => 'checkbox',
						'value'      => 'yes',
					),
					array(
						'type'  => 'text',
						'label' => __( 'Featured Audio', 'lifterlms' ),
						'desc'  => sprintf( __( 'Paste the url for a SoundCloud or Spotify song or a hosted audio file. For a full list of supported providers see %s.', 'lifterlms' ), '<a href="https://wordpress.org/documentation/article/embeds/#list-of-sites-you-can-embed-from" target="_blank">WordPress oEmbeds</a>' ),
						'id'    => $this->prefix . 'audio_embed',
						'class' => 'code input-full',
					),
					array(
						'type'  => 'basic-editor',
						'label' => __( 'Featured Pricing Information', 'lifterlms' ),
						'desc'  => __( 'Enter information on pricing for this membership, to be displayed on the catalog page.', 'lifterlms' ),
						'id'    => $this->prefix . 'featured_pricing',
						'class' => 'code input-full',
						'value' => 'test',
					),
				),
			),

			array(
				'title'  => __( 'Restrictions', 'lifterlms' ),
				'fields' => array(
					array(
						'allow_null'    => false,
						'class'         => '',
						'desc'          => __( 'When a non-member attempts to access content restricted to this membership', 'lifterlms' ),
						'id'            => $this->prefix . 'restriction_redirect_type',
						'is_controller' => true,
						'type'          => 'select',
						'label'         => __( 'Restricted Access Redirect', 'lifterlms' ),
						'value'         => array(
							array(
								'key'   => 'none',
								'title' => __( 'Stay on page', 'lifterlms' ),
							),
							array(
								'key'   => 'membership',
								'title' => __( 'Redirect to this membership page', 'lifterlms' ),
							),
							array(
								'key'   => 'page',
								'title' => __( 'Redirect to a WordPress page', 'lifterlms' ),
							),
							array(
								'key'   => 'custom',
								'title' => __( 'Redirect to a Custom URL', 'lifterlms' ),
							),
						),
					),
					array(
						'class'            => 'llms-select2-post',
						'controller'       => '#' . $this->prefix . 'restriction_redirect_type',
						'controller_value' => 'page',
						'data_attributes'  => array(
							'post-type' => 'page',
						),
						'id'               => $this->prefix . 'redirect_page_id',
						'label'            => __( 'Select a WordPress Page', 'lifterlms' ),
						'type'             => 'select',
						'value'            => $redirect_options,
					),
					array(
						'class'            => '',
						'controller'       => '#' . $this->prefix . 'restriction_redirect_type',
						'controller_value' => 'custom',
						'id'               => $this->prefix . 'redirect_custom_url',
						'label'            => __( 'Enter a Custom URL', 'lifterlms' ),
						'type'             => 'text',
						'value'            => 'test',
					),
					array(
						'class'      => '',
						'controls'   => '#' . $this->prefix . 'restriction_notice',
						'default'    => 'yes',
						'desc'       => __( 'Check this box to output a message after redirecting. If no redirect is selected this message will replace the normal content that would be displayed.', 'lifterlms' ),
						'desc_class' => 'd-3of4 t-3of4 m-1of2',
						'id'         => $this->prefix . 'restriction_add_notice',
						'label'      => __( 'Display a Message', 'lifterlms' ),
						'type'       => 'checkbox',
						'value'      => 'yes',
					),
					array(
						'class'    => 'full-width',
						'desc'     => sprintf( __( 'Shortcodes like %s can be used in this message', 'lifterlms' ), '[lifterlms_membership_link id="' . $this->post->ID . '"]' ),
						'default'  => sprintf( __( 'You must belong to the %s membership to access this content.', 'lifterlms' ), '[lifterlms_membership_link id="' . $this->post->ID . '"]' ),
						'id'       => $this->prefix . 'restriction_notice',
						'label'    => __( 'Restricted Content Notice', 'lifterlms' ),
						'type'     => 'text',
						'sanitize' => 'shortcode',
					),
				),
			),

			array(
				'title'  => __( 'Instructors', 'lifterlms' ),
				'fields' => array(
					array(
						'button'  => array(
							'text' => __( 'Add Instructor', 'lifterlms' ),
						),
						'handler' => 'instructors_mb_store',
						'header'  => array(
							'default' => __( 'New Instructor', 'lifterlms' ),
						),
						'id'      => $this->prefix . 'instructors_data',
						'label'   => '',
						'type'    => 'repeater',
						'fields'  => array(
							array(
								'allow_null'      => false,
								'data_attributes' => array(
									'placeholder' => esc_attr__( 'Select an Instructor', 'lifterlms' ),
									'roles'       => 'administrator,lms_manager,instructor,instructors_assistant',
								),
								'class'           => 'llms-select2-student',
								'group'           => 'd-2of3',
								'id'              => $this->prefix . 'id',
								'type'            => 'select',
								'label'           => __( 'Instructor', 'lifterlms' ),
							),
							array(
								'group'   => 'd-1of6',
								'class'   => 'input-full',
								'default' => $instructor_defaults['label'],
								'id'      => $this->prefix . 'label',
								'type'    => 'text',
								'label'   => __( 'Label', 'lifterlms' ),
							),
							array(
								'allow_null' => false,
								'class'      => 'llms-select2',
								'default'    => $instructor_defaults['visibility'],
								'group'      => 'd-1of6',
								'id'         => $this->prefix . 'visibility',
								'type'       => 'select',
								'label'      => __( 'Visibility', 'lifterlms' ),
								'value'      => array(
									'visible' => esc_html__( 'Visible', 'lifterlms' ),
									'hidden'  => esc_html__( 'Hidden', 'lifterlms' ),
								),
							),
						),
					),
				),
			),

			array(
				'title'  => __( 'Auto Enrollment', 'lifterlms' ),
				'fields' => array(
					array(
						'label'      => __( 'Automatic Enrollment', 'lifterlms' ),
						'desc'       => sprintf( __( 'When a student joins this membership they will be automatically enrolled in these courses. Click %1$shere%2$s for more information.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/membership-auto-enrollment/" target="_blank">', '</a>' ),
						'id'         => $this->prefix . 'content_table',
						'titles'     => array( '', __( 'Course Name', 'lifterlms' ), '' ),
						'type'       => 'table',
						'table_data' => $this->get_content_table( $membership ),
					),
					array(
						'class'           => 'llms-select2-post',
						'data_attributes' => array(
							'placeholder'    => __( 'Select course(s)', 'lifterlms' ),
							'post-type'      => 'course',
							'no-view-button' => true,
						),
						'id'              => $this->prefix . 'auto_enroll',
						'label'           => __( 'Add Course(s)', 'lifterlms' ),
						'type'            => 'select',
						'value'           => array(),
					),
				),
			),
		);
	}

	/**
	 * Save field data.
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Autoenroll courses saved via AJAX and removed from this method.
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 * @since 3.36.3 Added logic to correctly sanitize fields of type 'multi' (array)
	 *               and 'shortcode' (preventing quotes encode).
	 *               Also align the return type to the parent `save()` method.
	 * @since 5.9.0 Stop using deprecated `FILTER_SANITIZE_STRING`.
	 *
	 * @see LLMS_Admin_Metabox::save_actions()
	 *
	 * @param int $post_id WP_Post ID of the post being saved.
	 * @return int `-1` When no user or user is missing required capabilities or when there's no or invalid nonce.
	 *             `0` during inline saves or ajax requests or when no fields are found for the metabox.
	 *             `1` if fields were found. This doesn't mean there weren't errors during saving.
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return -1;
		}

		// Return early during quick saves and ajax requests.
		if ( isset( $_POST['action'] ) && 'inline-save' === $_POST['action'] ) {
			return 0;
		} elseif ( llms_is_ajax() ) {
			return 0;
		}

		$membership = new LLMS_Membership( $post_id );

		if ( ! isset( $_POST[ $this->prefix . 'restriction_add_notice' ] ) ) {
			$_POST[ $this->prefix . 'restriction_add_notice' ] = 'no';
		}

		// Get all defined fields.
		$fields = $this->get_fields();

		// save all the fields.
		$save_fields = array(
			$this->prefix . 'restriction_redirect_type',
			$this->prefix . 'redirect_page_id',
			$this->prefix . 'redirect_custom_url',
			$this->prefix . 'restriction_add_notice',
			$this->prefix . 'restriction_notice',
			$this->prefix . 'sales_page_content_page_id',
			$this->prefix . 'sales_page_content_type',
			$this->prefix . 'sales_page_content_url',
			$this->prefix . 'featured_pricing',
			$this->prefix . 'video_embed',
			$this->prefix . 'audio_embed',
			$this->prefix . 'tile_featured_video',
			$this->prefix . 'instructors_data',
		);

		if ( ! is_array( $fields ) ) {
			return 0;
		}

		$to_return = 0;

		// Loop through the fields.
		foreach ( $fields as $group => $data ) {

			// Find the fields in each tab.
			if ( isset( $data['fields'] ) && is_array( $data['fields'] ) ) {

				// loop through the fields.
				foreach ( $data['fields'] as $field ) {

					// don't save things that don't have an ID or that are not listed in $save_fields.
					if ( isset( $field['id'] ) && in_array( $field['id'], $save_fields, true ) ) {

						if ( isset( $field['handler'] ) ) {
							$this->save_field( $post_id, $field );
							$to_return = 1;
							continue;
						}

						if ( isset( $field['sanitize'] ) && 'shortcode' === $field['sanitize'] ) {
							$val = llms_filter_input_sanitize_string( INPUT_POST, $field['id'], array( FILTER_FLAG_NO_ENCODE_QUOTES ) );
						} elseif ( isset( $field['multi'] ) && $field['multi'] ) {
							$val = llms_filter_input_sanitize_string( INPUT_POST, $field['id'], array( FILTER_REQUIRE_ARRAY ) );
						} elseif ( $field['type'] === 'basic-editor' ) {
							$val = wp_kses( $_POST[ $field['id'] ], LLMS_ALLOWED_HTML_PRICES );
						} else {
							$val = llms_filter_input_sanitize_string( INPUT_POST, $field['id'] );
						}

						$membership->set( substr( $field['id'], strlen( $this->prefix ) ), $val );
						$to_return = 1;
					}
				}
			}
		}

		return $to_return;
	}
}
