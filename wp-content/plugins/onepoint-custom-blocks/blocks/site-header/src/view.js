/**
 * Site Header block – frontend: mobile menu toggle.
 */
(function () {
	'use strict';
	function init() {
		document.querySelectorAll('.onepoint-site-header__toggle').forEach(function (btn) {
			if (btn.dataset.headerInit) return;
			btn.dataset.headerInit = '1';
			var nav = btn.closest('.onepoint-site-header') && btn.closest('.onepoint-site-header').querySelector('.onepoint-site-header__nav');
			if (!nav) return;
			btn.addEventListener('click', function () {
				var expanded = btn.getAttribute('aria-expanded') === 'true';
				btn.setAttribute('aria-expanded', !expanded);
				nav.classList.toggle('is-open', !expanded);
			});
		});
	}
	if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
	else init();
})();
