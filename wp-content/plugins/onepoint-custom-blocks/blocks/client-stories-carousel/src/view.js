/**
 * Client Stories Carousel – frontend: tabs, progress bar, play/pause, prev/next.
 */
(function () {
	'use strict';

	function initCarousels() {
		var carousels = document.querySelectorAll('.onepoint-client-stories:not([data-editor-preview])');
		carousels.forEach(function (el) {
			if (el.dataset.clientStoriesInit) return;
			el.dataset.clientStoriesInit = '1';

			var tabs = el.querySelectorAll('.onepoint-client-stories__tab');
			var slides = el.querySelectorAll('.onepoint-client-stories__slide');
			var prevBtn = el.querySelector('.onepoint-client-stories__arrow--prev');
			var nextBtn = el.querySelector('.onepoint-client-stories__arrow--next');
			var playPauseBtn = el.querySelector('.onepoint-client-stories__play-pause');

			var count = slides.length;
			if (count === 0) return;

			var autoplay = el.dataset.autoplay !== '0' && el.dataset.autoplay !== 'false';
			var intervalSec = Math.max(3, Math.min(15, parseInt(el.dataset.interval, 10) || 6));
			var current = 0;

			function restartProgressAnimation() {
				var activeTab = tabs[current];
				if (!activeTab) return;
				var progressFill = activeTab.querySelector('.onepoint-client-stories__tab-progress-fill');
				if (!progressFill) return;
				progressFill.style.animation = 'none';
				progressFill.offsetHeight;
				progressFill.style.animation = 'onepoint-progress-fill ' + intervalSec + 's linear forwards';
			}

			function goTo(index) {
				index = ((index % count) + count) % count;
				current = index;

				tabs.forEach(function (t, i) {
					t.classList.toggle('is-active', i === index);
					t.setAttribute('aria-selected', i === index);
				});
				slides.forEach(function (s, i) {
					s.classList.toggle('is-active', i === index);
					s.setAttribute('aria-hidden', i !== index);
				});

				restartProgressAnimation();
			}

			function advanceToNext() {
				if (!autoplay) return;
				goTo(current + 1);
			}

			function startAutoplay() {
				autoplay = true;
				if (playPauseBtn) playPauseBtn.classList.add('is-playing');
				if (playPauseBtn) playPauseBtn.classList.remove('is-paused');
				restartProgressAnimation();
			}

			function stopAutoplay() {
				autoplay = false;
				if (playPauseBtn) playPauseBtn.classList.remove('is-playing');
				if (playPauseBtn) playPauseBtn.classList.add('is-paused');
				var activeTab = tabs[current];
				var progressFill = activeTab && activeTab.querySelector('.onepoint-client-stories__tab-progress-fill');
				if (progressFill) progressFill.style.animationPlayState = 'paused';
			}

			tabs.forEach(function (tab, i) {
				tab.addEventListener('click', function () {
					goTo(i);
					if (autoplay) restartProgressAnimation();
				});
			});

			el.addEventListener('animationend', function (e) {
				if (e.target.classList && e.target.classList.contains('onepoint-client-stories__tab-progress-fill') && autoplay) {
					advanceToNext();
				}
			});

			if (prevBtn) {
				prevBtn.addEventListener('click', function () {
					goTo(current - 1);
					if (autoplay) restartProgressAnimation();
				});
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function () {
					goTo(current + 1);
					if (autoplay) restartProgressAnimation();
				});
			}
			if (playPauseBtn) {
				playPauseBtn.addEventListener('click', function () {
					autoplay = !autoplay;
					if (autoplay) {
						playPauseBtn.classList.add('is-playing');
						playPauseBtn.classList.remove('is-paused');
						var activeTab = tabs[current];
						var progressFill = activeTab && activeTab.querySelector('.onepoint-client-stories__tab-progress-fill');
						if (progressFill) progressFill.style.animationPlayState = 'running';
					} else {
						playPauseBtn.classList.remove('is-playing');
						playPauseBtn.classList.add('is-paused');
						var activeTab = tabs[current];
						var progressFill = activeTab && activeTab.querySelector('.onepoint-client-stories__tab-progress-fill');
						if (progressFill) progressFill.style.animationPlayState = 'paused';
					}
					playPauseBtn.setAttribute('aria-label', autoplay ? 'Pause carousel' : 'Play carousel');
				});
			}

			goTo(0);
			if (autoplay) {
				playPauseBtn && playPauseBtn.classList.add('is-playing');
				playPauseBtn && playPauseBtn.classList.remove('is-paused');
			} else {
				playPauseBtn && playPauseBtn.classList.remove('is-playing');
				playPauseBtn && playPauseBtn.classList.add('is-paused');
			}
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
