import { render } from 'react-dom';
import * as Icons from '../src';

const { Icon, ...icons } = Icons;

/**
 * React App that automatically renders all icons in the library into a static file.
 *
 * This app is rendered as a static HTML file and generate.js pulls the HTML the icons
 * and stores them as raw SVG files for display use in the README.md file between
 * the appropriate comment tokens.
 *
 * @since 1.0.0
 *
 * @return {Object} React component fragment.
 */
function App() {
	return (
		<>
			{ Object.entries( icons ).map( ( [ id, icon ] ) => {
				return (
					<div key={ id } id={ id }>
						<Icon icon={ icon } size="48" />
					</div>
				);
			} ) }
		</>
	);
}
render( <App />, document.getElementById( 'app' ) );
