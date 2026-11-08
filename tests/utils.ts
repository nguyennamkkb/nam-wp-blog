import { test, expect, type Page } from '@playwright/test';

/**
 * Waits for the page to finish loading fonts, lazy images and pending requests
 * so visual and layout assertions are stable.
 */
export async function settlePage(page: Page): Promise<void> {
	await page.waitForLoadState('networkidle');
	await page.evaluate(async () => {
		if (document.fonts && document.fonts.ready) {
			await document.fonts.ready;
		}
	});

	await page.evaluate(async () => {
		const step = Math.max(window.innerHeight - 100, 200);
		const height = document.documentElement.scrollHeight;

		for (let y = 0; y < height; y += step) {
			window.scrollTo(0, y);
			await new Promise((resolve) => window.setTimeout(resolve, 40));
		}

		window.scrollTo(0, 0);
	});

	await page.waitForLoadState('networkidle');
}

/**
 * Forces a stored theme before the document loads. The theme bootstrap script
 * in wp_head reads this value and sets data-theme before first paint.
 *
 * The value is seeded once per tab: later reloads keep whatever the page (or a
 * test) wrote, which is what the persistence assertions need to observe.
 */
export async function useTheme(page: Page, theme: 'light' | 'dark'): Promise<void> {
	await page.addInitScript((value) => {
		try {
			if (!window.sessionStorage.getItem('latent-theme-seeded')) {
				window.localStorage.setItem('latent-theme', value);
				window.sessionStorage.setItem('latent-theme-seeded', '1');
			}
		} catch {
			// Storage may be unavailable; the suite still exercises the fallback.
		}
	}, theme);

	await page.emulateMedia({ colorScheme: theme });
}

/**
 * Replaces remote avatar images with a transparent pixel so tests never depend
 * on gravatar.com being reachable and screenshots stay deterministic.
 */
export async function stubRemoteImages(page: Page): Promise<void> {
	const pixel = Buffer.from(
		'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
		'base64'
	);

	await page.route('**/secure.gravatar.com/**', (route) =>
		route.fulfill({ status: 200, contentType: 'image/png', body: pixel })
	);
}

/**
 * Reports whether the document is wider than the viewport by more than one
 * pixel (a one-pixel rounding allowance avoids false positives).
 */
export async function hasHorizontalOverflow(page: Page): Promise<boolean> {
	return page.evaluate(() => {
		const root = document.documentElement;

		return root.scrollWidth > window.innerWidth + 1;
	});
}
