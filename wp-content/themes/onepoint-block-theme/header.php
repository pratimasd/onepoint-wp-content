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
					<span class="site-name"><?php bloginfo('name'); ?></span>
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
		<div class="header-actions">
			<a href="<?php echo esc_url(home_url('/contact/')); ?>" class="header-cta"><?php esc_html_e('Contact', 'onepoint-block-theme'); ?></a>
		</div>
	</div>
</header>

<main id="content" class="site-content">
