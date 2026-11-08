import { test, expect } from '@playwright/test';

const POST_PATH = '/rag-tu-so-0-chunking/';

test.describe('Single post', () => {
	test.beforeEach(async ({ page }) => {
		await page.goto(POST_PATH);
	});

	test('renders one H1, a matching TOC, reading time, author and related posts', async ({ page }) => {
		await expect(page.getByRole('heading', { level: 1 })).toHaveCount(1);
		await expect(page.getByRole('heading', { level: 1 })).toContainText('RAG từ số 0');

		const toc = page.locator('nav[data-latent-toc]');
		const tocLinks = toc.locator('a[href^="#"]');
		const contentHeadings = page.locator('.latent-prose :is(h2, h3)[id]');

		await expect(toc).toBeVisible();
		await expect(contentHeadings).toHaveCount(4);
		await expect(tocLinks).toHaveCount(4);

		const headingIds = await contentHeadings.evaluateAll((headings) =>
			headings.map((heading) => `#${heading.id}`)
		);
		const tocHrefs = await tocLinks.evaluateAll((links) =>
			links.map((link) => link.getAttribute('href'))
		);

		expect(tocHrefs).toEqual(headingIds);

		await expect(page.locator('.latent-reading-time')).toHaveText(/\d+ phút đọc/);
		await expect(page.locator('.latent-author')).toBeVisible();
		await expect(page.locator('.latent-author .wp-block-post-author__name')).not.toBeEmpty();
		await expect(page.locator('.latent-related__card')).toHaveCount(3);
	});

	test('TOC links scroll to and focus their headings', async ({ page }) => {
		const firstLink = page.locator('nav[data-latent-toc] a[href^="#"]').first();
		const toggle = page.locator('[data-latent-toc-toggle]');

		if (!(await firstLink.isVisible())) {
			await toggle.click();
			await expect(toggle).toHaveAttribute('aria-expanded', 'true');
			await expect(firstLink).toBeVisible();
		}

		const href = await firstLink.getAttribute('href');

		expect(href).toBeTruthy();

		await firstLink.click();

		await expect(page).toHaveURL(new RegExp(`${href}$`));
		await expect(page.locator(href as string)).toBeInViewport();

		const activeLink = page.locator('nav[data-latent-toc] a[aria-current="location"]');

		await expect(activeLink).toHaveCount(1);
	});

	test('code blocks copy with visible live feedback', async ({ page, context }) => {
		try {
			await context.grantPermissions(['clipboard-read', 'clipboard-write']);
		} catch {
			// Firefox does not expose clipboard permissions; the click gesture is
			// enough for its clipboard implementation.
		}

		const copyButton = page.locator('.latent-code__copy').first();
		const status = page.locator('.latent-code__status').first();

		await expect(copyButton).toBeVisible();
		await expect(status).toHaveAttribute('aria-live', 'polite');

		await copyButton.click();

		await expect(status).toHaveText('Đã copy');
		await expect(copyButton).toHaveAttribute('data-copied', 'true');
	});
});
