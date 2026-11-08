import { test, expect } from '@playwright/test';

import { settlePage, stubRemoteImages, useTheme } from '../utils';

type VisualCase = {
	name: string;
	path: string;
};

const cases: VisualCase[] = [
	{ name: 'home', path: '/' },
	{ name: 'single', path: '/rag-tu-so-0-chunking/' },
	{ name: 'blog', path: '/blog/' },
	{ name: 'series', path: '/series/rag-tu-so-0/' },
];

const themes = ['light', 'dark'] as const;

test.describe('Visual baselines', () => {
	test.beforeEach(async ({ page }, testInfo) => {
		test.skip(
			testInfo.project.name !== 'chromium',
			'Visual baselines are generated for desktop Chromium only.'
		);

		await stubRemoteImages(page);
	});

	for (const theme of themes) {
		for (const visualCase of cases) {
			test(`${visualCase.name} — ${theme}`, async ({ page }) => {
				await useTheme(page, theme);
				await page.goto(visualCase.path);
				await settlePage(page);

				await expect(page).toHaveScreenshot(`${visualCase.name}-${theme}.png`, {
					fullPage: true,
				});
			});
		}
	}
});
