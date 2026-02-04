<?php
/**
 * Plugin Name: Onepoint Custom Blocks
 * Description: Gutenberg blocks POC (Plugin vs Theme approach)
 * Version: 0.1.0
 */

defined('ABSPATH') || exit;

/**
 * Register all blocks in blocks/ folder. Dynamic blocks get render_callback from mapping.
 */
function onepoint_register_blocks() {
	$blocks_dir = plugin_dir_path(__FILE__) . 'blocks';
	$render_callbacks = array(
		'onepoint/image-carousel'   => 'onepoint_render_image_carousel',
		'onepoint/initiative-card' => 'onepoint_render_initiative_card',
	);

	if (!is_dir($blocks_dir)) {
		return;
	}

	$items = array_filter(scandir($blocks_dir), function ($item) use ($blocks_dir) {
		return $item !== '.' && $item !== '..' && is_dir($blocks_dir . DIRECTORY_SEPARATOR . $item);
	});

	foreach ($items as $block_slug) {
		$block_path = $blocks_dir . DIRECTORY_SEPARATOR . $block_slug;
		$block_json = $block_path . DIRECTORY_SEPARATOR . 'block.json';
		if (!file_exists($block_json)) {
			continue;
		}
		$metadata = json_decode(file_get_contents($block_json), true);
		$block_name = isset($metadata['name']) ? $metadata['name'] : '';
		$args = array();
		if ($block_name && isset($render_callbacks[$block_name])) {
			$args['render_callback'] = $render_callbacks[$block_name];
		}
		register_block_type($block_path, $args);
	}
}
add_action('init', 'onepoint_register_blocks');

/**
 * Render the Image Carousel block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_image_carousel($attributes) {
	$images = isset($attributes['images']) && is_array($attributes['images']) ? $attributes['images'] : array();
	$direction = isset($attributes['direction']) ? sanitize_html_class($attributes['direction']) : 'left';
	$speed = isset($attributes['speed']) ? absint($attributes['speed']) : 30;
	$speed = $speed < 10 ? 10 : ($speed > 120 ? 120 : $speed);

	if (empty($images)) {
		return '<div class="onepoint-carousel-wrap" data-direction="' . esc_attr($direction) . '" data-speed="' . esc_attr($speed) . '"><p class="onepoint-carousel-empty">' . esc_html__('Add images in the block settings.', 'onepoint-custom-blocks') . '</p></div>';
	}

	$unique_id = 'onepoint-carousel-' . uniqid();
	$block_content = '<div class="onepoint-carousel-wrap" id="' . esc_attr($unique_id) . '" data-direction="' . esc_attr($direction) . '" data-speed="' . esc_attr($speed) . '">';
	$block_content .= '<div class="onepoint-carousel-track" aria-hidden="true">';
	$copies = 6;
	for ($i = 0; $i < $copies; $i++) {
		foreach ($images as $img) {
			$url = isset($img['url']) ? esc_url($img['url']) : '';
			$alt = isset($img['alt']) ? esc_attr($img['alt']) : '';
			if ($url) {
				$block_content .= '<div class="onepoint-carousel-slide"><img src="' . $url . '" alt="' . $alt . '" loading="lazy" /></div>';
			}
		}
	}
	$block_content .= '</div></div>';

	return $block_content;
}

/**
 * Render the Initiative Card block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_initiative_card($attributes) {
	$card_type   = isset($attributes['cardType']) && in_array($attributes['cardType'], array('featured', 'simple'), true) ? $attributes['cardType'] : 'simple';
	$accent      = isset($attributes['accentColor']) ? esc_attr($attributes['accentColor']) : '#00D3BA';
	$icon_url    = isset($attributes['iconUrl']) ? esc_url($attributes['iconUrl']) : '';
	$icon_alt    = isset($attributes['iconAlt']) ? esc_attr($attributes['iconAlt']) : '';
	$brand       = isset($attributes['brand']) ? esc_html($attributes['brand']) : 'Onepoint';
	$title       = isset($attributes['title']) ? esc_html($attributes['title']) : '';
	$heading     = isset($attributes['heading']) ? esc_html($attributes['heading']) : '';
	$description = isset($attributes['description']) ? esc_html($attributes['description']) : '';

	$style = '--onepoint-card-accent:' . $accent . ';';
	$html  = '<div class="onepoint-initiative-card" data-type="' . esc_attr($card_type) . '" style="' . esc_attr($style) . '">';
	if ($icon_url) {
		$html .= '<div class="onepoint-initiative-card__icon"><img src="' . $icon_url . '" alt="' . $icon_alt . '" /></div>';
	}
	$html .= '<div class="onepoint-initiative-card__brand">' . $brand . '</div>';
	if ($title) {
		$html .= '<div class="onepoint-initiative-card__title">' . $title . '</div>';
	}
	if ($card_type === 'featured') {
		if ($heading) {
			$html .= '<h3 class="onepoint-initiative-card__heading">' . $heading . '</h3>';
		}
		if ($description) {
			$html .= '<p class="onepoint-initiative-card__description">' . $description . '</p>';
		}
	}
	$html .= '</div>';
	return $html;
}
