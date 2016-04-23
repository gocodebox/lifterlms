<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Admin System Report Class
*
* System Report field Factory
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_System_Report {

	public static function output() {
		echo '<div class="wrap lifterlms">';

		self::get_wp_environment_box();
		self::get_server_environment_box();
		self::get_active_plugins_box();
		self::get_settings_box();
		self::get_lifterlms_pages_box();
		self::get_theme_box();
		self::add_debug_report_box();

		echo '</div>';
	}

	public static function get_wp_environment_box() {
		?>
		<div class="llms-widget-full top">
			<div class="llms-widget settings-box">
				<p class="llms-label"><?php _e( 'WordPress Environment', 'lifterlms' ); ?></p>
				<div class="llms-list">
					<ul>
						<li>
							<p><?php _e( 'Home URL', 'lifterlms' ); ?>: <strong><?php form_option( 'home' ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Site URL', 'lifterlms' ); ?>: <strong><?php form_option( 'siteurl' ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'LifterLMS Version', 'lifterlms' ); ?>: <strong><?php echo esc_html( LLMS()->version ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Wordpress Version', 'lifterlms' ); ?>: <strong><?php bloginfo( 'version' ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Wordpress Multisite', 'lifterlms' ); ?>: <strong><?php if ( is_multisite() ) { echo '&#10004;'; } else { echo '&ndash;'; } ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Wordpress Memory Limit', 'lifterlms' ); ?>: <strong><?php
									$memory = self::llms_let_to_num( WP_MEMORY_LIMIT );
							if ( function_exists( 'memory_get_usage' ) ) {
								$system_memory = self::llms_let_to_num( @ini_get( 'memory_limit' ) );
								$memory        = max( $memory, $system_memory );
							}
							if ( $memory < 67108864 ) {
								echo '<mark class="error">' . sprintf( __( '%s - We recommend setting memory to at least 64MB. See: %s', 'lifterlms' ), size_format( $memory ), '<a href="http://codex.wordpress.org/Editing_wp-config.php#Increasing_memory_allocated_to_PHP" target="_blank">' . __( 'Increasing memory allocated to PHP', 'woocommerce' ) . '</a>' ) . '</mark>';
							} else {
								echo '<mark class="yes">' . size_format( $memory ) . '</mark>';
							}
									?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Wordpress Debug Mode', 'lifterlms' ); ?>: <strong><?php if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) : ?>
											<mark class="yes">&#10004;</mark><?php else : ?><mark class="no">&ndash;</mark><?php endif; ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Wordpress Language', 'lifterlms' ); ?>: <strong><?php echo get_locale(); ?></strong></p>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	public static function get_server_environment_box() {
		?>
		<div class="llms-widget-full top">
			<div class="llms-widget settings-box">
				<p class="llms-label"><?php _e( 'Server Environment', 'lifterlms' ); ?></p>
				<div class="llms-list">
					<ul>
						<li>
							<p><?php _e( 'Server Info', 'lifterlms' ); ?>: <strong><?php echo esc_html( $_SERVER['SERVER_SOFTWARE'] ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'PHP Version', 'lifterlms' ); ?>: <strong><?php
									// Check if phpversion function exists.
							if ( function_exists( 'phpversion' ) ) {
								$php_version = phpversion();
								if ( version_compare( $php_version, '5.3', '<' ) ) {
									echo '<mark class="error">' . sprintf( __( '%s - We recommend a minimum PHP version of 5.3.', 'lifterlsm' ), esc_html( $php_version ) ) . '</mark>';
								} else {
									echo '<mark class="yes">' . esc_html( $php_version ) . '</mark>';
								}
							} else {
								_e( "Couldn't determine PHP version because phpversion() doesn't exist.", 'lifterlsm' );
							}
									?></strong></p>
						</li>
						<li>
							<p><?php _e( 'PHP Max Post Size', 'lifterlms' ); ?>: <strong><?php echo size_format( self::llms_let_to_num( ini_get( 'post_max_size' ) ) ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Max Upload Size', 'lifterlms' ); ?>: <strong><?php echo size_format( wp_max_upload_size() ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'PHP Time Limit', 'lifterlms' ); ?>: <strong><?php echo ini_get( 'max_execution_time' ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'PHP Max Input Vars', 'lifterlms' ); ?>: <strong><?php echo ini_get( 'max_input_vars' ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Default Timezone is UTC', 'lifterlms' ); ?>: <strong><?php
									$default_timezone = date_default_timezone_get();
							if ( 'UTC' !== $default_timezone ) {
								echo '<mark class="error">&#10005; ' . sprintf( __( 'Default timezone is %s - it should be UTC', 'woocommerce' ), $default_timezone ) . '</mark>';
							} else {
								echo '<mark class="yes">&#10004;</mark>';
							} ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'MySQL Version', 'lifterlms' ); ?>: <strong><?php
							/** @global wpdb $wpdb */
							global $wpdb;
							echo $wpdb->db_version();
							?></strong></p>
						</li>
						<li>
							<p><?php _e( 'GZip', 'lifterlms' ); ?>: <strong><?php
							if ( is_callable( 'gzopen' ) ) {
								echo '<mark class="yes">&#10004;</mark>';
							} else {
								echo '<mark class="no">&#10005;</mark>';
							} ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'DOMDocument', 'lifterlms' ); ?>: <strong><?php
							if ( class_exists( 'DOMDocument' ) ) {
								echo '<mark class="yes">&#10004;</mark>';
							} else {
								echo '<mark class="no">&#10005;</mark>';
							} ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'SoapClient', 'lifterlms' ); ?>: <strong><?php
							if ( class_exists( 'SoapClient' ) ) {
								echo '<mark class="yes">&#10004;</mark>';
							} else {
								echo '<mark class="no">&#10005;</mark>';
							} ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'fsockopen/cURL', 'lifterlms' ); ?>: <strong><?php
							if ( function_exists( 'fsockopen' ) || function_exists( 'curl_init' ) ) {
								echo '<mark class="yes">&#10004;</mark>';
							} else {
								echo '<mark class="no">&#10005;</mark>';
							} ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Multibyte String', 'lifterlms' ); ?>: <strong><?php
							if ( extension_loaded( 'mbstring' ) ) {
								echo '<mark class="yes">&#10004;</mark>';
							} else {
								echo '<mark class="no">&#10005;</mark>';
							} ?></strong></p>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	public static function get_active_plugins_box() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		echo '<div class="llms-widget-full top">
                <div class="llms-widget settings-box">
                    <p class="llms-label">' . __( 'Active Plugins', 'lifterlms' ) . '</p>
                    <div class="llms-list">
                        <ul>';

		foreach ($active_plugins as $plugin) {
			$plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$version_string = '';
			$network_string = '';

			if ( ! empty( $plugin_data['Name'] ) ) {
				$plugin_name = esc_html( $plugin_data['Name'] );
				if ( ! empty( $plugin_data['PluginURI'] )) {
					$plugin_name = '<a href="' . esc_url( $plugin_data['PluginURI'] ) . '" title="' . esc_attr__( 'Visit plugin homepage', 'lifterlms' ) . '" target="_blank">' . $plugin_name . '</a>';
				}
			}

			if ( ! empty( $version_data['version'] ) && version_compare( $version_data['version'], $plugin_data['Version'], '>' ) ) {
				$version_string = ' &ndash; <strong style="color:red;">' . esc_html( sprintf( _x( '%s is available', 'Version info', 'lifterlms' ), $version_data['version'] ) ) . '</strong>';
			}
			if ( $plugin_data['Network'] != false ) {
				$network_string = ' &ndash; <strong style="color:black;">' . __( 'Network enabled', 'lifterlms' ) . '</strong>';
			}
			?>
			<li>
				<p><?php echo $plugin_name; ?>: <strong><?php echo sprintf( _x( 'by %s', 'by author', 'lifterlsm' ), $plugin_data['Author'] ) . ' &ndash; ' . esc_html( $plugin_data['Version'] ) . $version_string . $network_string; ?></strong></p>
			</li>
			<?php
		}

		echo '</ul>
                </div>
            </div>
        </div>';
	}

	public static function get_settings_box() {
		?>
		<div class="llms-widget-full top">
			<div class="llms-widget settings-box">
				<p class="llms-label"><?php _e( 'LifterLMS Settings', 'lifterlms' ); ?></p>
				<div class="llms-list">
					<ul>
						<li>
							<p><?php _e( 'Currency', 'lifterlms' ); ?>: <strong><?php echo get_lifterlms_currency(); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Auto-Advance Lesson', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_autoadvance', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Redirect Members to Checkout', 'lifterlms' ); ?>: <strong><?php echo get_option( 'redirect_to_checkout', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Display Student Memberships on Account Page', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_enable_myaccount_memberships_list', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Enable Paypal', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_gateway_enable_paypal', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Enable Sandbox Mode', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_gateways_paypal_enable_sandbox', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Enable Debug Mode', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_gateways_paypal_enable_debug', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Enable BuddyPress', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_buddypress_enabled', false ); ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Enable WooCommerce', 'lifterlms' ); ?>: <strong><?php echo get_option( 'lifterlms_woocommerce_enabled', false ); ?></strong></p>
						</li>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	public static function get_lifterlms_pages_box() {

		$pages = array(
			'shop' => 'Courses',
			'memberships' => 'Memberships',
			'myaccount' => 'My Account',
			'checkout' => 'Checkout',
		);

		echo '<div class="llms-widget-full top">
                <div class="llms-widget settings-box">
                    <p class="llms-label">' . __( 'LifterLMS Pages', 'lifterlms' ) . '</p>
                    <div class="llms-list">
                        <ul>';

		foreach ( $pages as $key => $name ) {

			$page_id = llms_get_page_id( $key );
			$page_name = '';
			$result = '';

			if ($page_id != -1) {
				$page_name = '<a href="' . get_edit_post_link( $page_id ) . '" title="' . esc_html( $name ) . '">' . esc_html( $name ) . '</a>';
			}

			if ($page_id == -1) {
				$result = '<mark class="error">' . __( 'Page not set', 'lifterlms' ) . '</mark>';
			} else {
				$page = get_post( $page_id );
				if (empty( $page )) {
					$result = '<mark class="error">' . sprintf( __( 'Page does not exist', 'lifterlms' ) ) . '</mark>';
				}
			}
			?>
			<li>
				<p><?php echo $name; ?>: <strong><?php echo $page_name . ' ' . $result; ?></strong></p>
			</li>
			<?php
		}

		echo '</ul>
                </div>
            </div>
        </div>';
	}

	public static function get_theme_box() {
		include_once( ABSPATH . 'wp-admin/includes/theme-install.php' );
		$active_theme = wp_get_theme();
		// @codingStandardsIgnoreStart
		$theme_version = $active_theme->Version;
		$theme_template = $active_theme->Template;
		// @codingStandardsIgnoreEnd
		?>

		<div class="llms-widget-full top">
			<div class="llms-widget settings-box">
				<p class="llms-label"><?php _e( 'Current Theme', 'lifterlms' ); ?></p>
				<div class="llms-list">
					<ul>
						<li>
							<p><?php _e( 'Theme', 'lifterlms' ); ?>: <strong><?php echo $active_theme; ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Theme Version', 'lifterlms' ); ?>: <strong><?php echo $theme_version; ?></strong></p>
						</li>
						<li>
							<p><?php _e( 'Child Theme', 'lifterlms' ); ?>: <strong><?php
									echo is_child_theme() ? '<mark class="yes">&#10004;</mark>' : '&#10005;'; ?></strong></p>
						</li>
						<?php
						if ( is_child_theme() ) :
							$parent_theme = wp_get_theme( $theme_template );
							?>
							<li>
								<p><?php _e( 'Parent Theme', 'lifterlms' ); ?>: <strong><?php echo $parent_theme; ?></strong></p>
							</li>
						<?php endif ?>
					</ul>
				</div>
			</div>
		</div>
		<?php
	}

	public static function add_debug_report_box() {
		?>
		<div class="llms-widget-full top">
			<div class="llms-widget">
				<p class="llms-label"><?php _e( 'Copy System Report for Support', 'lifterlms' ); ?></p>
				<p class="llms-description">
					<div id="llms-debug-report">
						<textarea rows="12" readonly="readonly"></textarea>
						<p class="submit"><button id="copy-for-support" class="button-primary" href="#" ><?php _e( 'Copy for Support', 'lifterlms' ); ?></button></p>
					</div>
				</p>
			</div>
		</div>
		<script>
			jQuery( document ).ready( function( $ ) {
				var $textArea = $( '#llms-debug-report' ).find( 'textarea' );

				$(".llms-widget.settings-box").each( function( index, element ) {

					var title = $(this).find('.llms-label').text();
					var val = $(this).find('li').text().replace(/  /g, '').replace(/\t/g, '').replace(/\n\n/g, '\n');
					$textArea.val($textArea.val() + title + '\n' + val + '\n\n');
				});

				$('#copy-for-support').on('click', function() {
					$( '#llms-debug-report' ).find( 'textarea' ).select();
					try {
						if(!document.execCommand('copy')) throw 'Not allowed.';
					} catch(e) {
						copyElement.remove();
						console.log("document.execCommand('copy'); is not supported");
						var text = $( '#debug-report' ).find( 'textarea' ).val();
						prompt('Copy the text below. (ctrl c, enter)', text);
					}
				})
			});
		</script>
		<?php
	}

	public static function llms_let_to_num( $size ) {
		$l   = substr( $size, -1 );
		$ret = substr( $size, 0, -1 );
		switch ( strtoupper( $l ) ) {
			case 'P':
				$ret *= 1024;
			case 'T':
				$ret *= 1024;
			case 'G':
				$ret *= 1024;
			case 'M':
				$ret *= 1024;
			case 'K':
				$ret *= 1024;
		}
		return $ret;
	}

}
