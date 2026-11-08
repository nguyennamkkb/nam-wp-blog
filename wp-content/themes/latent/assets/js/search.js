/**
 * Latent command palette search.
 *
 * Wires the header trigger and the `Ctrl/Cmd + K` shortcut to a native
 * `<dialog>`, queries the WordPress REST search endpoint and renders the
 * results with DOM APIs only. Keeps the no-JS search page as the fallback.
 */

(function () {
	'use strict';

	const DEBOUNCE_MS = 250;
	const MIN_CHARS = 2;
	const PER_PAGE = 8;

	const dialog = document.getElementById('latent-search');

	if (!dialog) {
		return;
	}

	const input = dialog.querySelector('.latent-search__input');
	const results = dialog.querySelector('.latent-search__results');
	const status = dialog.querySelector('.latent-search__status');
	const closeButton = dialog.querySelector('.latent-search__close');
	const panel = dialog.querySelector('.latent-search__panel');

	if (!input || !results || !status || !closeButton || !panel) {
		return;
	}

	const triggers = Array.prototype.slice.call(
		document.querySelectorAll('.latent-search-open')
	);

	let trigger = null;
	let controller = null;
	let debounceTimer = 0;
	let activeIndex = -1;
	let requestId = 0;
	let items = [];

	/**
	 * Reads the REST root WordPress advertises in the document head.
	 *
	 * @return {string} Absolute REST API base URL, with a trailing slash.
	 */
	function getRestBase() {
		const link = document.querySelector('link[rel="https://api.w.org/"]');
		const href = link ? link.getAttribute('href') : '';

		if (href) {
			return href;
		}

		return window.location.origin + '/wp-json/';
	}

	/**
	 * Reads the site home URL advertised by the REST link.
	 *
	 * @return {string} Absolute site URL, with a trailing slash.
	 */
	function getHomeUrl() {
		const restBase = getRestBase();

		try {
			return new URL(restBase).href.replace(/wp-json\/?$/, '');
		} catch {
			return window.location.origin + '/';
		}
	}

	/**
	 * Builds the REST search request URL for a query.
	 *
	 * @param {string} query Sanitised search term.
	 * @return {string} Absolute request URL.
	 */
	function buildSearchUrl(query) {
		const url = new URL('wp/v2/search', getRestBase());

		url.searchParams.set('search', query);
		url.searchParams.set('per_page', String(PER_PAGE));
		url.searchParams.set('_fields', 'id,title,url,subtype');

		return url.toString();
	}

	/**
	 * Builds the server-rendered search fallback URL.
	 *
	 * @param {string} query Search term.
	 * @return {string} Absolute search page URL.
	 */
	function buildSearchPageUrl(query) {
		return getHomeUrl() + '?s=' + encodeURIComponent(query);
	}

	/**
	 * Validates a result URL and rejects non-HTTP protocols.
	 *
	 * @param {unknown} value Raw URL from the API.
	 * @return {string} Safe absolute URL, or an empty string.
	 */
	function safeUrl(value) {
		if (typeof value !== 'string' || value === '') {
			return '';
		}

		try {
			const url = new URL(value, window.location.origin);

			if (url.protocol !== 'http:' && url.protocol !== 'https:') {
				return '';
			}

			return url.href;
		} catch {
			return '';
		}
	}

	/**
	 * Maps a REST subtype to a short Vietnamese label.
	 *
	 * @param {unknown} subtype REST result subtype.
	 * @return {string} Human-readable label.
	 */
	function subtypeLabel(subtype) {
		if (subtype === 'post') {
			return 'Bài viết';
		}

		if (subtype === 'page') {
			return 'Trang';
		}

		return typeof subtype === 'string' ? subtype : '';
	}

	/**
	 * Updates the palette state and its live status message.
	 *
	 * @param {string} message Text announced to assistive technology.
	 * @param {string} state   One of idle, loading, results, empty, error.
	 */
	function setStatus(message, state) {
		status.textContent = message;
		dialog.dataset.state = state;
	}

	/**
	 * Removes every rendered result and resets the active descendant.
	 */
	function clearResults() {
		results.textContent = '';
		results.hidden = true;
		items = [];
		activeIndex = -1;
		input.setAttribute('aria-expanded', 'false');
		input.removeAttribute('aria-activedescendant');
	}

	/**
	 * Marks one result as active and exposes it through aria-activedescendant.
	 *
	 * @param {number} index Index to activate, or -1 for none.
	 */
	function setActive(index) {
		items.forEach((item, itemIndex) => {
			const isActive = itemIndex === index;

			item.element.classList.toggle('is-active', isActive);
			item.element.setAttribute(
				'aria-selected',
				isActive ? 'true' : 'false'
			);
		});

		activeIndex = index;

		if (index > -1 && items[index]) {
			input.setAttribute(
				'aria-activedescendant',
				items[index].element.id
			);

			if (typeof items[index].element.scrollIntoView === 'function') {
				items[index].element.scrollIntoView({ block: 'nearest' });
			}

			return;
		}

		input.removeAttribute('aria-activedescendant');
	}

	/**
	 * Moves the active result, wrapping at both ends.
	 *
	 * @param {number} delta Direction: 1 for next, -1 for previous.
	 */
	function moveActive(delta) {
		if (!items.length) {
			return;
		}

		let next = activeIndex + delta;

		if (next < 0) {
			next = items.length - 1;
		}

		if (next >= items.length) {
			next = 0;
		}

		setActive(next);
	}

	/**
	 * Renders API results as links using textContent only.
	 *
	 * @param {Array<Object>} data REST search results.
	 */
	function renderResults(data) {
		const fragment = document.createDocumentFragment();

		items = [];

		data.forEach((result) => {
			const url = safeUrl(result && result.url);

			if (!url) {
				return;
			}

			const index = items.length;
			const item = document.createElement('li');
			const link = document.createElement('a');
			const title = document.createElement('span');
			const subtype = document.createElement('span');

			item.className = 'latent-search__result-item';
			item.setAttribute('role', 'presentation');

			link.className = 'latent-search__result';
			link.id = 'latent-search-result-' + index;
			link.href = url;
			link.tabIndex = -1;
			link.setAttribute('role', 'option');
			link.setAttribute('aria-selected', 'false');

			title.className = 'latent-search__result-title';
			title.textContent =
				typeof result.title === 'string' ? result.title : '';

			subtype.className = 'latent-search__result-subtype';
			subtype.textContent = subtypeLabel(result.subtype);

			link.append(title, subtype);
			item.append(link);
			fragment.append(item);
			items.push({ url, element: link });
		});

		results.textContent = '';
		results.append(fragment);
		results.hidden = items.length === 0;
		input.setAttribute('aria-expanded', items.length ? 'true' : 'false');

		if (items.length) {
			setActive(0);
		}
	}

	/**
	 * Requests results for a query and handles loading, success and errors.
	 *
	 * @param {string} query Search term with at least two characters.
	 */
	function startSearch(query) {
		const currentRequest = ++requestId;

		if (controller) {
			controller.abort();
		}

		controller = new AbortController();
		setStatus('Đang tìm…', 'loading');

		window
			.fetch(buildSearchUrl(query), {
				credentials: 'same-origin',
				headers: { Accept: 'application/json' },
				signal: controller.signal,
			})
			.then((response) => {
				if (!response.ok) {
					throw new Error('HTTP ' + response.status);
				}

				return response.json();
			})
			.then((data) => {
				if (currentRequest !== requestId) {
					return;
				}

				clearResults();

				if (!Array.isArray(data) || data.length === 0) {
					setStatus(
						'Không tìm thấy kết quả cho “' + query + '”.',
						'empty'
					);
					return;
				}

				renderResults(data);
				setStatus(
					data.length + ' kết quả cho “' + query + '”.',
					'results'
				);
			})
			.catch((error) => {
				if (currentRequest !== requestId) {
					return;
				}

				if (error && error.name === 'AbortError') {
					return;
				}

				clearResults();
				setStatus(
					'Không thể tìm kiếm lúc này. Kiểm tra kết nối rồi thử lại.',
					'error'
				);
			});
	}

	/**
	 * Cancels pending work and returns the palette to its idle state.
	 */
	function resetPalette() {
		input.value = '';

		if (debounceTimer) {
			window.clearTimeout(debounceTimer);
			debounceTimer = 0;
		}

		if (controller) {
			controller.abort();
			controller = null;
		}

		requestId += 1;
		clearResults();
		setStatus('Nhập ít nhất 2 ký tự để tìm bài viết.', 'idle');
	}

	/**
	 * Handles input debounce, minimum length and request cancellation.
	 */
	function onInput() {
		if (debounceTimer) {
			window.clearTimeout(debounceTimer);
			debounceTimer = 0;
		}

		if (controller) {
			controller.abort();
			controller = null;
		}

		requestId += 1;

		const query = input.value.trim();

		if (query.length < MIN_CHARS) {
			clearResults();
			setStatus(
				query.length === 0
					? 'Nhập ít nhất 2 ký tự để tìm bài viết.'
					: 'Nhập thêm một ký tự để tìm bài viết.',
				'idle'
			);
			return;
		}

		clearResults();
		setStatus('Đang tìm…', 'loading');

		debounceTimer = window.setTimeout(() => {
			debounceTimer = 0;
			startSearch(query);
		}, DEBOUNCE_MS);
	}

	/**
	 * Opens the modal palette and moves focus to the input.
	 *
	 * @param {Element|null} opener Element that triggered the palette.
	 */
	function openDialog(opener) {
		trigger = opener || document.querySelector('.latent-search-open');

		resetPalette();

		if (!dialog.open) {
			if (typeof dialog.showModal === 'function') {
				dialog.showModal();
			} else {
				dialog.setAttribute('open', '');
			}
		}

		document.documentElement.classList.add('latent-search-is-open');

		window.requestAnimationFrame(() => {
			input.focus();
		});
	}

	/**
	 * Closes the palette; the `close` handler restores focus.
	 */
	function closeDialog() {
		if (!dialog.open) {
			return;
		}

		if (typeof dialog.close === 'function') {
			dialog.close();
		} else {
			dialog.removeAttribute('open');
			dialog.dispatchEvent(new Event('close'));
		}
	}

	triggers.forEach((element) => {
		element.addEventListener('click', (event) => {
			event.preventDefault();
			openDialog(element);
		});
	});

	closeButton.addEventListener('click', closeDialog);
	input.addEventListener('input', onInput);

	input.addEventListener('keydown', (event) => {
		if (event.isComposing) {
			return;
		}

		if (event.key === 'ArrowDown' && items.length) {
			event.preventDefault();
			moveActive(1);
			return;
		}

		if (event.key === 'ArrowUp' && items.length) {
			event.preventDefault();
			moveActive(-1);
			return;
		}

		if (event.key === 'Enter') {
			if (activeIndex > -1 && items[activeIndex]) {
				event.preventDefault();
				items[activeIndex].element.click();
				return;
			}

			const query = input.value.trim();

			if (query.length >= MIN_CHARS) {
				event.preventDefault();
				window.location.assign(buildSearchPageUrl(query));
			}
		}
	});

	dialog.addEventListener('keydown', (event) => {
		if (event.key === 'Escape' && dialog.open) {
			event.preventDefault();
			event.stopPropagation();
			closeDialog();
		}
	});

	dialog.addEventListener('click', (event) => {
		if (event.target === dialog) {
			closeDialog();
		}
	});

	dialog.addEventListener('close', () => {
		document.documentElement.classList.remove('latent-search-is-open');
		requestId += 1;

		if (debounceTimer) {
			window.clearTimeout(debounceTimer);
			debounceTimer = 0;
		}

		if (controller) {
			controller.abort();
			controller = null;
		}

		const returnTarget =
			trigger || document.querySelector('.latent-search-open');

		trigger = null;

		if (returnTarget && typeof returnTarget.focus === 'function') {
			returnTarget.focus({ preventScroll: true });
		}
	});

	document.addEventListener('keydown', (event) => {
		const isShortcut =
			(event.ctrlKey || event.metaKey) &&
			!event.altKey &&
			!event.shiftKey &&
			(event.key === 'k' || event.key === 'K');

		if (!isShortcut) {
			return;
		}

		event.preventDefault();

		if (dialog.open) {
			closeDialog();
			return;
		}

		const active = dialog.ownerDocument.activeElement;
		const opener =
			active &&
			active.classList &&
			active.classList.contains('latent-search-open')
				? active
				: document.querySelector('.latent-search-open');

		openDialog(opener);
	});
})();
