<?php 

/**
 * Add, Customize, and Manage LifterLMS Coupon Post Table Columns
 *
 * @since    3.2.3
 * @version  3.2.3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class LLMS_Admin_Post_Table_Lessons {

	/**
	 * Constructor
	 * @return  void
	 * @since    3.2.3
	 * @version  3.2.3
	 */
	public function __construct() {
		add_filter( 'manage_lesson_posts_columns', array( $this, 'add_columns' ), 10, 1 );
		add_action( 'manage_lesson_posts_custom_column', array( $this, 'manage_columns' ), 10, 2 );

		//add course filter
		add_action( 'restrict_manage_posts', array( $this, 'filters' ), 10 );

		//change query
		add_filter( 'parse_query', array( $this, 'query_posts_filter' ), 10 );

		//disable default date
		add_filter( 'months_dropdown_results', array( $this, 'default_date_filter' ), 10 ,2 );
	}

	/**
	 * Add Custom lesson Columns
	 * @param  array   $columns  array of default columns
	 * @return array
	 * @since    3.2.3
	 * @version  3.2.3
	 */
	public function add_columns( $lessons_columns ) {
		$lessons_columns['cb'] = '<input type="checkbox" />';
		$lessons_columns['title'] = __( 'Lesson Title', 'lifterlms' );
		$lessons_columns['section'] = __( 'Section', 'lifterlms' );
		$lessons_columns['course'] = __( 'Course', 'lifterlms' );
		$lessons_columns['prereq'] = __( 'Prerequisite', 'lifterlms' );
		$lessons_columns['date'] = __( 'Date', 'lifterlms' );
		return $lessons_columns;
	}

	/**
	 * Manage content of custom lesson columns
	 * @param  string $column   column key/name
	 * @param  int    $post_id  WP Post ID of the lesson for the row
	 * @return void
	 * @since    3.2.3
	 * @version  3.2.3
	 */
	public function manage_columns( $column, $post_id ) {
		$crnt_l = new LLMS_Lesson( $post_id );
		switch ( $column ) {
			case 'course':
				$course = $crnt_l->get_parent_course();
				$edit_link = get_edit_post_link( $course );
				if ( ! empty( $course ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link , get_the_title( $course ) );
				}
				break;
			case 'section':
				$section = $crnt_l->get_parent_section();
				$edit_link = get_edit_post_link( $section );
				if ( ! empty( $section ) ) {
					printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $section ) );
				}
				break;
			case 'prereq':
				if ( $crnt_l->has_prerequisite() ) {
					$prereq = $crnt_l->get( 'prerequisite' );
					$edit_link = get_edit_post_link( $prereq );
					if ( $prereq ) {
						printf( '<a href="%1$s">%2$s</a>', $edit_link, get_the_title( $prereq ) );
					} else {
						echo '&ndash;';
					}
				} else {
					echo '&ndash;';
				}
				break;
		}
	}
	//to resolve complexity
	public function get_course_id() {
		$selected_course_id = isset( $_GET['flt_course_id'] ) ? sanitize_text_field( $_GET['flt_course_id'] ) : '';
		return $selected_course_id;
	}
	/**
	 * Add  filters
	 *
	 * @return string/html
	 * @since 3.9.6
	 */
	public function filters( $post_type ) {
		//only add filter to post type you want
		if ( 'lesson' !== $post_type ) {
			return;
		}
			global $wpdb;
			/** Grab  courses from  DB */
			$query = $wpdb->prepare('
				SELECT  * FROM %1$s 
				WHERE post_status = "%2$s" 
				AND post_type = "%3$s"
				ORDER BY ID DESC',
				$wpdb->posts,
				'publish',          // Post status - change as required
				'course'
			);
			$courses_array = $wpdb->get_col( $query );
			$selected_course_id = $this->get_course_id();
			?>
			<select name="flt_course_id">
				<option value=""><?php _e( 'All Courses ', 'lifterlms' ); ?></option>
				<?php foreach ( $courses_array as $course_id ) { ?>
					<option value="<?php echo $course_id; ?>" <?php selected( $course_id,$selected_course_id ); ?> ><?php echo get_the_title( $course_id ); ?></option>
				<?php } ?>
			</select>
			<?php

			//date filter
			global $wpdb ,$wp_locale;
			$extra_checks = "AND post_status != 'auto-draft'";
			$months = $wpdb->get_results( $wpdb->prepare( "
				SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
				FROM $wpdb->posts
				WHERE post_type = %s
				$extra_checks
				ORDER BY post_date DESC
			", $post_type ) );
			$month_count = count( $months );
			if ( ! $month_count || ( 1 == $month_count && 0 == $months[0]->month ) ) {
				 return;
			}
			$m_llms = isset( $_GET['m'] ) ? (int) $_GET['m'] : 0;
			?>
					<label for="filter-by-date" class="screen-reader-text"><?php _e( 'Filter by date', 'lifterlms' ); ?></label>
					<select name="m" id="filter-by-date">
						<option <?php selected( $m_llms, 0 ); ?> value="0"><?php _e( 'All dates', 'lifterlms' ); ?></option>
			<?php
			foreach ( $months as $arc_row ) {
				if ( 0 == $arc_row->year ) {
					 continue;
				}
				$month = zeroise( $arc_row->month, 2 );
				$year = $arc_row->year;
				printf( "<option %s value='%s'>%s</option>\n",
					selected( $m_llms, $year . $month, false ),
					esc_attr( $arc_row->year . $month ),
					sprintf( '%1$s %2$d', $wp_locale->get_month( $month ), $year )
				);
			}
			?>
					</select>
			<?php

			//date filter ends
	}
	/**
	 * Change query on filter submit
	 *
	 * @return Void
	 * @Since 3.9.6
	 */
	public function query_posts_filter( $query ) {
		global $pagenow;
		$type = 'post';
		if ( isset( $_GET['post_type'] ) ) {
			$type = $_GET['post_type'];
		}
		if ( 'lesson' == $type && is_admin() && $pagenow == 'edit.php' && isset( $_GET['flt_course_id'] ) && $_GET['flt_course_id'] != '' ) {
			$query->query_vars['meta_key'] = '_llms_parent_course';
			$query->query_vars['meta_value'] = sanitize_text_field( $_GET['flt_course_id'] );
		}
	}
	/**
	 * Hide default date filter  only on lesson post types
	 *
	 * @return empty array | months array
	 * @Since 3.9.6
	 */
	public function default_date_filter( $months, $post_type ) {
		if ( $post_type == 'lesson' ) {
			return array();
		}
		return $months;
	}
}
return new LLMS_Admin_Post_Table_Lessons();
