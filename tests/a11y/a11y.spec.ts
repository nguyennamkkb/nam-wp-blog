import AxeBuilder from '@axe-core/playwright';
import { test, expect } from '@playwright/test';

import { stubRemoteImages, useTheme } from '../utils';

type PageCase = {
	name: string;
	path: string;
};

const pages: PageCase[] = [
	{ name: 'home', path: '/' },
	{ name: 'single', path: '/rag-tu-so-0-chunking/' },
	{ name: 'blog', path: '/blog/' },
	{ name: 'category', path: '/category/llm-systems/' },
	{ name: 'series archive', path: '/series/rag-tu-so-0/' },
	{ name: 'series page', path: '/series/' },
	{ name: 'search', path: '/?s=rag' },
	{ name: '404', path: '/this-page-should-not-exist-axe/' },
];

const themes = ['light', 'dark'] as const;

test.describe('Accessibility (axe)', () => {
	for (const theme of themes) {
		for (const pageCase of pages) {
			test(`${pageCase.name} — ${theme}`, async ({ page }) => {
				await stubRemoteImages(page);
				await useTheme(page, theme);
				await page.goto(pageCase.path);
				await page.waitForLoadState('networkidle');

				const results = await new AxeBuilder({ page })
					.withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'])
					.analyze();

				const blocking = results.violations.filter(
					(violation) => violation.impact === 'serious' || violation.impact === 'critical'
				);

				const summary = blocking
					.map((violation) => {
						const targets = violation.nodes
							.flatMap((node) => node.target)
							.join(', ');

						return `${violation.id} (${violation.impact}): ${violation.help} → ${targets}`;
					})
					.join('\n');

				expect(blocking, summary).toEqual([]);
			});
		}
	}
});
