<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Quiz General Settings
*
* diplays text input for oembed video
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Quiz_General {

	/**
	 * Set up metabox
	 *
	 * @return string
	 * @param string $post
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
					$html .= '<br /><span class="description">' .  __( 'Number of alowed attempts. Must be at least 1.', 'lifterlms' ) . '</span>';
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