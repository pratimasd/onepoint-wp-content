<?php
/**
 * Onepoint Block Theme – functions and setup.
 */
defined('ABSPATH') || exit;

function onepoint_theme_setup() {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
	add_theme_support('custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	));
	add_theme_support('align-wide'); /* show Wide / Full width in block toolbar */
	register_nav_menus(array(
		'primary'      => __('Primary Menu', 'onepoint-block-theme'),
		'footer-col-1' => __('Footer Column 1', 'onepoint-block-theme'),
		'footer-col-2' => __('Footer Column 2', 'onepoint-block-theme'),
		'footer-col-3' => __('Footer Column 3', 'onepoint-block-theme'),
		'footer-col-4' => __('Footer Column 4', 'onepoint-block-theme'),
	));
}
add_action('after_setup_theme', 'onepoint_theme_setup');

/**
 * Enqueue theme stylesheet so header and layout are styled (not just default browser look).
 */
function onepoint_enqueue_assets() {
	wp_enqueue_style(
		'onepoint-block-theme-style',
		get_stylesheet_uri(),
		array(),
		wp_get_theme()->get('Version')
	);
}
add_action('wp_enqueue_scripts', 'onepoint_enqueue_assets');

/**
 * Fallback when no primary menu is assigned. Matches design: Architect for outcomes, Do data better, Innovate AI & more.
 */
function onepoint_header_fallback_menu() {
	$items = array(
		array('label' => __('Architect for outcomes', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Do data better', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Innovate AI & more', 'onepoint-block-theme'), 'url' => home_url('/')),
	);
	echo '<ul id="primary-menu" class="nav-menu">';
	foreach ($items as $item) {
		echo '<li class="menu-item"><a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a></li>';
	}
	echo '</ul>';
}

/**
 * URL for custom header icon (theme assets folder). Returns empty if no icon file present.
 * Used by header.php to decide whether to show custom icon or fallback flask SVG.
 * Checked: g629.png, header-icon.png, header-icon.svg
 */
function onepoint_header_icon_url() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	$candidates = array('g629.png', 'header-icon.png', 'header-icon.svg');
	foreach ($candidates as $file) {
		if (file_exists($dir . '/assets/' . $file)) {
			return $uri . '/assets/' . $file;
		}
	}
	return '';
}
