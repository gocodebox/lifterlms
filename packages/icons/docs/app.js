/**
 * React App that automatically renders all icons in the library into a static file.
 *
 * This app is rendered as a static HTML file and generate.js pulls the HTML the icons
 * and stores them as raw SVG files for display use in the README.md file between
 * the appropriate comment tokens.
 */

import { render } from 'react-dom';
import * as Icons from '../src';

const { Icon, ...icons } = Icons;

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
