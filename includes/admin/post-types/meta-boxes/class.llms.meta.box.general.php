<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Meta Box General
*
* diplays text input for oembed general
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_General {

	/**
	 * Set up general input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {

		$custom = get_post_custom( $post->ID );
		$has_prerequisite = get_post_meta( $post->ID, '_has_prerequisite', true );
		$prerequisite = get_post_meta( $post->ID, '_prerequisite', true );
	?>

	<table class="form-table">
		<tbody>
			<tr>
				<th><label for="_has_prerequisite"><?php _e( 'Has a Prerequisite', 'lifterlms' ); ?></label></th>
				<td>
					<input id="llms_has_prerequisite" type="checkbox" name="_has_prerequisite" <?php if ( $has_prerequisite == true ) { ?>checked="checked"<?php } ?> />
				</td>
			</tr>

			<tr class="llms_select_prerequisite">
				<?php
				$prerquisite_args = array(
					'posts_per_page'   => -1,
					'orderby'          => 'title',
					'order'            => 'ASC',
					'post_type'        => $post->post_type,
					'post__not_in'	   => array( $post->ID ),
					'suppress_filters' => true,
				);
				?>

				<?php
				$all_posts = get_posts( $prerquisite_args );
				if ($all_posts) : ?>

					<th><label for="_prerequisite"><?php _e( 'Select a prerequisite', 'lifterlms' ); ?></label></th>
					<td>
						<form action="" id="myform">
							<select id="_prerequisite" name="_prerequisite">
							    <option value="">None</option>
								<?php foreach ( $all_posts as $p  ) :
									if ( $p->ID == $prerequisite ) {
								?>
									<option value="<?php echo $p->ID; ?>" selected="selected"><?php echo $p->post_title; ?></option>

								<?php } else { ?>
									<option value="<?php echo $p->ID; ?>"><?php echo $p->post_title; ?></option>

								<?php } ?>
								<?php endforeach; ?>
					 		</select>
						</form>
					</td>

				<?php endif ?>
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

		$general = (isset( $_POST['_has_prerequisite'] ) ? true : false);
		update_post_meta( $post_id, '_has_prerequisite', ( $general === '' ) ? '' : $general );

		if ( isset( $_POST['_prerequisite'] ) ) {

			//update prerequisite select
			$prerequisite = ( llms_clean( $_POST['_prerequisite'] ) );
			update_post_meta( $post_id, '_prerequisite', ( $prerequisite === '' ) ? '' : $prerequisite );
		}
		if ( isset( $_POST['_prerequisite_track'] ) ) {

			//update prerequisite select
			$prerequisite_track = ( llms_clean( $_POST['_prerequisite_track'] ) );
			update_post_meta( $post_id, '_prerequisite_track', ( $prerequisite_track === '' ) ? '' : $prerequisite_track );
		}

	}

}
