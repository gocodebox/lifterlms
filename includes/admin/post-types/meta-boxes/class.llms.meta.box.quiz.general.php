<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Quiz General Settings
*
* Handles settings metabox display and update
*/
class LLMS_Meta_Box_Quiz_General {

	/**
	 * Static output class.
	 *
	 * Displays MetaBox
	 * Calls static class metabox_options
	 * Loops through meta-options array and displays appropriate fields based on type.
	 * 
	 * @param  object $post [WP post object]
	 * 
	 * @return void
	 */
	public static function output( $post ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$allowed_attempts = get_post_meta( $post->ID, '_llms_allowed_attempts', true );
		$passing_percent = get_post_meta( $post->ID, '_llms_passing_percent', true );
		?>

		<table class="form-table">
		<tbody>
			<tr>
				<th>
					<?php
					$label  = '';
					$label .= '<label for="_llms_allowed_attempts">' . __( 'Allowed Attempts', 'lifterlms' ) . '</label> ';
					echo $label;
					?>
				</th>
				<td>
					<?php
					$html  = '';
					$html .= '<input type="text" class="code" name="_llms_allowed_attempts" id="_llms_allowed_attempts" value="' . $allowed_attempts . '"/>';
					$html .= '<br /><span class="description">' .  __( 'Number of allowed attempts. Must be at least 1.', 'lifterlms' ) . '</span>';
					echo $html;
					?>
				</td>
			</tr>

			<tr>
				<th>
					<?php
					$label  = '';
					$label .= '<label for="_llms_passing_percent">' . __( 'Passing Percentage', 'lifterlms' ) . '</label> ';
					echo $label;
					?>
				</th>
				<td>
					<?php
					$html  = '';
					$html .= '<input type="text" class="code" name="_llms_passing_percent" id="_llms_passing_percent" value="' . $passing_percent . '"/>';
					$html .= '<br /><span class="description">' .  __( 'Enter the percent required to pass quiz. DO NOT USE % (IE: enter 50 to have a passing requirement of 50%.', 'lifterlms' ) . '</span>';
					echo $html;
					?>
				</td>
			</tr>
		</tbody>
		</table>

		<?php  
	}

	/**
	 * Static save method
	 *
	 * cleans variables and saves using update_post_meta
	 * 
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 * 
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['_llms_allowed_attempts'] ) ) {
			$allowed_attempts = ( llms_clean( $_POST['_llms_allowed_attempts']  ) );
			update_post_meta( $post_id, '_llms_allowed_attempts', ( $allowed_attempts === '' ) ? '1' : $allowed_attempts );		
		}
		if ( isset( $_POST['_llms_passing_percent'] ) ) {
			$passing_percent = ( llms_clean( $_POST['_llms_passing_percent']  ) );
			update_post_meta( $post_id, '_llms_passing_percent', ( $passing_percent === '' ) ? '0' : $passing_percent );		
		}
	}

}