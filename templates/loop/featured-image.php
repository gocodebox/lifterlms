<?php

if ( ! defined( 'ABSPATH' ) ) { exit; // Exit if accessed directly
}
global $post;

if ( has_post_thumbnail( $post->ID ) ) {

	echo llms_featured_img( $post->ID, 'full' );
} elseif ( llms_placeholder_img_src() ) {

	echo llms_placeholder_img();
}
