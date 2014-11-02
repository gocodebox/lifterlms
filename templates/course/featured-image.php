<?php
/**
 * @author 		codeBOX
 * @package 	lifterLMS/Templates
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<h1 class="llms-featured-image">

<?php

if ( has_post_thumbnail() ) {
	the_post_thumbnail();
} 

?></h1>