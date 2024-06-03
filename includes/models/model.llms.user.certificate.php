<?php
/**
 * LLMS_User_Certificate model class
 *
 * @package LifterLMS/Models/Classes
 *
 * @since 3.8.0
 * @version 6.4.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * A certificate awarded to a student.
 *
 * @since 3.8.0
 * @since 6.0.0 Utilize `LLMS_Abstract_User_Engagement` abstract.
 *
 * @property string  $allow_sharing Whether or not public certificate sharing is enabled for the certificate.
 *                                  Either "yes" or "no".
 * @property string  $awarded       MySQL timestamp recorded when the certificate was first awarded.
 * @property string  $background    The CSS background color for the certificate.
 * @property int     $author        WP_User ID of the user who the certificate belongs to.
 * @property string  $content       The merged certificate content.
 * @property int     $engagement    WP_Post ID of the `llms_engagement` post used to trigger the certificate.
 *                                  An empty value or `0` indicates the certificate was awarded manually or
 *                                  before the engagement value was stored.
 * @property float   $height        The certificate's height.
 * @property float[] $margins       The certificate's margins.
 * @property string  $orientation   The certificate's orientation.
 * @property int     $parent        WP_Post ID of the template `llms_certificate` post.
 * @property int     $related       WP_Post ID of the related post.
 * @property int     $sequential_id The sequential certificate ID.
 * @property string  $size          The certificate's registered size ID.
 * @property string  $title         Certificate title.
 * @property string  $unit          The certificate's registered unit ID.
 * @property float   $width         The certificate's width.
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
		'allow_sharing' => 'string',
		'awarded'       => 'string',
		'background'    => 'string',
		'engagement'    => 'absint',
		'height'        => 'float',
		'margins'       => 'array',
		'orientation'   => 'string',
		'related'       => 'absint',
		'sequential_id' => 'absint',
		'size'          => 'string',
		'unit'          => 'string',
		'width'         => 'float',
	);

	/**
	 * Array of default property values.
	 *
	 * In the form of key => default value.
	 *
	 * @var array
	 */
	protected $property_defaults = array(
		'background'    => '#ffffff',
		'orientation'   => 'landscape',
		'margins'       => array( 5, 5, 5, 5 ),
		'sequential_id' => 1,
	);

	/**
	 * Constructor.
	 *
	 * Overrides parent method to setup default properties that depend on other property values.
	 *
	 * @since 6.0.0
	 *
	 * @param string|int|LLMS_Post_Model|WP_Post $model Existing post or model object or ID
	 * @param array                              $args  Args to create the post, only applies when $model is 'new'.
	 * @return void
	 */
	public function __construct( $model, $args = array() ) {

		$this->set_property_defaults();

		parent::__construct( $model, $args );

	}

	/**
	 * Set this awarded certificate sequential id based on the parent's meta.
	 *
	 * @since 6.0.0
	 *
	 * @return int|false Returns the awarded certificate sequenatial id.
	 *                   Returns false if the awarded certificate has no parent template.
	 */
	public function update_sequential_id() {

		$parent = $this->get( 'parent' );
		if ( ! $parent ) {
			return false;
		}

		$next_sequential_id = llms_get_certificate_sequential_id( $parent, true );
		$this->set( 'sequential_id', $next_sequential_id );

		return $next_sequential_id;

	}

	/**
	 * Can user manage and make some actions on the certificate
	 *
	 * @since 4.5.0
	 * @since 6.0.0 Prevent logged out users from managing certificates not assigned to a user.
	 *
	 * @param int|null $user_id Optional. WP User ID (will use get_current_user_id() if none supplied). Default `null`.
	 * @return bool
	 */
	public function can_user_manage( $user_id = null ) {

		$user_id = $user_id ? $user_id : get_current_user_id();
		$result  = ( $user_id && ( $user_id === $this->get_user_id() || llms_can_user_bypass_restrictions( $user_id ) ) );

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
	 * Retrieves the certificate background color value.
	 *
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function get_background() {
		return $this->get( 'background' );
	}

	/**
	 * Retrieve information about the certificate background image.
	 *
	 * This function returns an array of information used for legacy certificates using the v1 template.
	 *
	 * When using the v2 template, only the `$src` value is utilized and the background image itself is
	 * always set to 100% width and height of certificate as defined by the certificate's sizing settings.
	 *
	 * @since 6.0.0
	 *
	 * @return array {
	 *     Returns an associative array of information about the background image.
	 *
	 *     @type string $src        The image source url.
	 *     @type int    $width      The image display width, in pixels.
	 *     @type int    $height     The image display height, in pixels.
	 *     @type bool   $is_default Whether or not the default image was returned.
	 * }
	 */
	public function get_background_image() {

		$id     = $this->get( 'id' );
		$img_id = get_post_thumbnail_id( $id );

		$size = 'full';
		if ( 1 === $this->get_template_version() ) {
			$size = llms_parse_bool( get_option( 'lifterlms_certificate_legacy_image_size', 'yes' ) ) ? 'full' : 'lifterlms_certificate_background';
		}

		if ( ! $img_id ) {

			// Get the source.
			$src = llms()->certificates()->get_default_image( $id );

			// Denote it's the default image in the return.
			$is_default = true;

			/**
			 * Filters the display height of the default certificate background image.
			 *
			 * This filter is used by legacy certificates only. If the certificate is utilizing
			 * the block editor the filtered value does not affect the size of the background image as
			 * the image is always set to fill the width and height of the certificate itself.
			 *
			 * @since 2.2.0
			 *
			 * @param int $height         Display height of the image, in pixels.
			 * @param int $certificate_id WP_Post ID of the awarded certificate.
			 */
			$height = apply_filters( 'lifterlms_certificate_background_image_placeholder_height', 616, $id );

			/**
			 * Filters the display width of the default certificate background image.
			 *
			 * This filter is used by legacy certificates only. If the certificate is utilizing
			 * the block editor the filtered value does not affect the size of the background image as
			 * the image is always set to fill the width and height of the certificate itself.
			 *
			 * @since 2.2.0
			 *
			 * @param int $width          Display width of the image, in pixels.
			 * @param int $certificate_id WP_Post ID of the awarded certificate.
			 */
			$width = apply_filters( 'lifterlms_certificate_background_image_placeholder_width', 800, $id );

		} else {

			list( $src, $width, $height ) = wp_get_attachment_image_src( $img_id, $size );

			// Denote it's not the default image in the return.
			$is_default = false;

			/**
			 * Filters the image source of the certificate background image.
			 *
			 * @since 2.2.0
			 *
			 * @param string $src            The image source url.
			 * @param int    $certificate_id WP_Post ID of the awarded certificate.
			 */
			$src = apply_filters( 'lifterlms_certificate_background_image_src', $src, $id );

			/**
			 * Filters the display height of the certificate background image.
			 *
			 * This filter is used by legacy certificates only. If the certificate is utilizing
			 * the block editor the filtered value does not affect the size of the background image as
			 * the image is always set to fill the width and height of the certificate itself.
			 *
			 * @since 2.2.0
			 *
			 * @param int $height         Display height of the image, in pixels.
			 * @param int $certificate_id WP_Post ID of the awarded certificate.
			 */
			$height = apply_filters( 'lifterlms_certificate_background_image_height', $height, $id );

			/**
			 * Filters the display width of the certificate background image.
			 *
			 * This filter is used by legacy certificates only. If the certificate is utilizing
			 * the block editor the filtered value does not affect the size of the background image as
			 * the image is always set to fill the width and height of the certificate itself.
			 *
			 * @since 2.2.0
			 *
			 * @param int $width          Display width of the image, in pixels.
			 * @param int $certificate_id WP_Post ID of the awarded certificate.
			 */
			$width = apply_filters( 'lifterlms_certificate_background_image_width', $width, $id );

		}

		return compact( 'src', 'width', 'height', 'is_default' );

	}

	/**
	 * Retrieves a list of the fonts used by the certificate.
	 *
	 * @since 6.0.0
	 *
	 * @see llms_get_certificate_fonts()
	 *
	 * @param array|null $blocks A list of parsed block arrays or null. If none supplied the certificate's
	 *                           content is parsed and used instead.
	 * @return array[] Array of fonts by the certificate. Each array is a font definition with the font's
	 *                 id added to the array.
	 */
	public function get_custom_fonts( $blocks = null ) {

		$fonts = array();

		$blocks = is_null( $blocks ) ? parse_blocks( $this->get( 'content', true ) ) : $blocks;
		foreach ( $blocks as $block ) {

			if ( ! empty( $block['attrs']['fontFamily'] ) ) {
				$fonts[] = $block['attrs']['fontFamily'];
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$fonts = array_merge( $fonts, wp_list_pluck( $this->get_custom_fonts( $block['innerBlocks'] ), 'id' ) );
			}
		}

		$valid_fonts = llms_get_certificate_fonts();

		return array_filter(
			array_map(
				function( $font ) use ( $valid_fonts ) {
					if ( 'default' === $font ) {
						return null;
					}
					$ret = $valid_fonts[ $font ] ?? null;
					if ( $ret ) {
						$ret['id'] = $font;
					}
					return $ret;
				},
				array_unique( $fonts )
			)
		);

	}

	/**
	 * Retrieves the value for either the width or height.
	 *
	 * @since 6.0.0
	 *
	 * @param string  $dimension Dimension key, either "width" or "height".
	 * @param boolean $with_unit Whether or not to include the unit in the return.
	 * @return string|float If `$with_unit` is `true`, returns a string with the unit, otherwise returns the dimension as a float.
	 */
	private function get_dimension( $dimension, $with_unit = false ) {

		$ret = 0;
		if ( 'CUSTOM' === $this->get_size() ) {
			$ret = $this->get( $dimension );
		} else {
			$size_info = $this->get_registered_size_data();
			$ret       = $size_info[ $dimension ];
		}

		return $with_unit ? sprintf( '%1$s%2$s', $ret, $this->get_unit() ) : $ret;

	}

	/**
	 * Retrieve dimensions adjusted for orientation.
	 *
	 * The width and height are always stored as if the certificate were to be displayed in portrait
	 * mode. This method will return the dimensions as necessary to use in styling rules.
	 *
	 * When the certificate is displaying in landscape the width and height are transposed
	 * automatically by this method.
	 *
	 * @since 6.0.0
	 *
	 * @param bool $with_units Whether or not to include the unit in the return.
	 * @return array {
	 *     Array of dimensions.
	 *
	 *     @type string|float $width  The display width.
	 *     @type string|float $height The display height.
	 * }
	 */
	public function get_dimensions_for_display( $with_units = true ) {

		$orientation = $this->get_orientation();
		$width       = $this->get_width( $with_units );
		$height      = $this->get_height( $with_units );

		return array(
			'width'  => 'portrait' === $orientation ? $width : $height,
			'height' => 'portrait' === $orientation ? $height : $width,
		);

	}

	/**
	 * Retrieve the height dimension.
	 *
	 * @since 6.0.0
	 *
	 * @param boolean $with_unit Whether or not to include the unit in the return.
	 * @return string|float If `$with_unit` is `true`, returns a string with the unit, otherwise returns the height as a float.
	 */
	public function get_height( $with_unit = false ) {
		return $this->get_dimension( 'height', $with_unit );
	}

	/**
	 * Retrieves the certificate's margins.
	 *
	 * @since 6.0.0
	 *
	 * @param boolean $with_units Whether or not to include the percent sign unit in the return.
	 * @return float[] Array of floats representing the margins. The margins are listed as they would be
	 *                 when defining the margins of an element in CSS: `array( $left, $top, $right, $bottom )`.
	 */
	public function get_margins( $with_units = false ) {

		$margins = $this->get( 'margins' );

		if ( $with_units ) {
			$margins = array_map(
				function( $margin ) {
					return $margin . '%';
				},
				$margins
			);
		}

		return $margins;
	}

	/**
	 * Retrieve merge codes and data.
	 *
	 * @since 6.0.0
	 * @since 6.1.0 Added `{earned_date}` merge code.
	 *              Allowed `{current_date}` to be mocked.
	 *
	 * @return string[] Array mapping merge codes to the merge data.
	 */
	protected function get_merge_data() {

		$template_id   = $this->get( 'parent' );
		$user_id       = $this->get_user_id();
		$related_id    = $this->get( 'related' );
		$engagement_id = $this->get( 'engagement' );
		$date_format   = get_option( 'date_format' );

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
			'{current_date}'   => wp_date( $date_format, llms_current_time( 'timestamp' ) ),
			'{earned_date}'    => $this->get_date( 'date', $date_format ),
			'{certificate_id}' => $this->get( 'id' ),
			'{sequential_id}'  => $this->get_sequential_id(),
		);

		$codes = LLMS_Engagement_Handler::do_deprecated_filter(
			$codes,
			array( $template_id, $user_id, $related_id ),
			'certificate',
			'llms_certificate_merge_codes',
			'llms_certificate_merge_data'
		);

		/**
		 * Filters the certificate merge data.
		 *
		 * @since 6.0.0
		 *
		 * @param array $codes      {
		 *    Merge codes and data.
		 *
		 *    @type string          $code The merge code. E.g. {first_name}.
		 *    @type int|string|bool $data The merga data to replace the merge code with. E.g. 'Dude'.
		 * }
		 * @param int   $user_id     WP User ID of the user who earned the certificate.
		 * @param int   $template_id WP_Post ID of the certificate template.
		 * @param int   $related_id  WP Post ID of the post which triggered the certificate to be awarded.
		 */
		return apply_filters( 'llms_certificate_merge_data', $codes, $user_id, $template_id, $related_id );

	}

	/**
	 * Retrieves the certificate's orientation value.
	 *
	 * @since 6.0.0
	 *
	 * @see llms_get_certificate_orientations()
	 *
	 * @return string
	 */
	public function get_orientation() {
		return $this->get( 'orientation' );
	}

	/**
	 * Retrieves the registered size data array for the certificate's size.
	 *
	 * This method should not be used without first verifying that the certificate's
	 * size is not set to CUSTOM as this is not a valid size and the sitewide default
	 * will be returned.
	 *
	 * @since 6.0.0
	 *
	 * @see llms_get_certificate_sizes()
	 *
	 * @return array
	 */
	private function get_registered_size_data() {

		$size  = $this->get_size();
		$sizes = llms_get_certificate_sizes();
		if ( ! $size || empty( $sizes[ $size ] ) ) {
			$size = get_option( 'lifterlms_certificate_default_size', 'LETTER' );
		}

		return $sizes[ $size ] ?? array_values( $sizes )[0];

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
	 * @since 6.0.0
	 *
	 * @return string
	 */
	public function get_sequential_id() {

		/**
		 * Filter certificate sequential id formatting settings.
		 *
		 * These settings are passed as arguments to `str_pad()`.
		 *
		 * @since 6.0.0
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
			$this
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
		 * @since 6.0.0
		 *
		 * @param string                $id          The formatted sequential ID.
		 * @param int                   $raw_id      The raw ID before formatting was applied.
		 * @param array                 $formatting  Array of formatting settings, see `llms_certificate_sequential_id_format`.
		 * @param LLMS_User_Certificate $certificate Instance of the certificate object.
		 */
		return apply_filters( 'llms_certificate_sequential_id', $id, $raw_id, $formatting, $this );

	}

	/**
	 * Retrieves the ID of the certificate's size.
	 *
	 * @since 6.0.0
	 *
	 * @see llms_get_certificate_sizes()
	 *
	 * @return string
	 */
	public function get_size() {
		return $this->get( 'size' );
	}

	/**
	 * Retrieves the certificate's template version.
	 *
	 * Since LifterLMS 6.0.0, certificates are created using the block editor.
	 *
	 * Certificates created in the classic editor will use template version 1 while any certificates
	 * created in the block editor use template version 2. Therefore a certificate that has content
	 * and no blocks will use template version 1 and any empty certificates or those containing blocks
	 * will use template version 2.
	 *
	 * @since 6.0.0
	 *
	 * @return integer
	 */
	public function get_template_version() {

		$version = empty( $this->get( 'content', true ) ) || has_blocks( $this->get( 'id' ) ) ? 2 : 1;

		/**
		 * Filters a certificate's template version.
		 *
		 * @since 6.0.0
		 *
		 * @param int $version The template version.
		 */
		return apply_filters( 'llms_certificate_template_version', $version, $this );

	}

	/**
	 * Retrieves the ID of the certificate's unit.
	 *
	 * @since 6.0.0
	 *
	 * @see llms_get_certificate_units()
	 *
	 * @return string
	 */
	public function get_unit() {

		if ( 'CUSTOM' === $this->get_size() ) {
			return $this->get( 'unit' );
		}

		$size_info = $this->get_registered_size_data();
		return $size_info['unit'];

	}

	/**
	 * Retrieve the width dimension.
	 *
	 * @since 6.0.0
	 *
	 * @param boolean $with_unit Whether or not to include the unit in the return.
	 * @return string|float If `$with_unit` is `true`, returns a string with the unit, otherwise returns the width as a float.
	 */
	public function get_width( $with_unit = false ) {
		return $this->get_dimension( 'width', $with_unit );
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

	/**
	 * Merges the post content based on content from the template.
	 *
	 * @since 6.0.0
	 * @since 6.4.0 Added optional `$content` and `$load_reusable_blocks` parameters.
	 *              Removed initialization of shortcodes now that they are registered earlier.
	 *
	 * @param string $content              Optionally use the given content instead of `$this->content`.
	 * @param bool   $load_reusable_blocks Optionally replace reusable blocks with their actual blocks.
	 * @return string
	 */
	public function merge_content( $content = null, $load_reusable_blocks = false ) {

		$content = parent::merge_content( $content, $load_reusable_blocks );

		// Merge.
		$merge   = $this->get_merge_data();
		$content = str_replace( array_keys( $merge ), array_values( $merge ), $content );

		// Do shortcodes.
		add_filter( 'llms_user_info_shortcode_user_id', array( $this, 'get_user_id' ) );
		$content = do_shortcode( $content );
		remove_filter( 'llms_user_info_shortcode_user_id', array( $this, 'get_user_id' ) );

		// Preserve legacy functionality which wraps the post content in the HTML specified in the template file.
		$use_template = apply_filters_deprecated(
			'llms_certificate_use_legacy_template',
			array( false, $this ),
			'6.0.0',
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

		return $content;

	}

	/**
	 * Configure non-static property defaults.
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	private function set_property_defaults() {

		// Default size is configured via a site option.
		$default_size                    = get_option( 'lifterlms_certificate_default_size', 'LETTER' );
		$this->property_defaults['size'] = ! $default_size ? 'LETTER' : $default_size;

	}

	/**
	 * Sync block editor layout properties.
	 *
	 * @since 6.0.0
	 *
	 * @param LLMS_User_Certificate $template
	 * @return void
	 */
	protected function sync_meta( $template ) {

		if ( 1 === $template->get_template_version() ) {
			return;
		}

		$props = array(
			'background',
			'height',
			'margins',
			'orientation',
			'size',
			'unit',
			'width',
		);

		foreach ( $props as $prop ) {
			$this->set( $prop, $template->get( $prop ) );
		}

	}

}
