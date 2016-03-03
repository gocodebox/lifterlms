<?php

/**
* Session handler class
*
* Handles session and cookie data
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Session_Handler extends LLMS_Session {

	/**
	* cookie name
	* @access private
	* @var string
	*/
	private $_cookie;

	/**
	* session expiration
	* @access private
	* @var datetime
	*/
	private $_session_expiring;

	/**
	* cookie name
	* @access private
	* @var datetime
	*/
	private $_session_expiration;

	/**
	* Cookie exist?
	* @access private
	* @var bool
	*/
	private $_has_cookie = false;

	/**
	* Constructor
	*
	* initializes sessions and cookies
	*/
	public function __construct() {

		$this->_cookie = 'wp_lifterlms_session_' . 'COOKIEHASH';

		if ( $cookie = $this->get_session_cookie() ) {

			$this->_person_id        = $cookie[0];
			$this->_session_expiration = $cookie[1];
			$this->_session_expiring   = $cookie[2];
			$this->_has_cookie         = true;

			// Update session if its close to expiring
			if ( time() > $this->_session_expiring ) {

				$this->set_session_expiration();
				$session_expiry_option = '_llms_session_expires_' . $this->_person_id;

				// Check if option exists first to avoid auloading cleaned up sessions
				if ( false === get_option( $session_expiry_option ) ) {

					add_option( $session_expiry_option, $this->_session_expiration, '', 'no' );

				} else {

					update_option( $session_expiry_option, $this->_session_expiration );

				}

			}

		} else {

			$this->set_session_expiration();
			$this->_person_id = $this->generate_person_id();

		}

		$this->_data = $this->get_session_data();

		add_action( 'lifterlms_cleanup_sessions', array( $this, 'cleanup_sessions' ), 10 );
		add_action( 'shutdown', array( $this, 'save_data' ), 20 );
	}


	/**
	 * Sets session cookie
	 *
	 * @return void
	 */
	public function set_person_session_cookie( $set ) {

		if ( $set ) {
	    	// Set/renew our cookie
			$to_hash           = $this->_person_id . $this->_session_expiration;
			$cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
			$cookie_value      = $this->_person_id . '||' . $this->_session_expiration . '||' . $this->_session_expiring . '||' . $cookie_hash;
			$this->_has_cookie = true;

	    	// Set the cookie
	    	llms_setcookie( $this->_cookie, $cookie_value, $this->_session_expiration, apply_filters( 'llms_session_use_secure_cookie', false ) );

	    }

	}

	/**
	 * Returns true if user has active session
	 *
	 * @return void
	 */
	public function has_session() {
		return isset( $_COOKIE[ $this->_cookie ] ) || $this->_has_cookie || is_user_logged_in();
	}

	/**
	 * Set session expiration
	 *
	 * @return void
	 */
	public function set_session_expiration() {
	    $this->_session_expiring    = time() + intval( apply_filters( 'llms_session_expiring', 60 * 60 * 47 ) ); // 47 Hours
		$this->_session_expiration  = time() + intval( apply_filters( 'llms_session_expiration', 60 * 60 * 48 ) ); // 48 Hours
	}

	/**
	 * If user is not logged in create a user id and store it in the cookie
	 *
	 * TODO: Having issues checking if user is logged in. This method is currently broken.
	 *
	 * @return string
	 */
	public function generate_person_id() {

			return get_current_user_id();
	}

	/**
	 * Get the cookie
	 *
	 * @return string
	 */
	public function get_session_cookie() {

		if ( empty( $_COOKIE[ $this->_cookie ] ) ) {

			return false;

		}

		list( $person_id, $session_expiration, $session_expiring, $cookie_hash ) = explode( '||', $_COOKIE[ $this->_cookie ] );

		// Validate hash
		$to_hash = $person_id . $session_expiration;
		$hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

		if ( $hash != $cookie_hash ) {

			return false;

		}

		return array( $person_id, $session_expiration, $session_expiring, $cookie_hash );

	}

	/**
	 * Get the session data
	 *
	 * @return array
	 */
	public function get_session_data() {

		return (array) get_option( '_llms_session_' . $this->_person_id, array() );

	}

	/**
	 * Save the session data
	 *
	 * @return void
	 */
	public function save_data() {

		if ( $this->_dirty && $this->has_session() ) {

			$session_option = '_llms_session_' . $this->_person_id;
			$session_expiry_option = '_llms_session_expires_' . $this->_person_id;

	    	if ( false === get_option( $session_option ) ) {

	    		add_option( $session_option, $this->_data, '', 'no' );
		    	add_option( $session_expiry_option, $this->_session_expiration, '', 'no' );

	    	} else {

		    	update_option( $session_option, $this->_data );

	    	}
	    }

	}

	/**
	 * Clean up the expired session data
	 *
	 * @return void
	 */
	public function cleanup_sessions() {
		global $wpdb;

		if ( ! defined( 'WP_SETUP_CONFIG' ) && ! defined( 'WP_INSTALLING' ) ) {

			$now = time();
			$expired_sessions   = array();
			$llms_session_expires = $wpdb->get_results( "SELECT option_name, option_value FROM $wpdb->options WHERE option_name LIKE '_llms_session_expires_%'" );

			foreach ( $llms_session_expires as $llms_session_expire ) {

				if ( $now > intval( $llms_session_expire->option_value ) ) {

					$session_id         = substr( $llms_session_expire->option_name, 20 );
					$expired_sessions[] = $llms_session_expire->option_name;  // Expires key
					$expired_sessions[] = "_llms_session_$session_id"; // Session key

				}

			}

			if ( ! empty( $expired_sessions ) ) {

				$expired_sessions_chunked = array_chunk( $expired_sessions, 100 );

				foreach ( $expired_sessions_chunked as $chunk ) {

					$option_names = implode( "','", $chunk );
					$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name IN ('$option_names')" );

				}

			}

		}

	}

	public function delete_all_session_data() {

		global $wpdb;

		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '_llms_session%'" );
	}

}
