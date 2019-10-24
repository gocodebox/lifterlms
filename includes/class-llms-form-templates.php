<?php
/**
 * Manage block editor templates for LifterLMS Forms.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Form_Templates class..
 *
 * @since [version]
 */
class LLMS_Form_Templates {

	/**
	 * Singleton instance
	 *
	 * @var  null
	 */
	protected static $instance = null;

	/**
	 * Get Main Singleton Instance.
	 *
	 * @since [version]
	 *
	 * @return LLMS_Form_Templates
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Retrieves the block HTML for a form field block.
	 *
	 * @since [version]
	 *
	 * @param string $block_name Field name which will be appended to "wp:llms/form-field-user-".
	 * @param array  $settings Settings to add to the defaults provided by `$this->get_block_settings()`.
	 * @return string
	 */
	protected function get_block( $block_name, $settings = array() ) {
		return sprintf( '<!-- wp:llms/form-field-%1$s %2$s /-->', $block_name, $this->get_block_settings( $settings ) );
	}

	/**
	 * Retrieve JSON settings string for a LifterLMS Form Field Block.
	 *
	 * Merges the supplied settings into an array of defaults.
	 *
	 * @since [version]
	 *
	 * @param array $settings {
	 *     Array of settings to merge into the default settings.
	 *
	 *     @type string $description Field description text.
	 *     @type string $field Field type, eg "text", "email", "select".
	 *     @type string $id HTML "id" attribute.
	 *     @type string $label Field label text.
	 *     @type string $name HTML "name" attribute. Uses `$id` if none is supplied.
	 *     @type string $label Field placeholder text.
	 *     @type bool $required Whether or not the field is required. Defaults to `true`.
	 * }
	 * @return [type]
	 */
	protected function get_block_settings( $settings = array() ) {

		$settings = wp_parse_args(
			$settings,
			array(
				'description' => '',
				'field'       => 'text',
				'id'          => '',
				'label'       => '',
				'name'        => '',
				'placeholder' => '',
				'required'    => true,
			)
		);

		if ( empty( $settings['name'] ) ) {
			$settings['name'] = $settings['id'];
		}

		return wp_json_encode( $settings );

	}

	/**
	 * Retrieve block for the city address row.
	 *
	 * Returns an empty array if address fields are disabled by legacy settings.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_address_city( $location ) {

		$option = get_option( sprintf( 'lifterlms_user_info_field_address_%s_visibility', $location ), 'required' );
		if ( 'hidden' === $option ) {
			return array();
		}

		return array(
			$this->get_block(
				'user-address-city',
				array(
					'id'       => 'llms_billing_city',
					'label'    => __( 'City', 'lifterlms' ),
					'required' => ( 'required' === $option ),
				)
			),
		);

	}

	/**
	 * Retrieve blocks for the country/state/zip row.
	 *
	 * Returns an empty array if address fields are disabled by legacy settings.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_address_l10n( $location ) {

		$cols = array();

		$option = get_option( sprintf( 'lifterlms_user_info_field_address_%s_visibility', $location ), 'required' );
		if ( 'hidden' === $option ) {
			return $cols;
		}

		$cols[] = $this->get_block(
			'user-address-country',
			array(
				'field'          => 'select',
				'id'             => 'llms_billing_country',
				'label'          => __( 'Country / Region', 'lifterlms' ),
				'options'        => array(),
				'options_preset' => 'countries',
				'required'       => ( 'required' === $option ),
			)
		);

		$cols[] = $this->get_block(
			'user-address-state',
			array(
				'field'          => 'select',
				'id'             => 'llms_billing_state',
				'label'          => __( 'State / Province', 'lifterlms' ),
				'options'        => array(),
				'options_preset' => 'states',
				'required'       => ( 'required' === $option ),
			)
		);

		$cols[] = $this->get_block(
			'user-address-zip',
			array(
				'id'       => 'llms_billing_zip',
				'label'    => __( 'Zip / Postal Code', 'lifterlms' ),
				'required' => ( 'required' === $option ),
			)
		);

		return $this->wrap_columns( $cols );

	}

	/**
	 * Retrieve blocks for the street address row.
	 *
	 * Returns an empty array if address fields are disabled by legacy settings.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_address_street( $location ) {

		$cols = array();

		$option = get_option( sprintf( 'lifterlms_user_info_field_address_%s_visibility', $location ), 'required' );
		if ( 'hidden' === $option ) {
			return $cols;
		}

		$cols[] = array(
			'content' => $this->get_block(
				'user-address',
				array(
					'id'       => 'llms_billing_address_1',
					'label'    => __( 'Street Address', 'lifterlms' ),
					'required' => ( 'required' === $option ),
				)
			),
			'width'   => 66.66,
		);

		$cols[] = array(
			'content' => $this->get_block(
				'user-address-additional',
				array(
					'id'               => 'llms_billing_address_2',
					'placeholder'      => __( 'Apartment, suite, or unit', 'lifterlms' ),
					'required'         => false,
					'label_show_empty' => true,
				)
			),
			'width'   => 33.33,
		);

		return $this->wrap_columns( $cols );

	}

	/**
	 * Retrieve block(s) for the email address row.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location. Accepts template options passed to `$this->get_template()`.
	 * @return array
	 */
	protected function get_row_email( $location ) {

		$cols = array();

		$cols[] = $this->get_block(
			'user-email',
			array(
				'field' => 'email',
				'id'    => 'email_address',
				'label' => __( 'Email Address', 'lifterlms' ),
				'match' => 'email_address_confirm',
			)
		);

		$option = get_option( sprintf( 'lifterlms_user_info_field_email_confirmation_%s_visibility', $location ) );
		if ( ! llms_parse_bool( $option ) ) {
			return $cols;
		}

		$cols[] = $this->get_block(
			'user-email-confirm',
			array(
				'field'          => 'email',
				'id'             => 'email_address_confirm',
				'label'          => __( 'Confirm Email Address', 'lifterlms' ),
				'match'          => 'email_address',
				'data_store_key' => false,
			)
		);

		return $this->wrap_columns( $cols, 'logged_out' );

	}

	/**
	 * Retrieve blocks for the name row.
	 *
	 * Returns an empty array if names are disabled by legacy settings.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location. Accepts template options passed to `$this->get_template()`.
	 * @return array
	 */
	protected function get_row_names( $location ) {

		$cols = array();

		$option = get_option( sprintf( 'lifterlms_user_info_field_names_%s_visibility', $location ), 'required' );
		if ( 'hidden' === $option ) {
			return $cols;
		}

		$cols[] = $this->get_block(
			'user-first-name',
			array(
				'id'       => 'first_name',
				'label'    => __( 'First Name', 'lifterlms' ),
				'required' => ( 'required' === $option ),
			)
		);

		$cols[] = $this->get_block(
			'user-last-name',
			array(
				'id'       => 'last_name',
				'label'    => __( 'Last Name', 'lifterlms' ),
				'required' => ( 'required' === $option ),
			)
		);

		return $this->wrap_columns( $cols );

	}

	/**
	 * Retrieve blocks for the password row.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_password() {

		$cols = array();

		$cols[] = $this->get_block(
			'user-password',
			array(
				'field' => 'password',
				'id'    => 'password',
				'label' => __( 'Password', 'lifterlms' ),
				'match' => 'password_confirm',
			)
		);

		$cols[] = $this->get_block(
			'user-password-confirm',
			array(
				'field'          => 'password',
				'id'             => 'password_confirm',
				'label'          => __( 'Confirm Password', 'lifterlms' ),
				'match'          => 'password',
				'data_store_key' => false,
			)
		);

		return $this->wrap_columns( $cols, 'logged_out' );

	}

	/**
	 * Retrieve the password strength meter field row.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_password_meter() {

		$option = llms_parse_bool( get_option( 'lifterlms_registration_password_strength' ) );
		if ( ! $option ) {
			return array();
		}

		return array(
			$this->get_block(
				'password-strength-meter',
				array(
					'className'       => 'llms-password-strength-meter',
					'field'           => 'html',
					'id'              => 'llms-password-strength-meter',
					'description'     => sprintf( __( 'A %1$s password is required. The password must be at least %2$s characters in length. Consider adding letters, numbers, and symbols to increase the password strength.', 'lifterlms' ), '{min_strength}', '{min_length}' ),
					'min_strength'    => get_option( 'lifterlms_registration_password_min_strength', 'strong' ), // Use legacy option.
					'min_length'      => 6,
					'llms_visibility' => 'logged_out',
				)
			),
		);

	}

	/**
	 * Retrieve block for the phone row.
	 *
	 * Returns an empty array of phone collection is disabled via legacy settings.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location. Accepts template options passed to `$this->get_template()`.
	 * @return array
	 */
	protected function get_row_phone( $location ) {

		$option = get_option( sprintf( 'lifterlms_user_info_field_phone_%s_visibility', $location ), 'optional' );
		if ( 'hidden' === $option ) {
			return array();
		}

		return array(
			$this->get_block(
				'user-phone',
				array(
					'field'    => 'tel',
					'id'       => 'llms_phone',
					'label'    => __( 'Phone Number', 'lifterlms' ),
					'required' => ( 'required' === $option ),
				)
			),
		);

	}

	/**
	 * Retrieve block for the username row.
	 *
	 * If username generation is enabled returns an empty array as the username block isn't required.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_username() {

		$option = get_option( 'lifterlms_registration_generate_username', 'yes' );
		if ( llms_parse_bool( $option ) ) {
			return array();
		}

		return array(
			$this->get_block(
				'user-username',
				array(
					'id'              => 'user_login',
					'label'           => __( 'Username', 'lifterlms' ),
					'llms_visibility' => 'logged_out',
				)
			),
		);

	}

	/**
	 * Retrieve block for the voucher row.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function get_row_voucher() {

		$option = get_option( 'lifterlms_voucher_field_registration_visibility', 'optional' );
		if ( 'hidden' === $option ) {
			return array();
		}

		return array(
			$this->get_block(
				'redeem-voucher',
				array(
					'id'          => 'llms_voucher',
					'label'       => __( 'Have a voucher?', 'lifterlms' ),
					'placeholder' => __( 'Voucher Code', 'lifterlms' ),
					'required'    => ( 'required' === $option ),
					'toggleable'  => true,
				)
			),
		);

	}

	/**
	 * Retrieve the block template HTML for a given location.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location. Accepts "checkout",, "registration", or "account".
	 * @return string
	 */
	public function get_template( $location ) {

		$method = sprintf( 'template_%s', $location );
		if ( method_exists( $this, $method ) ) {
			return $this->to_string( $this->{$method}() );
		}

		return '';

	}

	/**
	 * Get the default template used for the basis of all forms.
	 *
	 * @since [version]
	 *
	 * @param string $location Form location ID.
	 * @return array
	 */
	protected function template_default( $location ) {

		return array_merge(
			$this->get_row_username(),
			$this->get_row_email( $location ),
			$this->get_row_password(),
			$this->get_row_password_meter(),
			$this->get_row_names( $location ),
			$this->get_row_address_street( $location ),
			$this->get_row_address_city( $location ),
			$this->get_row_address_l10n( $location ),
			$this->get_row_phone( $location )
		);

	}

	/**
	 * Retrieve the default template for the edit account screen.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function template_account() {
		return $this->template_default( 'account' );
	}

	/**
	 * Retrieve the default template for the checkout screen.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function template_checkout() {
		return $this->template_default( 'checkout' );
	}

	/**
	 * Retrieve the default template for the registration screen.
	 *
	 * @since [version]
	 *
	 * @return array
	 */
	protected function template_registration() {
		return array_merge(
			$this->template_default( 'registration' ),
			$this->get_row_voucher()
		);
	}


	/**
	 * Convert an array of block lines to a composed string that can be used in the post_content of a form.
	 *
	 * @since [version]
	 *
	 * @param array $template Array of block strings.
	 * @return string
	 */
	protected function to_string( $template ) {

		return implode( '', $template );

	}

	/**
	 * Wrap blocks in column blocks.
	 *
	 * @since [version]
	 *
	 * @param array $columns Array of block strings or block settings.
	 * @return array
	 */
	protected function wrap_columns( $columns = array(), $visibility = '' ) {

		$settings = $visibility ? ' ' . wp_json_encode( array( 'llms_visibility' => $visibility ) ) : '';
		$cols     = array();

		$cols[] = sprintf( '<!-- wp:columns%s --><div class="wp-block-columns">', $settings );

		foreach ( $columns as $column ) {

			if ( is_string( $column ) ) {
				$column = array(
					'width'   => false,
					'content' => $column,
				);
			}

			$width = $column['width'] ? $column['width'] : false;
			$json  = $width ? wp_json_encode( array( 'width' => $width ) ) . ' ' : '';
			$css   = $width ? sprintf( ' style="flex-basis:%s%%"', $width ) : '';

			$cols[] = sprintf( '<!-- wp:column %1$s--><div class="wp-block-column"%2$s>', $json, $css );
			$cols[] = $column['content'];
			$cols[] = '</div><!-- /wp:column -->';

		}

		$cols[] = '</div><!-- /wp:column -->';

		return $cols;

	}

}
