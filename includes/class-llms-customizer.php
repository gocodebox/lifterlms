<?php
/**
 * Manage customizer controls for LifterLMS fields and pages.
 *
 * @package  LifterLMS/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Customizer class..
 *
 * @since [version]
 */
class LLMS_Customizer {

	/**
	 * Constructor.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function __construct() {

		add_action( 'customize_register', array( $this, 'register' ) );
		add_action( 'customize_controls_print_scripts', array( $this, 'print_scripts' ), 50 );

	}

	/**
	 * Retrieves url to use for previewing the Checkout Page.
	 *
	 * Will get the URL for the first access plan found in the database and falls back to the checkout
	 * page (with no plan parameters) which won't show the checkout fields.
	 *
	 * @since [version]
	 *
	 * @return string
	 */
	private function get_checkout_url() {

		$query = new WP_Query( array(
			'post_type' => 'llms_access_plan',
			'post_status' => 'publish',
			'posts_per_page' => 1,
		) );

		if ( $query->have_posts() ) {
			$plan = llms_get_post( $query->posts[0] );
			if ( is_a( $plan, 'LLMS_Access_Plan' ) ) {
				return $plan->get_checkout_url( false );
			}
		}

		return llms_get_page_url( 'checkout' );

	}

	public function print_scripts() {

		?>
		<script>( function( $ ) {

			$( document ).on( 'ready', function() {

				wp.customize.section( 'llms_checkout', function( section ) {
					section.expanded.bind( function( isExpanded ) {
						if ( isExpanded ) {
							wp.customize.previewer.previewUrl.set( '<?php echo esc_js( $this->get_checkout_url() ); ?>' );
						}
					} );
				} );

			} );

		}( jQuery ) );</script>
		<?php
	}

	/**
	 * Register settings to the customizer.
	 *
	 * @since [version]
	 *
	 * @link https://developer.wordpress.org/reference/hooks/customize_register/
	 *
	 * @param WP_Customize_Manager $wp_customize Customizer manager instnace.
	 *
	 * @return void
	 */
	public function register( $wp_customize ) {

		$wp_customize->add_panel(
			'lifterlms',
			array(
				'priority'       => 500,
				// 'capability'     => 'manage_plugins',
				'theme_supports' => '',
				'title'          => __( 'LifterLMS', 'lifterlms' ),
			)
		);

		$wp_customize->add_section(
			'llms_checkout',
			array(
				'title'       => __( 'Checkout', 'lifterlms' ),
				'priority'    => 20,
				'panel'       => 'lifterlms',
				'description' => __( 'These options let you change the appearance of the WooCommerce checkout.', 'lifterlms' ),
			)
		);

			$wp_customize->add_setting(
				'llms_checkout_field_phone',
				array(
					'type'              => 'option',
					// 'capability'        => 'manage_plugins',
					'default'           => 'required',
					// 'sanitize_callback' => function( $wut, $yes ) { return $wut; },
				)
			);

			$wp_customize->add_control(
				'llms_checkout_field_phone',
				array(
					/* Translators: %s field name. */
					'label'    => sprintf( __( '%s field', 'woocommerce' ), 'Phone' ),
					'section'  => 'llms_checkout',
					'setting'  => 'llms_checkout_field_phone',
					'type'     => 'select',
					'choices'  => array(
						'hidden'   => __( 'Hidden', 'woocommerce' ),
						'optional' => __( 'Optional', 'woocommerce' ),
						'required' => __( 'Required', 'woocommerce' ),
					),
				)
			);

	}

}

return new LLMS_Customizer();
