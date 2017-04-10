<?php
?>

<div class="llms-metabox">
	<div class="llms-metabox-section d-all">

		<?php foreach ( $triggers as $trigger => $title ): ?>

			<button class="llms-button-secondary" name="<?php echo $trigger; ?>" type="button"><?php echo $title; ?></button>

		<?php endforeach; ?>

	</div>
</div>
