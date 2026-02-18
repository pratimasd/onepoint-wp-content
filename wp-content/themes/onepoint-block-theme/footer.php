<?php
defined('ABSPATH') || exit;
?>
</main>
<?php
// Footer is provided by the plugin (onepoint/footer block). Template uses the block.
echo do_blocks( '<!-- wp:onepoint/footer /-->' );
?>
<?php wp_footer(); ?>
<script>
(function() {
	var headerBtn = document.querySelector('.header-toggle');
	var headerNav = document.querySelector('.header-nav');
	if (headerBtn && headerNav) {
		headerBtn.addEventListener('click', function() {
			var open = headerBtn.getAttribute('aria-expanded') === 'true';
			headerBtn.setAttribute('aria-expanded', !open);
			headerNav.classList.toggle('is-open', !open);
		});
	}
	var footerBtn = document.querySelector('.footer-toggle-full');
	var footerTop = document.querySelector('#footer-top');
	var footerText = document.querySelector('.footer-toggle-full-text');
	if (footerBtn && footerTop && footerText) {
		footerBtn.addEventListener('click', function() {
			var expanded = footerBtn.getAttribute('aria-expanded') === 'true';
			footerBtn.setAttribute('aria-expanded', !expanded);
			footerTop.hidden = expanded;
			footerText.textContent = expanded ? (footerBtn.dataset.labelExpand || 'Show full footer') : (footerBtn.dataset.labelCollapse || 'Hide full footer');
		});
	}
})();
</script>
</body>
</html>
