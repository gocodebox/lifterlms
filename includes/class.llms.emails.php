<?php
/**
 * LifterLMS Emails Class
 *
 * Manages finding the appropriate email.
 *
 * @package LifterLMS/Classes
 *
 * @since 1.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Emails
 *
 * @since 1.0.0
 * @since 3.8.0 Unknown.
 * @since 5.3.0 Replace singleton code with `LLMS_Trait_Singleton`.
 * @since 6.0.0 Removed the deprecated `LLMS_Emails::$_instance` property.
 */
class LLMS_Emails {

	use LLMS_Trait_Singleton;

	/**
	 * Class names of all emails
	 *
	 * @var string[]
	 */
	public $emails;

	/**
	 * Constructor
	 *
	 * Initializes class.
	 * Adds actions to trigger emails off of events.
	 *
	 * @since 1.0.0
	 * @since 3.8.0 Unknown.
	 * @since 6.0.0 Removed loading of class files that don't instantiate their class in favor of autoloading.
	 *
	 * @return void
	 */
	private function __construct() {

		// Template functions.
		llms()->include_template_functions();

		// Email base class.
		$this->emails['generic'] = 'LLMS_Email';

		// Email child classes.
		$this->emails['engagement']     = 'LLMS_Email_Engagement';
		$this->emails['reset_password'] = 'LLMS_Email_Reset_Password';

		$this->emails = apply_filters( 'lifterlms_email_classes', $this->emails );
	}

	/**
	 * Get a string of inline CSS to add to an email button
	 *
	 * Use {button_style} merge code to output in HTML emails.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_button_style() {
		/**
		 * Filters the default email button CSS rules
		 *
		 * @since 3.8.0
		 *
		 * @param array $email_button_css Associative array of the type css-property => definition.
		 */
		$rules  = apply_filters(
			'llms_email_button_css',
			array(
				'background-color' => $this->get_css( 'button-background-color', false ),
				'color'            => $this->get_css( 'button-font-color', false ),
				'display'          => 'inline-block',
				'padding'          => '10px 15px',
				'text-decoration'  => 'none',
			)
		);
		$styles = '';
		foreach ( $rules as $rule => $style ) {
			$styles .= sprintf( '%1$s:%2$s !important;', $rule, $style );
		}
		return $styles;
	}

	/**
	 * Get css rules specific to the the email templates
	 *
	 * @since 3.8.0
	 * @since 5.2.0 Early bail if no rule is provided.
	 *
	 * @param string  $rule Optional. Name of the css rule. Default is empty string.
	 *                      If not provided an empty string will be returned/echoed.
	 * @param boolean $echo Optional. If true, echo the definition. Default is `true`.
	 * @return string
	 */
	public function get_css( $rule = '', $echo = true ) {

		if ( empty( $rule ) ) {
			return '';
		}

		/**
		 * Filters the default email CSS rules
		 *
		 * @since 3.8.0
		 *
		 * @param array $email_css Associative array of the type css-property => definition.
		 */
		$css = apply_filters(
			'llms_email_css',
			array(
				'background-color'         => '#f6f6f6',
				'border-radius'            => '3px',
				'button-background-color'  => '#2295ff',
				'button-font-color'        => '#ffffff',
				'divider-color'            => '#cecece',
				'font-color'               => '#222222',
				'font-family'              => 'sans-serif',
				'font-size'                => '15px',
				'font-size-small'          => '13px',
				'heading-background-color' => '#2295ff',
				'heading-font-color'       => '#ffffff',
				'main-color'               => '#2295ff',
				'max-width'                => '580px',
			)
		);

		if ( isset( $css[ $rule ] ) ) {

			if ( $echo ) {
				echo esc_attr( $css[ $rule ] );
			}

			return $css[ $rule ];

		}
	}

	/**
	 * Get an HTML divider for use in HTML emails
	 *
	 * Can use shortcode {divider} to output in any email.
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_divider_html() {
		return '<div style="height:1px;width:100%;margin:15px auto;background-color:' . $this->get_css( 'divider-color', false ) . '"></div>';
	}

	/**
	 * Retrieve a new instance of an email
	 *
	 * @since 3.8.0
	 *
	 * @param string $id   Email id.
	 * @param array  $args Optional arguments to pass to the email.
	 * @return LLMS_Email
	 */
	public function get_email( $id, $args = array() ) {

		$emails = $this->get_emails();

		// If we have an email matching the ID, return an instance of that email class.
		if ( isset( $emails[ $id ] ) ) {
			return new $emails[ $id ]( $args );
		}

		// Otherwise return a generic email and set the ID to be the requested ID.
		/** @var LLMS_Email $generic */
		$generic = new $emails['generic']( $args );
		$generic->set_id( $id );
		return $generic;
	}

	/**
	 * Get all email objects
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Array of all email class names.
	 */
	public function get_emails() {
		return $this->emails;
	}

	/**
	 * Retrieve the source url of the header image as defined in LifterLMS settings
	 *
	 * @since 3.8.0
	 *
	 * @return string
	 */
	public function get_header_image_src() {
		$src = get_option( 'lifterlms_email_header_image', '' );
		if ( is_numeric( $src ) ) {
			$attachment = wp_get_attachment_image_src( $src, 'full' );
			$src        = $attachment ? $attachment[0] : '';
		}
		/**
		 * Filters the header image src
		 *
		 * @since 3.8.0
		 *
		 * @param string $src Image `src` attribute value.
		 */
		return apply_filters( 'llms_email_header_image_src', $src );
	}

	/**
	 * Returns an array with the table's tags inline style
	 *
	 * It makes sure that all the required tags (table, tr, td) are set.
	 *
	 * @since 5.2.0
	 *
	 * @return array {
	 *     Array of table style.
	 *
	 *     @type string $0 Style of the table tag.
	 *     @type string $1 Style of the tr tag.
	 *     @type string $2 Style of the td tag.
	 * }
	 */
	private function get_parsed_table_style() {

		$table_style = $this->get_table_style();
		$table_style = is_array( $table_style ) ? $table_style : array( $table_style );

		$table_style = wp_parse_args(
			$table_style,
			array(
				'table' => '',
				'tr'    => '',
				'td'    => '',
			)
		);

		return array_values( $table_style );
	}

	/**
	 * Return an associative array with the table's tags inline style
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function get_table_style() {
		return array(
			'table' => $this->get_table_table_style(),
			'tr'    => $this->get_table_tr_style(),
			'td'    => $this->get_table_td_style(),
		);
	}

	/**
	 * Return the table's `table` tag inline style
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function get_table_table_style() {
		return sprintf(
			'border-collapse:collapse;color:%1$s;font-family:%2$s;font-size:%3$s;Margin-bottom:15px;text-align:left;width:100%%;',
			$this->get_css( 'font-color', false ),
			$this->get_css( 'font-family', false ),
			$this->get_css( 'font-size', false )
		);
	}

	/**
	 * Return the table's `tr` tag inline style
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function get_table_tr_style() {
		return 'color:inherit;font-family:inherit;font-size:inherit;';
	}

	/**
	 * Return the table's `td` tag inline style
	 *
	 * @since 5.2.0
	 *
	 * @return string
	 */
	protected function get_table_td_style() {
		return sprintf(
			'border-bottom:1px solid %s;color:inherit;font-family:inherit;font-size:inherit;padding:10px;',
			$this->get_css( 'divider-color', false )
		);
	}

	/**
	 * Returns the table html
	 *
	 * @since 5.2.0
	 *
	 * @param array $rows Array of rows to populate the table with.
	 * @return string
	 */
	public function get_table_html( $rows ) {

		if ( empty( $rows ) ) {
			return '';
		}

		ob_start();
		$this->output_table_html( $rows );

		return ob_get_clean();
	}

	public function output_table_html( $rows ) {

		if ( empty( $rows ) ) {
			return '';
		}

		list( $table_style, $tr_style, $td_style ) = $this->get_parsed_table_style();

		?>
		<table style="<?php echo esc_attr( $table_style ); ?>">
			<?php foreach ( $rows as $code => $name ) : ?>
				<tr style="<?php echo esc_attr( $tr_style ); ?>">
					<th style="<?php echo esc_attr( $td_style ); ?>width:33.3333%;"><?php echo esc_html( $name ); ?></th>
					<td style="<?php echo esc_attr( $td_style ); ?>">{{<?php echo esc_html( $code ); ?>}}</td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php
	}
}
