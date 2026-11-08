/**
 * Latent theme toggle.
 *
 * The inline script in `inc/assets.php` sets `data-theme` before first paint.
 * This file only handles the manual toggle: persist the choice, keep the
 * button state and icons in sync, and follow the OS until a choice is made.
 */

(function () {
	'use strict';

	const STORAGE_KEY = 'latent-theme';
	const root = document.documentElement;
	const media = window.matchMedia
		? window.matchMedia('(prefers-color-scheme: dark)')
		: null;

	/**
	 * Reads the stored preference, ignoring invalid values.
	 *
	 * @return {string|null} "light", "dark", or null when unset.
	 */
	function readStoredTheme() {
		try {
			const stored = window.localStorage.getItem(STORAGE_KEY);
			return stored === 'light' || stored === 'dark' ? stored : null;
		} catch {
			return null;
		}
	}

	/**
	 * Returns the current operating-system preference.
	 *
	 * @return {string} "light" or "dark".
	 */
	function systemTheme() {
		return media && media.matches ? 'dark' : 'light';
	}

	/**
	 * Returns the theme currently applied to the document.
	 *
	 * @return {string} "light" or "dark".
	 */
	function currentTheme() {
		if (root.dataset.theme === 'light' || root.dataset.theme === 'dark') {
			return root.dataset.theme;
		}

		return readStoredTheme() || systemTheme();
	}

	/**
	 * Persists an explicit choice.
	 *
	 * @param {string} theme Theme to store.
	 */
	function storeTheme(theme) {
		try {
			window.localStorage.setItem(STORAGE_KEY, theme);
		} catch {
			// Storage may be unavailable (private mode); the session still works.
		}
	}

	/**
	 * Syncs the toggle's pressed state and sun/moon icon.
	 *
	 * @param {string} theme Active theme.
	 */
	function syncToggles(theme) {
		const isDark = theme === 'dark';

		document.querySelectorAll('.latent-theme-toggle').forEach((toggle) => {
			toggle.setAttribute('aria-pressed', isDark ? 'true' : 'false');

			const sun = toggle.querySelector('.latent-theme-toggle__icon--sun');
			const moon = toggle.querySelector(
				'.latent-theme-toggle__icon--moon'
			);

			// SVGSVGElement has no `hidden` IDL property, so toggle the
			// attribute directly (the markup ships `hidden` on the moon).
			if (sun) {
				sun.toggleAttribute('hidden', isDark);
			}

			if (moon) {
				moon.toggleAttribute('hidden', !isDark);
			}
		});
	}

	/**
	 * Applies a theme, optionally persisting it and notifying listeners.
	 *
	 * @param {string}  theme    Theme to apply.
	 * @param {boolean} persist  Whether to save the choice.
	 * @param {boolean} announce Whether to dispatch `latent:themechange`.
	 */
	function applyTheme(theme, persist, announce) {
		root.dataset.theme = theme;

		if (persist) {
			storeTheme(theme);
		}

		syncToggles(theme);

		if (announce) {
			document.dispatchEvent(
				new CustomEvent('latent:themechange', { detail: { theme } })
			);
		}
	}

	/**
	 * Flips between light and dark and remembers the choice.
	 */
	function onToggleClick() {
		applyTheme(currentTheme() === 'dark' ? 'light' : 'dark', true, true);
	}

	document.querySelectorAll('.latent-theme-toggle').forEach((toggle) => {
		toggle.addEventListener('click', onToggleClick);
	});

	// Until the visitor chooses, mirror OS changes live.
	if (media) {
		const onSystemChange = () => {
			if (readStoredTheme() === null) {
				applyTheme(systemTheme(), false, true);
			}
		};

		if (typeof media.addEventListener === 'function') {
			media.addEventListener('change', onSystemChange);
		} else if (typeof media.addListener === 'function') {
			media.addListener(onSystemChange);
		}
	}

	// The inline script already set the theme; just sync the button chrome.
	syncToggles(currentTheme());
})();
