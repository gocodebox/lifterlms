<?php
/**
 * Update LifterLMS Database to 3.0.0-beta.10
 *
 * @author   LifterLMS
 * @category Admin
 * @package  LifterLMS/Admin/Updates
 * @version  3.0.0-beta.10
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;

$r = 'success';


/**
 * rewrite audio meta keys for consistency
 */
$audios = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_audio_embed'
 	 WHERE p.post_type = 'lesson' AND m.meta_key = '_audio_embed';"
);
if ( false === $audios ) {
	return false;
}

/**
 * rewrite video meta keys for consistency
 */
$videos = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_video_embed'
 	 WHERE p.post_type = 'lesson' AND m.meta_key = '_video_embed';"
);
if ( false === $videos ) {
	return false;
}



return $r;
