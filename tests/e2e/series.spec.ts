import { test, expect } from '@playwright/test';

const SERIES_ARCHIVE = '/series/rag-tu-so-0/';

test.describe('Series', () => {
	test('archive lists parts oldest to newest', async ({ page }) => {
		await page.goto(SERIES_ARCHIVE);

		const titles = page.locator('.latent-query .latent-card__title a');

		await expect(titles).toHaveCount(3);
		await expect(titles).toHaveText([
			'Vector database: chọn gì cho RAG?',
			'3 cách giảm latency RAG 40%',
			'RAG từ số 0: chunking nào thực sự tốt?',
		]);

		const dates = await page
			.locator('.latent-query .latent-card__date time')
			.evaluateAll((elements) => elements.map((element) => element.getAttribute('datetime') ?? ''));

		expect(dates).toEqual([...dates].sort());
	});

	test('current part is marked with aria-current', async ({ page }) => {
		await page.goto('/giam-latency-rag-40/');

		const seriesNav = page.locator('nav.latent-series-nav');

		await expect(seriesNav).toBeVisible();
		await expect(seriesNav.locator('.latent-series-nav__title')).toHaveText('RAG từ số 0');
		await expect(seriesNav.locator('.latent-series-nav__count')).toContainText('Phần 2/3');

		const current = seriesNav.locator('.latent-series-nav__link[aria-current="page"]');

		await expect(current).toHaveCount(1);
		await expect(current).toContainText('3 cách giảm latency RAG 40%');
	});

	test('Series page lists all three series with part counts', async ({ page }) => {
		await page.goto('/series/');

		const items = page.locator('.latent-series-list__item');

		await expect(items).toHaveCount(3);
		await expect(items.locator('.latent-card__title a')).toHaveText([
			'AI Agents thực chiến',
			'Production AI',
			'RAG từ số 0',
		]);

		for (const item of await items.all()) {
			await expect(item.locator('.latent-series-list__meta')).toHaveText(/\d+ phần/);
		}
	});
});
