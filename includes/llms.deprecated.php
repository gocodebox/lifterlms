<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
/**
 * Deprecated class and function stubs to prevent fatal errors
 */

// @codingStandardsIgnoreStart
/**
 * Plugin Updater Factory class used by extensions for plugin updates
 * If users update LLMS 2.0 and not their extensions they'll get a fatal error because the class no longer exists
 */
if ( ! class_exists( 'PucFactory' ) ) {

	class PucFactory {

		public function __construct() {}

		public static function buildUpdateChecker( $deprecated1, $deprecated2, $deprecated3, $deprecated4 ) {
			return new self();
		}

		public function addQueryArgFilter( $deprecated ) {}

	}

}
// @codingStandardsIgnoreEnd
