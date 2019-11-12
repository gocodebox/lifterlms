<?php
/**
 * Plugin installation scripts.
 *
 * @package LifterLMS_REST/Classes
 *
 * @since 1.0.0-beta.1
 * @version 1.0.0-beta.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin installation scripts.
 *
 * @since 1.0.0-beta.1
 */
class LLMS_REST_Install {

	/**
	 * Initialize the install class.
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return   void
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'check_version' ), 5 );
		add_filter( 'llms_install_get_schema', array( __CLASS__, 'get_schema' ), 20, 2 );
	}

	/**
	 * Checks the current LLMS version and runs installer if required
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return   void
	 */
	public static function check_version() {
		if ( ! defined( 'IFRAME_REQUEST' ) && get_option( 'llms_rest_version' ) !== LLMS_REST_API()->version ) {
			self::install();
			do_action( 'llms_rest_updated' );
		}

	}

	/**
	 * Adds REST API Keys table to the LifterLMS DB Table Schema
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @see LLMS_Install::get_schema()
	 *
	 * @param string $schema String of DB table creation statements.
	 * @param string $collate Collation string.
	 * @return string
	 */
	public static function get_schema( $schema, $collate ) {

		global $wpdb;

		$schema .= "
CREATE TABLE `{$wpdb->prefix}lifterlms_api_keys` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `permissions` varchar(10) NOT NULL,
  `consumer_key` char(64) NOT NULL,
  `consumer_secret` char(43) NOT NULL,
  `truncated_key` char(7) NOT NULL,
  `last_access` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `consumer_key` (`consumer_key`),
  KEY `consumer_secret` (`consumer_secret`)
) $collate;
CREATE TABLE `{$wpdb->prefix}lifterlms_webhooks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL,
  `name` text NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `delivery_url` text NOT NULL,
  `secret` text NOT NULL,
  `topic` varchar(255) NOT NULL,
  `created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `updated` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `failure_count` smallint(3) unsigned NOT NULL DEFAULT '0',
  `pending_delivery` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) $collate;

		";

		return $schema;

	}

	/**
	 * Core install function
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @return void
	 */
	public static function install() {

		if ( ! is_blog_installed() ) {
			return;
		}

		do_action( 'llms_rest_before_install' );

		LLMS_Roles::install();
		LLMS_Install::create_tables();
		self::update_version();

		do_action( 'llms_rest_after_install' );

	}


	/**
	 * Update the LifterLMS rest version record to the latest version
	 *
	 * @since 1.0.0-beta.1
	 *
	 * @param  string $version version number.
	 * @return void
	 */
	public static function update_version( $version = null ) {
		delete_option( 'llms_rest_version' );
		add_option( 'llms_rest_version', is_null( $version ) ? LLMS_REST_API()->version : $version );
	}

}

LLMS_REST_Install::init();
