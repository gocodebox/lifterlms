<?php
/**
 * Single Achievement Template
 * @since    1.0.0
 * @version  3.14.6
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

?>

<a class="llms-achievement" data-id="<?php echo $achievement->get( 'id' ); ?>" href="#<?php printf( 'achievement-%d', $achievement->get( 'id' ) ); ?>" id="<?php printf( 'llms-achievement-%d', $achievement->get( 'id' ) ); ?>">

	<?php do_action( 'lifterlms_before_achievement', $achievement ); ?>

	<div class="llms-achievement-image"><?php echo $achievement->get_image_html(); ?></div>

	<h4 class="llms-achievement-title"><?php echo $achievement->get( 'achievement_title' ); ?></h4>

	<div class="llms-achievement-info">
		<div class="llms-achievement-content"><?php echo $achievement->get( 'content' ); ?></div>
		<div class="llms-achievement-date"><?php printf( _x( 'Awarded on %s', 'achievement earned date','lifterlms' ), $achievement->get_earned_date() ); ?></div>
	</div>

	<?php do_action( 'lifterlms_after_achievement', $achievement ); ?>

</a>

