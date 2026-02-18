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
<<<<<<< HEAD
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
=======
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
		});
	}
	var footerToggle = document.getElementById('footer-toggle-full');
	var footerFull = document.getElementById('footer-full');
	if (footerToggle && footerFull) {
		var labelCollapse = footerToggle.getAttribute('data-label-collapse') || 'Hide full footer';
		var labelExpand   = footerToggle.getAttribute('data-label-expand') || 'Show full footer';
		footerToggle.addEventListener('click', function() {
			var expanded = footerToggle.getAttribute('aria-expanded') === 'true';
			footerToggle.setAttribute('aria-expanded', !expanded);
			footerFull.hidden = expanded;
			footerToggle.textContent = expanded ? labelCollapse : labelExpand;
			footerToggle.setAttribute('aria-label', expanded ? labelCollapse : labelExpand);
		});
	}
	var triggers = document.querySelectorAll('.footer-accordion-trigger');
	triggers.forEach(function(btn) {
		btn.addEventListener('click', function() {
			var panel = document.getElementById(btn.getAttribute('aria-controls'));
			var isExpanded = btn.getAttribute('aria-expanded') === 'true';
			btn.setAttribute('aria-expanded', !isExpanded);
			if (panel) panel.hidden = isExpanded;
		});
	});
})();
</script>
</body>
</html>
