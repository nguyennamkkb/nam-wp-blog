import { test, expect } from '@playwright/test';

import { hasHorizontalOverflow } from '../utils';

test.describe('Empty and error states', () => {
	test('unknown URLs return a real 404 with recovery links', async ({ page }) => {
		const response = await page.goto('/this-page-should-not-exist-xyz/');

		expect(response?.status()).toBe(404);
		await expect(page.getByRole('heading', { level: 1 })).toHaveText('Không tìm thấy trang');
		await expect(page.getByRole('link', { name: 'Về trang chủ' })).toBeVisible();
		await expect(page.getByRole('link', { name: 'Xem Blog' })).toBeVisible();
		await expect(page.getByRole('searchbox')).toBeVisible();

		expect(await page.content()).not.toMatch(/Fatal error|Warning:|Stack trace/);
	});

	test('search without matches shows a clear empty state', async ({ page }) => {
		const response = await page.goto('/?s=zzzzznope');

		expect(response?.status()).toBe(200);
		await expect(page.locator('.latent-query-status')).toContainText('0 kết quả');
		await expect(page.locator('.latent-empty')).toBeVisible();
		await expect(page.locator('.latent-empty__title')).toHaveText('Không tìm thấy bài viết phù hợp');
		await expect(page.getByRole('link', { name: 'xem tất cả bài viết' })).toBeVisible();
	});

	for (const width of [320, 390]) {
		test(`has no horizontal overflow at ${width}px`, async ({ page }) => {
			await page.setViewportSize({ width, height: 844 });

			for (const path of ['/', '/blog/', '/rag-tu-so-0-chunking/', '/series/']) {
				await page.goto(path);
				expect(await hasHorizontalOverflow(page), `${path} overflows at ${width}px`).toBe(false);
			}
		});
	}
});
