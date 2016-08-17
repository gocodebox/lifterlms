<?php
/**
 * LifterLMS Course Author Info
 *
 * @since   3.0.0
 * @version 3.0.0
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly
?>

<h3 class="llms-meta-title"><?php _e( 'Author Information', 'lifterlms' ); ?></h3>

<?php
echo llms_get_author( array(
	'avatar_size' => 48,
	'bio' => true,
) );
