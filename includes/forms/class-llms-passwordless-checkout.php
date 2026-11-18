<?php
/**
 * LLMS_Passwordless_Checkout class
 *
 * @package LifterLMS/Classes/Forms
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * Passwordless checkout.
 *
 * When the "Passwordless checkout" option is enabled, logged-out visitors check out without a
 * password field. A random password is generated for the new account (by `LLMS_Form_Handler`),
 * the student is logged in right away, and they're emailed a link to set their own password.
 *
 * Until the student sets a password, the edit account form doesn't ask for a "current password"
 * they never saw; it offers to email the set password link again instead.
 *
 * @since [version]
 */
class LLMS_Passwordless_Checkout {

	/**
	 * User meta key flagging a user who hasn't chosen their own password yet.
	 *
	 * @var string
	 */
	const META_KEY = 'llms_password_not_set';

	/**
	 * Option name of the setting that enables passwordless checkout.
	 *
	 * @var string
	 */
	const OPTION = 'lifterlms_checkout_passwordless';

	/**
	 * Form field IDs removed from the form when no password is needed.
	 *
	 * @var string[]
	 */
	const PASSWORD_FIELD_IDS = array(
		'password',
		'password_confirm',
		'password_current',
		'llms-password-strength-meter',
		'llms-field-toggle--password',
	);

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_filter( 'llms_forms_required_block_fields', array( $this, 'filter_required_block_fields' ), 10, 2 );

		// Before the password strength meter is added (priority 10) so there's no meter without a password.
		add_filter( 'llms_get_form_blocks', array( $this, 'remove_checkout_password_blocks' ), 5, 2 );

		// After `LLMS_Forms_Dynamic_Fields::modify_account_form()` (priority 15) adds the password toggle and current password field.
		add_filter( 'llms_get_form_blocks', array( $this, 'modify_account_form' ), 20, 2 );

		add_filter( 'llms_user_registration_signon_without_password', array( $this, 'signon_without_password' ), 10, 2 );
		add_action( 'lifterlms_user_registered', array( $this, 'on_user_registered' ), 10, 3 );

		add_action( 'after_password_reset', array( $this, 'on_password_reset' ) );
		add_action( 'profile_update', array( $this, 'on_profile_update' ), 10, 2 );

		add_action( 'init', array( $this, 'maybe_resend_set_password_email' ) );
		add_filter( 'llms_display_student_dashboard', array( $this, 'maybe_display_password_form' ) );
	}

	/**
	 * Determines whether passwordless checkout is enabled.
	 *
	 * @since [version]
	 *
	 * @return bool
	 */
	public static function is_enabled() {

		/**
		 * Filters whether passwordless checkout is enabled.
		 *
		 * @since [version]
		 *
		 * @param bool $enabled Whether passwordless checkout is enabled.
		 */
		return (bool) apply_filters( 'llms_passwordless_checkout_enabled', llms_parse_bool( get_option( self::OPTION, 'no' ) ) );
	}

	/**
	 * Determines whether the password field should be removed from a form location for the current visitor.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location ID.
	 * @return bool
	 */
	private function is_passwordless_checkout( $location ) {
		return 'checkout' === $location && ! is_user_logged_in() && self::is_enabled();
	}

	/**
	 * Determines whether a user still has the random password assigned during passwordless checkout.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID.
	 * @return bool
	 */
	public static function is_password_not_set( $user_id ) {
		return $user_id && llms_parse_bool( get_user_meta( $user_id, self::META_KEY, true ) );
	}

	/**
	 * Stops the password field from being forced onto the checkout form.
	 *
	 * @since [version]
	 *
	 * @param array[] $fields   Array of field_id => block_name required.
	 * @param string  $location The request form location ID.
	 * @return array[]
	 */
	public function filter_required_block_fields( $fields, $location ) {

		if ( $this->is_passwordless_checkout( $location ) ) {
			unset( $fields['password'] );
		}

		return $fields;
	}

	/**
	 * Removes the password, password confirmation, and strength meter blocks from the checkout form.
	 *
	 * Without a password field in the form `LLMS_Form_Handler::prepare_data_for_insert()` generates
	 * a random password for the new user.
	 *
	 * @since [version]
	 *
	 * @param array[] $blocks   Array of parsed WP_Block arrays.
	 * @param string  $location The form location ID.
	 * @return array[]
	 */
	public function remove_checkout_password_blocks( $blocks, $location ) {

		if ( ! $this->is_passwordless_checkout( $location ) ) {
			return $blocks;
		}

		$this->remove_password_blocks( $blocks );

		return $blocks;
	}

	/**
	 * Replaces the password fields on the edit account form for users who never set a password.
	 *
	 * They can't provide the "current password" required to change it, so the fields are replaced
	 * with a message and a link that emails them the set password link again.
	 *
	 * @since [version]
	 *
	 * @param array[] $blocks   Array of parsed WP_Block arrays.
	 * @param string  $location The form location ID.
	 * @return array[]
	 */
	public function modify_account_form( $blocks, $location ) {

		if ( 'account' !== $location || ! self::is_password_not_set( get_current_user_id() ) ) {
			return $blocks;
		}

		$index = $this->remove_password_blocks( $blocks );
		if ( false === $index ) {
			return $blocks;
		}

		$url = wp_nonce_url( add_query_arg( 'llms_send_set_password', 1 ), 'llms_send_set_password' );

		$notice = parse_blocks(
			LLMS_Forms::instance()->get_custom_field_block_markup(
				array(
					'type'  => 'html',
					'id'    => 'llms-password-not-set',
					'value' => sprintf(
						'<p>%1$s <a href="%2$s">%3$s</a></p>',
						esc_html__( "You haven't set a password yet. We emailed you a link to set one.", 'lifterlms' ),
						esc_url( $url ),
						esc_html__( 'Send me the link again', 'lifterlms' )
					),
				)
			)
		);

		array_splice( $blocks, $index, 0, $notice );

		return $blocks;
	}

	/**
	 * Removes all password related blocks from a list of blocks.
	 *
	 * A confirm group containing the password field is removed as a whole. Blocks nested
	 * in other blocks (like columns) are removed together with their `innerContent` reference.
	 *
	 * @since [version]
	 *
	 * @param array[] $blocks Array of parsed WP_Block arrays (passed by reference).
	 * @return int|false Top-level index of the first removed block, or `false` if nothing was removed.
	 */
	private function remove_password_blocks( &$blocks ) {

		$first = false;

		foreach ( $blocks as $index => &$block ) {

			if ( $this->is_password_block( $block ) ) {
				unset( $blocks[ $index ] );
				$first = false === $first ? $index : $first;
				continue;
			}

			if ( ! empty( $block['innerBlocks'] ) && false !== $this->remove_password_blocks( $block['innerBlocks'] ) ) {
				$first = false === $first ? $index : $first;
				$this->sync_inner_content( $block );
			}
		}
		unset( $block );

		$blocks = array_values( $blocks );

		// Blocks before `$first` were kept, so it's still the right top-level position after re-indexing.
		return $first;
	}

	/**
	 * Determines whether a block is a password field or a confirm group containing one.
	 *
	 * @since [version]
	 *
	 * @param array $block Parsed WP_Block array.
	 * @return bool
	 */
	private function is_password_block( $block ) {

		if ( ! empty( $block['attrs']['id'] ) && in_array( $block['attrs']['id'], self::PASSWORD_FIELD_IDS, true ) ) {
			return true;
		}

		if ( 'llms/form-field-confirm-group' === $block['blockName'] && ! empty( $block['innerBlocks'] ) ) {
			foreach ( $block['innerBlocks'] as $inner ) {
				if ( $this->is_password_block( $inner ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Drops `innerContent` placeholders left over after inner blocks were removed.
	 *
	 * `innerContent` holds one `null` per inner block, in order. Extra placeholders would make
	 * WordPress try to render inner blocks that no longer exist.
	 *
	 * @since [version]
	 *
	 * @param array $block Parsed WP_Block array (passed by reference).
	 * @return void
	 */
	private function sync_inner_content( &$block ) {

		$extra = count( array_filter( $block['innerContent'], 'is_null' ) ) - count( $block['innerBlocks'] );

		for ( $i = count( $block['innerContent'] ) - 1; $i >= 0 && $extra > 0; $i-- ) {
			if ( is_null( $block['innerContent'][ $i ] ) ) {
				array_splice( $block['innerContent'], $i, 1 );
				--$extra;
			}
		}
	}

	/**
	 * Allows signing on a user registered during passwordless checkout.
	 *
	 * @since [version]
	 *
	 * @param bool   $signon Whether to sign on a newly registered user who didn't submit a password.
	 * @param string $screen Form location, either "registration" or "checkout".
	 * @return bool
	 */
	public function signon_without_password( $signon, $screen ) {
		return $signon || ( 'checkout' === $screen && self::is_enabled() );
	}

	/**
	 * Flags a user registered during passwordless checkout and emails them a set password link.
	 *
	 * @since [version]
	 *
	 * @param int    $user_id     WP_User ID of the user.
	 * @param array  $posted_data Array of user submitted data.
	 * @param string $location    Form location.
	 * @return void
	 */
	public function on_user_registered( $user_id, $posted_data, $location ) {

		if ( 'checkout' !== $location || ! empty( $posted_data['password'] ) || ! self::is_enabled() ) {
			return;
		}

		update_user_meta( $user_id, self::META_KEY, 'yes' );

		if ( $this->send_set_password_email( $user_id ) ) {
			llms_add_notice( __( 'Your account has been created. We emailed you a link to set your password.', 'lifterlms' ) );
		}
	}

	/**
	 * Clears the "password not set" flag after a password reset.
	 *
	 * @since [version]
	 *
	 * @param WP_User $user The user.
	 * @return void
	 */
	public function on_password_reset( $user ) {
		delete_user_meta( $user->ID, self::META_KEY );
	}

	/**
	 * Clears the "password not set" flag when a user's password changes, for example from the WP admin profile screen.
	 *
	 * @since [version]
	 *
	 * @param int     $user_id       WP_User ID.
	 * @param WP_User $old_user_data User object before the update.
	 * @return void
	 */
	public function on_profile_update( $user_id, $old_user_data ) {

		$user = get_userdata( $user_id );
		if ( $user && $old_user_data instanceof WP_User && $user->user_pass !== $old_user_data->user_pass ) {
			delete_user_meta( $user_id, self::META_KEY );
		}
	}

	/**
	 * Emails the set password link again when requested from the edit account form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function maybe_resend_set_password_email() {

		if ( ! llms_filter_input( INPUT_GET, 'llms_send_set_password', FILTER_SANITIZE_NUMBER_INT ) ) {
			return;
		}

		$nonce   = llms_filter_input_sanitize_string( INPUT_GET, '_wpnonce' );
		$user_id = get_current_user_id();
		if ( ! wp_verify_nonce( $nonce, 'llms_send_set_password' ) || ! self::is_password_not_set( $user_id ) ) {
			return;
		}

		if ( $this->send_set_password_email( $user_id ) ) {
			llms_add_notice( __( 'Check your email for the link to set your password.', 'lifterlms' ) );
		} else {
			llms_add_notice( __( 'We were unable to send the email. Please try again.', 'lifterlms' ), 'error' );
		}

		llms_redirect_and_exit( remove_query_arg( array( 'llms_send_set_password', '_wpnonce' ) ) );
	}

	/**
	 * Shows the password form instead of the dashboard when a logged-in user who never set a password follows the emailed link.
	 *
	 * The lost password endpoint is normally only shown to logged-out visitors, but students are still logged
	 * in after passwordless checkout when they open the email.
	 *
	 * @since [version]
	 *
	 * @param bool $display Whether to display the student dashboard.
	 * @return bool
	 */
	public function maybe_display_password_form( $display ) {

		global $wp;

		if ( $display && isset( $wp->query_vars['lost-password'] ) && self::is_password_not_set( get_current_user_id() ) ) {
			return false;
		}

		return $display;
	}

	/**
	 * Sends a user an email with a link to set their password.
	 *
	 * @since [version]
	 *
	 * @param int $user_id WP_User ID.
	 * @return bool
	 */
	private function send_set_password_email( $user_id ) {

		$user = get_user_by( 'id', $user_id );
		$key  = $user ? get_password_reset_key( $user ) : false;
		if ( ! $key || is_wp_error( $key ) ) {
			return false;
		}

		$email = llms()->mailer()->get_email(
			'set_password',
			array(
				'key'           => $key,
				'user'          => $user,
				'login_display' => LLMS_Forms::instance()->are_usernames_enabled() ? $user->user_login : $user->user_email,
			)
		);

		return $email && $email->send();
	}
}

return new LLMS_Passwordless_Checkout();
