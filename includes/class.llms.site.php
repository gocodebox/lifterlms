<?php
/**
 * Handle Site switching to prevent recurring payment duplicates
 * when using stating sites
 *
 * Heavily inspired by WC Subscriptions, thanks <3
 *
 * @since 3.0.0
 * @version 3.7.4
 */
class LLMS_Site {

	public static $lock_string = '_[llms_site_url]_';

	/**
	 * Clears the value of the lock URL
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function clear_lock_url() {
		update_option( 'llms_site_url', '' );
	}

	/**
	 * Get the lock url for the current site
	 * gets the WP site url and adds the lock string to it
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_lock_url() {

		$site_url = get_site_url();
		return substr_replace( $site_url, self::$lock_string, strlen( $site_url ) / 2, 0 );

	}

	/**
	 * Stores the current site's lock url into the database
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function set_lock_url() {

		update_option( 'llms_site_url', self::get_lock_url() );

	}

	/**
	 * Gets the stored url and cleans it for comparisons
	 * @return   string
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_url() {

		$url = get_option( 'llms_site_url' );

		// remove the lock string before returning it
		$url = str_replace( self::$lock_string, '', $url );

		$url = set_url_scheme( $url );

		return apply_filters( 'llms_site_get_url', $url );

	}

	/**
	 * Get a single feature's status
	 * @param    string     $feature  feature id/key
	 * @return   bool
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_feature( $feature ) {
		$features = self::get_features();
		if ( isset( $features[ $feature ] ) ) {
			return $features[ $feature ];
		}
		return false;
	}

	/**
	 * Get a list of automated features that it migth be useful
	 * to disable on testing or staging environments
	 * @todo  need to add engagement email support here
	 * @return   array
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function get_features() {

		$defaults = apply_filters( 'llms_site_default_features', array(
			// 'emails' => true, // @todo
			'recurring_payments' => true,
		) );

		return get_option( 'llms_site_get_features', $defaults );

	}

	/**
	 * Update the status of a specific feature and save it to the db
	 * @param    string     $feature  name / key of the feature
	 * @param    bool       $val      status of the feature [true = enabled; false = disabled]
	 * @return   void
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function update_feature( $feature, $val ) {

		$features = self::get_features();
		$features[ $feature ] = $val;
		update_option( 'llms_site_get_features', $features );

	}

	/**
	 * Determine if this is a cloned site
	 * Compares the stored (and cleaned) llms_site_url against the WP site url
	 *
	 * @return   boolean        true if it's a cloned site (urls DO NOT match)
	 *                          false if it's not (urls DO match)
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function is_clone() {

		return apply_filters( 'llms_site_is_clone', ( get_site_url() !== self::get_url() ) );

	}

	/**
	 * Determines whether or not the clone warning notice has been ignored
	 * this prevents the warning from redisplaying when the site is a clone
	 * and automatic payments remain disabled
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public static function is_clone_ignored() {

		$ignore = apply_filters( 'llms_site_is_clone_ignored', get_option( 'llms_site_url_ignore', 'no' ) );
		return ( 'yes' === $ignore );

	}

}
