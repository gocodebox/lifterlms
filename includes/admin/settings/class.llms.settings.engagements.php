<?php
/**
 * Admin Settings Page: Engagements
 *
 * @package LifterLMS/Admin/Settings/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Settings_Engagements class
 *
 * @since 1.0.0
 * @since 3.8.0 Unknown.
 * @since 3.37.3 Renamed setting field IDs to be unique.
 *              Removed redundant functions defined in the `LLMS_Settings_Page` class.
 *              Removed constructor and added `get_label()` method to be compatible with changes in `LLMS_Settings_Page`.
 * @since 3.40.0 Add a section that displays conditionally for email delivery provider connections.
 */
class LLMS_Settings_Engagements extends LLMS_Settings_Page {

	/**
	 * Settings identifier
	 *
	 * @var string
	 */
	public $id = 'engagements';

	/**
	 * Constructor.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {
		parent::__construct();
		add_action( "lifterlms_settings_{$this->id}", array( $this, 'output_js' ) );
	}

	/**
	 * Retrieve the page label.
	 *
	 * @since 3.37.3
	 *
	 * @return string
	 */
	protected function set_label() {
		return __( 'Engagements', 'lifterlms' );
	}

	/**
	 * Get settings array
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 * @since 3.37.3 Refactor to pull each settings group from its own method.
	 * @since 3.40.0 Include an email delivery section.
	 * @since 6.0.0 Include achievements section.
	 *
	 * @return array
	 */
	public function get_settings() {

		/**
		 * Modify LifterLMS Admin Settings on the "Engagements" tab,
		 *
		 * @since 1.0.0
		 *
		 * @param array[] $settings Array of settings fields arrays.
		 */
		return apply_filters(
			'lifterlms_engagements_settings',
			array_merge(
				$this->get_settings_group_email(),
				$this->get_settings_group_email_delivery(),
				$this->get_settings_group_achievements(),
				$this->get_settings_group_certs()
			)
		);

	}

	/**
	 * Retrieve fields for the achievements settings group.
	 *
	 * @since 6.0.0
	 *
	 * @return array[]
	 */
	protected function get_settings_group_achievements() {

		return $this->generate_settings_group(
			'achievement_options',
			__( 'Achievement Settings', 'lifterlms' ),
			'',
			array(
				array(
					'title'    => __( 'Placeholder Image', 'lifterlms' ),
					'desc'     => $this->get_award_image_desc( __( 'achievement', 'lifterlms' ) ),
					'id'       => 'lifterlms_achievement_default_img',
					'type'     => 'image',
					'value'    => llms()->achievements()->get_default_image( 0 ),
					'autoload' => false,
				),
			)
		);

	}

	/**
	 * Retrieve fields for the certificates settings group.
	 *
	 * @since 3.37.3
	 * @since 6.0.0 Add background image options.
	 *               Only load legacy certificate options when the legacy option is enabled.
	 *
	 * @return array[]
	 */
	protected function get_settings_group_certs() {

		$certificate_sizes = llms_get_certificate_sizes();

		$settings = array(
			array(
				'title'    => __( 'Placeholder Background Image', 'lifterlms' ),
				'desc'     => $this->get_award_image_desc( __( 'certificate', 'lifterlms' ) ),
				'id'       => 'lifterlms_certificate_default_img',
				'type'     => 'image',
				'value'    => llms()->certificates()->get_default_image( 0 ),
				'autoload' => false,
			),
			array(
				'title'    => __( 'Default Size', 'lifterlms' ),
				'desc'     => __( 'The default size used when creating new certificates.', 'lifterlms' ),
				'id'       => 'lifterlms_certificate_default_size',
				'type'     => 'select',
				'options'  => $this->get_certificate_size_opts(),
				'default'  => 'LETTER',
				'autoload' => false,
			),
			array(
				'title' => __( 'User Defined Certificate Size', 'lifterlms' ),
				'desc'  => __( 'Use these settings to customize the User Defined Certificate size.', 'lifterlms' ),
				'id'    => 'cert_user_defined_size_settings',
				'type'  => 'subtitle',
			),
			array(
				'title'             => __( 'Width', 'lifterlms' ),
				'id'                => 'lifterlms_certificate_default_user_defined_width',
				'type'              => 'number',
				'default'           => $certificate_sizes['USER_DEFINED']['width'],
				'autoload'          => false,
				'custom_attributes' => array(
					'step' => '0.01',
				),
			),
			array(
				'title'             => __( 'Height', 'lifterlms' ),
				'id'                => 'lifterlms_certificate_default_user_defined_height',
				'type'              => 'number',
				'default'           => $certificate_sizes['USER_DEFINED']['height'],
				'autoload'          => false,
				'custom_attributes' => array(
					'step' => '0.01',
				),
			),
			array(
				'title'    => __( 'Unit', 'lifterlms' ),
				'id'       => 'lifterlms_certificate_default_user_defined_unit',
				'type'     => 'select',
				'options'  => $this->get_certificate_units_opts(),
				'default'  => $certificate_sizes['USER_DEFINED']['unit'],
				'autoload' => false,
			),
		);

		if ( $this->has_legacy_certificates() ) {

			$settings = array_merge(
				$settings,
				array(
					array(
						'title' => __( 'Legacy Certificate Background Image Settings', 'lifterlms' ),
						'type'  => 'subtitle',
						'desc'  => __( 'Use these settings to determine the dimensions of legacy certificate background images created using the classic editor. These settings have no effect on certificates created using the block editor. After changing these settings, you may need to <a href="http://wordpress.org/extend/plugins/regenerate-thumbnails/" target="_blank">regenerate your thumbnails</a>.', 'lifterlms' ),
						'id'    => 'cert_bg_image_settings',
					),
					array(
						'title'    => __( 'Image Width', 'lifterlms' ),
						'desc'     => __( 'in pixels', 'lifterlms' ),
						'id'       => 'lifterlms_certificate_bg_img_width',
						'default'  => '800',
						'type'     => 'number',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Image Height', 'lifterlms' ),
						'id'       => 'lifterlms_certificate_bg_img_height',
						'desc'     => __( 'in pixels', 'lifterlms' ),
						'default'  => '616',
						'type'     => 'number',
						'autoload' => false,
					),
					array(
						'title'    => __( 'Legacy compatibility', 'lifterlms' ),
						'desc'     => __( 'Use legacy certificate image sizes.', 'lifterlms' ) .
										'<br><em>' . __( 'Enabling this will override the above dimension settings and set the image dimensions to match the dimensions of the uploaded image.', 'lifterlms' ) . '</em>',
						'id'       => 'lifterlms_certificate_legacy_image_size',
						'default'  => 'no',
						'type'     => 'checkbox',
						'autoload' => false,
					),
				)
			);

		}

		return $this->generate_settings_group(
			'certificates_options',
			__( 'Certificate Settings', 'lifterlms' ),
			'',
			$settings
		);

	}

	/**
	 * Retrieve fields for the email settings group.
	 *
	 * @since 3.37.3
	 *
	 * @return array[]
	 */
	protected function get_settings_group_email() {

		return $this->generate_settings_group(
			'email_options',
			__( 'Email Settings', 'lifterlms' ),
			__( 'Settings for all emails sent by LifterLMS. Notification and engagement emails will adhere to these settings.', 'lifterlms' ),
			array(
				array(
					'title'   => __( 'Sender Name', 'lifterlms' ),
					'desc'    => __( 'Name to be displayed in From field.', 'lifterlms' ),
					'id'      => 'lifterlms_email_from_name',
					'type'    => 'text',
					'default' => esc_attr( get_bloginfo( 'title' ) ),
				),
				array(
					'title'   => __( 'Sender Email', 'lifterlms' ),
					'desc'    => __( 'Email Address displayed in the From field.', 'lifterlms' ),
					'id'      => 'lifterlms_email_from_address',
					'type'    => 'email',
					'default' => get_option( 'admin_email' ),
				),
				array(
					'title'    => __( 'Header Image', 'lifterlms' ),
					'id'       => 'lifterlms_email_header_image',
					'type'     => 'image',
					'default'  => '',
					'autoload' => false,
				),
				array(
					'title'   => __( 'Email Footer Text', 'lifterlms' ),
					'desc'    => __( 'Text you would like displayed in the footer of all emails.', 'lifterlms' ),
					'id'      => 'lifterlms_email_footer_text',
					'type'    => 'textarea',
					'default' => '',
				),
			)
		);

	}

	/**
	 * Retrieve email delivery partner settings groups.
	 *
	 * @since 3.40.0
	 *
	 * @return array
	 */
	protected function get_settings_group_email_delivery() {

		/**
		 * Filter settings for available email delivery services.
		 *
		 * @since 3.40.0
		 *
		 * @param array[] $settings Array of settings arrays.
		 */
		$services = apply_filters( 'llms_email_delivery_services', array() );

		// If there's no services respond with an empty array so we don't output the whole section.
		if ( ! $services ) {
			return array();
		}

		// Output the a section.
		return $this->generate_settings_group(
			'email_delivery',
			__( 'Email Delivery', 'lifterlms' ),
			'',
			$services
		);

	}

	/**
	 * Retrieves the options array for the `lifterlms_certificate_default_size` option.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	private function get_certificate_size_opts() {

		$units = llms_get_certificate_units();

		$sizes = array();

		foreach ( llms_get_certificate_sizes() as $size_id => $data ) {

			$unit = $units[ $data['unit'] ] ?? '';

			$sizes[ $size_id ] = sprintf(
				'%1$s (%2$s%4$s x %3$s%4$s)',
				$data['name'],
				$data['width'],
				$data['height'],
				$unit['symbol'] ?? ''
			);

		}

		return $sizes;

	}

	/**
	 * Retrieves the options array for the `lifterlms_certificate_default_user_defined_units` option.
	 *
	 * @since 6.0.0
	 *
	 * @return array
	 */
	private function get_certificate_units_opts() {

		$units = llms_get_certificate_units();
		$opts  = array();

		foreach ( $units as $unit => $data ) {
			$opts[ $unit ] = sprintf(
				'%1$s (%2$s)',
				$unit,
				$data['name']
			);
		}
		return $opts;

	}

	/**
	 * Retrieves the award image setting description HTML.
	 *
	 * @since 6.0.0
	 *
	 * @param string $post_type Translated post type name.
	 * @return string
	 */
	private function get_award_image_desc( $post_type ) {

		$desc = sprintf(
			__( 'A default image used for any %1$s template or award which does not specify an image. Changing this setting will affect all existing templates and awards which do not specify their own image.', 'lifterlms' ),
			$post_type
		);
		return '<p class="description">' . $desc . '</p>';

	}

	/**
	 * Determines if legacy certificate options should be displayed.
	 *
	 * The option used to determine if there are certificates is set during a migration to version from versions
	 * earlier than 6.0.0. During the migration if at least one certificate template is migrated, the option
	 * is set and the legacy options will be displayed.
	 *
	 * Even after all certificates have been individually migrated the option will still be set and should be
	 * deleted via the db, set to 'no' via the options.php screen or disabled by returning `false` from the short
	 * circuit filter {@see llms_has_legacy_certificates}.
	 *
	 * @since 6.0.0
	 *
	 * @return boolean
	 */
	private function has_legacy_certificates() {

		/**
		 * Short-circuits the legacy certificates check preventing a database call.
		 *
		 * This can be used to force-enable or force-disable legacy certificate settings regardless
		 * of the value found in the database option.
		 *
		 * @since 6.0.0
		 *
		 * @param boolean $has_legacy_certificates Return `true` to force legacy certificate settings on
		 *                                         and `false` to force them off.
		 */
		$pre = apply_filters( 'llms_has_legacy_certificates', null );
		if ( ! is_null( $pre ) ) {
			return $pre;
		}

		return llms_parse_bool( get_option( 'llms_has_certificates_with_legacy_default_image', 'no' ) );

	}

	/**
	 * Outputs inline Javascript utilized on the engagements settings tab.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function output_js() {
		?>
		<script>(function(){
			const fields = {
				height: document.getElementById( 'lifterlms_certificate_default_user_defined_height' ),
				width: document.getElementById( 'lifterlms_certificate_default_user_defined_width' ),
				unit: document.getElementById( 'lifterlms_certificate_default_user_defined_unit' ),
			};
			/**
			 * Updates the USER_DEFINED <option> text when the values of the custom inputs change.
			 *
			 * @since 6.0.0
			 *
			 * @return {void}
			 */
			function updateOptionText() {
				const opt = document.getElementById( 'lifterlms_certificate_default_size' ).querySelector( 'option[value="USER_DEFINED"]' ),
					newStr = fields.width.value + fields.unit.value + ' x ' + fields.height.value + fields.unit.value;
				if ( opt ) {
					opt.textContent = opt.textContent.replace( / \(.*\)/, ' (' + newStr + ')' );
				}
			}
			// When any of the fields change, update the value of the option.
			Object.values( fields ).map( function( el ) {
				el.addEventListener( 'change', updateOptionText );
			} );
		})();</script>
		<?php
	}

}

return new LLMS_Settings_Engagements();
