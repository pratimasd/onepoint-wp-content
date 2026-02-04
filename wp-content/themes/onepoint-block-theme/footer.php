<?php
defined('ABSPATH') || exit;
?>
</main>
<?php wp_footer(); ?>
<script>
(function() {
	var btn = document.querySelector('.header-toggle');
	var nav = document.querySelector('.header-nav');
	if (btn && nav) {
		btn.addEventListener('click', function() {
			var open = btn.getAttribute('aria-expanded') === 'true';
			btn.setAttribute('aria-expanded', !open);
			nav.classList.toggle('is-open', !open);
		});
	}
})();
</script>
</body>
</html>
