import { defineConfig, devices } from '@playwright/test';

/**
 * Latent E2E configuration.
 *
 * DDEV serves the site from outside this process, so there is deliberately no
 * `webServer` block. Point PLAYWRIGHT_BASE_URL at another environment to run
 * the same suite against staging.
 *
 * Local verified browser: chromium. Firefox and WebKit are configured for
 * parity but need extra OS libraries (`npx playwright install-deps`) on this
 * machine; see tests/README.md.
 */
const baseURL = process.env.PLAYWRIGHT_BASE_URL || 'https://wp-blog.ddev.site';

export default defineConfig({
	testDir: './tests',
	outputDir: './test-results',
	timeout: 60_000,
	expect: {
		timeout: 10_000,
		toHaveScreenshot: {
			animations: 'disabled',
			caret: 'hide',
			maxDiffPixelRatio: 0.015,
		},
	},
	fullyParallel: true,
	forbidOnly: Boolean(process.env.CI),
	retries: process.env.CI ? 1 : 0,
	workers: process.env.CI ? 1 : undefined,
	reporter: [
		['list'],
		['html', { outputFolder: 'playwright-report', open: 'never' }],
	],
	use: {
		baseURL,
		ignoreHTTPSErrors: true,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
		video: 'retain-on-failure',
		actionTimeout: 15_000,
		navigationTimeout: 30_000,
	},
	projects: [
		{
			name: 'chromium',
			use: { ...devices['Desktop Chrome'] },
		},
		{
			name: 'firefox',
			use: { ...devices['Desktop Firefox'] },
		},
		{
			name: 'webkit',
			use: { ...devices['Desktop Safari'] },
		},
		{
			name: 'mobile-chromium',
			use: {
				...devices['Pixel 7'],
				// Pixel-ish portrait viewport requested by the test plan.
				viewport: { width: 390, height: 844 },
			},
		},
	],
});
