<?php
/**
 * Membership Settings Metabox
 *
 * @since 1.0.0
 * @version 3.36.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Membership Settings Metabox
 *
 * @since 1.0.0
 * @since 3.30.3 Fixed spelling errors; removed duplicate array keys.
 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
 * @since 3.36.0 Allow some fields to store values with quotes.
 */
class LLMS_Meta_Box_Membership extends LLMS_Admin_Metabox {

	/**
	 * This function allows extending classes to configure required class properties
	 * $this->id, $this->title, and $this->screens should be configured in this function
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
	 * Get array of data to pass to the auto enrollment courses table
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

				'<span class="llms-drag-handle" style="color:#999;"><i class="fa fa-ellipsis-v" aria-hidden="true" style="margin-right:2px;"></i><i class="fa fa-ellipsis-v" aria-hidden="true"></i></span>',
				'<a href="' . get_edit_post_link( $course->get( 'id' ) ) . '">' . $title . ' (ID#' . $course_id . ')</a>',
				'<a class="llms-button-danger small" data-id="' . $course_id . '" href="#llms-course-remove" style="float:right;">' . __( 'Remove course', 'lifterlms' ) . '</a>
				 <a class="llms-button-secondary small" data-id="' . $course_id . '" href="#llms-course-bulk-enroll" style="float:right;">' . __( 'Enroll All Members', 'lifterlms' ) . '</a>',

			);

		}

		return apply_filters( 'llms_membership_get_content_table_data', $data, $membership );

	}

	/**
	 * This function is where extending classes can configure all the fields within the metabox
	 * The function must return an array which can be consumed by the "output" function
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
	 * Save field data
	 *
	 * @since 3.0.0
	 * @since 3.30.0 Autoenroll courses saved via AJAX and removed from this method.
	 * @since 3.35.0 Verify nonces and sanitize `$_POST` data.
	 *
	 * @see LLMS_Admin_Metabox::save_actions()
	 *
	 * @param int $post_id WP_Post ID of the post being saved.
	 * @return void
	 */
	public function save( $post_id ) {

		if ( ! llms_verify_nonce( 'lifterlms_meta_nonce', 'lifterlms_save_data' ) ) {
			return;
		}

		$membership = new LLMS_Membership( $post_id );

		if ( ! isset( $_POST[ $this->prefix . 'restriction_add_notice' ] ) ) {
			$_POST[ $this->prefix . 'restriction_add_notice' ] = 'no';
		}

		// save all the fields
		$fields = array(
			'restriction_redirect_type',
			'redirect_page_id',
			'redirect_custom_url',
			'restriction_add_notice',
			'restriction_notice',
			'sales_page_content_page_id',
			'sales_page_content_type',
			'sales_page_content_url',
		);
		foreach ( $fields as $field ) {

			if ( isset( $_POST[ $this->prefix . $field ] ) ) {

				$membership->set( $field, llms_filter_input( INPUT_POST, $this->prefix . $field, FILTER_SANITIZE_STRING ) );

			}
		}

	}

}
