<?php
// phpcs:ignoreFile -- WordPress requires the index.asset.php filename for block asset metadata.
/**
 * Asset metadata for the latent/related-posts editor script.
 *
 * Declared by hand because the block ships without a build step.
 *
 * @package latent
 */

return array(
	'dependencies' => array( 'wp-blocks', 'wp-element', 'wp-server-side-render', 'wp-i18n' ),
	'version'      => '0.1.0',
);
