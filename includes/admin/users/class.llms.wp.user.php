<?php
if ( ! defined( 'ABSPATH' ) )
	exit;

if ( ! class_exists( 'LLMS_WP_User' ) ) {

	class LLMS_WP_User {

		public function __construct() {

			add_action( 'manage_users_extra_tablenav', array( $this, 'select_ui' ) );
		}

		public function select_ui() {
			?>
			<div class="alignleft actions">
				<label class="screen-reader-text" for="_llms_bulk_enroll_product">
					<?php _e( 'Choose Course/Membership', 'lifterlms' ) ?>
				</label>
				<select id="llms-bulk-enroll-product" class="llms-bulk-enroll-product" data-post-type="llms_membership,course" name="_llms_bulk_enroll_product" style="max-width:200px">
				</select>
				<input type="submit" name="llms_bulk_enroll" id="llms_bulk_enroll" class="button" value="Enroll">
			</div>
			<?php
		}

	}

}

return new LLMS_WP_User;