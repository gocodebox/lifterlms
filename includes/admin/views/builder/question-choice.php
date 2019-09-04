<?php
/**
 * Builder question view
 *
 * @since   3.16.0
 * @version 3.17.8
 */
?>
<script type="text/html" id="tmpl-llms-question-choice-template">

	<label class="llms-choice-id">
		<# if ( data.is_selectable() ) { #>
			<input name="correct" type="checkbox"<# if ( data.get( 'correct' ) ) { print( ' checked="checked"' ) ;} #>>
		<# } #>
		<span class="llms-marker<# if ( data.is_selectable() ) { print ( ' selectable' ) }#>">
			<b>{{{ data.get( 'marker' ) }}}</b>
			<i class="fa fa-check" aria-hidden="true"></i>
		</span>
	</label>

	<# if ( 'text' === data.get( 'choice_type' ) ) { #>
		<div class="llms-input-wrapper">
			<span class="llms-input llms-editable-title" contenteditable="true" data-attribute="choice" data-formatting="b,i,u,em,strong" data-original-content="{{{ data.get( 'choice' ) }}}" data-placeholder="<?php esc_attr_e( 'Enter a choice...', 'lifterlms' ); ?>">{{{ data.get( 'choice' ) }}}</span>
		</div>
	<# } else if ( 'image' === data.get( 'choice_type' ) ) { #>
		<div class="llms-editable-image">
			<# if ( data.get( 'choice' ).get( 'src' ) ) { #>
				<div class="llms-image">
					<a class="llms-action-icon danger tip--top-left" data-attribute="choice" data-tip="<?php esc_attr_e( 'Remove image', 'lifterlms' ); ?>" href="#llms-remove-image">
						<i class="fa fa-times-circle" aria-hidden="true"></i>
						<span class="screen-reader-text"><?php _e( 'Remove image', 'lifterlms' ); ?></span>
					</a>
					<img alt="<?php esc_attr_e( 'image preview', 'lifterlms' ); ?>" src="{{{ data.get( 'choice' ).get( 'src' ) }}}">
				</div>
			<# } else { #>
				<button class="llms-element-button small llms-add-image" data-attribute="choice" data-image-size="full">
					<span class="fa fa-picture-o"></span> <?php _e( 'Add Image', 'lifterlms' ); ?>
				</button>
			<# } #>
		</div>
	<# } #>

	<div class="llms-action-icons">

		<a class="llms-action-icon circle tip--top-left" data-tip="<?php _e( 'Add Choice', 'lifterlms' ); ?>" href="#llms-add-choice" tabindex="-1">
			<i class="fa fa-plus" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Add Choice', 'lifterlms' ); ?></span>
		</a>

		<a class="llms-action-icon circle danger tip--top-left" data-tip="<?php _e( 'Delete Choice', 'lifterlms' ); ?>" href="#llms-del-choice" tabindex="-1">
			<i class="fa fa-minus" aria-hidden="true"></i>
			<span class="screen-reader-text"><?php _e( 'Delete Choice', 'lifterlms' ); ?></span>
		</a>

	</div>

</script>
