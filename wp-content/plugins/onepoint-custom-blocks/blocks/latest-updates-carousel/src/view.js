/**
 * Latest Updates Carousel – frontend: infinite loop, arrows, auto-slide, smooth transitions.
 */
(function () {
	'use strict';

	function initCarousels() {
		var carousels = document.querySelectorAll('.onepoint-latest-updates:not([data-editor-preview])');
		carousels.forEach(function (el) {
			if (el.dataset.latestUpdatesInit) return;
			el.dataset.latestUpdatesInit = '1';

			var trackInner = el.querySelector('.onepoint-latest-updates__track-inner');
			var prevBtn = el.querySelector('.onepoint-latest-updates__arrow--prev');
			var nextBtn = el.querySelector('.onepoint-latest-updates__arrow--next');
			var cards = el.querySelectorAll('.onepoint-latest-updates__card');
			var totalCards = cards.length;

			if (totalCards === 0) return;

			var originalCount = parseInt(el.dataset.originalCount, 10) || totalCards;
			var isInfinite = originalCount > 1 && totalCards === originalCount * 3;

			var autoplay = el.dataset.autoplay !== '0' && el.dataset.autoplay !== 'false';
			var intervalSec = Math.max(3, Math.min(15, parseInt(el.dataset.interval, 10) || 6));

			var displayIndex = isInfinite ? originalCount : 0;
			var isTransitioning = false;

			function getLayout() {
				var track = el.querySelector('.onepoint-latest-updates__track');
				if (!track || !trackInner) return { stepPx: 0, cardWidthPx: 0, visibleWidth: 0, gapPx: 0 };
				var trackStyle = window.getComputedStyle(track);
				var innerStyle = window.getComputedStyle(trackInner);
				var elStyle = window.getComputedStyle(el);
				var gapPx = parseFloat(String(innerStyle.gap || innerStyle.columnGap || '0')) || 0;
				var visibleCount = parseInt(elStyle.getPropertyValue('--lu-visible'), 10) || 3;
				var padL = parseFloat(trackStyle.paddingLeft) || 0;
				var padR = parseFloat(trackStyle.paddingRight) || 0;
				var visibleWidth = Math.floor(track.clientWidth - padL - padR);
				var gapsInView = Math.max(0, visibleCount - 1);
				/* Floor card width to avoid sub-pixel overflow showing slivers of adjacent cards */
				var cardWidthPx = Math.floor((visibleWidth - gapsInView * gapPx) / visibleCount);
				var stepPx = cardWidthPx + gapPx;
				return { stepPx: stepPx, cardWidthPx: cardWidthPx, visibleWidth: visibleWidth, gapPx: gapPx };
			}

			function syncLayoutToCss() {
				var layout = getLayout();
				if (layout.visibleWidth <= 0) return;
				var cardW = layout.cardWidthPx;
				var gap = layout.gapPx;
				/* Set CSS vars so card sizing matches transform exactly (pixel-perfect) */
				el.style.setProperty('--lu-card-width-px', cardW + 'px');
				el.style.setProperty('--lu-step-px', layout.stepPx + 'px');
				var trackInnerWidth = totalCards * cardW + Math.max(0, totalCards - 1) * gap;
				if (trackInner) trackInner.style.setProperty('--lu-track-inner-width-px', trackInnerWidth + 'px');
			}

			function applyTransform(instant) {
				var layout = getLayout();
				var offsetPx = Math.round(displayIndex * layout.stepPx);
				var transformValue = 'translateX(-' + offsetPx + 'px)';
				if (trackInner) {
					if (instant) {
						trackInner.style.transition = 'none';
						trackInner.style.transform = transformValue;
						trackInner.offsetHeight;
						trackInner.style.transition = '';
					} else {
						trackInner.style.transform = transformValue;
					}
				}
			}

			function onTransitionEnd(e) {
				if (!isTransitioning || !e || e.target !== trackInner) return;
				isTransitioning = false;
				trackInner.removeEventListener('transitionend', onTransitionEnd);

				if (isInfinite) {
					if (displayIndex >= originalCount * 2) {
						displayIndex = originalCount;
						applyTransform(true);
					} else if (displayIndex < originalCount) {
						displayIndex = originalCount * 2 - 1;
						applyTransform(true);
					}
				}
			}

			function slideTo(index, instant) {
				displayIndex = index;
				applyTransform(instant);
			}

			function goNext() {
				if (isInfinite) {
					displayIndex++;
					isTransitioning = true;
					trackInner.addEventListener('transitionend', onTransitionEnd);
					applyTransform(false);
				} else {
					displayIndex = (displayIndex + 1) % totalCards;
					applyTransform(false);
				}
				if (autoplay) resetAutoplay();
			}

			function goPrev() {
				if (isInfinite) {
					if (displayIndex <= 0) {
						/* At start of first set: jump to last set then animate back one */
						displayIndex = originalCount * 2;
						applyTransform(true);
						displayIndex = originalCount * 2 - 1;
						isTransitioning = true;
						trackInner.addEventListener('transitionend', onTransitionEnd);
						applyTransform(false);
					} else if (displayIndex === originalCount) {
						/* At start of middle set: jump to last set then animate back one */
						displayIndex = originalCount * 2;
						applyTransform(true);
						displayIndex = originalCount * 2 - 1;
						isTransitioning = true;
						trackInner.addEventListener('transitionend', onTransitionEnd);
						applyTransform(false);
					} else {
						displayIndex--;
						isTransitioning = true;
						trackInner.addEventListener('transitionend', onTransitionEnd);
						applyTransform(false);
					}
				} else {
					displayIndex = (displayIndex - 1 + totalCards) % totalCards;
					applyTransform(false);
				}
				if (autoplay) resetAutoplay();
			}

			var autoplayTimer = null;
			function startAutoplay() {
				if (totalCards <= 1) return;
				autoplayTimer = setInterval(goNext, intervalSec * 1000);
			}

			function stopAutoplay() {
				if (autoplayTimer) {
					clearInterval(autoplayTimer);
					autoplayTimer = null;
				}
			}

			function resetAutoplay() {
				stopAutoplay();
				if (autoplay && totalCards > 1) startAutoplay();
			}

			if (prevBtn) {
				prevBtn.addEventListener('click', function () { goPrev(); });
			}
			if (nextBtn) {
				nextBtn.addEventListener('click', function () { goNext(); });
			}

			syncLayoutToCss();
			slideTo(displayIndex, true);
			el._luSyncLayout = function () {
				syncLayoutToCss();
				applyTransform(true);
			};
			if (autoplay && totalCards > 1) {
				startAutoplay();
			}

			el.addEventListener('mouseenter', stopAutoplay);
			el.addEventListener('mouseleave', function () {
				if (autoplay && totalCards > 1) startAutoplay();
			});

			/* Re-sync layout and transform on resize */
			var resizeTimer;
			function onResize() {
				clearTimeout(resizeTimer);
				resizeTimer = setTimeout(function () {
					syncLayoutToCss();
					applyTransform(true);
				}, 100);
			}
			window.addEventListener('resize', onResize);
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

	/* Re-sync layout after images/fonts load (can change dimensions) */
	window.addEventListener('load', function () {
		document.querySelectorAll('.onepoint-latest-updates[data-latest-updates-init]').forEach(function (el) {
			var sync = el._luSyncLayout;
			if (typeof sync === 'function') sync();
		});
	});
})();
