/**
 * Purpose Cards Accordion – frontend: horizontal accordion, click to expand/collapse.
 */
(function () {
	'use strict';

	function initAccordions() {
		var accordions = document.querySelectorAll('.onepoint-purpose-cards:not([data-editor-preview])');
		accordions.forEach(function (el) {
			if (el.dataset.purposeCardsInit) return;
			el.dataset.purposeCardsInit = '1';

			var trackInner = el.querySelector('.onepoint-purpose-cards__track-inner');
			var cards = trackInner ? trackInner.querySelectorAll('.onepoint-purpose-cards__card') : [];
			if (cards.length === 0 || !trackInner) return;

			cards.forEach(function (card) {
				card.addEventListener('click', function () {
					cards.forEach(function (c) {
						c.classList.remove('is-active');
					});
					card.classList.add('is-active');
				});
			});
		});
	}

	function run() {
		initAccordions();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', run);
	} else {
		run();
	}
	window.addEventListener('load', run);
})();
