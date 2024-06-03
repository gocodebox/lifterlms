<?php
/**
 * LifterLMS Site Information.
 *
 * Handle Site switching to prevent recurring payment duplicates
 * when using stating sites
 *
 * Heavily inspired by WC Subscriptions. Thank you!
 *
 * @package LifterLMS/Classes
 *
 * @since 3.0.0
 * @version 5.9.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Site class.
 *
 * @since 3.0.0
 */
class LLMS_Site {

	/**
	 * String part used to encrypt and decrypt the lock url.
	 *
	 * @var string
	 */
	public static $lock_string = '_[llms_site_url]_';

	/**
	 * Clears the value of the lock URL
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function clear_lock_url() {
		update_option( 'llms_site_url', '' );
	}

	/**
	 * Check if the site is cloned and not ignored
	 *
	 * @since 4.12.0
	 * @since 4.13.0 Reverse the order of checks in the `if` statements for a minor performance improvement
	 *               when the `LLMS_SITE_IS_CLONE` constant is being used.
	 *
	 * @return boolean Returns `true` when a clone is detected, otherwise `false`.
	 */
	public static function check_status() {

		if ( self::is_clone() && ! self::is_clone_ignored() ) {

			/**
			 * Action triggered when the current website is determined to be a "cloned" site
			 *
			 * @since 3.7.4
			 * @since 4.12.0 Moved from LLMS_Admin_Notices_Core::check_staging().
			 */
			do_action( 'llms_site_clone_detected' );

			return true;

		}

		return false;

	}

	/**
	 * Get the lock url for the current site.
	 *
	 * Gets the WP site url and inserts the lock string into the (approximate) middle of the url.
	 *
	 * @since 3.0.0
	 * @since 5.9.0 Pass an explicit integer to `substr_replace()`.
	 *
	 * @return string
	 */
	public static function get_lock_url() {
		$site_url = get_site_url();
		return substr_replace( $site_url, self::$lock_string, intval( strlen( $site_url ) / 2 ), 0 );
	}

	/**
	 * Stores the current site's lock url into the database
	 *
	 * @since 3.0.0
	 *
	 * @return void
	 */
	public static function set_lock_url() {
		update_option( 'llms_site_url', self::get_lock_url() );
	}

	/**
	 * Gets the stored url and cleans it for comparisons
	 *
	 * @since 3.0.0
	 *
	 * @return string
	 */
	public static function get_url() {

		$url = get_option( 'llms_site_url' );

		// Remove the lock string before returning it.
		$url = str_replace( self::$lock_string, '', $url );

		$url = set_url_scheme( $url );

		/**
		 * Filters the stored LLMS_Site URL
		 *
		 * @since 3.0.0
		 *
		 * @param string $url The cleaned LLMS_Site URL.
		 */
		return apply_filters( 'llms_site_get_url', $url );

	}

	/**
	 * Get a single feature's status
	 *
	 * Checks for a feature constant first and, if none is defined,
	 * uses the stored site setting (with a fallback to the default value), and
	 * a final fallback to `false` if the feature cannot be found.
	 *
	 * @since 3.0.0
	 * @since 4.12.0 Allow feature configuration via constants.
	 *
	 * @param string $feature Feature id/key.
	 * @return bool
	 */
	public static function get_feature( $feature ) {

		$status = self::get_feature_constant( $feature );
		if ( is_null( $status ) ) {

			$features = self::get_features();
			$status   = isset( $features[ $feature ] ) ? $features[ $feature ] : false;

		}

		/**
		 * Filters the status of a LLMS_Site feature.
		 *
		 * @since 4.12.0
		 *
		 * @param boolean $status  Status of the feature.
		 * @param string  $feature The feature ID/key.
		 */
		return apply_filters( 'llms_site_get_feature', $status, $feature );

	}

	/**
	 * Retrieve a constant value for a site feature
	 *
	 * This allows site features to be explicitly enabled or disabled
	 * in a wp-config.php file.
	 *
	 * @since 4.12.0
	 *
	 * @param string $feature Feature id/key.
	 * @return bool
	 */
	protected static function get_feature_constant( $feature ) {

		$constant = sprintf( 'LLMS_SITE_FEATURE_%s', strtoupper( $feature ) );
		if ( defined( $constant ) ) {
			return constant( $constant );
		}

		return null;

	}

	/**
	 * Get a list of automated features
	 *
	 * These features are features that should be disabled
	 * in testing or staging environments.
	 *
	 * @since 3.0.0
	 *
	 * @return array An associative array of site features.
	 */
	public static function get_features() {

		/**
		 * Filters the default values for LLMS_Site features
		 *
		 * @since 3.0.0
		 *
		 * @param array $defaults An associative array of site features.
		 */
		$defaults = apply_filters(
			'llms_site_default_features',
			array(
				'recurring_payments' => true,
			)
		);

		return get_option( 'llms_site_get_features', $defaults );

	}

	/**
	 * Update the status of a specific feature and save it to the db
	 *
	 * @since 3.0.0
	 *
	 * @param string $feature Name / key of the feature.
	 * @param bool   $val     Status of the feature [true = enabled; false = disabled].
	 * @return void
	 */
	public static function update_feature( $feature, $val ) {

		$features             = self::get_features();
		$features[ $feature ] = $val;
		update_option( 'llms_site_get_features', $features );

	}

	/**
	 * Determine if this is a cloned site
	 *
	 * Compares the stored (and cleaned) llms_site_url against the WP site url.
	 *
	 * @since 3.0.0
	 * @since 4.13.0 Add `LLMS_SITE_IS_CLONE` constant check.
	 *
	 * @return boolean Returns `true` if it's a cloned site (urls do not match)
	 *                 and `false` if it's not (urls DO match).
	 */
	public static function is_clone() {

		$is_clone = defined( 'LLMS_SITE_IS_CLONE' ) ? LLMS_SITE_IS_CLONE : ( get_site_url() !== self::get_url() );

		/**
		 * Filters whether or not the site is a "cloned" site
		 *
		 * @since 3.0.0
		 *
		 * @param boolean $is_clone When `true` the site is considered a "clone", otherwise it is not.
		 */
		return apply_filters( 'llms_site_is_clone', $is_clone );

	}

	/**
	 * Determines whether or not the clone warning notice has been ignored
	 *
	 * This prevents the warning from redisplaying when the site is a clone
	 * and automatic payments remain disabled.
	 *
	 * @since 3.0.0
	 * @since 4.12.0 Use `llms_parse_bool()` to determine check the option value.
	 *
	 * @return boolean
	 */
	public static function is_clone_ignored() {

		/**
		 * Filters whether or not the "clone" site has already been ignored.
		 *
		 * @since 3.0.0
		 *
		 * @param boolean $is_clone_ignored If `true`, the clone is ignored, otherwise it is not.
		 */
		return apply_filters( 'llms_site_is_clone_ignored', llms_parse_bool( get_option( 'llms_site_url_ignore', 'no' ) ) );

	}

}
