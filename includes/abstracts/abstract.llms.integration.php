<?php
/**
* Analytics Widget Abstract
*
* @since   3.0.0
* @version 3.0.0
*
* @todo  do this class...
*/

if ( ! defined( 'ABSPATH' ) ) { exit; }

abstract class LLMS_Integration {

	/**
	 * Detemine if the integration had been enabled via checkbox
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	abstract public function is_enabled();

	/**
	 * Determine if the related plugin, theme, 3rd party is
	 * installed and activated
	 * if this does not apply, this should return true without
	 * doing any checks
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	abstract public function is_installed();

	/**
	 * Determine if the integration is enabled via the checkbox on the admin panel
	 * and the necessary plugin (if any) is installed and activated
	 * @return   boolean
	 * @since    3.0.0
	 * @version  3.0.0
	 */
	public function is_available() {
		return ( $this->is_enabled() && $this->is_installed() );
	}

}
