<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Students
*
* Allows users to add and remove students from a course. Only displays on course post. 
*/
class LLMS_Meta_Box_Students {

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

    	$enrolled_students = array();
    	$users_not_enrolled = array();
    	$enrolled_student_ids = array();

    	$user_args = array(
    		'blog_id'      => $GLOBALS['blog_id'],
			'include'      => array(),
			'exclude'      => $enrolled_students,
			'orderby'      => 'login',
			'order'        => 'ASC',
			'count_total'  => false,
			'fields'       => 'all',
    	);
    	$all_users = get_users( $user_args );

    	foreach ( $all_users as $key => $value  ) :
    		if ( llms_is_user_enrolled( $value->ID, $post->ID ) ) {
    			$enrolled_students[$value->ID] = $value->display_name;
    			array_push($enrolled_student_ids, $value->ID);

    		}

    	endforeach;

    	$user_args = array(
    		'blog_id'      => $GLOBALS['blog_id'],
			'include'      => array(),
			'exclude'      => $enrolled_student_ids,
			'orderby'      => 'login',
			'order'        => 'ASC',
			'count_total'  => false,
			'fields'       => 'all',
    	);
    	$users_not_enrolled = get_users( $user_args );
    	?>

		<table class="form-table">
			<tbody>

				<tr>
					<th><label for="'_days_before_avalailable'">Add Students</label></th>
					<td>

						<select id="add_new_user" name="add_new_user">
						<option value="" selected>Select a user...</option>
							<?php foreach ( $users_not_enrolled as $key => $value  ) : ?>
								<option value="<?php echo $value->ID; ?>"><?php echo $value->display_name; ?></option>
							<?php endforeach; ?>
				 		</select>
				 		<input type="submit" class="button metabox_submit" name="add_student_submit" value="<?php _e( 'Add', 'lifterlms' ); ?>" />
					</td>
				</tr>
				<tr>
					<th><label for="'_days_before_avalailable'">Remove Students</label></th>
					<td>
						<select id="remove_student" name="remove_student">
						<option value="" selected>Select a student...</option>
							<?php foreach ( $enrolled_students as $key => $value  ) : ?>
								<option value="<?php echo $key; ?>"><?php echo $value; ?></option>
							<?php endforeach; ?>
				 		</select>
				 		<input type="submit" class="button metabox_submit" name="remove_student_submit" value="<?php _e( 'Remove', 'lifterlms' ); ?>" />
				 		</form>

					</td>
				</tr>
			</tbody>
		</table>
    <?php
	}

	/**
	 * Sets the users status to enrolled in the usermeta table. 
	 * @param int $user_id [ID of the user]
	 * @param int $post_id [ID of the post]
	 *
	 * @return void
	 */
	public static function add_student( $user_id, $post_id ) {
		global $wpdb;

		if ( empty($user_id) || empty($post_id ) ) {
				return false;
		}

		self::create_order($user_id, $post_id);

		$user_metadatas = array(
			'_start_date' => 'yes',
			'_status' => 'Enrolled',
		);
		foreach( $user_metadatas as $key => $value ) {
			$update_user_postmeta = $wpdb->insert( $wpdb->prefix .'lifterlms_user_postmeta',
				array(
					'user_id' 			=> $user_id,
					'post_id' 			=> $post_id,
					'meta_key'			=> $key,
					'meta_value'		=> $value,
					'updated_date'		=> current_time('mysql'),
				)
			);
		}
		do_action('lifterlms_student_added_by_admin', $user_id, $post_id);
	}

	/**
	 * Removes the student from the course by setting the date to 0:00:00
	 * @param int $user_id [ID of the user]
	 * @param int $post_id [ID of the post]
	 * 
	 * @return void
	 */
	public static function remove_student( $user_id, $post_id ) {
		global $wpdb;

		if ( empty($user_id) || empty($post_id ) ) {
				return;
		}

		$user_metadatas = array(
			'_start_date' => 'yes',
			'_status' => 'Enrolled',
		);

		$table_name = $wpdb->prefix . 'lifterlms_order';

		$order_id = $wpdb->get_results( $wpdb->prepare(
			'SELECT order_post_id FROM '.$table_name.' WHERE user_id = %s and product_id = %d', $user_id, $post_id) );

		foreach ($order_id as $key => $value) {
			if ($order_id[$key]->order_post_id) {
				wp_delete_post( $order_id[$key]->order_post_id);
			}
		}

		$result = $wpdb->update( $wpdb->prefix .'lifterlms_order',
			array(
				'completed_date' 	=> current_time('mysql'),
				'order_completed' 	=> 'yes',
				'order_post_id'		=> $order_post_id,
			),
			array(
				'user_id' 			=> $user_id,
				'product_id' 		=> $post_id,
			)
		);

		foreach( $user_metadatas as $key => $value ) {
		$update_user_postmeta = $wpdb->delete( $wpdb->prefix .'lifterlms_user_postmeta',
			array(
				'user_id' 			=> $user_id,
				'post_id' 			=> $post_id,
				'meta_key'			=> $key,
				'meta_value'		=> $value,
				)
			);
		}
		do_action('lifterlms_student_removed_by_admin', $user_id, $post_id);
	}

	/**
	 * Creates a order post to associate with the enrollment of the user. 
	 * @param int $user_id [ID of the user]
	 * @param int $post_id [ID of the post]
	 * 
	 * @return void
	 */
	public static function create_order($user_id, $post_id) {
		global $wpdb, $post;

		$sku = get_post_meta( $post_id, '_sku', true );

		$order_data = apply_filters( 'lifterlms_new_order', array(
			'post_type' 	=> 'order',
			'post_title' 	=> sprintf( __( 'Order &ndash; %s', 'lifterlms' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'lifterlms' ) ) ),
			'post_status' 	=> 'publish',
			'ping_status'	=> 'closed',
			'post_author' 	=> 1,
			'post_password'	=> uniqid( 'order_' )
		) );

		$order_post_id = wp_insert_post( $order_data, true );

		$result = $wpdb->insert( $wpdb->prefix .'lifterlms_order',
			array(
				'user_id'			=> $user_id,
				'created_date' 		=> current_time('mysql'),
				'completed_date' 	=> current_time('mysql'),
				'order_completed' 	=> 'yes',
				'product_id'		=> $post_id,
				'order_post_id'		=> $order_post_id,
			)
		);

		$result = $wpdb->update( $wpdb->prefix .'lifterlms_order',
			array(
				'completed_date' 	=> current_time('mysql'),
				'order_completed' 	=> 'yes',
				'order_post_id'		=> $order_post_id,
			),
			array(
				'user_id' 			=> $user_id,
				'product_id' 		=> $post_id,
			)
		);

		update_post_meta($order_post_id,'_llms_user_id', $user_id);
		update_post_meta($order_post_id,'_llms_payment_method', 'assinged_by_admin');
		update_post_meta($order_post_id,'_llms_product_title', $post->post_title);
		update_post_meta($order_post_id,'_llms_order_total', '0');
		update_post_meta($order_post_id,'_llms_product_sku', $sku);
		update_post_meta($order_post_id,'_llms_order_currency', get_lifterlms_currency_symbol());
		update_post_meta($order_post_id,'_llms_order_product_id', $post_id);
	}


	/**
	 * Static save method
	 *
	 * Triggers add or remove method based on selection values.
	 * 
	 * @param  int 		$post_id [id of post object]
	 * @param  object 	$post [WP post object]
	 * 
	 * @return void
	 */
	public static function save( $post_id, $post ) {
		global $wpdb;

		if ( isset( $_POST['add_new_user']) && $_POST['add_new_user'] != '') {
			//triggers add_student static method
			$add_user = self::add_student( $_POST['add_new_user'], $post_id );
		}

		if ( isset( $_POST['remove_student']) && $_POST['remove_student'] != '') {
			//triggers remove_student static method
			$remove_user = self::remove_student( $_POST['remove_student'], $post_id );
		}

	}

}