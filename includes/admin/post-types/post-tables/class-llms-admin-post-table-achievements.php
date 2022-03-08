<?php
/**
 * LLMS_Admin_Post_Table_Achievement class
 *
 * @package LifterLMS/Admin/PostTypes/PostTables/Classes
 *
 * @since 6.0.0
 * @version 6.0.0
 */

defined( 'ABSPATH' ) || exit;

// TODO: remove this when the new loader will be implemented.
require_once LLMS_PLUGIN_DIR . '/includes/traits/llms-trait-award-templates-post-list-table.php';

/**
 * Customize display of the achievement post table.
 *
 * @since 6.0.0
 */
class LLMS_Admin_Post_Table_Achievements {

	use LLMS_Trait_Award_Templates_Post_List_Table;
	use LLMS_Trait_User_Engagement_Type;

	/**
	 * Constructor
	 *
	 * @since 6.0.0
	 *
	 * @return void
	 */
	public function __construct() {

		$this->engagement_type = 'achievement';
		$this->award_template_row_actions(); // defined in LLMS_Trait_Award_Templates_Post_List_Table.

	}

}

return new LLMS_Admin_Post_Table_Achievements();
