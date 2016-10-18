<?php
/**
 * Update Database to version 3.0.2
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

class LLMS_Update_303 extends LLMS_Update {

	/**
	 * Array of callable function names (within the class)
	 * that need to be called to complete the update
	 *
	 * if functions are dependent on each other
	 * the functions themselves should schedule additional actions
	 * via $this->schedule_function() upon completion
	 *
	 * @var  array
	 */
	protected $functions = array(
		'rename_students_role',
	);

	/**
	 * Version number of the update
	 * @var  string
	 */
	protected $version = '3.0.3';

	/**
	 * Renames all users with bugged "studnet" role to "Student"
	 * @param    integer    $page  page of users for paginated results
	 * @return   void
	 * @since    3.0.3
	 * @version  3.0.2
	 */
	public function rename_students_role() {

		$this->log( 'function `rename_students_role()` started' );

		// add the bugges role so we can remove it
		// we delete it at the conclusion of the function
		if ( ! get_role( 'studnet' ) ) {

			add_role( 'studnet', __( 'Student', 'lifterlms' ),
				array(
					'read' => true,
				)
			);

		}

		$limit = 2;

		$users = new WP_User_Query( array(

			'number' => $limit,
			'offset' => 0, // don't need to calc this b/c the query is going to return fewer results next time
			'role__in' => array( 'studnet' ),

		) );

		if ( $users->get_results() ) {

			foreach ( $users->get_results() as $user ) {

				$user->remove_role( 'studnet' );
				$user->add_role( 'student' );

			}

		}

		// schedule another go of the function if theres more results
		if ( $users->get_total() > $limit ) {

			$this->schedule_function( 'rename_students_role' );

		} // finished
		else {

			// remove the bugged role when finished
			remove_role( 'studnet' );

			$this->function_complete( 'rename_students_role' );

		}

	}

}

return new LLMS_Update_303;
