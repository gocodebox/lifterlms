import { __ } from '@wordpress/i18n';
import { PanelBody } from '@wordpress/components';
import { useRef, useEffect } from '@wordpress/element';

export const CourseBuilderPanel = () => {
	const ref = useRef( null );

	useEffect( () => {
		const builderLauncherDiv = document.querySelectorAll(
			'.llms-builder-launcher'
		)[ 0 ];

		if ( ! builderLauncherDiv ) {
			return null;
		}

		while ( ref.current.firstChild ) {
			ref.current.removeChild( ref.current.firstChild );
		}

		const clone = builderLauncherDiv.cloneNode( true );

		ref.current.appendChild( clone );
	}, [] );

	const courseBuilderPanelClassName = 'llms-course-builder-panel';

	return <>
		<PanelBody
			title={ __( 'Course Builder', 'lifterlms' ) }
			className={ courseBuilderPanelClassName }
			opened={ true }
			onToggle={ () => {
				const courseBuilderPanel = document.getElementsByClassName(
					courseBuilderPanelClassName
				)[ 0 ];

				courseBuilderPanel.classList.toggle(
					courseBuilderPanelClassName + '--close'
				);
			} }
		>
			<div ref={ ref }></div>
		</PanelBody>
	</>;
};
