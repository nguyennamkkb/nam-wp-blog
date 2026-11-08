export default {
	extends: ['@wordpress/stylelint-config'],
	ignoreFiles: [
		'**/node_modules/**',
		'**/vendor/**',
		'wp-admin/**',
		'wp-includes/**',
		'wp-content/uploads/**',
	],
};
