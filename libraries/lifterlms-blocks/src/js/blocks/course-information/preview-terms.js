/**
 * Component used to create a list of taxonomy terms in the block editor preview area
 *
 * @since Unknown
 * @version Unknown
 */

import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';

/**
 * Terms list preview component
 *
 * @since Unknown
 */
export default class Preview extends Component {
	state = {
		terms: false,
	};

	/**
	 * Retrieve a list of terms via the WP REST API
	 *
	 * @since Unknown
	 *
	 * @return {void}
	 */
	getTerms() {
		const { currentPost, taxonomy } = this.props;

		const link = currentPost._links[ 'wp:term' ].filter(
			( term ) => term.taxonomy === taxonomy
		)[ 0 ].href;

		wp.apiFetch( {
			url: wp.url.addQueryArgs( link, { per_page: -1 } ), // eslint-disable-line camelcase
		} ).then( ( terms ) => {
			this.setState( { terms } );
		} );
	}

	/**
	 * Determines if terms should be retrieved via the API
	 *
	 * @since Unknown
	 *
	 * @param {Object} lastProps  Last known properties object.
	 * @return {void}
	 */
	componentDidUpdate( lastProps ) {
		if (
			lastProps.currentPost[ this.props.taxonomy ] !==
			this.props.currentPost[ this.props.taxonomy ]
		) {
			this.getTerms();
		}
	}

	/**
	 * Fetch new terms via the API before the component mounts.
	 *
	 * @since Unknown
	 *
	 * @return {void}
	 */
	componentWillMount() {
		this.getTerms();
	}

	/**
	 * Renders the HTML for the terms list
	 *
	 * @since Unknown
	 *
	 * @param {Array<Object>} terms Array of term objects.
	 * @return {Fragment} Component html fragment.
	 */
	renderTerms( terms ) {
		const last = terms.length - 1;
		return (
			<Fragment>
				{ !! terms
					? terms.map( ( term, index ) =>
							this.renderTerm( term, last === index )
					  )
					: __( 'Loading…', 'lifterlms' ) }
			</Fragment>
		);
	}

	/**
	 * Render the HTML for a single term.
	 *
	 * @since Unknown
	 *
	 * @param {Object}  term A term object.
	 * @param {boolean} last Whether or not this is the last term in the list.
	 * @return {Fragment} Component html fragment.
	 */
	renderTerm( term, last ) {
		return (
			<Fragment>
				<a href={ term.link } target="_blank" rel="noreferrer">
					{ term.name }
				</a>
				{ last ? '' : ', ' }
			</Fragment>
		);
	}

	/**
	 * Render the component.
	 *
	 * @since Unknown
	 *
	 * @return {Fragment} Component html fragment.
	 */
	render() {
		const { terms } = this.state;
		const { taxonomyName } = this.props;

		return Array.isArray( terms ) && ! terms.length ? (
			''
		) : (
			<li>
				<strong>{ taxonomyName }</strong>: { this.renderTerms( terms ) }
			</li>
		);
	}
}
