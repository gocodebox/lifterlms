<?php
/**
 * AddOn ChannelSet class file
 *
 * @package LifterLMS/CLI
 *
 * @since 0.0.1
 * @version 0.0.2
 */

namespace LifterLMS\CLI\Commands\AddOn;

/**
 * AddOn channel-set command
 *
 * @since 0.0.1
 */
trait ChannelSet {

	/**
	 * Set the update channel subscription for an add-on.
	 *
	 * ## OPTIONS
	 *
	 * <slug>
	 * : The slug of the add-on.
	 *
	 * [<channel>]
	 * : The update channel to subscribe to.
	 * ---
	 * default: 'stable'
	 * options:
	 *   - stable
	 *   - beta
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     # Subscribe the Groups add-on to the beta channel.
	 *     $ wp llms addon channel-set lifterlms-groups stable
	 *
	 *     # Subscribe to the stable channel.
	 *     $ wp llms addon channel-set lifterlms-groups stable
	 *
	 * @subcommand channel-set
	 *
	 * @since 0.0.1
	 * @since 0.0.2 Updated success message.
	 *
	 * @param array $args Indexed array of positional command arguments.
	 * @return null
	 */
	public function channel_set( $args ) {

		$addon = $this->get_addon( $args[0], true );
		$addon->subscribe_to_channel( $args[1] );
		return \WP_CLI::success( sprintf( 'Subscribed to the %s channel.', $args[1] ) );

	}

}
