/**
 * Editor registration for the latent/reading-time block.
 *
 * Plain JavaScript: no JSX and no build step. WordPress loads this file
 * directly and resolves the `wp-*` dependencies declared in index.asset.php.
 */
const el = window.wp.element.createElement;

window.wp.blocks.registerBlockType('latent/reading-time', {
	/**
	 * Renders the preview with the markup produced by render.php.
	 *
	 * @return {Object} Server-side render element.
	 */
	edit() {
		return el(window.wp.serverSideRender, {
			block: 'latent/reading-time',
		});
	},
	/**
	 * Markup is rendered server-side, so nothing is saved to post content.
	 *
	 * @return {null} Nothing.
	 */
	save() {
		return null;
	},
});
