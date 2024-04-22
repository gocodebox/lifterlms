<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'db.sqlite' );

/** Database username */
define( 'DB_USER', '' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', '' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '>,Q{mWzD#;M]iiDh^@<yjA2+:N|`Qrj.3usXFt@G+0E$XP|@mMQ}p9N-CAGsnq0|' );
define( 'SECURE_AUTH_KEY',  'Q{*Br_m`{~:wx!EgP/BBuR1SueDA!M!yKL_o,lDF{Bc5Y1WVpE`#K,a}IYZ~pU>R' );
define( 'LOGGED_IN_KEY',    '?~2}O:mGV/T@(~+N/C80}XWJK}m.o3Gf1lx8t`}BpJoMr-KR`]auvjJ8O`58-Ef4' );
define( 'NONCE_KEY',        ';|)d>]9u@&Lp?S$ou9!mbf_7O[}T3R^x>%6ES@^v[WRCJM>g~,p6f![[,:OWakqH' );
define( 'AUTH_SALT',        ']rPW(-w,4Ll9A$P,J#lG71S:MapRk/v$xHS$8SppJPPvT:6Yn)w^Z44xd<rM-rD8' );
define( 'SECURE_AUTH_SALT', '$)Wl.yYgZq|GqKgmnzT:!TVue%NcC8Dv1$x+)5@y9SV4eTlueuG}nyp+Mr9{!ak.' );
define( 'LOGGED_IN_SALT',   'Ga9,dq]$UvH}*up[SU*Ru}`jYWVgGB>hAN^],dWVk?`0Fo_kF-L-:iZTl3G:ka0%' );
define( 'NONCE_SALT',       'Ua%Hq]{/5%dx@91Ado@$PMv4QY?[/lJ/@Omx<m^k7]b3CmHv5v-Fh?`c2v2]X3C1' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */

define( 'DB_DIR', __DIR__ . '/data' );
define( 'DB_FILE', 'db.sqlite' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
