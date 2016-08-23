<?php
/**
 * Update LifterLMS Database to 3.0.0-beta.2
 *
 * @author   LifterLMS
 * @category Admin
 * @package  LifterLMS/Admin/Updates
 * @version  3.0.0-beta.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

global $wpdb;

$r = 'success';

/**
 * Migrate Email postmeta data
 */
$emails_subject = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_email_subject'
 	 WHERE p.post_type = 'llms_email' AND m.meta_key = '_email_subject';"
);
if ( false === $emails_subject ) {
	return false;
}

$emails_heading = $wpdb->query(
	"UPDATE {$wpdb->prefix}postmeta AS m
	 INNER JOIN {$wpdb->prefix}posts AS p ON p.ID = m.post_ID
	 SET m.meta_key = '_llms_email_heading'
 	 WHERE p.post_type = 'llms_email' AND m.meta_key = '_email_heading';"
);
if ( false === $emails_heading ) {
	return false;
}



return $r;
