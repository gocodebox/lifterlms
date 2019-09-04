<?php
/**
 * LifterLMS Update Functions
 *
 * Functions here are used by the background updater during db updates
 *
 * @since    3.4.3
 * @version  3.28.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Duplicate a WP Post & all relate metadata
 *
 * @param    int $id  WP Post ID
 * @return   int          WP Post ID of the new duplicate
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_update_util_post_duplicator( $id ) {

	$copy = (array) get_post( $id );
	unset( $copy['ID'] );
	$new_id = wp_insert_post( $copy );
	foreach ( get_post_custom( $id ) as $key => $values ) {
		foreach ( $values as $value ) {
			add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
		}
	}

	return $new_id;

}

/**
 * Update the key of a postmeta item
 *
 * @param    string $post_type   post type
 * @param    string $new_key     new postmeta key
 * @param    string $old_key     old postmeta key
 * @return   void
 * @since    3.4.3
 * @version  3.4.3
 */
function llms_update_util_rekey_meta( $post_type, $new_key, $old_key ) {

	global $wpdb;

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_key = %s
	 	 WHERE p.post_type = %s AND m.meta_key = %s;",
			array( $new_key, $post_type, $old_key )
		)
	);

}

/*
	  /$$$$$$      /$$$$$$      /$$$$$$
	 /$$__  $$    /$$$_  $$    /$$$_  $$
	|__/  \ $$   | $$$$\ $$   | $$$$\ $$
	   /$$$$$/   | $$ $$ $$   | $$ $$ $$
	  |___  $$   | $$\ $$$$   | $$\ $$$$
	 /$$  \ $$   | $$ \ $$$   | $$ \ $$$
	|  $$$$$$//$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/ \______/|__/ \______/
*/

/**
 * Creates access plans for each course & membership
 *
 * Creates up to 3 plans per course and up to two plans per membership
 *
 * Migrates price & subscription data to a single & recurring plan where applicable
 *
 * if course is restricted to a membership a free members only plan will be created
 * in addition to paid open recurring & single plans
 *
 * if course is restricted to a membership and no price is found
 * only one free members only plan will be created
 */
function llms_update_300_create_access_plans() {

	$courses = new WP_Query(
		array(
			'post_type'      => array( 'course', 'llms_membership' ),
			'posts_per_page' => -1,
			'status'         => 'any',
		)
	);

	if ( $courses->have_posts() ) {
		foreach ( $courses->posts as $post ) {

			$meta = get_post_meta( $post->ID );

			$is_free       = ( ! $meta['_price'][0] || floatval( 0 ) === floatval( $meta['_price'][0] ) );
			$has_recurring = ( 1 == $meta['_llms_recurring_enabled'][0] );
			if ( 'course' === $post->post_type ) {
				$members_only = ( 'on' === $meta['_llms_is_restricted'][0] && $meta['_llms_restricted_levels'][0] );
			} else {
				$members_only = false;
			}

			// base plan for single & recurring
			$base_plan = array(

				'access_expiration'         => 'lifetime',
				'availability'              => 'open',
				'availability_restrictions' => array(),
				'content'                   => '',
				'enroll_text'               => ( 'course' === $post->post_type ) ? __( 'Enroll', 'lifterlms' ) : __( 'Join', 'lifterlms' ),
				'featured'                  => 'no',
				'frequency'                 => 0,
				'is_free'                   => 'no',
				'product_id'                => $post->ID,
				'sku'                       => $meta['_sku'][0],
				'trial_offer'               => 'no',

			);

			$single = array_merge(
				array(
					'price' => $meta['_price'][0],
				),
				$base_plan
			);

			$recurring = array_merge(
				array(
					'price' => $meta['_llms_subscription_price'][0],
				),
				$base_plan
			);

			/**
			 * determine what kinds of plans to create
			 */

			// free and members only, only available to members
			if ( $is_free && $members_only ) {

				$free_members_only = true;
				$single_paid_open  = false;
				$single_free_open  = false;
				$recurring_paid    = false;

			} elseif ( ! $is_free && $members_only ) {

				$free_members_only = true;
				$single_paid_open  = true;
				$single_free_open  = false;
				$recurring_paid    = $has_recurring;

			} else {
				// no restrictions, normal settings apply

				$free_members_only = false;
				$single_paid_open  = ! $is_free ? true : false;
				$single_free_open  = $is_free ? true : false;
				$recurring_paid    = $has_recurring;

			}

			$order = 1;

			/**
			 * CREATE THE PLANS
			 */
			if ( $free_members_only ) {

				$plan                              = $single;
				$plan['menu_order']                = $order;
				$plan['is_free']                   = 'yes';
				$plan['sku']                       = ! empty( $plan['sku'] ) ? $plan['sku'] . '-membersonly' : '';
				$plan['availability']              = 'members';
				$plan['availability_restrictions'] = unserialize( $meta['_llms_restricted_levels'][0] );

				$obj = new LLMS_Access_Plan( 'new', __( 'Members Only', 'lifterlms' ) );
				foreach ( $plan as $key => $val ) {
					$obj->set( $key, $val );
				}

				unset( $plan );
				$order++;

			}

			if ( $single_paid_open ) {

				$plan               = $single;
				$plan['menu_order'] = $order;
				$plan['sku']        = ! empty( $plan['sku'] ) ? $plan['sku'] . '-onetime' : '';
				$plan['on_sale']    = ! empty( $meta['_sale_price'][0] ) ? 'yes' : 'no';

				if ( 'yes' === $plan['on_sale'] ) {

					$plan['sale_end']   = ! empty( $meta['_sale_price_dates_to'][0] ) ? date( 'm/d/Y', strtotime( $meta['_sale_price_dates_to'][0] ) ) : '';
					$plan['sale_start'] = ! empty( $meta['_sale_price_dates_from'][0] ) ? date( 'm/d/Y', strtotime( $meta['_sale_price_dates_from'][0] ) ) : '';
					$plan['sale_price'] = $meta['_sale_price'][0];

				}

				$obj = new LLMS_Access_Plan( 'new', __( 'One-Time Payment', 'lifterlms' ) );
				foreach ( $plan as $key => $val ) {
					$obj->set( $key, $val );
				}

				unset( $plan );
				$order++;

			}

			if ( $single_free_open ) {

				$plan               = $single;
				$plan['menu_order'] = $order;
				$plan['is_free']    = 'yes';
				$plan['sku']        = ! empty( $plan['sku'] ) ? $plan['sku'] . '-free' : '';

				$obj = new LLMS_Access_Plan( 'new', __( 'Free', 'lifterlms' ) );
				foreach ( $plan as $key => $val ) {
					$obj->set( $key, $val );
				}

				unset( $plan );
				$order++;

			}

			if ( $recurring_paid ) {

				$plan               = $recurring;
				$plan['menu_order'] = $order;
				$plan['sku']        = ! empty( $plan['sku'] ) ? $plan['sku'] . '-subscription' : '';

				if ( isset( $meta['_llms_subscription_first_payment'][0] ) && $meta['_llms_subscription_first_payment'][0] != $meta['_llms_subscription_price'][0] ) {
					$plan['trial_offer']  = 'yes';
					$plan['trial_length'] = $meta['_llms_billing_freq'][0];
					$plan['trial_period'] = $meta['_llms_billing_period'][0];
					$plan['trial_price']  = $meta['_llms_subscription_first_payment'][0];
				}

				$plan['frequency'] = $meta['_llms_billing_freq'][0];
				$plan['length']    = $meta['_llms_billing_cycle'][0];
				$plan['period']    = $meta['_llms_billing_period'][0];

				$obj = new LLMS_Access_Plan( 'new', __( 'Subscription', 'lifterlms' ) );
				foreach ( $plan as $key => $val ) {
					$obj->set( $key, $val );
				}

				unset( $plan );
				$order++;

			}

			$keys = array(
				'_regular_price',
				'_price',
				'_sale_price',
				'_sale_price_dates_from',
				'_sale_price_dates_to',
				'_on_sale',
				'_llms_recurring_enabled',
				'_llms_subscription_price',
				'_llms_subscription_first_payment',
				'_llms_billing_period',
				'_llms_billing_freq',
				'_llms_billing_cycle',
				'_llms_subscriptions',
				'_sku',
				'_is_custom_single_price',
				'_custom_single_price_html',
				'_llms_is_restricted',
				'_llms_restricted_levels',

				'_llms_expiration_interval',
				'_llms_expiration_period',
			);

			foreach ( $keys as $key ) {
				delete_post_meta( $post->ID, $key );
			}
		}// End foreach().
	}// End if().

}

/**
 * Delete deprecated options that are no longer used by LifterLMS after 3.0.0
 */
function llms_update_300_del_deprecated_options() {

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
	 * Delete course and membership display & related options
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

}

/**
 * Migrate deprecated account field related options to new ones
 */
function llms_update_300_migrate_account_field_options() {

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

}

/**
 * Move coupon title (previously used for description) to the postmeta table in the new description field
 * Move old coupon code from meta table to the coupon post title *
 */
function llms_update_300_migrate_coupon_data() {

	global $wpdb;

	$coupon_title_metas = $wpdb->get_results(
		"SELECT * FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_coupon_title';"
	);

	foreach ( $coupon_title_metas as $obj ) {

		// update new description field with the title b/c the title previously acted as a description
		update_post_meta( $obj->post_id, '_llms_description', get_the_title( $obj->post_id ) );

		// update the post title to be the value of the old meta field
		wp_update_post(
			array(
				'ID'         => $obj->post_id,
				'post_title' => $obj->meta_value,
			)
		);

		// clean up
		delete_post_meta( $obj->post_id, '_llms_coupon_title' );

	}

}

/**
 * Update keys of course meta fields for consistency
 */
function llms_update_300_migrate_course_postmeta() {

	global $wpdb;

	// rekey meta fields
	llms_update_util_rekey_meta( 'course', '_llms_audio_embed', '_audio_embed' );
	llms_update_util_rekey_meta( 'course', '_llms_video_embed', '_video_embed' );
	llms_update_util_rekey_meta( 'course', '_llms_has_prerequisite', '_has_prerequisite' );
	llms_update_util_rekey_meta( 'course', '_llms_length', '_lesson_length' );
	llms_update_util_rekey_meta( 'course', '_llms_capacity', '_lesson_max_user' );
	llms_update_util_rekey_meta( 'course', '_llms_prerequisite', '_prerequisite' );
	llms_update_util_rekey_meta( 'course', '_llms_prerequisite_track', '_prerequisite_track' );

	llms_update_util_rekey_meta( 'course', '_llms_start_date', '_course_dates_from' );
	llms_update_util_rekey_meta( 'course', '_llms_end_date', '_course_dates_to' );

	// updates course enrollment settings and reformats existing dates
	$dates = $wpdb->get_results(
		"SELECT m.meta_id, m.post_id, m.meta_value
		 FROM {$wpdb->postmeta} AS m
		 INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_ID
	 	 WHERE p.post_type = 'course' AND ( m.meta_key = '_llms_start_date' OR m.meta_key = '_llms_end_date' );"
	);
	foreach ( $dates as $r ) {
		// if no value in the field skip it otherwise we end up with start of the epoch
		if ( ! $r->meta_value ) {
			continue; }
		$wpdb->update(
			$wpdb->postmeta,
			array(
				'meta_value' => date( 'm/d/Y', strtotime( $r->meta_value ) ),
			),
			array(
				'meta_id' => $r->meta_id,
			)
		);
		add_post_meta( $r->post_id, '_llms_time_period', 'yes' );
		add_post_meta( $r->post_id, '_llms_course_opens_message', sprintf( __( 'This course opens on [lifterlms_course_info id="%d" key="start_date"].', 'lifterlms' ), $r->post_id ) );
		add_post_meta( $r->post_id, '_llms_course_closed_message', sprintf( __( 'This course closed on [lifterlms_course_info id="%d" key="end_date"].', 'lifterlms' ), $r->post_id ) );
	}

	// update course capacity bool and related settings
	$capacity = $wpdb->get_results(
		"SELECT m.post_id, m.meta_value
		 FROM {$wpdb->postmeta} AS m
		 INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_ID
	 	 WHERE p.post_type = 'course' AND m.meta_key = '_llms_capacity';"
	);
	foreach ( $capacity as $r ) {
		if ( $r->meta_value ) {
			add_post_meta( $r->post_id, '_llms_enable_capacity', 'yes' );
			add_post_meta( $r->post_id, '_llms_capacity_message', __( 'Enrollment has closed because the maximum number of allowed students has been reached.', 'lifterlms' ) );
		}
	}

	// convert numeric has_preqeq to "yes"
	$prereq = $wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'yes'
	 	 WHERE p.post_type = 'course' AND m.meta_key = '_llms_has_prerequisite' AND m.meta_value = 1;"
	);

	// convert empty has_prereq to "no"
	$prereq = $wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'no'
	 	 WHERE p.post_type = 'course' AND m.meta_key = '_llms_has_prerequisite' AND m.meta_value = '';"
	);

}

/**
 * Update keys of email meta fields for consistency
 */
function llms_update_300_migrate_email_postmeta() {

	llms_update_util_rekey_meta( 'llms_email', '_llms_email_subject', '_email_subject' );
	llms_update_util_rekey_meta( 'llms_email', '_llms_email_heading', '_email_heading' );

}

/**
 * Update keys of lesson meta fields for consistency
 */
function llms_update_300_migrate_lesson_postmeta() {

	global $wpdb;

	llms_update_util_rekey_meta( 'lesson', '_llms_audio_embed', '_audio_embed' );
	llms_update_util_rekey_meta( 'lesson', '_llms_video_embed', '_video_embed' );
	llms_update_util_rekey_meta( 'lesson', '_llms_has_prerequisite', '_has_prerequisite' );
	llms_update_util_rekey_meta( 'lesson', '_llms_prerequisite', '_prerequisite' );
	llms_update_util_rekey_meta( 'lesson', '_llms_days_before_available', '_days_before_avalailable' );

	// convert numeric has_preqeq to "yes"
	// convert numeric free_lesson to "yes"
	// convert numeric require_passing_grade to "yes"
	$wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'yes'
	 	 WHERE p.post_type = 'lesson' AND (
	 	 	   ( m.meta_key = '_llms_has_prerequisite' AND m.meta_value = 1 )
	 	 	OR ( m.meta_key = '_llms_free_lesson' AND m.meta_value = 1 )
	 	 	OR ( m.meta_key = '_llms_require_passing_grade' AND m.meta_value = 1 )
	 	 );"
	);

	// convert empty has_prereq to "no"
	// convert empty free_lesson to "no"
	// convert empty require_passing_grade to "no"
	$wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'no'
	 	 WHERE p.post_type = 'lesson' AND (
	 	 	   ( m.meta_key = '_llms_has_prerequisite' AND m.meta_value = '' )
	 	 	OR ( m.meta_key = '_llms_free_lesson' AND m.meta_value = '' )
	 	 	OR ( m.meta_key = '_llms_require_passing_grade' AND m.meta_value = '' )
	 	 );"
	);

	// updates course enrollment settings and reformats existing dates
	$drips = $wpdb->get_results(
		"SELECT m.post_id
		 FROM {$wpdb->postmeta} AS m
		 INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_ID
	 	 WHERE p.post_type = 'lesson' AND m.meta_key = '_llms_days_before_available';"
	);
	foreach ( $drips as $r ) {
		add_post_meta( $r->post_id, '_llms_drip_method', 'enrollment' );
	}

}

/**
 * Change the post type of orders and rekey meta fields
 */
function llms_update_300_migrate_order_data() {

	global $wpdb;

	// prefix the old unprefixed order post type
	$wpdb->query(
		"UPDATE {$wpdb->posts}
		 SET post_type = 'llms_order'
		 WHERE post_type = 'order';"
	);

	// rekey postmetas
	llms_update_util_rekey_meta( 'llms_order', '_llms_payment_gateway', '_llms_payment_method' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_product_id', '_llms_order_product_id' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_currency', '_llms_order_currency' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_coupon_id', '_llms_order_coupon_id' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_coupon_code', '_llms_order_coupon_code' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_coupon_type', '_llms_order_coupon_type' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_coupon_amount', '_llms_order_coupon_amount' );

	llms_update_util_rekey_meta( 'llms_order', '_llms_billing_frequency', '_llms_order_billing_freq' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_billing_length', '_llms_order_billing_cycle' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_billing_period', '_llms_order_billing_period' );

	llms_update_util_rekey_meta( 'llms_order', '_llms_gateway_api_mode', '_llms_stripe_api_mode' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_gateway_subscription_id', '_llms_stripe_subscription_id' );
	llms_update_util_rekey_meta( 'llms_order', '_llms_gateway_customer_id', '_llms_stripe_customer_id' );

	llms_update_util_rekey_meta( 'llms_order', '_llms_trial_total', '_llms_order_first_payment' );

	llms_update_util_rekey_meta( 'llms_order', '_llms_start_date', '_llms_order_date' );

}

/**
 * Migrate all orders from the 2.x to 3.x data structure
 */
function llms_update_300_update_orders() {

	$args = array(
		'post_type'      => array( 'llms_order' ),
		'posts_per_page' => -1,
		'status'         => 'publish',
	);

	$orders = new WP_Query( $args );

	if ( $orders->have_posts() ) {
		foreach ( $orders->posts as $post ) {

			$order = new LLMS_Order( $post );

			// add an order key
			$order->set( 'order_key', $order->generate_order_key() );

			$order->set( 'access_expiration', 'lifetime' );

			// add coupon used info
			$coupon_used = $order->get( 'coupon_id' ) ? 'yes' : 'no';
			$order->set( 'coupon_used', $coupon_used );

			// add data about the user to the order if we can find it
			if ( isset( $order->user_id ) ) {

				$id = $order->get( 'user_id' );

				if ( $id && get_user_by( 'ID', $id ) ) {

					$student = new LLMS_Student( $id );

					$metas = array(
						'billing_address_1'  => 'billing_address_1',
						'billing_address_2'  => 'billing_address_2',
						'billing_city'       => 'billing_city',
						'billing_country'    => 'billing_country',
						'billing_email'      => 'user_email',
						'billing_first_name' => 'first_name',
						'billing_last_name'  => 'last_name',
						'billing_state'      => 'billing_state',
						'billing_zip'        => 'billing_zip',
					);

					foreach ( $metas as $ordermeta => $usermeta ) {

						$v = $student->$usermeta;
						if ( $v ) {

							$order->set( $ordermeta, $v );

						}
					}
				}
			}

			// setup trial info if there was a first payment recorded
			if ( $order->get( 'trial_total' ) ) {

				$order->set( 'trial_offer', 'yes' );
				$order->set( 'trial_length', $order->get( 'billing_length' ) );
				$order->set( 'trial_period', $order->get( 'billing_period' ) );
				$order->set( 'trial_original_total', $order->get( 'trial_total' ) );

			} else {

				$order->set( 'trial_offer', 'no' );

			}

			$total = $order->is_recurring() ? get_post_meta( $post->ID, '_llms_order_recurring_price', true ) : get_post_meta( $post->ID, '_llms_order_total', true );
			$order->set( 'original_total', $total );
			$order->set( 'total', $total );

			$order->add_note( sprintf( __( 'This order was migrated to the LifterLMS 3.0 data structure. %1$sLearn more%2$s.', 'lifterlms' ), '<a href="https://lifterlms.com/docs/lifterlms-orders#migration" target="_blank">', '</a>' ) );

			// remove deprecated
			delete_post_meta( $post->ID, '_llms_order_recurring_price' );
			delete_post_meta( $post->ID, '_llms_order_total' );
			delete_post_meta( $post->ID, '_llms_order_coupon_limit' );
			delete_post_meta( $post->ID, '_llms_order_product_price' );
			delete_post_meta( $post->ID, '_llms_order_billing_start_date' );
			delete_post_meta( $post->ID, '_llms_order_coupon_value' );
			delete_post_meta( $post->ID, '_llms_order_original_total' );

		}// End foreach().
	}// End if().
}

/**
 * Update db version at conclusion of 3.0.0 updates
 */
function llms_update_300_update_db_version() {

	LLMS_Install::update_db_version( '3.0.0' );

}

/*
	  /$$$$$$      /$$$$$$      /$$$$$$
	 /$$__  $$    /$$$_  $$    /$$__  $$
	|__/  \ $$   | $$$$\ $$   |__/  \ $$
	   /$$$$$/   | $$ $$ $$      /$$$$$/
	  |___  $$   | $$\ $$$$     |___  $$
	 /$$  \ $$   | $$ \ $$$    /$$  \ $$
	|  $$$$$$//$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/ \______/|__/ \______/
*/

/**
 * Fix students with the bugged role "studnets"
 */
function llms_update_303_update_students_role() {

	// add the bugged role so we can remove it
	// we delete it at the conclusion of the function
	if ( ! get_role( 'studnet' ) ) {

		add_role(
			'studnet',
			__( 'Student', 'lifterlms' ),
			array(
				'read' => true,
			)
		);

	}

	$users = new WP_User_Query(
		array(
			'number'   => -1,
			'role__in' => array( 'studnet' ),
		)
	);

	if ( $users->get_results() ) {
		foreach ( $users->get_results() as $user ) {
			$user->remove_role( 'studnet' );
			$user->add_role( 'student' );
		}
	}

	// remove the bugged role when finished
	remove_role( 'studnet' );

}

/**
 * Update db version at conclusion of 3.0.3 updates
 */
function llms_update_303_update_db_version() {

	LLMS_Install::update_db_version( '3.0.3' );
}

/*
	  /$$$$$$  /$$   /$$      /$$$$$$
	 /$$__  $$| $$  | $$     /$$__  $$
	|__/  \ $$| $$  | $$    |__/  \ $$
	   /$$$$$/| $$$$$$$$       /$$$$$/
	  |___  $$|_____  $$      |___  $$
	 /$$  \ $$      | $$     /$$  \ $$
	|  $$$$$$//$$   | $$ /$$|  $$$$$$/
	 \______/|__/   |__/|__/ \______/
*/

/**
 * Rename meta keys for parent section and parent course relationships for
 * all LifterLMS Lessons and Sections
 */
function llms_update_343_update_relationships() {

	global $wpdb;

	// update parent course key for courses and lessons
	$wpdb->query(
		"UPDATE {$wpdb->postmeta} AS m
		 JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
		 SET m.meta_key = '_llms_parent_course'
		 WHERE m.meta_key = '_parent_course'
		   AND ( p.post_type = 'lesson' OR p.post_type = 'section' );"
	);

	// update parent section key for lessons
	$wpdb->query(
		"UPDATE {$wpdb->postmeta} AS m
		 JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
		 SET m.meta_key = '_llms_parent_section'
		 WHERE m.meta_key = '_parent_section'
		   AND p.post_type = 'lesson';"
	);

}

/**
 * Update db version at conclusion of 3.4.3 updates
 */
function llms_update_343_update_db_version() {

	LLMS_Install::update_db_version( '3.4.3' );

}

/*
	  /$$$$$$      /$$$$$$      /$$$$$$
	 /$$__  $$    /$$__  $$    /$$$_  $$
	|__/  \ $$   | $$  \__/   | $$$$\ $$
	   /$$$$$/   | $$$$$$$    | $$ $$ $$
	  |___  $$   | $$__  $$   | $$\ $$$$
	 /$$  \ $$   | $$  \ $$   | $$ \ $$$
	|  $$$$$$//$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/ \______/|__/ \______/
*/

/**
 * Add course and membership visibility settings
 * Default course is catalog only and default membership is catalog & search
 * Courses were NOT SEARCHABLE in earlier versions
 */
function llms_update_360_set_product_visibility() {
	$query = new WP_Query(
		array(
			'post_status'    => 'any',
			'post_type'      => array( 'course', 'llms_membership' ),
			'posts_per_page' => -1,
		)
	);
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$visibility = ( 'course' === $post->post_type ) ? 'catalog' : 'catalog_search';
			wp_set_object_terms( $post->ID, $visibility, 'llms_product_visibility', false );
		}
	}
}

/**
 * Update db version at conclusion of 3.6.0 updates
 */
function llms_update_360_update_db_version() {

	LLMS_Install::update_db_version( '3.6.0' );

}

/*
	  /$$$$$$      /$$$$$$      /$$$$$$
	 /$$__  $$    /$$__  $$    /$$$_  $$
	|__/  \ $$   | $$  \ $$   | $$$$\ $$
	   /$$$$$/   |  $$$$$$/   | $$ $$ $$
	  |___  $$    >$$__  $$   | $$\ $$$$
	 /$$  \ $$   | $$  \ $$   | $$ \ $$$
	|  $$$$$$//$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/ \______/|__/ \______/
*/

/**
 * Add visibility settings to all access plans
 * and delete the "featured" meta values for all access plans
 */
function llms_update_380_set_access_plan_visibility() {
	$query = new WP_Query(
		array(
			'post_status'    => 'any',
			'post_type'      => array( 'llms_access_plan' ),
			'posts_per_page' => -1,
		)
	);
	if ( $query->have_posts() ) {
		foreach ( $query->posts as $post ) {
			$plan       = llms_get_post( $post );
			$visibility = $plan->is_featured() ? 'featured' : 'visible';
			wp_set_object_terms( $post->ID, $visibility, 'llms_access_plan_visibility', false );
			delete_post_meta( $post->ID, '_llms_featured' );
		}
	}
}

/**
 * Update db version at conclusion of 3.8.0 updates
 */
function llms_update_380_update_db_version() {

	LLMS_Install::update_db_version( '3.8.0' );

}


/**
 * Add end dates to LifterLMS Orders which have a length but no saved end date
 */
function llms_update_3120_update_order_end_dates() {

	global $wpdb;

	$ids = $wpdb->get_col(
		"SELECT posts.ID
		 FROM {$wpdb->posts} AS posts
		 JOIN {$wpdb->postmeta} AS meta1 ON meta1.post_id = posts.ID AND meta1.meta_key = '_llms_billing_length'
		 LEFT JOIN {$wpdb->postmeta} AS meta2 ON meta2.post_id = posts.ID AND meta2.meta_key = '_llms_date_billing_end'
		 WHERE posts.post_type = 'llms_order'
		   AND meta2.meta_value IS NULL
		   AND meta1.meta_value > 0;"
	);

	foreach ( $ids as $id ) {

		$order = llms_get_post( $id );
		if ( ! is_a( $order, 'LLMS_Order' ) ) {
			continue;
		}

		$order->maybe_schedule_payment( true );

	}

}

/**
 * Rename options for bbPress and BuddyPress to follow the abstract integration options structure
 */
function llms_update_3120_update_integration_options() {

	global $wpdb;
	$wpdb->update(
		$wpdb->options,
		array(
			'option_name' => 'llms_integration_bbpress_enabled',
		),
		array(
			'option_name' => 'lifterlms_bbpress_enabled',
		)
	);

	$wpdb->update(
		$wpdb->options,
		array(
			'option_name' => 'llms_integration_buddypress_enabled',
		),
		array(
			'option_name' => 'lifterlms_buddypress_enabled',
		)
	);

}

/**
 * Update db version at conclusion of 3.12.0 updates
 */
function llms_update_3120_update_db_version() {

	LLMS_Install::update_db_version( '3.12.0' );

}

/*
	  /$$$$$$       /$$    /$$$$$$      /$$$$$$
	 /$$__  $$    /$$$$   /$$__  $$    /$$$_  $$
	|__/  \ $$   |_  $$  |__/  \ $$   | $$$$\ $$
	   /$$$$$/     | $$     /$$$$$/   | $$ $$ $$
	  |___  $$     | $$    |___  $$   | $$\ $$$$
	 /$$  \ $$     | $$   /$$  \ $$   | $$ \ $$$
	|  $$$$$$//$$ /$$$$$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/|______/ \______/|__/ \______/
*/

/**
 * Setup default instructor data for courses and memberships
 */
function llms_update_3130_create_default_instructors() {

	$query = new WP_Query(
		array(
			'post_type'      => array( 'course', 'llms_membership' ),
			'posts_per_page' => -1,
		)
	);

	foreach ( $query->posts as $post ) {
		$course = llms_get_post( $post );
		$course->set_instructors();
	}

}

/**
 * Add an admin notice about the new builder
 */
function llms_update_3130_builder_notice() {

	require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

	LLMS_Admin_Notices::add_notice(
		'update-3130',
		array(
			'html'        => sprintf(
				__( 'Welcome to LifterLMS 3.13.0! We\'ve packed a ton of features into this release: Take a moment to get familiar with the all new %1$scourse builder%3$s and our new %2$suser roles%3$s.', 'lifterlms' ),
				'<a href="https://lifterlms.com/docs/using-course-builder/" target="_blank">',
				'<a href="https://lifterlms.com/docs/roles-and-capabilities/" target="_blank">',
				'</a>'
			),
			'type'        => 'info',
			'dismissible' => true,
			'remindable'  => false,
		)
	);

}

/**
 * Update db version at conclusion of 3.13.0 updates
 */
function llms_update_3130_update_db_version() {

	LLMS_Install::update_db_version( '3.13.0' );

}




/*
	  /$$$$$$       /$$    /$$$$$$      /$$$$$$
	 /$$__  $$    /$$$$   /$$__  $$    /$$$_  $$
	|__/  \ $$   |_  $$  | $$  \__/   | $$$$\ $$
	   /$$$$$/     | $$  | $$$$$$$    | $$ $$ $$
	  |___  $$     | $$  | $$__  $$   | $$\ $$$$
	 /$$  \ $$     | $$  | $$  \ $$   | $$ \ $$$
	|  $$$$$$//$$ /$$$$$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/|______/ \______/|__/ \______/

*/

/**
 * Add yes/no vals for quiz new quiz settings
 *
 * @return   void
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_update_quiz_settings() {

	global $wpdb;
	$ids = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'llms_quiz'" );

	foreach ( $ids as $id ) {

		$quiz = llms_get_post( $id );

		if ( $quiz->get( 'time_limit' ) > 0 ) {
			$quiz->set( 'limit_time', 'yes' );
		}

		if ( $quiz->get( 'allowed_attempts' ) > 0 ) {
			$quiz->set( 'limit_attempts', 'yes' );
		}
	}

}

/**
 * Rename meta keys for lesson -> quiz relationship
 *
 * @return   void
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_lesson_to_quiz_relationships_migration() {

	global $wpdb;
	$wpdb->update(
		$wpdb->postmeta,
		array(
			'meta_key' => '_llms_quiz',
		),
		array(
			'meta_key' => '_llms_assigned_quiz',
		)
	);

}

/**
 * Migrate attempt data from the former location on the wp_usermeta table
 *
 * @return   void
 * @since    3.16.0
 * @version  3.24.1
 */
function llms_update_3160_attempt_migration() {

	global $wpdb;
	$query = $wpdb->get_results( "SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = 'llms_quiz_data' LIMIT 100;" );

	// finished
	if ( ! $query ) {
		set_transient( 'llms_update_3160_attempt_migration', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $query as $record ) {

		if ( ! empty( $record->meta_value ) ) {

			foreach ( unserialize( $record->meta_value ) as $attempt ) {

				if ( ! is_array( $attempt ) ) {
					continue;
				}

				$to_insert = array();
				$format    = array();

				$start = $attempt['start_date'];
				$end   = $attempt['end_date'];

				if ( $end ) {
					$to_insert['update_date'] = $end;
					$format[]                 = '%s';
				} elseif ( $start ) {
					$to_insert['update_date'] = $start;
					$format[]                 = '%s';
				} else {
					continue;
				}

				foreach ( $attempt as $key => $val ) {

					$insert_key = $key;
					$insert_val = $val;

					if ( 'assoc_lesson' === $key ) {
						$insert_key = 'lesson_id';
					} elseif ( 'id' === $key ) {
						$insert_key = 'quiz_id';
					} elseif ( 'user_id' === $key ) {
						$insert_key = 'student_id';
					} elseif ( 'wpnonce' === $key ) {
						continue;
					} elseif ( 'current' === $key ) {
						continue;
					} elseif ( 'questions' === $key ) {
						$insert_val = serialize( $val );
					} elseif ( 'passed' === $key ) {
						$insert_key = 'status';
						if ( $val ) {
							$insert_val = 'pass';
						} else {
							// quiz has been initialized but hasn't been started yet
							// we don't need to migrate these
							if ( ! $start && ! $end ) {
								// $insert_val = 'new';
								continue;
							} elseif ( $start && ! $end ) {
								// still taking the quiz
								if ( isset( $attempt['current'] ) && $attempt['current'] ) {
									$insert_val = 'current';
								}
								// quiz was abandoned
								$insert_val = 'incomplete';
								// actual failure
							} else {
								$insert_val = 'fail';
							}
						}
					}// End if().

					switch ( $insert_key ) {

						case 'lesson_id':
						case 'quiz_id':
						case 'student_id':
						case 'attempt':
							$insert_format = '%d';
							break;

						case 'grade':
							$insert_format = '%f';
							break;

						default:
							$insert_format = '%s';

					}

					$to_insert[ $insert_key ] = $insert_val;
					$format[]                 = $insert_format;

				}// End foreach().

				$wpdb->insert( $wpdb->prefix . 'lifterlms_quiz_attempts', $to_insert, $format );

			} // End foreach().
		}// End if().

		// backup original
		update_user_meta( $record->user_id, 'llms_legacy_quiz_data', $record->meta_value );

		// delete the original so it's not there on the next run
		delete_user_meta( $record->user_id, 'llms_quiz_data' );

	}// End foreach().

	// needs to run again
	return true;

}

/**
 * Create duplicate questions for each question attached to multiple quizzes
 *
 * @return   [void]
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_ensure_no_dupe_question_rels() {

	if ( 'complete' !== get_transient( 'llms_update_3160_attempt_migration' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_3160_skipper_dupe_q' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_3160_skipper_dupe_q', $skip + 20, DAY_IN_SECONDS );

	global $wpdb;
	$question_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT ID
		 FROM {$wpdb->posts}
		 WHERE post_type = 'llms_question'
		 ORDER BY ID ASC
		 LIMIT %d, 20;",
			$skip
		)
	);

	if ( ! $question_ids ) {
		set_transient( 'llms_update_3160_ensure_no_dupe_question_rels_status', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $question_ids as $qid ) {

		$parts = array(
			serialize(
				array(
					'id' => $qid,
				)
			),
			serialize(
				array(
					'id' => absint( $qid ),
				)
			),
		);

		foreach ( $parts as &$part ) {
			$part = substr( $part, 5, -1 );
		}

		// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$quiz_ids = $wpdb->get_col(
			"
			SELECT post_id
			FROM {$wpdb->postmeta}
			WHERE meta_key = '_llms_questions'
			  AND ( meta_value LIKE '%{$parts[0]}%' OR meta_value LIKE '%{$parts[1]}%' );"
		);
		// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

		// question is attached to 2 or more quizzes
		if ( count( $quiz_ids ) >= 2 ) {

			// remove the first quiz and duplicate questions for the remaining quizzes
			array_shift( $quiz_ids );

			foreach ( $quiz_ids as $quiz_id ) {

				// copy the question and add update the reference on the quiz
				$question_copy_id = llms_update_util_post_duplicator( $qid );
				$questions        = get_post_meta( $quiz_id, '_llms_questions', true );
				foreach ( $questions as &$qdata ) {
					if ( $qdata['id'] == $qid ) {
						$qdata['id'] = $question_copy_id;
					}
				}
				update_post_meta( $quiz_id, '_llms_questions', $questions );

				// update references to the quiz in quiz attempts
				// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$attempt_ids = $wpdb->get_col(
					"
					SELECT id
					FROM {$wpdb->prefix}lifterlms_quiz_attempts
					WHERE quiz_id = {$quiz_id}
					  AND ( questions LIKE '%{$parts[0]}%' OR questions LIKE '%{$parts[1]}%' );"
				);
				// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

				foreach ( $attempt_ids as $aid ) {

					$attempt    = new LLMS_Quiz_Attempt( $aid );
					$attempt_qs = $attempt->get_questions();
					foreach ( $attempt_qs as &$answer ) {
						if ( $answer['id'] == $qid ) {
							$answer['id'] = $question_copy_id;
						}
					}
					$attempt->set_questions( $attempt_qs, true );

				}
			}
		}// End if().
	}// End foreach().

	// need to run again
	return true;

}

/**
 * Create duplicates for any quiz attached to multiple lessons
 *
 * @return   void
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_ensure_no_lesson_dupe_rels() {

	if ( 'complete' !== get_transient( 'llms_update_3160_ensure_no_dupe_question_rels_status' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_3160_skipper_dupe_l' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_3160_skipper_dupe_l', $skip + 100, DAY_IN_SECONDS );

	global $wpdb;
	$res = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id AS lesson_id, meta_value AS quiz_id
		 FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_quiz'
		   AND meta_value != 0
		 ORDER BY lesson_id ASC
		 LIMIT %d, 100
		;",
			$skip
		)
	);

	if ( ! $res ) {
		set_transient( 'llms_update_3160_ensure_no_lesson_dupe_rels', 'complete', DAY_IN_SECONDS );
		return false;
	}

	$quizzes_set = array();

	foreach ( $res as $data ) {

		$lesson = llms_get_post( $data->lesson_id );
		if ( ! $lesson ) {
			continue;
		}

		// quiz no longer exists, unset the data from the lesson
		$quiz = llms_get_post( $data->quiz_id );
		if ( ! $quiz ) {
			$lesson->set( 'quiz', 0 );
			$lesson->set( 'quiz_enabled', 'no' );
			continue;
		}

		// quiz already attached to a lesson
		// + duplicate it
		// + assign lesson/quiz relationships off new quiz
		// + find quiz attempts by old quiz / lesson
		// + update attempt quiz id
		// + update attempt question ids
		//
		if ( in_array( $data->quiz_id, $quizzes_set ) ) {

			$orig_questions = get_post_meta( $data->quiz_id, '_llms_questions', true );
			$qid_map        = array();
			$dupe_quiz_id   = llms_update_util_post_duplicator( $data->quiz_id );
			foreach ( $orig_questions as &$oqdata ) {
				$dupe_q                   = llms_update_util_post_duplicator( $oqdata['id'] );
				$qid_map[ $oqdata['id'] ] = $dupe_q;
				$oqdata['id']             = $dupe_q;
			}
			update_post_meta( $dupe_quiz_id, '_llms_questions', $orig_questions );
			update_post_meta( $dupe_quiz_id, '_llms_lesson_id', $data->lesson_id );

			$lesson->set( 'quiz', $dupe_quiz_id );

			// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$attempt_ids = $wpdb->get_col(
				"
				SELECT id
				FROM {$wpdb->prefix}lifterlms_quiz_attempts
				WHERE quiz_id = {$data->quiz_id} AND lesson_id = {$data->lesson_id}"
			);
			// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

			foreach ( $attempt_ids as $aid ) {
				$attempt   = new LLMS_Quiz_Attempt( $aid );
				$questions = $attempt->get_questions();
				foreach ( $questions as &$aqd ) {

					if ( isset( $qid_map[ $aqd['id'] ] ) ) {
						$aqd['id'] = $qid_map[ $aqd['id'] ];
					}
				}
				$attempt->set_questions( $questions, true );
				$attempt->set( 'quiz_id', $dupe_quiz_id );
				$attempt->save();

			}
		}

		$quizzes_set[] = $data->quiz_id;
		$lesson->set( 'quiz_enabled', 'yes' ); // ensure the new quiz enabled key is set

	}// End foreach().

	// run it again
	return true;

}

/**
 * Update question & choice data to new structure
 *
 * @return   void
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_update_question_data() {

	if ( 'complete' !== get_transient( 'llms_update_3160_ensure_no_lesson_dupe_rels' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_3160_skipper_qdata' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_3160_skipper_qdata', $skip + 100, DAY_IN_SECONDS );

	global $wpdb;
	$res = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT post_id AS quiz_id, meta_value AS questions
		 FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_questions'
		 ORDER BY post_id ASC
		 LIMIT %d, 100;",
			$skip
		)
	);

	// finished
	if ( ! $res ) {
		set_transient( 'llms_update_3160_update_question_data', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $res as $data ) {
		$questions = maybe_unserialize( $data->questions );
		if ( is_array( $questions ) ) {
			foreach ( $questions as $raw_question ) {

				$points = isset( $raw_question['points'] ) ? $raw_question['points'] : 1;

				$question = llms_get_post( $raw_question['id'] );

				if ( ! $question ) {
					continue;
				}

				$question->set( 'parent_id', $data->quiz_id );
				$question->set( 'question_type', 'choice' );
				$question->set( 'points', $points );
				update_post_meta( $question->get( 'id' ), '_llms_legacy_question_title', $question->get( 'title' ) );
				$question->set( 'title', strip_tags( str_replace( array( '<p>', '</p>' ), '', $question->get( 'content' ) ), '<b><em><u><strong><i>' ) );

				$options = get_post_meta( $question->get( 'id' ), '_llms_question_options', true );

				update_post_meta( $question->get( 'id' ), '_llms_legacy_question_options', $options );
				delete_post_meta( $question->get( 'id' ), '_llms_question_options' );

				if ( ! $options ) {
					continue;
				}
				$clarify = '';

				$markers = range( 'A', 'Z' );

				foreach ( (array) $options as $index => $option ) {

					if ( ! isset( $option['option_text'] ) ) {
						continue;
					}

					$correct = false;
					// no correct_option set for the choice, set it to false
					if ( ! isset( $option['correct_option'] ) ) {
						$correct = false;
						// handle bool strings like "on" "off" "yes" "no"
						// and questions imported from a 3rd party Excel to LifterLMS plugin
						// that doesn't save options in the expected format...
						// dev if you're reading this I love you but you caused me a pretty large headache
						// trying to figure out where in our codebase we went wrong...
					} elseif ( is_string( $option['correct_option'] ) && '' !== $option['correct_option'] ) {
						$correct = true;
						// catch everything else and filter var it
					} else {

						$correct = filter_var( $option['correct_option'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );

						// nothing should get here but I'm tired...
						if ( is_null( $correct ) ) {
							$correct = true;
						}
					}

					$question->create_choice(
						array(
							'choice'  => $option['option_text'],
							'correct' => $correct,
							'marker'  => $markers[ $index ],
						)
					);

					// if an option desc is set
					if ( ! empty( $option['option_description'] ) ) {
						// if the description hasn't already been added to the new clarification
						if ( false === strpos( $clarify, $option['option_description'] ) ) {
							$clarify .= $option['option_description'] . '<br><br>';
						}
					}
				}// End foreach().

				if ( $clarify ) {
					$question->set( 'clarifications', trim( rtrim( $clarify, '<br><br>' ) ) );
					$question->set( 'clarifications_enabled', 'yes' );
				}
			}// End foreach().
		}// End if().
	}// End foreach().

	// run it again
	return true;

}

/**
 * Update question data to new formats & match question choice indexes to new choice IDs
 *
 * @return   void
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_update_attempt_question_data() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_question_data' ) ) {
		return true;
	}

	$skip = get_transient( 'llms_update_3160_skipper' );
	if ( ! $skip ) {
		$skip = 0;
	}
	set_transient( 'llms_update_3160_skipper', $skip + 500, DAY_IN_SECONDS );

	global $wpdb;
	$res = $wpdb->get_col( $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}lifterlms_quiz_attempts ORDER BY id ASC LIMIT %d, 500", $skip ) );

	// finished
	if ( ! $res ) {
		set_transient( 'llms_update_3160_update_attempt_question_data', 'complete', DAY_IN_SECONDS );
		return false;
	}

	foreach ( $res as $att_id ) {

		$attempt   = new LLMS_Quiz_Attempt( $att_id );
		$questions = $attempt->get_questions();
		foreach ( $questions as &$question ) {

			$question['earned'] = empty( $question['correct'] ) ? 0 : $question['points'];
			if ( ! isset( $question['answer'] ) ) {
				$question['answer'] = array();
			} elseif ( ! is_array( $question['answer'] ) && is_numeric( $question['answer'] ) ) {
				$obj = llms_get_post( $question['id'] );
				if ( $obj ) {
					$choices = $obj->get_choices();
					if ( isset( $choices[ $question['answer'] ] ) ) {
						$question['answer'] = array( $choices[ $question['answer'] ]->get( 'id' ) );
					}
				}
			}
		}

		$attempt->set_questions( $questions, true );

	}

	return true;

}

/**
 * Ensure quizzes backreference their parent lessons
 *
 * @return   void
 * @since    3.16.0
 * @version  3.16.10
 */
function llms_update_3160_update_quiz_to_lesson_rels() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_attempt_question_data' ) ) {
		return true;
	}

	global $wpdb;
	$ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_llms_quiz_enabled' AND meta_value = 'yes'" );

	foreach ( $ids as $id ) {

		$lesson = llms_get_post( $id );
		if ( $lesson ) {
			$quiz_id = $lesson->get( 'quiz' );
			if ( $quiz_id ) {
				$quiz = llms_get_post( $quiz_id );
				$quiz->set( 'lesson_id', $id );
			}
		}
	}

}

/**
 * Add an admin notice about new quiz things
 *
 * @return void
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_update_3160_builder_notice() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_attempt_question_data' ) ) {
		return true;
	}

	require_once LLMS_PLUGIN_DIR . 'includes/admin/class.llms.admin.notices.php';

	LLMS_Admin_Notices::add_notice(
		'update-3160',
		array(
			'html'        => sprintf(
				__( 'Welcome to LifterLMS 3.16.0! This update adds significant improvements to the quiz-building experience. Notice quizzes and questions are no longer found under "Courses" on the sidebar? Your quizzes have not been deleted but they have been moved! Read more about the all new %1$squiz builder%2$s.', 'lifterlms' ),
				'<a href="http://blog.lifterlms.com/hello-quizzes/" target="_blank">',
				'</a>'
			),
			'type'        => 'info',
			'dismissible' => true,
			'remindable'  => false,
		)
	);

}

/**
 * Update db version at conclusion of 3.16.0 updates
 *
 * @return void
 * @since    3.16.0
 * @version  3.16.0
 */
function llms_update_3160_update_db_version() {

	if ( 'complete' !== get_transient( 'llms_update_3160_update_attempt_question_data' ) ) {
		return true;
	}

	LLMS_Install::update_db_version( '3.16.0' );

}

/*
	  /$$$$$$      /$$$$$$   /$$$$$$      /$$$$$$
	 /$$__  $$    /$$__  $$ /$$__  $$    /$$$_  $$
	|__/  \ $$   |__/  \ $$| $$  \ $$   | $$$$\ $$
	   /$$$$$/     /$$$$$$/|  $$$$$$/   | $$ $$ $$
	  |___  $$    /$$____/  >$$__  $$   | $$\ $$$$
	 /$$  \ $$   | $$      | $$  \ $$   | $$ \ $$$
	|  $$$$$$//$$| $$$$$$$$|  $$$$$$//$$|  $$$$$$/
	 \______/|__/|________/ \______/|__/ \______/
*/

/**
 * Clear the unused cron `lifterlms_cleanup_sessions`
 *
 * @return  void
 * @since   3.28.0
 * @version 3.28.0
 */
function llms_update_3280_clear_session_cleanup_cron() {
	wp_clear_scheduled_hook( 'lifterlms_cleanup_sessions' );
}

/**
 * Update db version at conclusion of 3.28.0 updates
 *
 * @return void
 * @since    3.28.0
 * @version  3.28.0
 */
function llms_update_3280_update_db_version() {
	LLMS_Install::update_db_version( '3.28.0' );
}
