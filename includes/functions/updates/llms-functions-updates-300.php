<?php
/**
 * Update functions for version 3.0.0
 *
 * @package LifterLMS/Functions/Updates
 *
 * @since 3.39.0
 * @version 3.39.0
 */

defined( 'ABSPATH' ) || exit;


/**
 * Creates access plans for each course & membership
 *
 * Creates up to 3 plans per course and up to two plans per membership.
 *
 * Migrates price & subscription data to a single & recurring plan where applicable.
 *
 * If course is restricted to a membership a free members only plan will be created
 * in addition to paid open recurring & single plans.
 *
 * If course is restricted to a membership and no price is found
 * only one free members only plan will be created.
 *
 * @since 3.0.0
 *
 * @return void
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

			// Base plan for single & recurring.
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
			 * Determine what kinds of plans to create
			 */

			// Free and members only, only available to members.
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
				// No restrictions, normal settings apply.

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
		}
	}

}

/**
 * Delete deprecated options that are no longer used by LifterLMS after 3.0.0
 *
 * @since 3.0.0
 *
 * @return void
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
 *
 * @since 3.0.0
 *
 * @return void
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
 *
 * @since 3.0.0
 *
 * @return void
 */
function llms_update_300_migrate_coupon_data() {

	global $wpdb;

	$coupon_title_metas = $wpdb->get_results(
		"SELECT * FROM {$wpdb->postmeta}
		 WHERE meta_key = '_llms_coupon_title';"
	);

	foreach ( $coupon_title_metas as $obj ) {

		// Update new description field with the title b/c the title previously acted as a description.
		update_post_meta( $obj->post_id, '_llms_description', get_the_title( $obj->post_id ) );

		// Update the post title to be the value of the old meta field.
		wp_update_post(
			array(
				'ID'         => $obj->post_id,
				'post_title' => $obj->meta_value,
			)
		);

		// Clean up.
		delete_post_meta( $obj->post_id, '_llms_coupon_title' );

	}

}

/**
 * Update keys of course meta fields for consistency
 *
 * @since 3.0.0
 *
 * @return void
 */
function llms_update_300_migrate_course_postmeta() {

	global $wpdb;

	// Rekey meta fields.
	llms_update_util_rekey_meta( 'course', '_llms_audio_embed', '_audio_embed' );
	llms_update_util_rekey_meta( 'course', '_llms_video_embed', '_video_embed' );
	llms_update_util_rekey_meta( 'course', '_llms_has_prerequisite', '_has_prerequisite' );
	llms_update_util_rekey_meta( 'course', '_llms_length', '_lesson_length' );
	llms_update_util_rekey_meta( 'course', '_llms_capacity', '_lesson_max_user' );
	llms_update_util_rekey_meta( 'course', '_llms_prerequisite', '_prerequisite' );
	llms_update_util_rekey_meta( 'course', '_llms_prerequisite_track', '_prerequisite_track' );

	llms_update_util_rekey_meta( 'course', '_llms_start_date', '_course_dates_from' );
	llms_update_util_rekey_meta( 'course', '_llms_end_date', '_course_dates_to' );

	// Updates course enrollment settings and reformats existing dates.
	$dates = $wpdb->get_results(
		"SELECT m.meta_id, m.post_id, m.meta_value
		 FROM {$wpdb->postmeta} AS m
		 INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_ID
	 	 WHERE p.post_type = 'course' AND ( m.meta_key = '_llms_start_date' OR m.meta_key = '_llms_end_date' );"
	); // db call ok; no-cache ok.
	foreach ( $dates as $r ) {
		// If no value in the field skip it otherwise we end up with start of the epoch.
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
		); // db call ok; no-cache ok.
		add_post_meta( $r->post_id, '_llms_time_period', 'yes' );
		add_post_meta( $r->post_id, '_llms_course_opens_message', sprintf( __( 'This course opens on [lifterlms_course_info id="%d" key="start_date"].', 'lifterlms' ), $r->post_id ) );
		add_post_meta( $r->post_id, '_llms_course_closed_message', sprintf( __( 'This course closed on [lifterlms_course_info id="%d" key="end_date"].', 'lifterlms' ), $r->post_id ) );
	}

	// Update course capacity bool and related settings.
	$capacity = $wpdb->get_results(
		"SELECT m.post_id, m.meta_value
		 FROM {$wpdb->postmeta} AS m
		 INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_ID
	 	 WHERE p.post_type = 'course' AND m.meta_key = '_llms_capacity';"
	); // db call ok; no-cache ok.
	foreach ( $capacity as $r ) {
		if ( $r->meta_value ) {
			add_post_meta( $r->post_id, '_llms_enable_capacity', 'yes' );
			add_post_meta( $r->post_id, '_llms_capacity_message', __( 'Enrollment has closed because the maximum number of allowed students has been reached.', 'lifterlms' ) );
		}
	}

	// Convert numeric has_preqeq to "yes".
	$prereq = $wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'yes'
	 	 WHERE p.post_type = 'course' AND m.meta_key = '_llms_has_prerequisite' AND m.meta_value = 1;"
	); // db call ok; no-cache ok.

	// Convert empty has_prereq to "no".
	$prereq = $wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'no'
	 	 WHERE p.post_type = 'course' AND m.meta_key = '_llms_has_prerequisite' AND m.meta_value = '';"
	); // db call ok; no-cache ok.

}

/**
 * Update keys of email meta fields for consistency
 *
 * @since 3.0.0
 *
 * @return void
 */
function llms_update_300_migrate_email_postmeta() {

	llms_update_util_rekey_meta( 'llms_email', '_llms_email_subject', '_email_subject' );
	llms_update_util_rekey_meta( 'llms_email', '_llms_email_heading', '_email_heading' );

}

/**
 * Update keys of lesson meta fields for consistency
 *
 * @since 3.0.0
 *
 * @return void
 */
function llms_update_300_migrate_lesson_postmeta() {

	global $wpdb;

	llms_update_util_rekey_meta( 'lesson', '_llms_audio_embed', '_audio_embed' );
	llms_update_util_rekey_meta( 'lesson', '_llms_video_embed', '_video_embed' );
	llms_update_util_rekey_meta( 'lesson', '_llms_has_prerequisite', '_has_prerequisite' );
	llms_update_util_rekey_meta( 'lesson', '_llms_prerequisite', '_prerequisite' );
	llms_update_util_rekey_meta( 'lesson', '_llms_days_before_available', '_days_before_avalailable' );

	// Convert numeric has_preqeq to "yes".
	// Convert numeric free_lesson to "yes".
	// Convert numeric require_passing_grade to "yes".
	$wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'yes'
	 	 WHERE p.post_type = 'lesson' AND (
	 	 	   ( m.meta_key = '_llms_has_prerequisite' AND m.meta_value = 1 )
	 	 	OR ( m.meta_key = '_llms_free_lesson' AND m.meta_value = 1 )
	 	 	OR ( m.meta_key = '_llms_require_passing_grade' AND m.meta_value = 1 )
	 	 );"
	); // db call ok; no-cache ok.

	// Convert empty has_prereq to "no".
	// Convert empty free_lesson to "no".
	// Convert empty require_passing_grade to "no".
	$wpdb->query(
		"UPDATE {$wpdb->prefix}postmeta AS m
		 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
		 SET m.meta_value = 'no'
	 	 WHERE p.post_type = 'lesson' AND (
	 	 	   ( m.meta_key = '_llms_has_prerequisite' AND m.meta_value = '' )
	 	 	OR ( m.meta_key = '_llms_free_lesson' AND m.meta_value = '' )
	 	 	OR ( m.meta_key = '_llms_require_passing_grade' AND m.meta_value = '' )
	 	 );"
	); // db call ok; no-cache ok.

	// Updates course enrollment settings and reformats existing dates.
	$drips = $wpdb->get_results(
		"SELECT m.post_id
		 FROM {$wpdb->postmeta} AS m
		 INNER JOIN {$wpdb->posts} AS p ON p.ID = m.post_ID
	 	 WHERE p.post_type = 'lesson' AND m.meta_key = '_llms_days_before_available';"
	); // db call ok; no-cache ok.
	foreach ( $drips as $r ) {
		add_post_meta( $r->post_id, '_llms_drip_method', 'enrollment' );
	}

}

/**
 * Change the post type of orders and rekey meta fields
 *
 * @since 3.0.0
 *
 * @return void
 */
function llms_update_300_migrate_order_data() {

	global $wpdb;

	// Prefix the old unprefixed order post type.
	$wpdb->query(
		"UPDATE {$wpdb->posts}
		 SET post_type = 'llms_order'
		 WHERE post_type = 'order';"
	);

	// Rekey postmetas.
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
 *
 * @since 3.0.0
 *
 * @return void
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

			// Add an order key.
			$order->set( 'order_key', $order->generate_order_key() );

			$order->set( 'access_expiration', 'lifetime' );

			// Add coupon used info.
			$coupon_used = $order->get( 'coupon_id' ) ? 'yes' : 'no';
			$order->set( 'coupon_used', $coupon_used );

			// Add data about the user to the order if we can find it.
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

			// Setup trial info if there was a first payment recorded.
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

			// Remove deprecated.
			delete_post_meta( $post->ID, '_llms_order_recurring_price' );
			delete_post_meta( $post->ID, '_llms_order_total' );
			delete_post_meta( $post->ID, '_llms_order_coupon_limit' );
			delete_post_meta( $post->ID, '_llms_order_product_price' );
			delete_post_meta( $post->ID, '_llms_order_billing_start_date' );
			delete_post_meta( $post->ID, '_llms_order_coupon_value' );
			delete_post_meta( $post->ID, '_llms_order_original_total' );

		}
	}
}

/**
 * Update db version at conclusion of 3.0.0 updates
 *
 * @since 3.0.0
 *
 * @return void
 */
function llms_update_300_update_db_version() {

	LLMS_Install::update_db_version( '3.0.0' );

}
