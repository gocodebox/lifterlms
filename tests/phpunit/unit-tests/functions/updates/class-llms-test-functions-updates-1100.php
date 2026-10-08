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
