<?php
/**
 * Update the LifterLMS Database to 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Update_300 extends LLMS_Update {

	/**
	 * Array of callable function names (within the class)
	 * that need to be called to complete the update
	 *
	 * if functions are dependent on each other
	 * the functions themselves should schedule additional actions
	 * via $this->schedule_function() upon completion
	 *
	 * @var  array
	 */
	protected $functions = array(

		'del_deprecated_options',

		'migrate_accont_field_options',

		'migrate_coupon_data',
		'migrate_email_postmeta',
		'migrate_lesson_postmeta',

	);

	/**
	 * Version number of the update
	 * @var  string
	 */
	protected $version = '3.0.0';



	/**
	 * Delete deprecated options that are no longer used by LifterLMS after 3.0.0
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function del_deprecated_options() {

		$this->log( 'function `del_deprecated_options` started' );

		/**
		 * Delete legacy options related to LifterLMS updating
		 * prior to 2.0 release. this is long overdue
		 */
		delete_option( 'lifterlms_is_activated' );
		delete_option( 'lifterlms_update_key' );
		delete_option( 'lifterlms_authkey' );
		delete_option( 'lifterlms_activation_key' );

		/**
		 * Legacy option no longer needed
		 */
		delete_option( 'lifterlms_student_role_created' );

		/**
		 * Delete course and memberhip display & related options
		 * these are now filters or can be handled with action hooks
		 * moving forward
		 */
		delete_option( 'lifterlms_button_purchase_membership_custom_text' );
		delete_option( 'lifterlms_course_display_outline_lesson_thumbnails' );
		delete_option( 'lifterlms_course_display_author' );
		delete_option( 'lifterlms_course_display_banner' );
		delete_option( 'lifterlms_course_display_difficulty' );
		delete_option( 'lifterlms_course_display_length' );
		delete_option( 'lifterlms_course_display_categories' );
		delete_option( 'lifterlms_course_display_tags' );
		delete_option( 'lifterlms_course_display_tracks' );
		delete_option( 'lifterlms_lesson_nav_display_excerpt' );
		delete_option( 'lifterlms_course_display_outline' );
		delete_option( 'lifterlms_course_display_outline_titles' );
		delete_option( 'lifterlms_course_display_outline_lesson_thumbnails' );
		delete_option( 'lifterlms_display_lesson_complete_placeholders' );
		delete_option( 'redirect_to_checkout' );

		// finished
		$this->function_complete( 'del_deprecated_options' );

	}

	/**
	 * Migrate deprecated account field related options to new ones
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function migrate_accont_field_options() {

		$this->log( 'function `migrate_accont_field_options` started' );

		$email_confirm = get_option( 'lifterlms_registration_confirm_email' );
		if ( 'yes' === $email_confirm ) {
			$email_confirm = 'yes';
		} elseif ( 'no' === $email_confirm ) {
			$email_confirm = 'no';
		} else {
			$email_confirm = false;
		}

		$names = get_option( 'lifterlms_registration_require_name' );
		if ( 'yes' === $names ) {
			$names = 'required';
		} elseif ( 'no' === $names ) {
			$names = 'hidden';
		} else {
			$names = false;
		}

		$addresses = get_option( 'lifterlms_registration_require_address' );
		if ( 'yes' === $addresses ) {
			$addresses = 'required';
		} elseif ( 'no' === $addresses ) {
			$addresses = 'hidden';
		} else {
			$addresses = false;
		}

		$phone = get_option( 'lifterlms_registration_add_phone' );
		if ( 'yes' === $phone ) {
			$phone = 'optional';
		} elseif ( 'no' === $phone ) {
			$phone = 'hidden';
		} else {
			$phone = false;
		}

		foreach ( array( 'checkout', 'registration', 'account' ) as $screen ) {

			if ( $email_confirm ) {
				update_option( 'lifterlms_user_info_field_email_confirmation_' . $screen . '_visibility', $email_confirm );
			}
			if ( $names ) {
				update_option( 'lifterlms_user_info_field_names_' . $screen . '_visibility', $names );
			}
			if ( $addresses ) {
				update_option( 'lifterlms_user_info_field_address_' . $screen . '_visibility', $addresses );
			}
			if ( $phone ) {
				update_option( 'lifterlms_user_info_field_phone_' . $screen . '_visibility', $phone );
			}

		}

		delete_option( 'lifterlms_registration_confirm_email' );
		delete_option( 'lifterlms_registration_require_name' );
		delete_option( 'lifterlms_registration_require_address' );
		delete_option( 'lifterlms_registration_add_phone' );

		// finished
		$this->function_complete( 'migrate_accont_field_options' );

	}

	/**
	 * Move coupon title (previously used for description) to the postmeta table in the new description field
	 * Move old coupon code from meta table to the coupon post title *
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function migrate_coupon_data() {

		$this->log( 'function `migrate_coupon_data` started' );

		global $wpdb;

		$coupon_title_metas = $wpdb->get_results(
			"SELECT * FROM {$wpdb->postmeta}
			 WHERE meta_key = '_llms_coupon_title';"
		);

		foreach ( $coupon_title_metas as $obj ) {

			// update new description field with the title b/c the title previously acted as a description
			update_post_meta( $obj->post_id, '_llms_description', get_the_title( $obj->post_id ) );

			// update the post title to be the value of the old meta field
			wp_update_post( array(
				'ID' => $obj->post_id,
				'post_title' => $obj->meta_value,
			) );

			// clean up
			delete_post_meta( $obj->post_id, '_llms_coupon_title' );

		}

		// finished
		$this->function_complete( 'migrate_coupon_data' );

	}

	/**
	 * Update keys of email meta fields for consistency
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function migrate_email_postmeta() {

		$this->log( 'function `migrate_email_postmeta` started' );

		global $wpdb;

		$emails_subject = $wpdb->query(
			"UPDATE {$wpdb->prefix}postmeta AS m
			 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
			 SET m.meta_key = '_llms_email_subject'
		 	 WHERE p.post_type = 'llms_email' AND m.meta_key = '_email_subject';"
		);

		$emails_heading = $wpdb->query(
			"UPDATE {$wpdb->prefix}postmeta AS m
			 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
			 SET m.meta_key = '_llms_email_heading'
		 	 WHERE p.post_type = 'llms_email' AND m.meta_key = '_email_heading';"
		);

		// finished
		$this->function_complete( 'migrate_email_postmeta' );

	}

	/**
	 * Update keys of lesson meta fields for consistency
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function migrate_lesson_postmeta() {

		$this->log( 'function `migrate_lesson_postmeta` started' );

		global $wpdb;

		$audios = $wpdb->query(
			"UPDATE {$wpdb->prefix}postmeta AS m
			 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
			 SET m.meta_key = '_llms_audio_embed'
		 	 WHERE p.post_type = 'lesson' AND m.meta_key = '_audio_embed';"
		);

		$videos = $wpdb->query(
			"UPDATE {$wpdb->prefix}postmeta AS m
			 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
			 SET m.meta_key = '_llms_video_embed'
		 	 WHERE p.post_type = 'lesson' AND m.meta_key = '_video_embed';"
		);

		// finished
		$this->function_complete( 'migrate_lesson_postmeta' );

	}


}

return new LLMS_Update_300;
