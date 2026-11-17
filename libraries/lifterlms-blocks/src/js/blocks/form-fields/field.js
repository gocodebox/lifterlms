/**
 * Generic Field component
 *
 * @since 1.6.0
 * @version 1.6.0
 */

// WP deps.
import { RichText } from '@wordpress/block-editor';
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { Slot } from '@wordpress/components';

// Internal deps.
import './editor.scss';
import InputGroupOptions from './input-group-options';

/**
 * Render a field in the block editor
 *
 * @since 1.6.0
 * @since 1.7.1 Add block editor rendering for password type fields.
 */
export default class Field extends Component {
	/**
	 * Determine the type of field.
	 *
	 * @since 1.7.1
	 *
	 * @return {string} Field type identifier.
	 */
	getFieldType() {
		const {
			attributes: { field },
		} = this.props;

		if (
			-1 !== [ 'email', 'text', 'number', 'url', 'tel' ].indexOf( field )
		) {
			return 'input';
		}

		return field;
	}

	/**
	 * Render the field.
	 *
	 * @since 1.6.0
	 * @since 1.7.1 Add rendering for password type fields.
	 *
	 * @return {Object} HTML Fragment.
	 */
	render() {
		const { attributes, setAttributes, block, clientId } = this.props,
			{ description, label, options, placeholder, required } = attributes,
			editFills = block.supports.llms_edit_fill;

		const classes = [];
		if ( required ) {
			classes.push( 'llms-is-required' );
		}

		const fieldType = this.getFieldType();

		/**
		 * Get the default option for a select field.
		 *
		 * @since 1.6.0
		 *
		 * @return {string} Default option value.
		 */
		const getDefaultOption = () => {
			if ( placeholder ) {
				return placeholder;
			}

			if ( ! options.length ) {
				return '';
			}

			let text = options[ 0 ].text;

			const defaults = options.filter( ( opt ) => 'yes' === opt.default );

			if ( defaults.length ) {
				text = defaults[ 0 ].text;
			}

			return text;
		};

		return (
			<Fragment>
				<div className="llms-field">
					{ 'html' !== fieldType && (
						<RichText
							tagName="label"
							className={ classes.join( ' ' ) }
							value={ label }
							onChange={ ( val ) => {
								setAttributes( {
									label: val,
								} );
							} }
							allowedFormats={ [ 'bold', 'italic' ] }
							aria-label={
								label
									? __( 'Field label' )
									: __(
											'Empty field label; start writing to add a label'
									  )
							}
							placeholder={ __( 'Enter a label' ) }
						/>
					) }
					{ 'input' === fieldType && (
						<input
							onChange={ ( event ) =>
								setAttributes( {
									placeholder: event.target.value,
								} )
							}
							value={ placeholder }
							placeholder={ __(
								'Add optional placeholder text',
								'lifterlms'
							) }
						/>
					) }
					{ 'password' === fieldType && (
						<input
							disabled="disabed"
							type="password"
							value="F4K3p4$50Rd"
						/>
					) }
					{ 'textarea' === fieldType && (
						<textarea
							rows={ this.props.attributes.html_attrs.rows }
							onChange={ ( event ) =>
								setAttributes( {
									placeholder: event.target.value,
								} )
							}
							value={ placeholder }
							placeholder={ __(
								'Add optional placeholder text',
								'lifterlms'
							) }
						/>
					) }
					{ 'select' === fieldType && (
						<select>
							<option>{ getDefaultOption() }</option>
						</select>
					) }
					<RichText
						tagName="span"
						value={ description }
						onChange={ ( val ) => {
							setAttributes( {
								description: val,
							} );
						} }
						allowedFormats={ [ 'bold', 'strikethrough', 'link' ] }
						aria-label={
							label
								? __( 'Optional field description' )
								: __(
										'Empty field description; start writing to add a description'
								  )
						}
						placeholder={ __( 'Add optional description text' ) }
						style={ { color: '#808285', fontStyle: 'italic' } }
					/>
					{ ( 'radio' === fieldType || 'checkbox' === fieldType ) && (
						<InputGroupOptions
							options={ options }
							fieldType={ fieldType }
						/>
					) }
				</div>

				{ editFills.after && (
					<Slot
						name={ `llmsEditFill.after.${ editFills.after }.${ clientId }` }
					/>
				) }
			</Fragment>
		);
	}
}
