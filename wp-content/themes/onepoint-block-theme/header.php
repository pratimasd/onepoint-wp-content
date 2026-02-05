<?php
defined('ABSPATH') || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header id="masthead" class="site-header" role="banner">
	<div class="header-inner">
		<div class="header-brand">
			<a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
				<?php if (has_custom_logo()) : ?>
					<?php the_custom_logo(); ?>
				<?php else : ?>
					<span class="site-name">ONEPOINT</span>
				<?php endif; ?>
			</a>
		</div>
		<button type="button" class="header-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle menu', 'onepoint-block-theme'); ?>">
			<span class="hamburger" aria-hidden="true"></span>
		</button>
		<nav id="site-navigation" class="header-nav" aria-label="<?php esc_attr_e('Primary', 'onepoint-block-theme'); ?>">
			<?php
			wp_nav_menu(array(
				'theme_location' => 'primary',
				'menu_id'        => 'primary-menu',
				'menu_class'     => 'nav-menu',
				'container'      => false,
				'fallback_cb'    => 'onepoint_header_fallback_menu',
			));
			?>
		</nav>
		<div class="header-icons" aria-hidden="true">
			<?php
			$icon_url = onepoint_header_icon_url();
			if ($icon_url) :
				for ($i = 0; $i < 3; $i++) :
			?>
			<span class="header-icon header-icon-custom">
				<img src="<?php echo esc_url($icon_url); ?>" alt="" width="24" height="24" loading="lazy" />
			</span>
			<?php endfor; else :
				$flask_svg = '<svg class="icon-flask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 2v5M15 2v5"/><path d="M8 7h8l2.5 14H5.5L8 7z"/><line x1="8" y1="14" x2="16" y2="14"/><circle cx="10" cy="15" r="1"/><circle cx="14" cy="16" r="1"/></svg>';
			?>
			<span class="header-icon header-icon-flask"><?php echo $flask_svg; ?></span>
			<span class="header-icon header-icon-flask"><?php echo $flask_svg; ?></span>
			<span class="header-icon header-icon-flask"><?php echo $flask_svg; ?></span>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="content" class="site-content">
