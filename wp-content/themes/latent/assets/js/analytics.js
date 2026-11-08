/**
 * Latent privacy-first analytics bridge.
 *
 * Turns theme interactions into Umami events without sending content. Only
 * event names and non-identifying properties (theme name, outbound host) are
 * reported: never search terms, page text, emails or user IDs.
 *
 * Every call is a no-op until `window.umami` is available, so a blocked or
 * disabled tracker cannot break the page.
 */

(function () {
	'use strict';

	const SCROLL_THRESHOLD = 0.75;

	/**
	 * Sends one Umami event when the tracker is available.
	 *
	 * @param {string} name Event name.
	 * @param {Object} data Minimal, non-personal event properties.
	 */
	function track(name, data) {
		const umami = window.umami;

		if (!umami || typeof umami.track !== 'function') {
			return;
		}

		try {
			umami.track(name, data);
		} catch {
			// Analytics must never break the reading experience.
		}
	}

	/**
	 * Reports a click on a link that leaves the current site.
	 *
	 * Only the destination hostname is sent; paths and query strings may
	 * contain search terms or other content, so they are dropped.
	 *
	 * @param {HTMLAnchorElement} link Clicked link.
	 */
	function trackOutbound(link) {
		const href = link.getAttribute('href');

		if (!href) {
			return;
		}

		let url;

		try {
			url = new URL(href, window.location.href);
		} catch {
			return;
		}

		if (url.protocol !== 'http:' && url.protocol !== 'https:') {
			return;
		}

		if (url.hostname === window.location.hostname) {
			return;
		}

		track('outbound_click', { host: url.hostname });
	}

	// Theme toggle: the custom event is dispatched by `theme.js`.
	document.addEventListener('latent:themechange', (event) => {
		const theme =
			event.detail && event.detail.theme === 'dark' ? 'dark' : 'light';

		track('theme_toggle', { theme });
	});

	// Clicks are delegated so dynamically rendered results are covered too.
	document.addEventListener('click', (event) => {
		const target = event.target;

		if (!target || typeof target.closest !== 'function') {
			return;
		}

		if (target.closest('.latent-search-open')) {
			track('search_open');
			return;
		}

		if (target.closest('.latent-search__result')) {
			track('search_result_click');
			return;
		}

		if (target.closest('.latent-code__copy')) {
			track('code_copy');
			return;
		}

		const link = target.closest('a[href]');

		if (link) {
			trackOutbound(link);
		}
	});

	let scrollTracked = false;
	let scrollTicking = false;

	/**
	 * Records the 75% reading milestone once per page view.
	 */
	function checkScrollDepth() {
		scrollTicking = false;

		if (scrollTracked) {
			return;
		}

		const page = document.documentElement;
		const progress =
			(window.scrollY + window.innerHeight) / page.scrollHeight;

		if (progress < SCROLL_THRESHOLD) {
			return;
		}

		scrollTracked = true;
		track('scroll_75');
	}

	window.addEventListener(
		'scroll',
		() => {
			if (scrollTicking) {
				return;
			}

			scrollTicking = true;
			window.requestAnimationFrame(checkScrollDepth);
		},
		{ passive: true }
	);

	// A restored scroll position may already be past the threshold.
	window.requestAnimationFrame(checkScrollDepth);
})();
