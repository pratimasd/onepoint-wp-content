(function () {
	'use strict';

	function initCarousels() {
		var wraps = document.querySelectorAll('.onepoint-carousel-wrap[data-direction][data-speed]');
		wraps.forEach(function (wrap) {
			if (wrap.dataset.carouselInit) return;
			wrap.dataset.carouselInit = '1';

			var track = wrap.querySelector('.onepoint-carousel-track');
			if (!track || !track.children.length) return;

			var direction = wrap.dataset.direction || 'left';
			var speedSec = parseInt(wrap.dataset.speed, 10) || 30;
			var isLeft = direction === 'left';

			if (!wrap.id) wrap.id = 'onepoint-carousel-' + Math.random().toString(36).slice(2);

			// Track has 6 copies of the image set; move by one set (1/6) for seamless loop
			var setFraction = (100 / 6).toFixed(4);
			var translateStart = isLeft ? '0%' : '-' + setFraction + '%';
			var translateEnd = isLeft ? '-' + setFraction + '%' : '0%';

			var animName = 'onepoint-carousel-run-' + Math.random().toString(36).slice(2);
			var styleEl = document.createElement('style');
			styleEl.textContent =
				'@keyframes ' + animName + ' { ' +
				'0% { transform: translateX(' + translateStart + '); } ' +
				'100% { transform: translateX(' + translateEnd + '); } ' +
				'} ' +
				'#' + wrap.id + ' .onepoint-carousel-track { ' +
				'animation: ' + animName + ' ' + speedSec + 's linear infinite; ' +
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
	// Run again on load in case layout/scripts affected carousel (e.g. fonts loaded)
	window.addEventListener('load', run);
})();
