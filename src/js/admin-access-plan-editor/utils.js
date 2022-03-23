import { __, _n, sprintf } from '@wordpress/i18n';

export function getTitle( { title } ) {

	const { raw: rawTitle } = title;
	return rawTitle || title;

}

export function getPricingType( { price, frequency } ) {

	if ( 0 === price ) {
		return 'free';
	}
	if ( 0 === frequency ) {
		return 'single';
	}
	return 'recurring';

}

function getOptions( optKey ) {
	return window.llms.accessPlanOptions[ optKey ];
}

export function getPlanLimit() {
	return getOptions( 'limit' );
}

export function getRedirectOptions() {
	const redirects = getOptions( 'redirects' ),
		opts = [];

	return Object.keys( redirects ).map( value => {
		return {
			label: redirects[ value ],
			value,
		};
	} );
}

export function getLengthOptions( period ) {

	const lengths = getOptions( 'lengths' ),
		maxLength = lengths[ period ],
		opts = [],
		getLabel = ( length ) => {

			if ( 0 === length ) {
				return __( 'until canceled', 'lifterlms' );
			}

			// Translators: %1$d = billing length; %2$s = singular period.
			return sprintf( _n( 'for %1$d payment', 'for %1$d payments', length, 'lifterlms' ), length );

		};

	let value = 0;
	while ( value <= maxLength ) {

		opts.push( {
			label: getLabel( value ),
			value,
		} );

		++value;
	}

	return opts;

}

export function getFrequencyOptions() {

	const opts = [],
		getLabel = ( frequency ) => {

			if ( 1 === frequency ) {
				return __( 'Every', 'lifterlms' );
			}

			const map = {

				2: __( 'second', 'lifterlms' ),
				3: __( 'third', 'lifterlms' ),
				4: __( 'fourth', 'lifterlms' ),
				5: __( 'fifth', 'lifterlms' ),
				6: __( 'sixth', 'lifterlms' ),

			};

			// Translators: %s = An ordinal number.
			return sprintf( __( 'Every %s', 'lifterlms' ), map[ frequency ] );

		};

	let value = 1;
	while ( value <= 6 ) {

		opts.push( {
			value,
			label: getLabel( value ),
		} );

		++value;

	}

	return opts;

}

export function getPeriodOptions( plural = false ) {
	const periods = getOptions( 'periods' ),
		index = plural ? 1 : 0;
	return Object.keys( periods ).map( value => {
		return {
			label: periods[ value ][ index ],
			value,
		};
	} );
}


export function getVisibilities() {
	return getOptions( 'visibilities' );
}
