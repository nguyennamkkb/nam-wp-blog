import { test, expect } from '@playwright/test';

import { useTheme } from '../utils';

test.describe('Theme', () => {
	test('sets data-theme in the head before stylesheets and follows the OS', async ({ page }) => {
		await page.emulateMedia({ colorScheme: 'dark' });
		await page.addInitScript(() => {
			try {
				window.localStorage.removeItem('latent-theme');
			} catch {
				// Ignore private-mode storage failures.
			}
		});

		await page.goto('/');

		const html = page.locator('html');

		await expect(html).toHaveAttribute('data-theme', 'dark');

		const bootstrapRunsBeforeStyles = await page.evaluate(() => {
			const bootstrap = document.getElementById('latent-theme-init');
			const firstStyle = document.head?.querySelector('link[rel="stylesheet"], style');

			if (!bootstrap || !firstStyle) {
				return false;
			}

			return Boolean(
				bootstrap.compareDocumentPosition(firstStyle) & Node.DOCUMENT_POSITION_FOLLOWING
			);
		});

		expect(bootstrapRunsBeforeStyles).toBe(true);

		await page.emulateMedia({ colorScheme: 'light' });
		await expect(html).toHaveAttribute('data-theme', 'light');
	});

	test('toggle persists the choice across reloads and changes the palette', async ({ page }) => {
		await useTheme(page, 'light');
		await page.goto('/');

		const html = page.locator('html');
		const toggle = page.getByRole('button', { name: 'Chuyển chế độ sáng/tối' });

		await expect(html).toHaveAttribute('data-theme', 'light');
		await expect(toggle).toHaveAttribute('aria-pressed', 'false');

		const lightBackground = await page.evaluate(
			() => window.getComputedStyle(document.body).backgroundColor
		);

		await toggle.click();
		await expect(html).toHaveAttribute('data-theme', 'dark');
		await expect(toggle).toHaveAttribute('aria-pressed', 'true');

		const darkBackground = await page.evaluate(
			() => window.getComputedStyle(document.body).backgroundColor
		);

		expect(darkBackground).not.toBe(lightBackground);
		expect(await page.evaluate(() => window.localStorage.getItem('latent-theme'))).toBe('dark');

		await page.reload();
		await expect(html).toHaveAttribute('data-theme', 'dark');
		await expect(toggle).toHaveAttribute('aria-pressed', 'true');

		await toggle.click();
		await expect(html).toHaveAttribute('data-theme', 'light');
		await page.reload();
		await expect(html).toHaveAttribute('data-theme', 'light');
	});
});
