<?php
/**
 * LifterLMS User Certificate
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_User_Certificate model class
 *
 * @since 3.8.0
 * @since [version] Utilize `LLMS_Abstract_User_Engagement` abstract.
 */
class LLMS_User_Certificate extends LLMS_Abstract_User_Engagement {

	/**
	 * Database (WP) post type name
	 *
	 * @var string
	 */
	protected $db_post_type = 'llms_my_certificate';

	/**
	 * Post type model name
	 *
	 * @var string
	 */
	protected $model_post_type = 'certificate';

	/**
	 * Object properties
	 *
	 * @var array
	 */
	protected $properties = array(
		'certificate_title'    => 'string',
		'certificate_image'    => 'absint',
		'certificate_template' => 'absint',
		'engagement'           => 'absint',
		'related'              => 'absint',
		'allow_sharing'        => 'yesno',
		'sequential_id'        => 'absint',
	);

	/**
	 * Called immediately after creating / inserting a new post into the database
	 *
	 * This stub can be overwritten by child classes.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function after_create() {

		$this->set( 'sequential_id', llms_get_certificate_sequential_id( $this->get( 'certificate_template' ), true ) );
		$this->merge_content( true );

	}

	/**
	 * Can user manage and make some actions on the certificate
	 *
	 * @since 4.5.0
	 *
	 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 * @return bool
	 */
	public function can_user_manage( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		$result  = ( $user_id === $this->get_user_id() || llms_can_user_bypass_restrictions( $user_id ) );

		/**
		 * Filter whether or not a user can manage a given certificate.
		 *
		 * @since 4.5.0
		 *
		 * @param boolean               $result      Whether or not the user can manage certificate.
		 * @param int                   $user_id     WP_User ID of the user viewing the certificate.
		 * @param LLMS_User_Certificate $certificate Certificate class instance.
		 */
		return apply_filters( 'llms_certificate_can_user_manage', $result, $user_id, $this );

	}

	/**
	 * Can user view the certificate
	 *
	 * @since 4.5.0
	 *
	 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 * @return bool
	 */
	public function can_user_view( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		$result  = $this->can_user_manage( $user_id ) || $this->is_sharing_enabled();

		/**
		 * Filter whether or not a user can view a user's certificate.
		 *
		 * @since 4.5.0
		 *
		 * @param boolean               $result      Whether or not the user can view the certificate.
		 * @param int                   $user_id     WP_User ID of the user viewing the certificate.
		 * @param LLMS_User_Certificate $certificate Certificate class instance.
		 */
		return apply_filters( 'llms_certificate_can_user_view', $result, $user_id, $this );

	}

	/**
	 * Retrieve the formatted sequential id for the certificate.
	 *
	 * The sequential ID is stored as an integer and formatted for display according the filterable
	 * settings found in this method.
	 *
	 * By default, the sequential ID will appear as a 6 character number, left-side padded with zeros.
	 *
	 * Examples:
	 *   + 1      = 000001
	 *   + 20     = 000020
	 *   + 12345  = 012345
	 *   + 999999 = 999999
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	public function get_sequential_id() {

		/**
		 * Filter certificate sequential id formatting settings.
		 *
		 * These settings are passed as arguments to `str_pad()`.
		 *
		 * @since [version]
		 *
		 * @link https://www.php.net/manual/en/function.str-pad.php
		 *
		 * @param array {
		 *    Array of formatting settings.
		 *
		 *    @type int    $length    Number of characters for the ID.
		 *    @type string $character Padding character.
		 *    @type int    $type      String padding type. Expects a valid `pad_type` PHP constant: STR_PAD_RIGHT, STR_PAD_LEFT, or STR_PAD_BOTH.
		 * }
		 * @param LLMS_User_Certificate $certificate Instance of the certificate object.
		 */
		$formatting = apply_filters(
			'llms_certificate_sequential_id_format',
			array(
				'length'    => 6,
				'character' => '0',
				'type'      => STR_PAD_LEFT,
			),
			$this,
		);

		$raw_id = $this->get( 'sequential_id' );

		$id = str_pad(
			(string) $raw_id,
			$formatting['length'],
			$formatting['character'],
			$formatting['type']
		);

		/**
		 * Filters the formatted certificate sequential ID string.
		 *
		 * @since [version]
		 *
		 * @param string                $id          The formatted sequential ID.
		 * @param int                   $raw_id      The raw ID before formatting was applied.
		 * @param array                 $formatting  Array of formatting settings, see `llms_certificate_sequential_id_format`.
		 * @param LLMS_User_Certificate $certificate Instance of the certificate object.
		 */
		return apply_filters( 'llms_certificate_sequential_id', $id, $raw_id, $formatting, $this );

	}

	/**
	 * Retrieve merge codes and data.
	 *
	 * @since [version]
	 *
	 * @return array Array mapping merge codes to the merge data.
	 */
	protected function get_merge_data() {

		$template_id   = $this->get( 'certificate_template' );
		$user_id       = $this->get_user_id();
		$related_id    = $this->get( 'related' );
		$engagement_id = $this->get( 'engagement' );

		$user = get_userdata( $user_id );

		$codes = array(
			// Site.
			'{site_title}'     => wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES ),
			'{site_url}'       => get_permalink( llms_get_page_id( 'myaccount' ) ),
			// User.
			'{user_login}'     => $user ? $user->user_login : '',
			'{first_name}'     => $user ? $user->first_name : '',
			'{last_name}'      => $user ? $user->last_name : '',
			'{email_address}'  => $user ? $user->user_email : '',
			'{student_id}'     => $user ? $user_id : '',
			// Certificate.
			'{current_date}'   => date_i18n( get_option( 'date_format' ), current_time( 'timestamp' ) ),
			'{certificate_id}' => $this->get( 'id' ),
			'{sequential_id}'  => $this->get_sequential_id(),
		);

		if ( $user_id ) {
			/**
			 * Retains deprecated functionality where an instance of LLMS_Certificate_User is passed as a parameter to the filter.
			 *
			 * Since there's no good way to recreate that functionality we'll handle it in this manner
			 * until `LLMS_Certificate_User` is removed.
			 */
			$old_cert = new LLMS_Certificate_User();
			$old_cert->init( $template_id, $user_id, $related_id );
			$codes = apply_filters_deprecated( 'llms_certificate_merge_codes', array( $codes, $old_cert ), '[version]', 'llms_certificate_merge_data' );
		}

		return apply_filters( 'llms_certificate_merge_data', $codes, $user_id, $template_id, $related_id );

	}

	/**
	 * Merges the post content based on content from the template.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	protected function merge_content() {

		// Merge.
		$merge   = $this->get_merge_data();
		$content = str_replace( array_keys( $merge ), array_values( $merge ), $this->get( 'content', true ) );

		// Do shortcodes.
		LLMS_Shortcodes::init(); // In certain circumstances shortcodes won't be registered yet.
		add_filter( 'llms_user_info_shortcode_user_id', array( $this, 'get_user_id' ) );
		$content = do_shortcode( $content );
		remove_filter( 'llms_user_info_shortcode_user_id', array( $this, 'get_user_id' ) );

		// Preserve legacy functionality which wraps the post content in the HTML specified in the template file.
		$use_template = apply_filters_deprecated(
			'llms_certificate_use_legacy_template',
			array( false, $this ),
			'[version]',
			'', // There is no direct replacement.
			__( 'Loading custom HTML from the certificate template is deprecated. All HTML should be added to the certificate directly via the editor or applied via post content filters.', 'lifterlms' )
		);
		if ( $use_template ) {
			ob_start();
			llms_get_template(
				'certificates/template.php',
				array(
					'email_message' => $content,
					'title'         => $this->get( 'title' ),
					'image'         => $this->get( 'certificate_image' ),
				)
			);
			$content = ob_get_clean();
		}

		// Save the fully merged content.
		$this->set( 'content', $content );

	}

	/**
	 * Update the certificate by regenerating it's content and title from the template.
	 *
	 * @since [version]
	 *
	 * @return WP_Error|boolean Returns a `WP_Error` if an error is encountered checking the template post, otherwise returns `true`.
	 */
	public function sync() {

		$template_id = $this->get( 'certificate_template' );
		$check = LLMS_Engagement_Handler::check_post( $template_id, 'llms_certificate' );
		if ( is_wp_error( $check ) ) {
			return $check;
		}

		$template = get_post( $template_id );

		$this->set( 'title', $template->post_title );
		$this->set( 'content', $template->post_content );

		$this->merge_content();

		return true;

	}

	/**
	 * Is sharing enabled
	 *
	 * @since 4.5.0
	 *
	 * @return bool
	 */
	public function is_sharing_enabled() {

		/**
		 * Filter whether or not sharing is enabled for a certificate.
		 *
		 * @since 4.5.0
		 *
		 * @param boolean               $enabled     Whether or not sharing is enabled.
		 * @param LLMS_User_Certificate $certificate Certificate class instance.
		 */
		return apply_filters( 'llms_certificate_is_sharing_enabled', llms_parse_bool( $this->get( 'allow_sharing' ) ), $this );

	}

}
