/**
 * Latent table of contents.
 *
 * Enhances the server-rendered TOC: mobile toggle, smooth anchor scrolling
 * that respects reduced motion, and an IntersectionObserver scroll spy that
 * keeps `aria-current="location"` on the heading currently being read.
 */

(function () {
	'use strict';

	const navs = document.querySelectorAll('[data-latent-toc]');

	if (!navs.length) {
		return;
	}

	const reducedMotion = window.matchMedia
		? window.matchMedia('(prefers-reduced-motion: reduce)')
		: null;

	/**
	 * Returns the heading target for an in-page TOC link.
	 *
	 * @param {HTMLAnchorElement} link TOC link.
	 * @return {HTMLElement|null} Target heading.
	 */
	function getTarget(link) {
		const hash = link.getAttribute('href') || '';

		if (!hash.startsWith('#') || hash.length < 2) {
			return null;
		}

		try {
			return document.getElementById(decodeURIComponent(hash.slice(1)));
		} catch {
			return document.getElementById(hash.slice(1));
		}
	}

	/**
	 * Smoothly scrolls to a heading and moves focus there.
	 *
	 * @param {Event} event Click event.
	 */
	function handleLinkClick(event) {
		const target = getTarget(event.currentTarget);

		if (!target) {
			return;
		}

		event.preventDefault();

		const behavior =
			reducedMotion && reducedMotion.matches ? 'auto' : 'smooth';

		target.scrollIntoView({ behavior, block: 'start' });

		if (window.history && window.history.pushState) {
			window.history.pushState(null, '', event.currentTarget.hash);
		}

		target.setAttribute('tabindex', '-1');
		target.focus({ preventScroll: true });
		target.addEventListener(
			'blur',
			() => {
				target.removeAttribute('tabindex');
			},
			{ once: true }
		);
	}

	/**
	 * Wires up one TOC instance.
	 *
	 * @param {HTMLElement} nav TOC nav element.
	 */
	function initNav(nav) {
		const toggle = nav.querySelector('[data-latent-toc-toggle]');

		nav.classList.add('is-enhanced');

		if (toggle) {
			toggle.addEventListener('click', () => {
				const isOpen = toggle.getAttribute('aria-expanded') === 'true';

				toggle.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
				nav.classList.toggle('is-open', !isOpen);
			});
		}

		const links = Array.from(nav.querySelectorAll('a[href^="#"]'));

		if (!links.length) {
			return;
		}

		/** @type {Map<HTMLElement, HTMLAnchorElement>} */
		const targets = new Map();

		links.forEach((link) => {
			const target = getTarget(link);

			if (!target) {
				return;
			}

			targets.set(target, link);
			link.addEventListener('click', handleLinkClick);
		});

		const headings = Array.from(targets.keys());

		if (!headings.length) {
			return;
		}

		let activeLink = null;

		/**
		 * Marks one link as the current location.
		 *
		 * @param {HTMLAnchorElement|null} link Link to activate.
		 */
		function setActive(link) {
			if (!link || link === activeLink) {
				return;
			}

			if (activeLink) {
				activeLink.removeAttribute('aria-current');
			}

			link.setAttribute('aria-current', 'location');
			activeLink = link;
		}

		/**
		 * Picks the last heading above the reading line.
		 */
		function updateActive() {
			const readingLine = window.innerHeight * 0.3;
			let current = headings[0];

			headings.forEach((heading) => {
				if (heading.getBoundingClientRect().top <= readingLine) {
					current = heading;
				}
			});

			setActive(targets.get(current) || null);
		}

		if ('IntersectionObserver' in window) {
			const observer = new window.IntersectionObserver(updateActive, {
				rootMargin: '-20% 0px -60% 0px',
				threshold: [0, 1],
			});

			headings.forEach((heading) => observer.observe(heading));
		}

		let ticking = false;

		window.addEventListener(
			'scroll',
			() => {
				if (ticking) {
					return;
				}

				ticking = true;
				window.requestAnimationFrame(() => {
					updateActive();
					ticking = false;
				});
			},
			{ passive: true }
		);

		updateActive();
	}

	navs.forEach(initNav);
})();
