<?php
/**
 * Model Field Settings Template.
 *
 * @since 3.17.0
 * @since 3.24.0 Unknown.
 * @since 7.4.0 Added support for `upsell` field type and multiple input fields.
 * @version 7.4.0
 */
defined( 'ABSPATH' ) || exit;
?>
<script type="text/html" id="tmpl-llms-settings-fields-template">

<# _.each( data.get_groups(), function( group_data, group_id ) { #>
	<section class="llms-model-settings active settings-group--{{{ group_id }}}{{ data.is_group_hidden( group_id ) ? ' hidden' : '' }}" id="llms-{{{ data.model.get( 'type' ) }}}-settings-group--{{{ group_id }}}">

		<# if ( group_data.title ) { #>
			<header class="llms-settings-group-header">
				<h4 class="llms-settings-group-title">{{{ group_data.title }}}</h4>
				<# if ( group_data.toggleable ) { #>
					<a class="llms-action-icon llms-settings-group-toggle" href="#llms-group-toggle">
						<i class="fa fa-caret-up" aria-hidden="true"></i>
						<i class="fa fa-caret-down" aria-hidden="true"></i>
					</a>
				<# } #>
			</header>
		<# } #>

		<div class="llms-settings-group-body">

		<# _.each( group_data.fields, function( row, row_index ) { #>
			<div class="llms-settings-row">
			<# _.each( row, function( orig_field, field_index ) { #>

				<#
					var field = data.setup_field( orig_field, field_index );
					if ( ! field ) { return; }
				#>

				<div class="llms-settings-field settings-field--{{{ field.type }}}<# if ( field.label_after ) { #> has-label-after<# } #>" id="llms-model-settings-field--{{{ field.id }}}">

					<# if ( data.has_switch( field.type ) ) { #>
						<div class="llms-editable-select{{{ field.classes }}}" >
							<label class="llms-switch">
								<span class="llms-label">
									{{{ field.label }}}
									<# if ( field.tip ) { #>
										<span class="tip--{{{ field.tip_position }}}" data-tip="{{{ field.tip }}}"><i class="fa fa-question-circle"></i></span>
									<# } #>
								</span>
								<input data-on="{{{ field.switch_on }}}" data-off="{{{ field.switch_off }}}" data-rerender="{{{ data.should_rerender_on_toggle( field.type ) }}}" name="{{{ data.get_switch_attribute( field ) }}}" type="checkbox"{{{ _.checked( field.switch_on, data.model.get( data.get_switch_attribute( field ) ) ) }}}>
								<div class="llms-switch-slider"></div>
							</label>
						</div>
					<# } else if ( field.label ) { #>
						<span class="llms-label">
							{{{ field.label }}}
							<# if ( field.tip ) { #>
								<span class="tip--{{{ field.tip_position }}}" data-tip="{{{ field.tip }}}"><i class="fa fa-question-circle"></i></span>
							<# } #>
						</span>
					<# } #>

					<# if ( 'permalink' === field.type ) { #>

						<a target="_blank" href="{{{ data.model.get( 'permalink' ) }}}">{{{ data.model.get( 'permalink' ) }}}</a>
						<input class="llms-input permalink" data-attribute="name" data-original-content="{{{ data.model.get( 'name' ) }}}" data-type="permalink" name="name" type="text" value="{{{ data.model.get( 'name' ) }}}">
						<a class="llms-action-icon" href="#llms-edit-slug"><i class="fa fa-pencil" aria-hidden="true"></i></a>

					<# } else if ( 'upsell' === field.type ) { #>

						<a target="_blank" href="{{{ field.url }}}">
							<span class="llms-disabled">{{{ field.text }}}</span>
						</a>

					<# } else if ( 'select' === field.type || ( 'switch-select' === field.type && data.is_switch_condition_met( field ) ) ) { #>

						<div class="llms-editable-select{{{ field.classes }}}" >
							<select name="{{{ field.attribute }}}"{{{ field.multiple ? ' multiple' : '' }}}>{{{ data.render_select_options( field.options, field.attribute ) }}}</select>
						</div>

					<# } else if ( 'radio' === field.type || ( 'switch-radio' === field.type && data.is_switch_condition_met( field ) ) ) { #>

						<div class="llms-editable-radio{{{ field.classes }}}">
							<# _.each( field.options, function( label, val ) { #>
								<label for="{{{ field.id }}}_{{{ val }}}" class="llms-radio">
									<input id="{{{ field.id }}}_{{{ val }}}" name="{{{ field.attribute }}}" type="radio" value="{{{ val }}}"{{{ _.checked( val, data.model.get( field.attribute ) ) }}}>
									{{{ label }}}
								</label>
							<# } ); #>
						</div>

					<# } else if ( data.is_editor_field( field.type ) ) { #>

						<# if ( -1 === field.type.indexOf( 'switch-' ) || ( -1 !== field.type.indexOf( 'switch-' ) && data.is_switch_condition_met( field ) ) ) { #>
							<div class="llms-editable-editor{{{ field.classes }}}">
								<textarea data-attribute="{{{ field.attribute }}}" id="{{{ field.id }}}">{{{ data.model.get( field.attribute ) }}}</textarea>
							</div>
						<# } #>

					<# } else if ( data.is_default_field( field.type ) ) { #>

						<# const field_inputs = field.inputs?.length ? field.inputs : [ field ]; #>

						<# if ( -1 === field.type.indexOf( 'switch-' ) || ( -1 !== field.type.indexOf( 'switch-' ) && data.is_switch_condition_met( field ) ) ) { #>
							<div class="llms-editable-input{{{ field.classes }}}">
								<# field_inputs.forEach( input => { #>
									<div class="llms-input-wrapper">
										<# if ( field_inputs.length > 1 && input.label ) { #>
											<span class="label">{{{ input.label }}}</span>
										<# } #>
										<input
											class="llms-input standard"
											data-attribute="{{{ input.attribute }}}"
											data-original-content="{{{ data.model.get( input.attribute ) }}}"
											<# if ( 'datepicker' === input.type ) { #>
												<# if ( input.datepicker ) { #> data-date-datepicker="{{{ input.datepicker }}}" <# } #>
												<# if ( input.timepicker ) { #> data-date-timepicker="{{{ input.timepicker }}}" <# } #>
												<# if ( input.date_format ) { #> data-date-format="{{{ input.date_format }}}" <# } #>
											<# } #>
											<# if ( input.hasOwnProperty( 'min' ) ) { #> min="{{{ input.min }}}" <# } #>
											<# if ( input.hasOwnProperty( 'max' ) ) { #> max="{{{ input.max }}}" <# } #>
											name="{{{ input.attribute }}}"
											<# if ( input.placeholder ) { #> placeholder="{{{ input.placeholder }}}" <# } #>
											type="{{{ input.input_type }}}"
											value="{{{ data.model.get( input.attribute ) }}}"
										>
										<# if ( input.input_description ) { #>
											<small class="llms-description">{{{ input.input_description }}}</small>
										<# } #>
									</div>
								<# } ); #>
							</div>
						<# } #>
					<# } #>

					<# if ( field.label_after ) { #>
						<span class="llms-label llms-label--after">{{{ field.label_after }}}</span>
					<# } #>

					<# if ( field.detail ) { #>
						<div class="llms-detail">{{{ field.detail }}}</div>
					<# } #>
				</div>
			<# } ); #>
			</div>
		<# } ); #>

		</div>

	</section>
<# } ); #>

</script>
