<?php
/**
 * Add, Customize, and Manage LifterLMS Engagement Post Table Columns
 * @since    3.1.0
 * @version  3.7.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Admin_Post_Table_Engagements {

	/**
	 * Constructor
	 * @return  void
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	public function __construct() {

		add_filter( 'manage_llms_engagement_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_llms_engagement_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

	}

	/**
	 * Add Custom Coupon Columns
	 * @param    array  $columns array of default columns
	 * @return   array
	 * @since    3.1.0
	 * @version  3.1.0
	 */
	public function add_columns( $columns ) {

		$date = $columns['date'];
		unset( $columns['date'] );

	    $columns['trigger'] = __( 'Trigger', 'lifterlms' );
	    $columns['type'] = __( 'Type', 'lifterlms' );
	    $columns['delay'] = __( 'Delay', 'lifterlms' );

	    $columns['date'] = $date;

		return $columns;

	}


	/**
	 * Manage content of custom coupon columns
	 * @param  string $column  column key/name
	 * @param  int $post_id WP Post ID of the coupon for the row
	 * @return void
	 * @since    3.1.0
	 * @version  3.7.0
	 */
	public function manage_columns( $column, $post_id ) {

		// global $post;

		switch ( $column ) {

			case 'trigger':

				$triggers = llms_get_engagement_triggers();

				$trigger = get_post_meta( $post_id, '_llms_trigger_type', true );

				echo isset( $triggers[ $trigger ] ) ? $triggers[ $trigger ] : $trigger;

				$tid = get_post_meta( $post_id, '_llms_engagement_trigger_post', true );
				if ( $tid ) {

					echo '<br>';

					if ( 'course_track_completed' === $trigger ) {
						$term = get_term( $tid, 'course_track' );
						$title = $term->name;
						$link = get_edit_term_link( $tid, 'course_track', 'course' );
					} else {
						$title = get_the_title( $tid );
						$link = get_edit_post_link( $tid );
					}

					printf( '<a href="%s">%s (ID# %d)</a>', $link, $title, $tid );

				}

			break;

			case 'type':

				$types = llms_get_engagement_types();

				$type = get_post_meta( $post_id, '_llms_engagement_type', true );

				echo isset( $types[ $type ] ) ? $types[ $type ] : $type;

				$eid = get_post_meta( $post_id, '_llms_engagement', true );
				if ( $eid ) {

					echo '<br>';
					printf( '<a href="%s">%s (ID# %d)</a>', get_edit_post_link( $eid ), get_the_title( $eid ), $eid );

				}

			break;

			case 'delay':

				$delay = get_post_meta( $post_id, '_llms_engagement_delay', true );

				if ( $delay ) {

					printf( __( '%d days', 'lifterlms' ), $delay );

				} else {

					echo '&ndash;';

				}

			break;

		}// End switch().

	}

}
return new LLMS_Admin_Post_Table_Engagements();
