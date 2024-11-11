<?php
/**
 * LifterLMS Loop End Wrapper
 *
 * @package LifterLMS/Templates
 *
 * @since   1.0.0
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;

global $post;

$model = llms_get_post( $post );

if ( ! $model->get( 'featured_pricing' ) ) {
	return;
}
?>

<div class="llms-featured-pricing">
	<p>
		<?php
		echo wp_kses_post( $model->get( 'featured_pricing' ) );
		?>
	</p>
</div>
