<?php
/**
 * LLMS_Admin_Post_Table_Certificates class
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 6.0.0
 * @version 6.2.0
 */

defined( 'ABSPATH' ) || exit;

// TODO: remove this when the new loader will be implemented.
require_once LLMS_PLUGIN_DIR . '/includes/traits/llms-trait-award-templates-post-list-table.php';
/**
 * Customize display of the certificate post tables.
 *
 * @since 6.0.0
 */
class LLMS_Admin_Post_Table_Certificates {

	use LLMS_Trait_Award_Templates_Post_List_Table;
	use LLMS_Trait_User_Engagement_Type;

	/**
	 * Query string variable used to identify the migration action.
	 *
	 * @var string
	 */
	const MIGRATE_ACTION = 'llms-migrate-legacy-certificate';

	/**
	 * Constructor
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		$this->engagement_type = 'certificate';
		$this->award_template_row_actions(); // defined in LLMS_Trait_Award_Templates_Post_List_Table.

		if ( ! llms_is_block_editor_supported_for_certificates() ) {
			return;
		}

		$post_types = array( 'llms_certificate', 'llms_my_certificate' );
		if ( in_array( llms_filter_input( INPUT_GET, 'post_type' ), $post_types, true ) ) {
			add_filter( 'display_post_states', array( $this, 'add_states' ), 20, 2 );
			add_filter( 'post_row_actions', array( $this, 'add_actions' ), 20, 2 );
		}

		if ( 1 === (int) llms_filter_input( INPUT_GET, self::MIGRATE_ACTION, FILTER_SANITIZE_NUMBER_INT ) ) {
			add_filter( 'llms_certificate_template_version', array( $this, 'upgrade_template' ), 10 );
		}

		add_filter( 'manage_llms_my_certificate_posts_columns', array( $this, 'mod_cols' ), 10, 1 );

	}

	/**
	 * Add post row actions.
	 *
	 * @since 6.0.0
	 *
	 * @param array   $actions Array of post row actions.
	 * @param WP_Post $post    Post object for the row.
	 * @return array
	 */
	public function add_actions( $actions, $post ) {

		$cert = llms_get_certificate( $post, true );
		if ( 1 === $cert->get_template_version() ) {

			$url                             = esc_url( add_query_arg( self::MIGRATE_ACTION, 1, get_edit_post_link( $post ) ) );
			$actions[ self::MIGRATE_ACTION ] = '<a href="' . $url . '">' . __( 'Migrate legacy certificate', 'lifterlms' ) . '</a>';

		}

		return $actions;

	}

	/**
	 * Add state information denoting the usage of the legacy template.
	 *
	 * @since 6.0.0
	 * @since 6.2.0 Made sure to only process certificates.
	 *
	 * @param string[] $states Array of post states.
	 * @param WP_Post  $post   Post object.
	 * @return string[]
	 */
	public function add_states( $states, $post ) {

		$cert = llms_get_certificate( $post, true );
		if ( $cert && 1 === $cert->get_template_version() ) {
			$states['llms-legacy-template'] = __( 'Legacy', 'lifterlms' );
		}

		return $states;

	}

	/**
	 * Modify the columns list for the `llms_my_certificate` post type.
	 *
	 * @since 6.0.0
	 *
	 * @param array $cols Array of columns.
	 * @return array
	 */
	public function mod_cols( $cols ) {
		unset( $cols['author'] );
		return $cols;
	}

	/**
	 * Callback function for `llms_certificate_template_version` forcing an upgrade to version 2.
	 *
	 * @since 6.0.0
	 *
	 * @param integer $version Current template version.
	 * @return integer
	 */
	public function upgrade_template( $version ) {
		return 2;
	}

}

return new LLMS_Admin_Post_Table_Certificates();
