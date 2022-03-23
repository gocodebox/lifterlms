import { __ } from '@wordpress/i18n';
import { ExternalLink, Snackbar } from '@wordpress/components';
import { getAuthority, getPathAndQueryString } from '@wordpress/url';

import { CopyButton } from '@lifterlms/components';

import { useEffect, useState } from '@wordpress/element';


export default function( { accessPlan } ) {

	const { permalink } = accessPlan,
		[ copied, setCopied ] = useState( false ),
		shortLink = `${ getAuthority( permalink ) }${ getPathAndQueryString( permalink ) }`;

	useEffect( () => {

		if ( copied ) {
			setTimeout( () => setCopied( false ), 1200 );
		}

	} );

	return (
		<>
			<CopyButton
				isSecondary
				buttonText={ __( 'Copy link', 'lifterlms' ) }
				copyText={ permalink }
				tooltipText={ __( 'Click to copy the permalink to the clipboard.', 'lifterlms' ) }
				onCopy={ () => setCopied( true ) }
				style={ { marginRight: '5px' } }
			/>
			{ copied && (
				<div style={ { position: 'absolute', left: '20px', bottom: '68px' } }>
					<Snackbar>{ __( 'Link copied', 'lifterlms' ) }</Snackbar>
				</div>
			) }
			<ExternalLink href={ permalink }>{ shortLink }</ExternalLink>
		</>
	);


	// buttonText,
	// copyText,
	// onCopy,
	// tooltipText = null,
	// ...buttonProps
}
