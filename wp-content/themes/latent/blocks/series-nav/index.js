/**
 * Editor registration for the latent/series-nav block.
 *
 * Plain JavaScript: no JSX and no build step.
 */
const el = window.wp.element.createElement;

window.wp.blocks.registerBlockType('latent/series-nav', {
	/**
	 * Renders the preview with the markup produced by render.php.
	 *
	 * @return {Object} Server-side render element.
	 */
	edit() {
		return el(window.wp.serverSideRender, {
			block: 'latent/series-nav',
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
