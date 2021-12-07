// WP deps.
import { __ } from '@wordpress/i18n';
import { RichTextToolbarButton } from '@wordpress/block-editor';
import { Button, Modal } from '@wordpress/components';
import { registerFormatType, insert } from '@wordpress/rich-text';
import { useState } from '@wordpress/element';

import { CopyButton } from '@lifterlms/components';
import { Icon, lifterlms } from '@lifterlms/icons';

function MergeCodeList( { closeModal, onChange, value } ) {

	const { merge_codes: list } = window.llms.certificates;

	return Object.entries( list ).map( ( [ code, name ], index ) => {
		return (
			<tr key={ index }>
				<td style={ { textAlign: 'left' } }>{ name }</td>
				<td>
					<CopyButton
						buttonText={ code }
						copyText={ code }
						onCopy={ closeModal }
						isLink
					/>
				</td>
				<td>
					<Button
						isSecondary
						isSmall
						onClick={ () => {
							closeModal();
							onChange( insert( value, code ) );
						} }
					>
						{ __( 'Insert', 'lifterlms' ) }
					</Button>
				</td>
			</tr>
		);
	} );

}

function Edit( props ) {

	const [ isOpen, setOpen ] = useState( false ),
		openModal = () => setOpen( true ),
		closeModal = () => setOpen( false ),
		{ value, onChange } = props;

	return (
		<>
			<RichTextToolbarButton
				icon={ <Icon icon={ lifterlms } /> }
				title={ __( 'Merge Codes', 'lifterlms' ) }
				onClick={ openModal }
			/>

			{ isOpen && (
				<Modal
					className="llms-certificate-merge-codes-modal"
					title={ __(
						'LifterLMS Certificate Merge Codes',
						'lifterlms'
					) }
					onRequestClose={ closeModal }
				>
					<div className="llms-certificate-merge-codes-modal--main">
						<table className="llms-table zebra" style={ { width: '480px' } }>
							<thead>
								<tr>
									<th style={ { textAlign: 'left' } }>{ __( 'Name', 'lifterlms' ) }</th>
									<th>{ __( 'Merge code', 'lifterlms' ) }</th>
									<th>{ __( 'Insert', 'lifterlms' ) }</th>
								</tr>
							</thead>
							<tbody>
								<MergeCodeList { ...{ closeModal, onChange, value } } />
							</tbody>
						</table>
					</div>
				</Modal>
			) }
		</>
	);

}


registerFormatType( 'llms/certificate-merge-codes', {
	title: __( 'LifterLMS Certificate Merge Codes', 'lifterlms' ),
	tagName: 'span',
	className: 'llms-cert-mc-wrap',
	edit: Edit,
} );
