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
	register_nav_menus(array(
		'primary' => __('Primary Menu', 'onepoint-block-theme'),
	));
}
add_action('after_setup_theme', 'onepoint_theme_setup');

/**
 * Fallback when no primary menu is assigned.
 */
function onepoint_header_fallback_menu() {
	echo '<ul id="primary-menu" class="nav-menu"><li class="menu-item"><a href="' . esc_url(admin_url('nav-menus.php')) . '">' . esc_html__('Assign a menu', 'onepoint-block-theme') . '</a></li></ul>';
}
