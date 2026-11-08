/**
 * Editor registration for the latent/toc block.
 *
 * Plain JavaScript: no JSX and no build step.
 */
const el = window.wp.element.createElement;

window.wp.blocks.registerBlockType('latent/toc', {
	/**
	 * Renders the preview with the markup produced by render.php.
	 *
	 * @return {Object} Server-side render element.
	 */
	edit() {
		return el(window.wp.serverSideRender, {
			block: 'latent/toc',
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
