/**
 * Latent code block copy button.
 *
 * Progressive enhancement for `core/code` blocks rendered inside
 * `.latent-prose`: wraps each `pre` in a toolbar shell, adds a copy button,
 * an optional `language-*` label and an `aria-live` status. Copying uses the
 * async Clipboard API and falls back to `document.execCommand('copy')` on
 * browsers that lack it (or block it outside a secure context).
 */

(function () {
	'use strict';

	const COPY_LABEL = 'Copy';
	const COPY_ARIA_LABEL = 'Copy đoạn mã';
	const COPIED_MESSAGE = 'Đã copy';
	const ERROR_MESSAGE = 'Không copy được';
	const RESET_DELAY = 2400;

	/**
	 * Returns the exact text to copy for a code block.
	 *
	 * The toolbar is a sibling of `pre`, never a child, so `textContent` is
	 * already clean. It also preserves `<`, `>`, `&`, quotes and emoji.
	 *
	 * @param {HTMLPreElement} pre Code block element.
	 * @return {string} Text content of the code block.
	 */
	function codeText(pre) {
		const code = pre.querySelector('code');

		return code ? code.textContent : pre.textContent;
	}

	/**
	 * Reads a `language-*` class from the block or its `<code>` child.
	 *
	 * @param {HTMLPreElement} pre Code block element.
	 * @return {string} Language slug, or an empty string when absent.
	 */
	function codeLanguage(pre) {
		const candidates = [pre, pre.querySelector('code')];

		for (const element of candidates) {
			if (!element || !element.classList) {
				continue;
			}

			for (const className of element.classList) {
				const match = /^language-(.+)$/.exec(className);

				if (match) {
					return match[1].replace(/[-_]+/g, ' ');
				}
			}
		}

		return '';
	}

	/**
	 * Copies text with the async Clipboard API, falling back to a temporary
	 * textarea when the API is missing or rejected.
	 *
	 * @param {string} text Text to place on the clipboard.
	 * @return {Promise<boolean>} Whether the copy succeeded.
	 */
	async function copyText(text) {
		if (
			window.isSecureContext &&
			navigator.clipboard &&
			typeof navigator.clipboard.writeText === 'function'
		) {
			try {
				await navigator.clipboard.writeText(text);

				return true;
			} catch {
				// Permission denied or clipboard busy: try the legacy path.
			}
		}

		return legacyCopy(text);
	}

	/**
	 * Copies text with a hidden textarea and `execCommand`.
	 *
	 * @param {string} text Text to place on the clipboard.
	 * @return {boolean} Whether the copy succeeded.
	 */
	function legacyCopy(text) {
		const textarea = document.createElement('textarea');
		const previousFocus = textarea.ownerDocument.activeElement;

		textarea.value = text;
		textarea.setAttribute('readonly', '');
		textarea.setAttribute('aria-hidden', 'true');
		textarea.style.position = 'fixed';
		textarea.style.insetBlockStart = '0';
		textarea.style.insetInlineStart = '-9999px';
		textarea.style.opacity = '0';

		document.body.appendChild(textarea);
		textarea.select();
		textarea.setSelectionRange(0, text.length);

		let copied = false;

		try {
			copied = document.execCommand('copy');
		} catch {
			copied = false;
		}

		textarea.remove();

		if (previousFocus && typeof previousFocus.focus === 'function') {
			previousFocus.focus();
		}

		return copied;
	}

	/**
	 * Shows feedback for a block and clears it after a short delay.
	 *
	 * @param {HTMLElement} status  Live region for the current block.
	 * @param {string}      message Message to announce.
	 */
	function announce(status, message) {
		window.clearTimeout(status.dataset.resetTimer);
		status.textContent = message;

		status.dataset.resetTimer = String(
			window.setTimeout(() => {
				status.textContent = '';
			}, RESET_DELAY)
		);
	}

	/**
	 * Builds the toolbar shell around a code block.
	 *
	 * @param {HTMLPreElement} pre Code block element.
	 */
	function enhance(pre) {
		if (pre.dataset.latentCodeEnhanced === 'true') {
			return;
		}

		pre.dataset.latentCodeEnhanced = 'true';

		const wrapper = document.createElement('div');
		const toolbar = document.createElement('div');
		const language = codeLanguage(pre);

		wrapper.className = 'latent-code';
		toolbar.className = 'latent-code__toolbar';

		// Keep wide/full alignment on the wrapper so the block still spans the
		// layout slots it was authored with.
		const alignments = [
			'alignwide',
			'alignfull',
			'alignleft',
			'alignright',
		];

		for (const alignment of alignments) {
			if (pre.classList.contains(alignment)) {
				wrapper.classList.add(alignment);
				pre.classList.remove(alignment);
			}
		}

		if (language) {
			const label = document.createElement('span');

			label.className = 'latent-code__language';
			label.textContent = language;
			toolbar.appendChild(label);
		}

		const actions = document.createElement('div');
		const status = document.createElement('span');
		const button = document.createElement('button');

		actions.className = 'latent-code__actions';

		status.className = 'latent-code__status';
		status.setAttribute('role', 'status');
		status.setAttribute('aria-live', 'polite');

		button.type = 'button';
		button.className = 'latent-code__copy';
		button.setAttribute('aria-label', COPY_ARIA_LABEL);
		button.textContent = COPY_LABEL;

		actions.append(status, button);
		toolbar.appendChild(actions);

		pre.parentNode.insertBefore(wrapper, pre);
		wrapper.append(toolbar, pre);

		button.addEventListener('click', async () => {
			const copied = await copyText(codeText(pre));

			announce(status, copied ? COPIED_MESSAGE : ERROR_MESSAGE);

			if (copied) {
				button.dataset.copied = 'true';
				window.setTimeout(() => {
					delete button.dataset.copied;
				}, RESET_DELAY);
			}
		});
	}

	/**
	 * Enhances every code block inside the article body.
	 */
	function init() {
		const selector = '.latent-prose pre.wp-block-code, .latent-prose pre';

		document.querySelectorAll(selector).forEach(enhance);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
