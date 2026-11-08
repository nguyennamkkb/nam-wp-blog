import { test, expect, type Page } from '@playwright/test';

const searchRequests = (page: Page): string[] => {
	const requests: string[] = [];

	page.on('request', (request) => {
		if (request.url().includes('/wp/v2/search')) {
			requests.push(request.url());
		}
	});

	return requests;
};

const openWithButton = async (page: Page) => {
	await page.goto('/');
	await page.getByRole('button', { name: /Tìm kiếm/ }).click();

	const dialog = page.getByRole('dialog', { name: 'Tìm kiếm bài viết' });

	await expect(dialog).toBeVisible();

	return dialog;
};

test.describe('Search command palette', () => {
	test('opens from the header button and Ctrl+K, and returns focus on close', async ({ page }) => {
		const dialog = await openWithButton(page);
		const input = page.getByRole('combobox', { name: /Tìm bài viết/ });

		await expect(input).toBeFocused();

		await page.keyboard.press('Escape');
		await expect(dialog).not.toBeVisible();
		await expect(page.getByRole('button', { name: /Tìm kiếm/ })).toBeFocused();

		await page.keyboard.press('Control+k');
		await expect(dialog).toBeVisible();
		await expect(input).toBeFocused();
	});

	test('does not call the API below two characters', async ({ page }) => {
		const requests = searchRequests(page);
		const dialog = await openWithButton(page);
		const input = page.getByRole('combobox', { name: /Tìm bài viết/ });

		await input.fill('r');
		await page.waitForTimeout(500);
		await expect(dialog).toHaveAttribute('data-state', 'idle');
		expect(requests).toEqual([]);

		await input.fill('ra');
		await expect.poll(() => requests.length).toBeGreaterThan(0);
	});

	test('renders results and supports arrow navigation plus Enter', async ({ page }) => {
		const dialog = await openWithButton(page);
		const input = page.getByRole('combobox', { name: /Tìm bài viết/ });

		await input.fill('rag');
		await expect(dialog).toHaveAttribute('data-state', 'results');

		const options = page.getByRole('option');

		await expect(options.first()).toBeVisible();
		await expect(options.first()).toContainText(/rag/i);
		await expect(options.first()).toHaveAttribute('aria-selected', 'true');

		await input.press('ArrowDown');
		await expect(options.nth(1)).toHaveAttribute('aria-selected', 'true');
		await expect(options.first()).toHaveAttribute('aria-selected', 'false');

		await input.press('ArrowUp');
		await expect(options.first()).toHaveAttribute('aria-selected', 'true');

		const href = await options.first().getAttribute('href');

		expect(href).toBeTruthy();

		await input.press('Enter');
		await page.waitForURL(new RegExp(`${new URL(href as string).pathname}$`));
		await expect(page.getByRole('heading', { level: 1 })).toBeVisible();
	});

	test('keeps a script payload inert', async ({ page }) => {
		const alerts: string[] = [];

		page.on('dialog', async (browserDialog) => {
			alerts.push(browserDialog.message());
			await browserDialog.dismiss();
		});

		const dialog = await openWithButton(page);
		const input = page.getByRole('combobox', { name: /Tìm bài viết/ });
		const payload = '<script>alert(1)</script><img src=x onerror=alert(2)>';

		await input.fill(payload);
		await expect(dialog).toHaveAttribute('data-state', /empty|results/);

		expect(alerts).toEqual([]);
		await expect(page.locator('#latent-search-results script')).toHaveCount(0);
		await expect(page.locator('#latent-search-results img')).toHaveCount(0);
		expect(await page.locator('#latent-search-results').innerHTML()).not.toContain('<script');
	});

	test('server search fallback at /?s=rag works without JavaScript', async ({ page }) => {
		const response = await page.goto('/?s=rag');

		expect(response?.status()).toBe(200);
		await expect(page.getByRole('heading', { level: 1 })).toHaveText('Tìm kiếm');
		await expect(page.locator('.latent-query-status')).toContainText('8 kết quả');
		await expect(page.locator('.latent-query .latent-card')).toHaveCount(8);
	});
});
