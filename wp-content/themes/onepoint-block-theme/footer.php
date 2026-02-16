<?php
defined('ABSPATH') || exit;
?>
</main>

<?php
// Footer block from plugin – edit via Appearance → Header & Footer.
$footer_output = onepoint_render_template_part('footer');
if ($footer_output !== '') {
	echo $footer_output;
} else {
	// Fallback if block not available (e.g. plugin inactive) so footer is still visible.
	echo '<footer id="site-footer" class="site-footer" role="contentinfo"><div class="footer-inner"><div class="footer-bottom"><p class="footer-copyright">© ' . esc_html(gmdate('Y')) . ' ' . esc_html(get_bloginfo('name')) . '</p></div></div></footer>';
}
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
