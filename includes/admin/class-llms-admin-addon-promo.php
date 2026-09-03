<?php
/**
 * Shared HTML for in-admin add-on promotional callouts.
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since [version]
 * @version [version]
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Admin_Addon_Promo class.
 *
 * @since [version]
 */
class LLMS_Admin_Addon_Promo {

	/**
	 * Retrieve promotional HTML.
	 *
	 * @since [version]
	 *
	 * @param array $args {
	 *     Promo configuration.
	 *
	 *     @type string $icon        Dashicon slug. Default empty.
	 *     @type string $headline    Heading text.
	 *     @type string $message     Body copy.
	 *     @type string $button_text Primary button label.
	 *     @type string $button_url  Primary button URL.
	 *     @type string $below_text  Optional text below the button.
	 *     @type string $below_url   Optional URL for the below-button text.
	 * }
	 * @return string
	 */
	public static function get_html( $args ) {

		$args = wp_parse_args(
			$args,
			array(
				'icon'        => '',
				'headline'    => '',
				'message'     => '',
				'button_text' => '',
				'button_url'  => '',
				'below_text'  => '',
				'below_url'   => '',
			)
		);

		ob_start();
		?>
		<div class="llms-addon-promo" style="padding:20px;text-align:center;">
			<?php if ( $args['icon'] ) : ?>
				<div class="dashicons dashicons-<?php echo esc_attr( $args['icon'] ); ?>" style="color:#2271b1;margin-bottom:12px;"></div>
			<?php endif; ?>
			<?php if ( $args['headline'] ) : ?>
				<h3><?php echo esc_html( $args['headline'] ); ?></h3>
			<?php endif; ?>
			<?php if ( $args['message'] ) : ?>
				<p><?php echo esc_html( $args['message'] ); ?></p>
			<?php endif; ?>
			<?php if ( $args['button_text'] && $args['button_url'] ) : ?>
				<a href="<?php echo esc_url( $args['button_url'] ); ?>" target="_blank" rel="noopener noreferrer" class="llms-button-primary">
					<?php echo esc_html( $args['button_text'] ); ?>
				</a>
			<?php endif; ?>
			<?php if ( $args['below_text'] ) : ?>
				<p class="llms-addon-promo-below" style="margin-top:12px;">
					<?php if ( $args['below_url'] ) : ?>
						<a href="<?php echo esc_url( $args['below_url'] ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html( $args['below_text'] ); ?>
						</a>
					<?php else : ?>
						<?php echo esc_html( $args['below_text'] ); ?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Retrieve a product URL with campaign query args.
	 *
	 * @since [version]
	 *
	 * @param string $url     Destination URL.
	 * @param string $medium  UTM medium.
	 * @param string $campaign UTM campaign. Default "Plugin to Sale".
	 * @return string
	 */
	public static function get_utm_url( $url, $medium, $campaign = 'Plugin to Sale' ) {

		return add_query_arg(
			array(
				'utm_source'   => rawurlencode( 'LifterLMS Plugin' ),
				'utm_medium'   => rawurlencode( $medium ),
				'utm_campaign' => rawurlencode( $campaign ),
			),
			$url
		);
	}
}
