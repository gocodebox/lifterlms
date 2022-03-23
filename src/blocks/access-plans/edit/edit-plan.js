import classnames from 'classnames';

// WP Deps
import { useDispatch } from '@wordpress/data'
import { __ } from '@wordpress/i18n';
import { FlexItem } from '@wordpress/components';
import { RichText, getColorClassName } from '@wordpress/block-editor';

import {
	createHigherOrderComponent,
} from '@wordpress/compose';

// LLMS deps.
import { accessPlanStore } from '@lifterlms/data';


function getColorProps( className, { accentColor, accentTextColor, style = {} } ) {

	return {
		className: classnames( className, {
			[ getColorClassName( 'background-color', accentColor.slug ) ]: accentColor.slug,
			[ accentTextColor.class ]: accentTextColor.class,
		} ),
		style: {
			...style,
			backgroundColor: accentColor.slug ? null : accentColor.color,
			color: accentTextColor.slug ? null : accentTextColor.color,
		}
	};
}

// border: currentColor to use text color as the border color

export default function EditPlan( { plan, setAttributes, plansPerRow, accentColor, accentTextColor } ) {

	const { id, title, price, visibility, enroll_text, featured_text = __( 'Featured', 'lifterlms' ) } = plan,
		{ editPlan } = useDispatch( accessPlanStore ),
		isFeatured = 'featured' === visibility;

	if ( 'hidden' === visibility ) {
		return null;
	}

	const flexBasis = +( 100 / plansPerRow ).toFixed( 4 ) + '%',
		flexGrow = isFeatured ? 1.5 : 1;

	return (
		<FlexItem
			className={ classnames( 'llms-ap--wrap', {
				'is-featured-plan': isFeatured,
				// 'has-foreground-color': textColor.color,
				// [ textColor.class ]: textColor.class,
			} ) }
			style={ {
				border: isFeatured ? `1px solid ${ accentColor.color }` : null,
				flexShrink: 0,
				flexGrow,
				flexBasis,
				// border: '1px solid #'
				// color: textColor.color,
			} }
		>
			{ isFeatured && (
				<RichText
					tagName="strong"
					{ ...getColorProps( 'llms-ap--featured', { accentColor, accentTextColor } ) }
					allowedFormats={ [] }
					value={ featured_text }
					onChange={ ( newText ) => editPlan( id, { featured_text: newText } ) }
				/>
			) }
			<div>{ price.toFixed( 2 ) }</div>
			<RichText
				tagName="strong"
				className="llms-ap--title"
				allowedFormats={ [] }
				value={ title }
				onChange={ ( newText ) => editPlan( id, { title: newText } ) }
			/>
			<RichText
				tagName="div"
				{ ...getColorProps(
					'llms-ap--button wp-block-button__link',
					{
						accentColor,
						accentTextColor,
					}
				) }
				allowedFormats={ [] }
				value={ enroll_text }
				onChange={ ( newText ) => editPlan( id, { enroll_text: newText } ) }
			/>
		</FlexItem>
	);
}
