import wordpress from '@wordpress/eslint-plugin';

export default [
	{
		ignores: [
			'**/node_modules/**',
			'**/vendor/**',
			'wp-admin/**',
			'wp-includes/**',
			'wp-content/uploads/**',
			'.ddev/**',
		],
	},
	...wordpress.configs.recommended,
];
