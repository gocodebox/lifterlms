<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
* Analytics Class
*
* Manages large queries of grouped data
*/
class LLMS_Analytics {

	/**
	 * Constructor
	 */
	public function __construct() {

	}

	//sales data
	//get all product orders

	//get orders
	public static function get_orders( $values ) {

		$args = array(
		  'post_type' 		=> 'llms_order',
		  'posts_per_page'	=> 5000,
		  'meta_query' 		=> array(),
		);

		if ( count( $values ) > 1 ) {
			$args['meta_query']['relation'] = 'AND';
		}

		foreach ( $values as $key => $value ) {
			$args['meta_query'][] = array(
		      'key' => $value['key'],
		      'value' => $value['value'],
		      'compare' => $value['compare'],
			);
		}

		$orders = get_posts( $args );

		$orders_data = array();

		foreach ( $orders as $key => $value ) {

			$order = LLMS_Product::get_order_data( $value->ID );
			array_push( $orders_data, $order );

		}

		return $orders_data;
	}


	/**
	 * Returns 10000 posts
	 * @param  [string] $post_type [post type filter]
	 * @return [mixed] [array of post objects or false if no results found]
	 */
	public static function get_posts( $post_type = '' ) {

		// Venues
		$args = array(
			'posts_per_page'   => 10000,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => $post_type,
			'suppress_filters' => true,
		);
		$posts = get_posts( $args );

		return $posts;
	}

	public static function get_products() {
		//get products
		$courses = self::get_posts( 'course' );
		$memberhsips = self::get_posts( 'llms_membership' );

		//if courses or membership are false
		//turn them into empty arrays
		if ( ! $courses ) {
			$courses  = array();
		}
		if ( ! $memberhsips ) {
			$memberships  = array();
		}

		$products = array_merge( $courses, $memberhsips );

		return $products;
	}

	/**
	 * Get array of date / total $ made on site
	 * Uses start and end date for timeline
	 *
	 * @param  [array] $orders     [array of order objects]
	 * @param  [string] $start_date [date yyyy-mm-dd]
	 * @param  [string] $end_date [date yyyy-mm-dd]
	 *
	 * @return [array]             [array of date / daily total]
	 */
	public static function get_total_sold_by_day( $orders, $start_date, $end_date ) {

		$total_by_day = array();

		$date = $start_date;

		while ( $date <= $end_date ) {

			//set daily total to 0;
			$daily_total = 0;

			//loop through all objects and add values to total
			foreach ( $orders as $key => $value ) {
				if ( $value->order_date == $date ) {
					$daily_total += $value->order_total;
				}
			}
			$results = array( $date, $daily_total );

			$total_by_day[] = $results;

			//add one day to date
			$date = LLMS_Date::db_date( $date . '+ 1 day' );

		}

		return $total_by_day;

	}

	public static function get_total_enrolled_by_day( $search ) {

		$total_by_day = array();
		$date = $search->start_date;
		// array key counter
		$i = 0;

		while ( $date <= $search->end_date ) {

			//create array for each date and add to $total_by_day
			$daily_results = array( $date );
			$total_by_day[] = $daily_results;

			if ( isset( $search->courses ) ) {

				foreach ( $search->courses as $course ) {

					$daily_total = 0;

					//loop through all students and count enrolled students
					if ( $search->students ) {
						foreach ( $search->students as $key => $value ) {

							if ( $value->post_id == $course->ID && LLMS_Date::db_date( $value->enrolled_date ) <= $date ) {

								if ( 'Enrolled' === $value->status ) {
									$daily_total++;
								}
							}
						}
					}
					array_push( $total_by_day[ $i ], $daily_total );
				}
			} elseif ( isset( $search->memberships ) ) {

				foreach ( $search->memberships as $membership ) {

					$daily_total = 0;

					//loop through all students and count enrolled students
					if ( $search->members ) {
						foreach ( $search->members as $key => $value ) {

							if ( $value->post_id == $membership->ID && LLMS_Date::db_date( $value->enrolled_date ) <= $date ) {

								if ( 'Enrolled' === $value->status ) {
									$daily_total++;
								}
							}
						}
					}
					array_push( $total_by_day[ $i ], $daily_total );
				}
			}// End if().

			//add 1 to array key counter
			$i++;
			//add one day to date
			$date = LLMS_Date::db_date( $date . '+ 1 day' );

		}// End while().

		return $total_by_day;

	}

	/**
	 * Get total volume sold
	 * @param  [array] $orders [array of order objects]
	 * @return [float] [total sold]
	 */
	public static function get_total_sales( $orders ) {

		$total_sold = 0;

		if ( $orders ) {

			foreach ( $orders as $order ) {

				$total_sold += $order->order_total;

			}
		}
		return $total_sold;
	}


	/**
	* Get number of products sold
	* @param  [array] $orders [array of order objects]
	* @return [int] [total number of items sold]
	*/
	public static function get_total_units_sold( $orders ) {

		$units = 0;

		if ( $orders ) {

			$units = count( $orders );

		}

		return $units;
	}

	/**
	* Get number of coupons used
	* @param  [array] $orders [array of order objects]
	* @return [int] [total number of coupons used]
	*/
	public static function get_total_coupons_used( $orders ) {

		$coupons = 0;

		if ( $orders ) {

			foreach ( $orders as $order ) {

				if ( ! empty( $order->coupon_id ) ) {
					$coupons++;
				}
			}
		}

		return $coupons;
	}

	/**
	* Get dollar value of coupons used
	* @param  [array] $orders [array of order objects]
	* @return [int] [total $ coupons used]
	*/
	public static function get_total_coupon_amount( $orders ) {

		$coupons = 0;

		if ( $orders ) {

			foreach ( $orders as $order ) {

				if ( ! empty( $order->coupon_id ) ) {

					//calculate coupon
					$product = new LLMS_Product( $order->id );
					$coupons += $product->get_coupon_discount_total( $order->order_total );

				}
			}
		}

		return $coupons;
	}

	/**
	 * Get all currently enrolled students
	 * @param  [int] $post_id [Course Id]
	 * @return [array]          [array of enrollment objects]
	 */
	public static function get_enrolled_users( $post_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT * FROM ' . $table_name .
					' WHERE post_id = %s
						AND meta_value = "Enrolled"',
				$post_id
			)
		);

		return $results;

	}

	/**
	 * Get all students enrolled in last n number of days
	 * @param  [int] $number_of_days
	 * @return [int]
	 */
	public static function get_users_enrolled_last_n_days( $number_of_days = 7 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$results = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT count(*) FROM ' . $table_name .
						' WHERE updated_date > DATE_SUB(NOW(), INTERVAL %s DAY)
						AND meta_value = "Enrolled"',
				$number_of_days
			)
		);

		return $results;
	}

	/**
	 * Get all members registered in last n number of days
	 * @param  [int] $number_of_days
	 * @return [int]
	 */
	public static function get_members_registered_last_n_days( $number_of_days = 7 ) {
		global $wpdb;

		$results = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT count(*) FROM ' . $wpdb->users .
						' WHERE user_registered > DATE_SUB(NOW(), INTERVAL %s DAY)',
				$number_of_days
			)
		);

		return $results;
	}

	/**
	 * Get all lessons completed in last n number of days
	 * @param  [int] $number_of_days
	 * @return [int]
	 */
	public static function get_lessons_completed_last_n_days( $number_of_days = 7 ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$results = $wpdb->get_var(
			$wpdb->prepare(
				'SELECT count(*) FROM ' . $table_name . '
				WHERE meta_key = "_is_complete"
				AND updated_date > DATE_SUB(NOW(), INTERVAL %s DAY)',
				$number_of_days
			)
		);

		return $results;
	}

	public static function get_total_sales_last_n_days( $number_of_days = 7 ) {

		$total = 0;
		$args = array(
				'post_type' 		=> 'llms_order',
				'posts_per_page'	=> 5000,
				'meta_query' 		=> array(),
				'date_query' => array(
						array(
								'after' => $number_of_days . ' day ago',
						),
				),
		);

		$orders = get_posts( $args );
		foreach ( $orders as $order ) {
			$total += get_post_meta( $order->ID, '_llms_order_total', true );
		}

		return LLMS_Number::format_money_no_decimal( $total );
	}



	/**
	 * Query user_postmeta for all users enrolled by course
	 * @return [type] [description]
	 */
	public static function get_total_users_all_time( $post_id, $end_date ) {
		global $wpdb;

		//add 1 day to time to account for striptime
		$end_date = LLMS_Date::db_date( $end_date . '1 day' );

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT
					p.user_id,
					p.post_id,
					MAX(IF(pa.meta_key = "_start_date", pa.updated_date, NULL)) AS enrolled_date,
					MAX(IF(pa.meta_key = "_status", pa.meta_value, NULL)) AS status,
					MAX(IF(pa.meta_key = "_is_complete", pa.updated_date, NULL)) AS completed_date
					from ' . $table_name . ' p
					left join ' . $table_name . ' pa on p.user_id = pa.user_id and p.post_id = pa.post_id
					where p.post_id = %s
					and p.updated_date <= %s
					group by p.user_id',
				$post_id, $end_date
			)
		);

		return $results;

	}

	public static function get_users( $post_id, $include_expired = false ) {
		global $wpdb;

		$students_array = array();
		$students_large = array();
		$students_small = array();

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';
		$users_table = $wpdb->prefix . 'users';

		if ( 'all_products' === $post_id ) {

			// query user_postmeta table
			$results = $wpdb->get_results(
				'SELECT
					user_id,
					meta_value,
					updated_date
				FROM ' . $table_name . '
				WHERE meta_key = "_status"
				AND ( meta_value = "Enrolled" OR meta_value = "Expired" )
				AND EXISTS(SELECT 1 FROM ' . $users_table . ' WHERE ID = user_id)
				group by user_id'
			);

		} else {

			// query user_postmeta table
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT
						user_id,
						meta_value,
						updated_date
					FROM ' . $table_name . '
					WHERE meta_key = "_status"
					AND post_id = %s
					AND ( meta_value = "Enrolled" OR meta_value = "Expired" )
					AND EXISTS(SELECT 1 FROM ' . $users_table . ' WHERE ID = user_id)
					group by user_id',
					$post_id
				)
			);

		}

		if ( $results ) {

			foreach ( $results as $key => $student ) {

				// if include expired students is false then remove expired students from array
				if ( ! $include_expired && 'Expired' === $student->meta_value ) {

					unset( $results[ $key ] );

				} else {

					//get member name
					$first_name = get_user_meta( $student->user_id, 'first_name', true );
					$last_name = get_user_meta( $student->user_id, 'last_name', true );
					$user = get_user_by( 'id', $student->user_id );
					$email = $user->user_email;
					$profile_link = '<a href="' . get_admin_url( '', 'admin.php?page=llms-students&tab=profile&student=' . $student->user_id ) . '">View</a>';
					//add data to large table array
					$student_data = array(
						$last_name,
						$first_name,
						$email,
						$profile_link,
					);
					array_push( $students_large, $student_data );

					//add data to small table array
					$student_data = array(
						$last_name,
						$first_name,
						$profile_link,
					);
					array_push( $students_small, $student_data );

				}
			}

			$students_array['large'] = $students_large;
			//$students_array['small'] = $students_small;
		}// End if().

		return $students_array;

	}

	/**
	* Get number of students enrolled in course ever
	* @param  [array] $students [array of student objects]
	* @return [int] [total number of enrolled users]
	*/
	public static function get_total_users( $students ) {

		$units = 0;

		if ( $students ) {

			$units = count( $students );

		}

		return $units;
	}






	/**
	* Get number of students enrolled in course with status of enrolled
	* @param  [array] $students [array of student objects]
	* @return [int] [total number of enrolled users]
	*/
	public static function get_total_current_enrolled_users( $students ) {

		$units = 0;

		if ( $students ) {

			foreach ( $students as $student ) {
				if ( 'Enrolled' === $student->status ) {
					$units++;
				}
			}
		}

		return $units;
	}

	/**
	* Get number of students enrolled in course with status of expired
	* @param  [array] $students [array of student objects]
	* @return [int] [total number of xpired users]
	*/
	public static function get_total_current_expired_users( $students ) {

		$units = 0;

		if ( $students ) {

			foreach ( $students as $student ) {
				if ( 'Expired' === $student->status ) {
					$units++;
				}
			}
		}

		return $units;
	}

	public static function course_completion_percentage( $students ) {

		if ( $students ) {

			$total_students  = count( $students );
			$total_done = 0;

			foreach ( $students as $student ) {
				if ( ! is_null( $student->completed_date ) ) {
					$total_done++;
				}
			}

			if ( 0 == $total_students ) {
				return 0;
			} else {
				return $total_done / $total_students;
			}
		}
	}

	public static function get_membership_retention( $members ) {

		if ( $members ) {

			$total_members  = count( $members );
			$current_members = 0;

			foreach ( $members as $member ) {

				if ( 'Enrolled' === $member->status ) {
					$current_members++;
				}
			}

			if ( 0 == $total_members ) {
				return 0;
			} else {
				return ( $current_members / $total_members );
			}
		}
	}

	public static function get_total_certs_issued( $product_id ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		if ( 'all_courses' === $product_id ) {
			$results = $wpdb->get_results(
				'SELECT *
					from ' . $table_name . '
					where meta_key = "_certificate_earned"'
			);

		} else {
			$results = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT *
						from ' . $table_name . '
						where meta_key = "_certificate_earned"
						AND post_id = %s', $product_id
				)
			);
		}

		if ( $results ) {
			return count( $results );
		} else {
			return 0;
		}

	}

	/**
	 * Gets completion percentage for each lesson in course
	 * @param  [array] $search [analytics search object]
	 * @return [array] [array of arrays]
	 */
	public static function get_lesson_completion_avg( $search ) {

		$lesson_completions = array();
		$all_students = 0;

		if ( ! empty( $search->lessons ) ) {
			//loop through each lesson
			foreach ( $search->lessons as $lesson ) {
				//create array and add post title
				$lesson_array = array( $lesson->post_title );

				$unit = 0;

				if ( ! empty( $search->students ) ) {

					$all_students = count( $search->students );

					//loop through each student and check if lesson is completed
					foreach ( $search->students as $student ) {

						if ( self::is_lesson_completed( $student->user_id, $lesson->ID, $search->end_date ) ) {
							$unit++;
						}
					}
				}
				if ( $all_students > 0 ) {
					//calculate completion percentage
					$completion_percent = LLMS_Number::whole_number( ( $unit / $all_students ) );
				} else {
					$completion_percent = 0;
				}

				//add unit count to lesson array
				array_push( $lesson_array, $completion_percent );

				$lesson_completions[] = $lesson_array;

			}

			return $lesson_completions;
		}// End if().

	}

	/**
	 * Checks whether a lesson is completed
	 * @param  [int]  $user_id   user id]
	 * @param  [int]  $lesson_id [lesson id]
	 * @param  string  $end_date  [optional end date]
	 * @return boolean            [if lesson was completed before end date]
	 */
	public static function is_lesson_completed( $user_id, $lesson_id, $end_date = '' ) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		$results = $wpdb->get_results( $wpdb->prepare(
		'SELECT updated_date FROM ' . $table_name . ' WHERE user_id = %s AND post_id = %d AND meta_key = "_is_complete"', $user_id, $lesson_id) );

		if ( $results ) {

			if ( ! empty( $end_date ) ) {

				if ( LLMS_Date::db_date( $results[0]->updated_date ) > $end_date ) {
					return false;
				}
			}

			return true;
		}

		return false;
	}

	public static function get_members( $search ) {

		$members_array = array();
		$members_large = array();
		$members_small = array();

		if ( ! empty( $search->members ) ) {

			foreach ( $search->members as $member ) {

				//get member name
				$first_name = get_user_meta( $member->user_id, 'first_name', true );
				$last_name = get_user_meta( $member->user_id, 'last_name', true );

				//get enrollment and expiration date
				$enrollment_date = LLMS_Date::db_date( $member->enrolled_date );
				$exp_date = self::get_membership_exp_date_by_user( $search->product_id, $enrollment_date );
				$profile_link = '<a href="' . get_admin_url( '', 'admin.php?page=llms-students&tab=profile&student=' . $member->user_id ) . '">View</a>';

				//add data to large table array
				$member_data = array(
					$last_name,
					$first_name,
					$enrollment_date,
					( $exp_date ? $exp_date : '' ),
					$profile_link,
				);
				array_push( $members_large, $member_data );

				//add data to small table array
				$member_data = array(
					$last_name,
					$first_name,
					$profile_link,
				);
				array_push( $members_small, $member_data );

			}
		}

		$members_array['large'] = $members_large;
		$members_array['small'] = $members_small;

		return $members_array;
	}

	public static function get_membership_exp_date_by_user( $membership_id, $enrollment_date ) {

		$interval = get_post_meta( $membership_id, '_llms_expiration_interval', true );

		if ( ! empty( $interval ) ) {

			$period = get_post_meta( $membership_id, '_llms_expiration_period', true );

			$exp_date = LLMS_Date::db_date( $enrollment_date . ' +' . $interval . ' ' . $period );

			if ( $exp_date !== $enrollment_date ) {
				return $exp_date;
			}
		}
		return false;
	}



	public static function get_students( $search ) {

		$student_arrays = array();
		$students_large = array();
		$students_small = array();

		if ( ! empty( $search->students ) ) {

			//create new course object
			$course = new LLMS_Course( $search->product_id );

			foreach ( $search->students as $student ) {

				//get student name
				$first_name = get_user_meta( $student->user_id, 'first_name', true );
				$last_name = get_user_meta( $student->user_id, 'last_name', true );
				$profile_link = '<a href="' . get_admin_url( '', 'admin.php?page=llms-students&tab=profile&student=' . $student->user_id ) . '">View</a>';

				//get student progress information
				$student_progress = $course->get_student_progress( $student->user_id );

				$start_date = LLMS_Date::db_date( $student_progress->start_date );

				//set variables for lesson progress statistics
				$completed_lesson_count = 0;
				$last_completed_lesson = '';
				$last_completed_lesson_date = 0;
				$all_lesson_count = 0;

				if ( ! empty( $student_progress->lessons ) ) {

					$all_lesson_count = count( $student_progress->lessons );

					foreach ( $student_progress->lessons as $lesson ) {

						if ( $lesson['is_complete'] ) {
							//add 1 to completed lesson count
							$completed_lesson_count++;
							//if lesson completed date is >  than the previous completed lesson date
							//Set the last completed lesson and date
							if ( $lesson['completed_date'] > $last_completed_lesson_date ) {
								$last_completed_lesson = get_the_title( $lesson['id'] );
								$last_completed_lesson_date = LLMS_Date::db_date( $lesson['completed_date'] );
							}
						}
					}
				} // End if().

				//calculate % complete
				if ( 0 == $all_lesson_count ) {
					$completion_percent = '0%';
				} else {
					$completion_percent = ( LLMS_Number::whole_number( $completed_lesson_count / $all_lesson_count ) . '%' );
				}

				//add data to large table array
				$student_data = array(
					$last_name,
					$first_name,
					$start_date,
					$completion_percent,
					( $last_completed_lesson ? $last_completed_lesson . ', ' . $last_completed_lesson_date : '' ),
					$profile_link,
				);
				array_push( $students_large, $student_data );

				//add data to small table
				$student_data = array(
					$first_name,
					$last_name,
					$profile_link,
				);
				array_push( $students_small, $student_data );
			} // End foreach().

			$student_arrays['large'] = $students_large;
			$student_arrays['small'] = $students_small;

			return $student_arrays;
		}// End if().

	}

	public static function get_user_enrollments( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'lifterlms_user_postmeta';

		// query user_postmeta table
		$results = $wpdb->get_results(
			$wpdb->prepare(
				'SELECT
					*
				FROM ' . $table_name . '
				WHERE meta_key = "_status"
				AND meta_value = "Enrolled"
				AND user_id = %s', $user_id
			)
		);

		if ( $results ) {

			foreach ( $results as $key => $value ) {

				$post = get_post( $value->post_id );

				if ( isset( $post ) ) {
					$results[ $key ]->post_title = $post->post_title;
					$results[ $key ]->post_type = $post->post_type;
				} else {
					$results[ $key ]->post_title = '';
					$results[ $key ]->post_type = '';
				}
			}
		}

		return $results;
	}

	public static function get_orders_by_user( $user_id ) {

		//set up search arguments
		$values = array(
			'0' => array(
			 	'key' => '_llms_user_id',
				'value' => $user_id,
				'compare' => '=',
			),
		);

		return self::get_orders( $values );

	}

	public static function get_courses_by_user_table( $user ) {

		$courses_array = array();

		foreach ( $user->courses as $course ) {
			$c = new LLMS_Course( $course->post_id );
			$comp = $c->get_percent_complete( $user->id );
			$status = ( '100' == $comp ) ? 'Completed' : 'Enrolled';
			$link = get_edit_post_link( $course->post_id );
			$title = ( $link ) ? '<a href="' . $link . '">' . $course->post_title . '</a>' : $course->post_title;
			$course_array = array( $title, LLMS_Date::db_date( $course->updated_date ), $status, $comp . '%' );
			array_push( $courses_array, $course_array );
		}

		return $courses_array;

	}

	public static function get_memberships_by_user_table( $user ) {

		$memberships = array();

		foreach ( $user->memberships as $membership ) {
			$link = get_edit_post_link( $membership->post_id );
			$title = ( $link ) ? '<a href="' . $link . '">' . $membership->post_title . '</a>' : $membership->post_title;

			$membership_array = array( $title, LLMS_Date::db_date( $membership->updated_date ), $membership->meta_value );

			array_push( $memberships, $membership_array );

		}

		return $memberships;

	}

}

return new LLMS_Analytics;
