<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

global $post, $course;

$course_not_class = get_post_custom($post->ID);

?>



<div class="llms-price-wrapper">
<?php echo 'yellow'; 
echo '<pre>';
var_dump($post);
echo '<h1>IF ANYTHING SHOWS HERE THEN get_post_custom is working</h1>';
var_dump($course_not_class);
echo '<h1>IF ANYTHING SHOWS HERE THEN I HAVE A COURSE CLASS</h1>';
var_dump($course);

?>


	<p class="llms-price"><?php echo $course //->get_price_html(); ?></p> 

</div>