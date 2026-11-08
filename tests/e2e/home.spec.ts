import { test, expect } from '@playwright/test';

test.describe('Home', () => {
	test.beforeEach(async ({ page }) => {
		await page.goto('/');
	});

	test('renders the hero, one featured post and six latest cards', async ({ page }) => {
		const h1 = page.getByRole('heading', { level: 1 });

		await expect(h1).toHaveCount(1);
		await expect(h1).toContainText('Ghi chú thực chiến');

		const featured = page.locator('.latent-featured');

		await expect(featured).toBeVisible();
		await expect(featured.locator('.latent-featured__title a')).toHaveAttribute('href', /\S+/);

		const latestSection = page
			.locator('.latent-section')
			.filter({ has: page.getByRole('heading', { name: 'Bài mới', exact: true }) });

		await expect(latestSection).toBeVisible();
		await expect(latestSection.locator('.latent-card')).toHaveCount(6);

		const seriesStrip = page.locator('.latent-series-strip');

		await expect(seriesStrip).toBeVisible();
		await expect(seriesStrip.getByRole('heading', { name: 'Series', exact: true })).toBeVisible();
	});

	test('has no console or page errors', async ({ page }) => {
		const errors: string[] = [];

		page.on('console', (message) => {
			if (message.type() === 'error') {
				errors.push(message.text());
			}
		});
		page.on('pageerror', (error) => {
			errors.push(error.message);
		});

		const response = await page.goto('/');

		expect(response?.status()).toBe(200);
		await page.waitForLoadState('networkidle');
		await expect(page.getByRole('heading', { level: 1 })).toBeVisible();

		expect(errors).toEqual([]);
	});
});
