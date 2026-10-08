/**
 * Shortcode registerFormatType() edit callback function file
 *
 * @since 2.0.0
 * @version 2.0.0
 */

// Internal deps.
import LifterLMSIcon from '../../icons/lifterlms-icon';
import { default as Table } from './table';

// WP deps.
import { __ } from '@wordpress/i18n';
import { Modal, TextControl } from '@wordpress/components';
import { useState } from '@wordpress/element';
import { RichTextToolbarButton } from '@wordpress/block-editor';

/**
 * Edit function component
 *
 * @since 2.0.0
 *
 * @param {Object} props Properties from registerFormatType().
 * @return {Object} Component fragment.
 */
export default function ( props ) {
	const [ isOpen, setOpen ] = useState( false ),
		[ searchQuery, setSearchQuery ] = useState( '' ),
		[ defaultValue, setDefaultValue ] = useState( '' ),
		openModal = () => setOpen( true ),
		closeModal = () => setOpen( false ),
		{ value, onChange, isActive } = props;

	return (
		<>
			<RichTextToolbarButton
				icon={ <LifterLMSIcon /> }
				title={ __( 'Shortcodes', 'lifterlms' ) }
				onClick={ openModal }
			/>

			{ isOpen && (
				<Modal
					className="llms-shortcodes-modal"
					title={ __(
						'LifterLMS User Information Shortcodes',
						'lifterlms'
					) }
					onRequestClose={ closeModal }
				>
					<div className="llms-shortcodes-modal--main">
						<aside>
							<TextControl
								type="search"
								label={ __(
									'Filter by label, key, or ID…',
									'lifterlms'
								) }
								onChange={ ( userQuery ) =>
									setSearchQuery( userQuery )
								}
							/>

							<TextControl
								label={ __( 'Default value', 'lifterlms' ) }
								onChange={ ( userValue ) =>
									setDefaultValue( userValue )
								}
								help={ __(
									'Optional text displayed when no information exists or the user is logged out.',
									'lifterlms'
								) }
							/>
						</aside>

						<section>
							<Table
								closeModal={ closeModal }
								isActive={ isActive }
								onChange={ onChange }
								searchQuery={ searchQuery }
								value={ value }
								defaultValue={ defaultValue }
							/>
						</section>
					</div>
				</Modal>
			) }
		</>
	);
}
