/**
 * Hero Banner block – frontend carousel: one slide visible, indicator, play/pause.
 */
(function () {
	'use strict';

	function initCarousels() {
		var carousels = document.querySelectorAll('.onepoint-hero-carousel:not([data-editor-preview])');
		carousels.forEach(function (el) {
			if (el.dataset.heroInit) return;
			el.dataset.heroInit = '1';

			var track = el.querySelector('.onepoint-hero-carousel__track');
			var slides = track ? track.querySelectorAll('.onepoint-hero-carousel__slide') : [];
			var indicators = el.querySelector('.onepoint-hero-carousel__indicators');
			var dots = indicators ? indicators.querySelectorAll('.onepoint-hero-carousel__dot') : [];
			var playPauseBtn = el.querySelector('.onepoint-hero-carousel__play-pause');
			var playPauseIcon = playPauseBtn ? playPauseBtn.querySelector('.onepoint-hero-carousel__play-pause-icon') : null;

			var count = slides.length;
			if (count <= 1) return;

			var autoplay = el.dataset.autoplay !== '0' && el.dataset.autoplay !== 'false';
			var intervalSec = parseInt(el.dataset.interval, 10) || 5;
			var current = 0;
			var timer = null;

			function goTo(index) {
				index = ((index % count) + count) % count;
				current = index;
				slides.forEach(function (s, i) {
					s.classList.toggle('is-active', i === index);
					s.setAttribute('aria-hidden', i !== index);
				});
				dots.forEach(function (d, i) {
					d.classList.toggle('is-active', i === index);
					d.setAttribute('aria-selected', i === index);
				});
			}

			function startTimer() {
				stopTimer();
				timer = setInterval(function () {
					goTo(current + 1);
				}, intervalSec * 1000);
			}

			function stopTimer() {
				if (timer) {
					clearInterval(timer);
					timer = null;
				}
			}

			function setPlaying(playing) {
				autoplay = !!playing;
				if (playPauseIcon) playPauseIcon.classList.toggle('is-paused', !playing);
				if (autoplay) startTimer();
				else stopTimer();
			}

			dots.forEach(function (dot, i) {
				dot.addEventListener('click', function () {
					goTo(i);
					if (autoplay) startTimer();
				});
			});

			if (playPauseBtn) {
				playPauseBtn.addEventListener('click', function () {
					setPlaying(!autoplay);
				});
			}

			goTo(0);
			if (autoplay) startTimer();
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
