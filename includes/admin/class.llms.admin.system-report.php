<?php
/**
 * Admin System Report
 *
 * @package LifterLMS/Admin/Classes
 *
 * @since 2.1.0
 * @version 7.1.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin System Report Class.
 *
 * @since 2.1.0
 */
class LLMS_Admin_System_Report {

	/**
	 * Output the system report
	 *
	 * @since 2.1.0
	 * @since 3.0.0 Unknown.
	 *
	 * @return void
	 */
	public static function output() {

		echo '<div class="wrap lifterlms">';

		self::output_copy_box();

		foreach ( LLMS_Data::get_data( 'system_report' ) as $key => $data ) {

			if ( is_array( $data ) ) {

				self::output_section( $key, $data );

			}
		}

		echo '</div>';

	}

	/**
	 * Output the copy for support box.
	 *
	 * @since 2.1.0
	 * @since 3.11.2 Unknown.
	 * @since 7.1.0 Style and HTML structure update.
	 * @since 7.1.1 Use the right CSS selector to target the elements to include into the system's report copy.
	 *
	 * @return void
	 */
	public static function output_copy_box() {
		?>
		<div class="llms-setting-group top">
			<p class="llms-label"><?php _e( 'Support', 'lifterlms' ); ?></p>
			<div id="llms-debug-report">
				<textarea style="display:none;width: 100%" rows="12" readonly="readonly"></textarea>
				<p class="submit">
					<button id="copy-for-support" class="llms-button-primary"><?php _e( 'Copy for Support', 'lifterlms' ); ?></button>
					<a class="llms-button-secondary" href="https://lifterlms.com/my-account/my-tickets/?utm_source=LifterLMS%20Plugin&utm_medium=System%20Report&utm_campaign=Get%20Help&utm_content=button001" target="_blank"><?php _e( 'Get Help', 'lifterlms' ); ?></a>
				</p>
			</div>
		</div>
		<script>
			jQuery( document ).ready( function( $ ) {
				var $textarea = $( '#llms-debug-report textarea' );

				$( '.llms-setting-group' ).each( function( index, element ) {
					var title = $( this ).find( '.llms-label' ).text();
					title = title + '\n' + '-------------------------------------------';
					var val = $( this ).find( 'li' ).text().replace(/  /g, '').replace(/\t/g, '').replace(/\n\n/g, '\n');
					$textarea.val( $textarea.val() + title + '\n' + val + '\n\n' );
				} );

				$( '#copy-for-support' ).on( 'click', function() {
					$textarea.show().select();
					try {
						if ( ! document.execCommand( 'copy' ) ) {
							throw 'Not allowed.';
						}
					} catch( e ) {
						alert( 'copy the text below' );
					}
				} );

				$textarea.on( 'click', function() {
					$( this ).select();
				} );
			});
		</script>
		<?php
	}

	/**
	 * Output a section of data in the system report
	 *
	 * @since 3.0.0
	 * @since 3.11.2 Unknown.
	 * @since 4.13.0 Don't strip underscores when outputting the constant keys.
	 * @since 7.1.0 Style and HTML structure update.
	 *
	 * @param string $section_title Title / key of the section.
	 * @param arry   $data          Array of data for the section.
	 * @return void
	 */
	public static function output_section( $section_title, $data ) {

		if ( 'plugins' === $section_title ) {

			$data = $data['active'];

		}

		?>
		<div class="llms-setting-group">
			<p class="llms-label"><?php echo self::title( $section_title ); ?></p>
			<div class="llms-list">
				<ul>
					<?php foreach ( $data as $key => $val ) : ?>
						<li><p>
						<?php if ( 'plugins' === $section_title ) : ?>
							<?php self::plugin_item( $val ); ?>
						<?php elseif ( 'template_overrides' === $section_title ) : ?>
							<?php self::template_item( $val ); ?>
						<?php else : ?>
							<?php echo 'constants' === $section_title ? $key : self::title( $key ); ?>: <strong><?php self::value( $val ); ?></strong>
						<?php endif; ?>
						</p></li>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Output data related to an active plugin in the system report
	 *
	 * @since 3.0.0
	 *
	 * @param array $data Array of plugin data.
	 * @return void
	 */
	private static function plugin_item( $data ) {
		?>
		<a href="<?php echo $data['PluginURI']; ?>"><?php echo $data['Name']; ?></a>: <strong><?php echo $data['Version']; ?></strong>
		<?php
	}

	/**
	 * Output data related to an overridden template system report
	 *
	 * @param array $data Array of template data.
	 * @return   void
	 * @since    3.11.2
	 * @version  3.11.2
	 */
	private static function template_item( $data ) {
		echo '<strong>' . $data['template'] . ' (ver: ' . $data['core_version'] . ')</strong>: ';
		echo '<code>' . $data['location'] . '</code> (ver: ' . $data['version'] . ')';
	}


	/**
	 * Output the title for an item in the system report.
	 *
	 * @since 3.0.0
	 * @since 7.1.0 Fixed misspelled WordPress.
	 *
	 * @param string $key Title.
	 * @return void
	 */
	private static function title( $key ) {

		$key = ucwords( str_replace( '_', ' ', $key ) );

		// Fix for capital P.
		if ( 'Wordpress' === $key ) { // phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled
			$key = 'WordPress';
		}

		echo $key;

	}

	/**
	 * Output the value of an item in the system report
	 *
	 * @since 3.0.0
	 *
	 * @param string $val Value.
	 * @return void
	 */
	private static function value( $val ) {

		echo $val;

	}

}
