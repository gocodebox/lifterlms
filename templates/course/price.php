<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

?>

<div class="llms-price-wrapper">

	<p class="llms-price"><?php echo $course->get_price_html(); ?></p> 

</div>