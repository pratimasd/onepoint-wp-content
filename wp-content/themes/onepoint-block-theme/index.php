<?php
/**
 * Main template: outputs page/post content so blocks (e.g. Image Carousel) render on preview and frontend.
 */
defined('ABSPATH') || exit;

get_header();
while (have_posts()) {
	the_post();
	the_content();
}
get_footer();
