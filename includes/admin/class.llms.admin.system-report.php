<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Admin System Report Class
*
* System Report field Factory
*
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Admin_System_Report {
    /**
     * analytics Page output tabs
     *
     * @return void
     */
    public static function output() {
        self::get_wp_environment_box();
        self::get_server_environment_box();
        self::get_active_plugins_box();
    }

    public static function get_wp_environment_box() {
        ?>
        <div class="llms-widget-full top">
            <div class="llms-widget">
                <p class="llms-label"><?php _e( 'WordPress Environment', 'lifterlms' ); ?></p>
                <p class="llms-description"></p>
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
                            <p><?php _e( 'Wordpress Version', 'lifterlms' ); ?>: <strong><?php bloginfo('version'); ?></strong></p>
                        </li>
                        <li>
                            <p><?php _e( 'Wordpress Multisite', 'lifterlms' ); ?>: <strong><?php if ( is_multisite() ) echo '&#10004;'; else echo '&ndash;'; ?></strong></p>
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
                                            <mark class="yes">&#10004;</mark>
                                        <?php else : ?>
                                            <mark class="no">&ndash;</mark>
                                        <?php endif; ?></strong></p>
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
            <div class="llms-widget">
                <p class="llms-label"><?php _e( 'Server Environment', 'lifterlms' ); ?></p>
                <p class="llms-description"></p>
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
                    </ul>
                </div>
            </div>
        </div>
        <?php
    }

    public static function get_active_plugins_box() {
        $active_plugins = (array) get_option( 'active_plugins', array() );

        echo '<div class="llms-widget-full top">
                <div class="llms-widget">
                    <p class="llms-label">' . __( 'Active Plugins', 'lifterlms' ) . '</p>
                    <p class="llms-description"></p>
                    <div class="llms-list">
                        <ul>';

        foreach($active_plugins as $plugin) {
            $plugin_data    = @get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
            $version_string = '';
            $network_string = '';

            if ( ! empty( $plugin_data['Name'] ) ) {
                // Link the plugin name to the plugin url if available.
                $plugin_name = esc_html($plugin_data['Name']);
                if (!empty($plugin_data['PluginURI'])) {
                    $plugin_name = '<a href="' . esc_url($plugin_data['PluginURI']) . '" title="' . esc_attr__('Visit plugin homepage', 'lifterlms') . '" target="_blank">' . $plugin_name . '</a>';
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

        echo "</ul>
                </div>
            </div>
        </div>";
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
