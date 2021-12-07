/**
 * React App that automatically renders all icons in the library into a table.
 *
 * This app is rendered as a static HTML file and generate.js pulls the HTML of the table
 * out of the file and stores it in the README.md file between the appropriate comment
 * tokens.
 */

import { render } from 'react-dom';
import { renderToStaticMarkup } from 'react-dom/server';
import * as Icons from '../src';

const { Icon, ...icons } = Icons;

/**
 * Render SVG components as a <img> with the svg as a data uri.
 *
 * GitHub Markdown won't render inline <svg> (I guess?).
 */
function SVG( { icon, id } ) {

    const svgString = encodeURIComponent( renderToStaticMarkup( <Icon icon={ icon } size="48" /> ) ),
		dataUri = `data:image/svg+xml,${ svgString }`;

	return (
		<img src={ dataUri } width="48" height="48" alt={ `${id} icon` } />
	);
}

function App() {
	return (
		<table>
			<thead>
				<tr>
					<th>Icon</th>
					<th>ID</th>
					<th>Usage</th>
				</tr>
			</thead>
			<tbody>
				{ Object.entries( icons ).map( ( [ id, icon ] ) => {
					return (
						<tr key={ icon }>
							<td><SVG icon={ icons[ id ] } id={ id } /></td>
							<td>{ id }</td>
							<td><code>{ `<Icon icon={ ${ id } } />` }</code></td>
						</tr>
					);
				} ) }
			</tbody>

		</table>
	);
}
render( <App />, document.getElementById( 'app' ) );
