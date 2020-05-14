<?php
/**
 * Person base class.
 *
 * @package LifterLMS/Classes
 *
 * @since Unknown
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Person base class.
 *
 * @since Unknown
 * @since [version] Removed unused private class var `$_changed`.
 *               Deprecated `$_data` protected class var.
 *               Deprecated `save_data()` method.
 */
class LLMS_Person {

	/**
	 * This var is no longer used but is preserved for backwards compatibility.
	 *
	 * @deprecated [version]
	 *
	 * @var [type]
	 */
	protected $_data;

	/**
	 * Constructor
	 *
	 * @since Unknown
	 * @since [version] Removed session initialization and related hooks.
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'wp_login', array( $this, 'set_user_login_timestamp' ), 10, 2 );

	}

	/**
	 * Set user login timestamp on login
	 *
	 * Update login timestamp on user login
	 *
	 * @param string  $user_login WP_User ID.
	 * @param WP_User $user       User object.
	 */
	public function set_user_login_timestamp( $user_login, $user ) {
		$now = current_time( 'timestamp' );
		update_user_meta( $user->ID, 'llms_last_login', $now );
	}

	/**
	 * Get user postmeta achievements
	 *
	 * @since Unknown
	 *
	 * @param int $user_id WP_User ID.
	 * @return array Associative array of users achievement data.
	 */
	public function get_user_achievements( $count = 1000, $user_id = 0 ) {
		global $wpdb;

		$user_id = ( ! $user_id ) ? get_current_user_id() : $user_id;

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %s and meta_key = %s ORDER BY updated_date DESC LIMIT %d", $user_id, '_achievement_earned', $count ) );

		$achievements = array();

		foreach ( $results as $key => $val ) {

			$achievement = array();

			$meta = get_post_meta( $val->meta_value );
			$post = get_post( $val->meta_value );

			$achievement['title']   = $meta['_llms_achievement_title'][0];
			$achievement['content'] = $post->post_content;

			$image_id = $meta['_llms_achievement_image'][0];

			$achievement['image'] = wp_get_attachment_image_src( $image_id, 'achievement' );

			if ( ! $achievement['image'] ) {
				$achievement['image'] = apply_filters( 'lifterlms_placeholder_img_src', LLMS()->plugin_url() . '/assets/images/optional_achievement.png' );
			} else {
				$achievement['image'] = $achievement['image'][0];
			}

			$achievement['date'] = date( get_option( 'date_format' ), strtotime( $val->updated_date ) );

			$achievements[] = $achievement;

		}

		return apply_filters( 'lifterlms_user_achievements', $achievements );

	}


	/**
	 * Get data about a specific users memberships
	 *
	 * @since Unknown
	 *
	 * @param int $user_id WP_User ID.
	 * @return array Array of objects containing details about users memberships.
	 */
	public function get_user_memberships_data( $user_id ) {

		$memberships = get_user_meta( $user_id, '_llms_restricted_levels', true );

		$r = array();

		if ( $memberships ) {

			foreach ( $memberships as $membership_id ) {

				$info = $this->get_user_postmeta_data( $user_id, $membership_id );

				if ( $info ) {

					$r[ $membership_id ] = $info;

				}
			}
		}

		return $r;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @since Unknown
	 *
	 * @param int $user_id WP_User ID.
	 * @param int $post_id WP_Post ID.
	 * @return array
	 */
	public function get_user_postmeta_data( $user_id, $post_id ) {
		global $wpdb;

		if ( empty( $user_id ) || empty( $post_id ) ) {
			return;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %s and post_id = %d",
				$user_id,
				$post_id
			)
		);

		if ( empty( $results ) ) {
			return;
		}

		$num_results = count( $results );
		for ( $i = 0; $i < $num_results; $i++ ) {
			$results[ $results[ $i ]->meta_key ] = $results[ $i ];
			unset( $results[ $i ] );
		}

		return $results;
	}

	/**
	 * Return array of objects containing user meta data for a single post.
	 *
	 * @since Unknown
	 *
	 * @param int $user_id  WP_User ID.
	 * @param int $meta_key Key name.
	 * @return array
	 */
	public function get_user_postmetas_by_key( $user_id, $meta_key ) {
		global $wpdb;

		if ( empty( $user_id ) || empty( $meta_key ) ) {
			return;
		}

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$wpdb->prefix}lifterlms_user_postmeta WHERE user_id = %s and meta_key = %s ORDER BY updated_date DESC",
				$user_id,
				$meta_key
			)
		);

		if ( empty( $results ) ) {
			return;
		}

		$num_results = count( $results );
		for ( $i = 0; $i < $num_results; $i++ ) {
			$results[ $results[ $i ]->post_id ] = $results[ $i ];
			unset( $results[ $i ] );
		}

		return $results;
	}


	/**
	 * Deprecated
	 *
	 * @since Unknown
	 * @deprecated [version]
	 *
	 * @return void
	 */
	public function save_data() {
		llms_deprecated_function( 'LLMS_Persion::save_data()', '[version]' );
	}

}
