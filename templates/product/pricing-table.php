<?php
/**
 * Product (Course & Membership) Pricing Table Template
 *
 * @package LifterLMS/Templates/Product
 *
 * @since 3.0.0
 * @version 3.38.0
 *
 * @property LLMS_Product $product          Product object of the course or membership.
 * @property bool         $is_enrolled      Determines if current viewer is enrolled in $product.
 * @property bool         $purchasable      Determines if current product is purchasable.
 * @property bool         $has_free         Determines if any free access plans are available for the product.
 * @property bool         $has_restrictions Determines if any free access plans are available for the product.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fix variable spelling in a backwards compatible way
 *
 * If the function `lifterlms_template_pricing_table()` is plugged or this template is loaded
 * some other way, and the misspelled variable is still passed in this will ensure that the
 * template continues to function as expected.
 *
 * @link https://github.com/gocodebox/lifterlms/issues/1128
 *
 * @deprecated 3.38.0
 */
if ( isset( $purchaseable ) && ! isset( $purchasable ) ) {
	llms_deprecated_function( 'Passing variable `$purchaseable` to template "product/pricing-table.php"', '3.38.0', '`$purchasable`' );
	$purchasable = $purchaseable;
}

$free_only = ( $has_free && ! $purchasable );
?>

<?php if ( ! $is_enrolled && ! $has_restrictions && ( $purchasable || $has_free ) ) : ?>

	<?php
		/**
		 * Run prior to output of a course or membership pricing table.
		 *
		 * @since Unknown
		 *
		 * @param int $id WP_Post ID of the course or membership.
		 */
		do_action( 'lifterlms_before_access_plans', $product->get( 'id' ) );
	?>

	<section class="llms-access-plans cols-<?php echo $product->get_pricing_table_columns_count( $free_only ); ?>">

		<?php
			/**
			 * Run prior to listing access plans.
			 *
			 * @since Unknown
			 *
			 * @param int $id WP_Post ID of the course or membership.
			 */
			do_action( 'lifterlms_before_access_plans_loop', $product->get( 'id' ) );
		?>

		<?php foreach ( $product->get_access_plans( $free_only ) as $plan ) : ?>

			<?php
				/**
				 * Outputs a single access plan
				 *
				 * Hooked: llms_template_access_plan - 10
				 *
				 * @since Unknown
				 *
				 * @param LLMS_Access_Plan $plan Access plan object
				 */
				do_action( 'llms_access_plan', $plan );
			?>

		<?php endforeach; ?>

		<?php
			/**
			 * Run prior to listing access plans.
			 *
			 * @since Unknown
			 *
			 * @param int $id WP_Post ID of the course or membership.
			 */
			do_action( 'lifterlms_after_access_plans_loop', $product->get( 'id' ) );
		?>

	</section>

	<?php
		/**
		 * Run after output of a course or membership pricing table.
		 *
		 * @since Unknown
		 *
		 * @param int $id WP_Post ID of the course or membership.
		 */
		do_action( 'lifterlms_after_access_plans', $product->get( 'id' ) );
	?>

<?php elseif ( ! $is_enrolled ) : ?>

	<?php
		/**
		 * Pricing table output when the user is not enrolled but the product is not purchasable.
		 *
		 * Hooked: llms_template_product_not_purchasable - 10
		 *
		 * @since Unknown
		 *
		 * @param int $id WP_Post ID of the course or membership.
		 */
		do_action( 'lifterlms_product_not_purchasable', $product->get( 'id' ) );
	?>

<?php endif; ?>
