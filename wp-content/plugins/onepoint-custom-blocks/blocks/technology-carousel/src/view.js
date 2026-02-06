/**
 * Technology Carousel block – frontend script (vertical upward scroll when > 9 items).
 * By default only 9 cards (3×3) are visible; with more than 9, the carousel slides upward.
 * Track is rendered as [content][content] (duplicate). Animating to -50% scrolls exactly
 * one content height, so the end state looks like the start – loop is seamless (top cards
 * come in from the bottom with no jump).
 */
(function () {
	'use strict';

	function initCarousels() {
		var wraps = document.querySelectorAll('.onepoint-tech-carousel-wrap[data-speed][data-count]');
		wraps.forEach(function (wrap) {
			if (wrap.dataset.carouselInit) return;

			var count = parseInt(wrap.dataset.count, 10) || 0;
			if (count <= 9) return;

			wrap.dataset.carouselInit = '1';
			wrap.classList.add('onepoint-tech-carousel-has-animation');

			var track = wrap.querySelector('.onepoint-tech-carousel-track');
			if (!track || !track.children.length) return;

			var speedSec = parseInt(wrap.dataset.speed, 10) || 25;
			if (!wrap.id) wrap.id = 'onepoint-tech-carousel-' + Math.random().toString(36).slice(2);

			/* Seamless loop: 0% = top of track, 100% = -50% (top of second duplicate = same as top). No jump on repeat. */
			var animName = 'onepoint-tech-carousel-run-' + Math.random().toString(36).slice(2);
			var styleEl = document.createElement('style');
			styleEl.textContent =
				'@keyframes ' + animName + ' { ' +
				'0% { transform: translateY(0); } ' +
				'100% { transform: translateY(-50%); } ' +
				'} ' +
				'#' + wrap.id + ' .onepoint-tech-carousel-track { ' +
				'animation: ' + animName + ' ' + speedSec + 's linear infinite; ' +
				'backface-visibility: hidden; ' +
				'}';
			wrap.appendChild(styleEl);
		});
	}

	function run() {
		initCarousels();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
	window.addEventListener('load', run);
})();
