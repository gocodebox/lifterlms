<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box General
*
* diplays text input for oembed general
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Lesson_Options {

	/**
	 * Set up general input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {

    	$days_before_avalailable = get_post_meta( $post->ID, '_days_before_avalailable', true );
    	?>

    	<table class="form-table">
			<tbody>
				<tr>
					<th><label for="'_days_before_avalailable'">Drip Content (in days)</label></th>
					<td>
						<input type="text" name="_days_before_avalailable" id="_days_before_avalailable" value="<?php echo $days_before_avalailable; ?>"/>
						<br /><span class="description">Number of days before lesson is available after course begins (date of purchase or set start date)</span>
					</td>
				</tr>
			</tbody>
		</table>

    <?php
	}

	public static function save( $post_id, $post ) {
		global $wpdb;

		$days = ( llms_clean( $_POST['_days_before_avalailable']  ) );
		update_post_meta( $post_id, '_days_before_avalailable', ( $days === '' ) ? '' : $days );
	}

}