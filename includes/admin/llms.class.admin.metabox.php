<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

// include all classes for each of the metabox types
foreach (glob( LLMS_PLUGIN_DIR . '/includes/admin/post-types/meta-boxes/fields/*.php' ) as $filename) {
	include_once $filename;
}

/**
* Admin Settings Class
*
* Settings field Factory
*
* @author codeBOX
* @project lifterLMS
*/
abstract class LLMS_Admin_Metabox {

	/**
	 * Function responsible for outputing the meta box.
	 * Parses the array of fields passed to it, then calls
	 * a helper method to generate the actual html
	 *
	 * @param  object $post Global WP post object
	 * @param  array $meta_fields_course_main Array of fields to be displayed in the box
	 * @return void
	 */
	public static function new_output( $post, $meta_fields_course_main ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		ob_start(); ?>

		<div class="container">
			<!--hidden field to pass info to js to load correct classes-->
			<input type="hidden" name="llms_post_edit_type" id="llms_post_edit_type" value="course">

			<!--Begin Tab Navigation-->
			<ul class="tabs">
				<?php
				$i = 0;
				foreach ($meta_fields_course_main as $meta_box) :
					$i++
				?>
					<li class="tab-link d-1of6 t-1of2 m-all
						<?php echo $i === 1 ? 'current' : ''; ?>" data-tab="tab-<?php echo $i; ?>">
						<?php echo $meta_box['title']; ?></li>

				<?php endforeach; ?>
			</ul> <!--End Tab Navigation-->

			<?php
			$i = 0;
			foreach ($meta_fields_course_main as $meta_box) :
				$i++
			?>
			<div id="tab-<?php echo $i; ?>" class="tab-content <?php echo $i === 1 ? 'current' : ''; ?>">

				<ul>
					<?php foreach ( $meta_box['fields'] as $field ) :
						$field_class_name = str_replace('{TOKEN}',
							ucfirst( strtr( preg_replace_callback( '/(\w+)/', create_function( '$m','return ucfirst($m[1]);' ), $field['type'] ),'-','_' ) ),
						'LLMS_Metabox_{TOKEN}_Field');
						$field_class = new $field_class_name($field);
						$field_class->Output();
						unset( $field_class );
					endforeach; ?>
				</ul>

			</div>

			<?php endforeach; ?>
			</div><!-- container -->

		<?php echo ob_get_clean();
	}
}
