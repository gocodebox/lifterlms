<?php
/**
 * Test updates functions when updating to 11.0.0.
 *
 * @package LifterLMS/Tests/Functions/Updates
 *
 * @group functions
 * @group updates
 * @group updates_1100
 *
 * @since [version]
 */
class LLMS_Test_Functions_Updates_1100 extends LLMS_UnitTestCase {

	/**
	 * Stock English meter description stored on forms created before this update.
	 *
	 * @var string
	 */
	const LEGACY_DESCRIPTION = 'A weak password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.';

	/**
	 * Setup before class.
	 *
	 * Include update functions file.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		require_once LLMS_PLUGIN_DIR . 'includes/functions/updates/llms-functions-updates-1100.php';
		require_once LLMS_PLUGIN_DIR . 'includes/functions/llms.functions.updates.php';
	}

	/**
	 * Setup the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();
		add_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
		delete_option( 'llms_has_transaction_backfill_cursor' );
	}

	/**
	 * Tear down the test.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function tear_down() {
		parent::tear_down();
		remove_filter( 'llms_update_items_per_page', array( $this, 'per_page' ) );
	}

	/**
	 * Callback to reduce items per page for testing pagination.
	 *
	 * @since [version]
	 *
	 * @return int
	 */
	public function per_page() {
		return 2;
	}

	/**
	 * Create a legacy order with a transaction but no `_llms_has_transaction` flag.
	 *
	 * @since [version]
	 *
	 * @return int Order post ID.
	 */
	private function create_legacy_order_with_transaction() {
		$order_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$txn_id   = $this->factory->post->create( array( 'post_type' => 'llms_transaction' ) );
		update_post_meta( $txn_id, '_llms_order_id', $order_id );
		// Simulate legacy data: flag not yet set.
		delete_post_meta( $order_id, '_llms_has_transaction' );
		return $order_id;
	}

	/**
	 * Test replace_weak_password_meter_descriptions().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_replace_weak_password_meter_descriptions() {

		$replacement = llms_get_password_meter_description( 'weak' );
		$strong      = 'A strong password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.';
		$custom      = 'Please use a long password.';

		$form_id = $this->factory->post->create(
			array(
				'post_type'    => 'llms_form',
				'post_content' => 'before ' . self::LEGACY_DESCRIPTION . ' after ' . self::LEGACY_DESCRIPTION,
			)
		);
		$block_id = $this->factory->post->create(
			array(
				'post_type'    => 'wp_block',
				'post_content' => self::LEGACY_DESCRIPTION,
			)
		);
		$other_id = $this->factory->post->create(
			array(
				'post_type'    => 'post',
				'post_content' => self::LEGACY_DESCRIPTION,
			)
		);
		$strong_id = $this->factory->post->create(
			array(
				'post_type'    => 'llms_form',
				'post_content' => $strong,
			)
		);
		$custom_id = $this->factory->post->create(
			array(
				'post_type'    => 'llms_form',
				'post_content' => $custom,
			)
		);

		add_filter( 'gettext', array( $this, 'translate_legacy_password_description' ), 10, 3 );

		$localized = sprintf(
			'Un mot de passe %s est requis avec au moins 8 caractères. Pour le renforcer, utilisez des lettres, des chiffres et des symboles.',
			'llms-weak'
		);
		$localized_id = $this->factory->post->create(
			array(
				'post_type'    => 'llms_form',
				'post_content' => $localized,
			)
		);

		\LLMS\Updates\Version_11_0_0\replace_weak_password_meter_descriptions();

		remove_filter( 'gettext', array( $this, 'translate_legacy_password_description' ), 10 );

		$this->assertSame(
			'before ' . $replacement . ' after ' . $replacement,
			get_post( $form_id )->post_content
		);
		$this->assertSame( $replacement, get_post( $block_id )->post_content );
		$this->assertSame( $replacement, get_post( $localized_id )->post_content );
		$this->assertSame( self::LEGACY_DESCRIPTION, get_post( $other_id )->post_content );
		$this->assertSame( $strong, get_post( $strong_id )->post_content );
		$this->assertSame( $custom, get_post( $custom_id )->post_content );
	}

	/**
	 * Translate the legacy weak password description for the current locale.
	 *
	 * @since [version]
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Text to translate.
	 * @param string $domain      Text domain.
	 * @return string
	 */
	public function translate_legacy_password_description( $translation, $text, $domain ) {

		if ( 'lifterlms' !== $domain ) {
			return $translation;
		}

		if ( 'weak' === $text ) {
			return 'llms-weak';
		}

		if ( 'A %s password is required with at least 8 characters. To make it stronger, use both upper and lower case letters, numbers, and symbols.' === $text ) {
			return 'Un mot de passe %s est requis avec au moins 8 caractères. Pour le renforcer, utilisez des lettres, des chiffres et des symboles.';
		}

		return $translation;
	}

	/**
	 * Test backfill_has_transaction_flag() flags only orders with transactions and
	 * paginates, returning true while more remain and false when complete.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_backfill_has_transaction_flag() {

		// 3 orders with transactions (per_page is 2, so this requires two passes).
		$with_txns = array(
			$this->create_legacy_order_with_transaction(),
			$this->create_legacy_order_with_transaction(),
			$this->create_legacy_order_with_transaction(),
		);

		// 1 order without a transaction (should never be flagged).
		$without_txn = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );

		// First pass: full page processed, more remain.
		$this->assertTrue( \LLMS\Updates\Version_11_0_0\backfill_has_transaction_flag() );

		// Second pass: remaining order processed, none left -> returns false.
		$this->assertFalse( \LLMS\Updates\Version_11_0_0\backfill_has_transaction_flag() );

		foreach ( $with_txns as $order_id ) {
			$this->assertEquals( 'yes', get_post_meta( $order_id, '_llms_has_transaction', true ), "Order {$order_id} should be flagged." );
		}

		$this->assertEmpty( get_post_meta( $without_txn, '_llms_has_transaction', true ) );
	}

	/**
	 * Test backfill_has_transaction_flag() returns false immediately with nothing to do.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_backfill_has_transaction_flag_noop() {
		$this->assertFalse( \LLMS\Updates\Version_11_0_0\backfill_has_transaction_flag() );
	}

	/**
	 * A trashed transaction does not flag its order.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_backfill_ignores_trashed_transactions() {

		$order_id = $this->factory->post->create( array( 'post_type' => 'llms_order' ) );
		$txn_id   = $this->factory->post->create(
			array(
				'post_type'   => 'llms_transaction',
				'post_status' => 'trash',
			)
		);
		update_post_meta( $txn_id, '_llms_order_id', $order_id );

		$this->assertFalse( \LLMS\Updates\Version_11_0_0\backfill_has_transaction_flag() );
		$this->assertEmpty( get_post_meta( $order_id, '_llms_has_transaction', true ) );
	}

	/**
	 * Test update_db_version().
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_update_db_version() {

		$orig = get_option( 'lifterlms_db_version' );

		delete_option( 'lifterlms_db_version' );

		\LLMS\Updates\Version_11_0_0\update_db_version();

		$this->assertEquals( \LLMS\Updates\Version_11_0_0\_get_db_version(), get_option( 'lifterlms_db_version' ) );

		update_option( 'lifterlms_db_version', $orig );
	}
}
