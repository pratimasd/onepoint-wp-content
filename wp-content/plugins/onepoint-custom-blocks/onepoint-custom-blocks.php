<?php
/**
 * Plugin Name: Onepoint Custom Blocks
 * Description: Gutenberg blocks POC (Plugin vs Theme approach)
 * Version: 0.5.0
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

/**
 * Role and capability: content editing
 *
 * Who can modify content: WordPress restricts the block editor to users with edit_posts / edit_pages.
 * Editors and above have these capabilities by default. No plugin check required for editor access.
 * Block render protection pattern: see commented example in onepoint_render_technology_carousel().
 */

/**
 * Register all blocks in blocks/ folder. Dynamic blocks get render_callback from mapping.
 */
function onepoint_register_blocks() {
	$blocks_dir = plugin_dir_path(__FILE__) . 'blocks';
	$render_callbacks = array(
		'onepoint/vision-block'               => 'onepoint_render_vision_block',
		'onepoint/image-carousel'             => 'onepoint_render_image_carousel',
		'onepoint/initiative-card'             => 'onepoint_render_initiative_card',
		'onepoint/hero-banner'                => 'onepoint_render_hero_banner',
		'onepoint/technology-carousel'        => 'onepoint_render_technology_carousel',
		'onepoint/site-header'                => 'onepoint_render_site_header',
		'onepoint/header'                     => 'onepoint_render_header',
		'onepoint/contact-form'               => 'onepoint_render_contact_form',
		'onepoint/client-stories-carousel'     => 'onepoint_render_client_stories_carousel',
		'onepoint/purpose-cards-carousel'      => 'onepoint_render_purpose_cards_carousel',
		'onepoint/footer'                      => 'onepoint_render_footer',
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
add_action('admin_post_onepoint_contact_submit', 'onepoint_handle_contact_form');
add_action('admin_post_nopriv_onepoint_contact_submit', 'onepoint_handle_contact_form');

/**
 * Render the Vision block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_vision_block( $attributes ) {
	$section_title   = isset( $attributes['sectionTitle'] ) ? sanitize_text_field( $attributes['sectionTitle'] ) : 'Vision';
	$quote           = isset( $attributes['quote'] ) ? wp_kses_post( $attributes['quote'] ) : '';
	$speaker_img_url = isset( $attributes['speakerImageUrl'] ) ? esc_url( $attributes['speakerImageUrl'] ) : '';
	$speaker_img_alt = isset( $attributes['speakerImageAlt'] ) ? esc_attr( $attributes['speakerImageAlt'] ) : '';
	$speaker_name    = isset( $attributes['speakerName'] ) ? esc_html( $attributes['speakerName'] ) : '';
	$speaker_title   = isset( $attributes['speakerTitle'] ) ? esc_html( $attributes['speakerTitle'] ) : '';
	$company_name    = isset( $attributes['companyName'] ) ? esc_html( $attributes['companyName'] ) : '';

	$html = '<div class="onepoint-vision">';
	$html .= '<h2 class="onepoint-vision__title">' . $section_title . '</h2>';
	$html .= '<div class="onepoint-vision__content">';
	$html .= '<span class="onepoint-vision__quote-mark" aria-hidden="true">"</span>';
	$html .= '<div class="onepoint-vision__left">';
	if ( $speaker_img_url ) {
		$html .= '<div class="onepoint-vision__image-wrap">';
		$html .= '<img src="' . $speaker_img_url . '" alt="' . $speaker_img_alt . '" class="onepoint-vision__image" loading="lazy" />';
		$html .= '</div>';
	} else {
		$html .= '<div class="onepoint-vision__image-placeholder" aria-hidden="true"></div>';
	}
	$html .= '</div>';
	$html .= '<div class="onepoint-vision__right">';
	if ( $quote ) {
		$html .= '<p class="onepoint-vision__quote">' . $quote . '</p>';
	}
	if ( $speaker_name || $speaker_title ) {
		$html .= '<p class="onepoint-vision__speaker">' . implode( ', ', array_filter( array( $speaker_name, $speaker_title ) ) ) . '</p>';
	}
	if ( $company_name ) {
		$html .= '<p class="onepoint-vision__company">' . $company_name . '</p>';
	}
	$html .= '</div></div></div>';
	return $html;
}

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
 * Chunk array into rows of N columns.
 *
 * @param array $items Items to chunk.
 * @param int   $cols  Number of columns per row.
 * @return array[] Rows of items.
 */
function onepoint_technology_carousel_chunk_rows( $items, $cols = 3 ) {
	$rows = array();
	for ( $i = 0; $i < count( $items ); $i += $cols ) {
		$rows[] = array_slice( $items, $i, $cols );
	}
	return $rows;
}

/**
 * Render one row of technology carousel (3 cells: left, center elevated, right).
 *
 * @param array $row Items for this row (1–3 items).
 * @return string HTML for the row.
 */
function onepoint_technology_carousel_render_row( $row ) {
	$html = '<div class="onepoint-tech-carousel-row">';
	for ( $col = 0; $col < 3; $col++ ) {
		$item = isset( $row[ $col ] ) ? $row[ $col ] : null;
		$is_center = $col === 1;
		$cell_class = 'onepoint-tech-carousel-cell';
		if ( ! $item ) {
			$html .= '<div class="' . esc_attr( $cell_class . ' onepoint-tech-carousel-cell--empty' ) . '"></div>';
			continue;
		}
		$card_class = 'onepoint-tech-carousel-cell onepoint-tech-carousel-card' . ( $is_center ? ' onepoint-tech-carousel-card--elevated' : '' );
		$logo_url  = isset( $item['logoUrl'] ) ? esc_url( $item['logoUrl'] ) : '';
		$logo_alt  = isset( $item['logoAlt'] ) ? esc_attr( $item['logoAlt'] ) : '';
		$label     = isset( $item['label'] ) ? esc_html( $item['label'] ) : '';
		$html     .= '<div class="' . esc_attr( $card_class ) . '">';
		if ( $logo_url ) {
			$html .= '<img src="' . $logo_url . '" alt="' . $logo_alt . '" class="onepoint-tech-carousel-card__img" loading="lazy" />';
		}
		if ( $label !== '' ) {
			$html .= '<span class="onepoint-tech-carousel-card__label">' . $label . '</span>';
		}
		$html .= '</div>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * Render the Technology Carousel block (frontend).
 *
 * Block render protection pattern (optional): restrict visibility by capability.
 * Uncomment the block below to hide this block from users who cannot edit others' posts (Editors and above).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_technology_carousel( $attributes ) {
	// Optional: restrict front-end visibility to users who can edit others' posts (Editors and above).
	// if ( ! current_user_can( 'edit_others_posts' ) ) {
	// 	return '';
	// }

	$items    = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
	$speed    = isset( $attributes['speed'] ) ? absint( $attributes['speed'] ) : 8;
	$speed    = $speed < 5 ? 5 : ( $speed > 45 ? 45 : $speed );
	$count    = count( $items );
	$badge    = isset( $attributes['badgeText'] ) ? $attributes['badgeText'] : 'Technology platforms & tools';
	$heading  = isset( $attributes['heading'] ) ? $attributes['heading'] : 'Trusted partnerships and proven tech expertise';
	$subtitle = isset( $attributes['subtitle'] ) ? $attributes['subtitle'] : 'We apply the right tech solutions quickly through strong partnerships and expertise.';

	$content = '<div class="onepoint-tech-carousel-section">';
	$content .= '<div class="onepoint-tech-carousel-header">';
	$content .= '<div class="onepoint-tech-carousel-label-wrap"><span class="onepoint-tech-carousel-label">' . esc_html( $badge ) . '</span></div>';
	$content .= '<h2>' . esc_html( $heading ) . '</h2>';
	$content .= '<p class="onepoint-tech-carousel-subtitle">' . esc_html( $subtitle ) . '</p>';
	$content .= '</div>';
	$content .= '<div class="onepoint-tech-carousel-carousel-container">';

	$section_label      = isset( $attributes['sectionLabel'] ) ? wp_kses_post( $attributes['sectionLabel'] ) : '';
	$section_heading    = isset( $attributes['sectionHeading'] ) ? wp_kses_post( $attributes['sectionHeading'] ) : '';
	$section_description = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';

	$html = '<div class="onepoint-tech-carousel">';

	if ( $section_label || $section_heading || $section_description ) {
		$html .= '<div class="onepoint-tech-carousel__header">';
		if ( $section_label ) {
			$html .= '<div class="onepoint-tech-carousel__label-wrap"><p class="onepoint-tech-carousel__label">' . $section_label . '</p></div>';
		}
		if ( $section_heading ) {
			$html .= '<h2>' . $section_heading . '</h2>';
		}
		if ( $section_description ) {
			$html .= '<p class="onepoint-tech-carousel__description">' . $section_description . '</p>';
		}
		$html .= '</div>';
	}

	if ( $count === 0 ) {
		$html .= '<div class="onepoint-tech-carousel-wrap" data-speed="' . esc_attr( $speed ) . '" data-count="0"><p class="onepoint-tech-carousel-empty">' . esc_html__( 'Add at least 6 technology logos in the block settings.', 'onepoint-custom-blocks' ) . '</p></div>';
		$html .= '</div>';
		return $html;
	}

	$rows       = onepoint_technology_carousel_chunk_rows( $items, 3 );
	$unique     = 'onepoint-tech-carousel-' . uniqid();
	$html      .= '<div class="onepoint-tech-carousel-wrap" id="' . esc_attr( $unique ) . '" data-speed="' . esc_attr( $speed ) . '" data-count="' . esc_attr( $count ) . '">';
	$html      .= '<div class="onepoint-tech-carousel-track" aria-hidden="true">';

	$track_html = '';
	foreach ( $rows as $row ) {
		$track_html .= onepoint_technology_carousel_render_row( $row );
	}

	if ( $count > 9 ) {
		$html .= $track_html . $track_html;
	} else {
		$html .= $track_html;
	}

	$html .= '</div></div></div>';
	return $html;
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

/**
 * Render the Hero Banner block (frontend) – carousel with multiple items, indicator, play/pause.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_hero_banner($attributes) {
	$items = isset($attributes['items']) && is_array($attributes['items']) ? $attributes['items'] : array();
	if (empty($items)) {
		$items = array(array(
			'greeting'       => isset($attributes['greeting']) ? $attributes['greeting'] : '',
			'headline'       => isset($attributes['headline']) ? $attributes['headline'] : '',
			'description'    => isset($attributes['description']) ? $attributes['description'] : '',
			'buttonText'     => isset($attributes['buttonText']) ? $attributes['buttonText'] : '',
			'buttonUrl'      => isset($attributes['buttonUrl']) ? $attributes['buttonUrl'] : '',
			'imageUrl'       => isset($attributes['imageUrl']) ? $attributes['imageUrl'] : '',
			'imageAlt'       => isset($attributes['imageAlt']) ? $attributes['imageAlt'] : '',
			'videoUrl'       => isset($attributes['videoUrl']) ? $attributes['videoUrl'] : '',
			'imageGrayscale' => isset($attributes['imageGrayscale']) ? $attributes['imageGrayscale'] : true,
		));
	}
	$autoplay = isset($attributes['autoplay']) ? (bool) $attributes['autoplay'] : true;
	$interval = isset($attributes['interval']) ? max(1, (int) $attributes['interval']) : 5;

	$html = '<div class="onepoint-hero-carousel" data-autoplay="' . ( $autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( $interval ) . '">';
	$html .= '<div class="onepoint-hero-carousel__track">';

	foreach ($items as $i => $s) {
		$greeting   = isset($s['greeting']) ? wp_kses_post($s['greeting']) : '';
		$headline   = isset($s['headline']) ? wp_kses_post($s['headline']) : '';
		$desc       = isset($s['description']) ? wp_kses_post($s['description']) : '';
		$btn_text   = isset($s['buttonText']) ? wp_kses_post($s['buttonText']) : '';
		$btn_url    = isset($s['buttonUrl']) ? esc_url($s['buttonUrl']) : '';
		$image_url  = isset($s['imageUrl']) ? esc_url($s['imageUrl']) : '';
		$image_alt  = isset($s['imageAlt']) ? esc_attr($s['imageAlt']) : '';
		$video_url  = isset($s['videoUrl']) ? esc_url($s['videoUrl']) : '';
		$grayscale  = isset($s['imageGrayscale']) ? (bool) $s['imageGrayscale'] : true;
		$active     = $i === 0 ? ' is-active' : '';

		$html .= '<div class="onepoint-hero-carousel__slide onepoint-hero-banner' . $active . '" aria-hidden="' . ( $i !== 0 ? 'true' : 'false' ) . '">';
		$html .= '<div class="onepoint-hero-banner__left">';
		if ($greeting) {
			$html .= '<div class="onepoint-hero-banner__greeting-wrap"><p class="onepoint-hero-banner__greeting">' . $greeting . '</p></div>';
		}
		if ($headline) {
			$html .= '<h2 class="onepoint-hero-banner__headline">' . $headline . '</h2>';
		}
		if ($desc) {
			$html .= '<p class="onepoint-hero-banner__description">' . $desc . '</p>';
		}
		if ($btn_text) {
			$href = $btn_url ? $btn_url : '#';
			$html .= '<a href="' . $href . '" class="onepoint-hero-banner__cta">' . $btn_text . '</a>';
		}
		$html .= '</div>';
		$html .= '<div class="onepoint-hero-banner__right">';
		if ($image_url) {
			$wrap_class = 'onepoint-hero-banner__image-wrap' . ( $grayscale ? ' onepoint-hero-banner__image-wrap--grayscale' : '' );
			$html .= '<div class="' . esc_attr($wrap_class) . '">';
			$html .= '<img src="' . $image_url . '" alt="' . $image_alt . '" class="onepoint-hero-banner__image" loading="lazy" />';
			$html .= '</div>';
			if ($video_url) {
				$html .= '<a href="' . $video_url . '" class="onepoint-hero-banner__play" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr__('Play video', 'onepoint-custom-blocks') . '">';
				$html .= '<span class="onepoint-hero-banner__play-icon" aria-hidden="true"></span>';
				$html .= '</a>';
			}
		} else {
			$html .= '<div class="onepoint-hero-banner__placeholder">';
			$html .= esc_html__('Add an image in the block settings.', 'onepoint-custom-blocks');
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '</div>';
	}

	$html .= '</div>';

	if (count($items) > 1) {
		$html .= '<div class="onepoint-hero-carousel__controls">';
		$html .= '<div class="onepoint-hero-carousel__indicators" role="tablist" aria-label="' . esc_attr__('Slide indicators', 'onepoint-custom-blocks') . '">';
		foreach ($items as $i => $_) {
			$dot_active = $i === 0 ? ' is-active' : '';
			/* translators: %d: slide number (1-based). */
			$html .= '<button type="button" role="tab" aria-selected="' . ( $i === 0 ? 'true' : 'false' ) . '" aria-label="' . esc_attr( sprintf( __( 'Slide %d', 'onepoint-custom-blocks' ), $i + 1 ) ) . '" class="onepoint-hero-carousel__dot' . $dot_active . '"></button>';
		}
		$html .= '</div>';
		$html .= '<button type="button" class="onepoint-hero-carousel__play-pause" aria-label="' . esc_attr( $autoplay ? __( 'Pause carousel', 'onepoint-custom-blocks' ) : __( 'Play carousel', 'onepoint-custom-blocks' ) ) . '">';
		$html .= '<span class="onepoint-hero-carousel__play-pause-icon' . ( $autoplay ? '' : ' is-paused' ) . '" aria-hidden="true"></span>';
		$html .= '</button>';
		$html .= '</div>';
	}

	$html .= '</div>';
	return $html;
}

/**

 * Render the Site Header block (frontend). Logo/menu from WordPress; optional icon from block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_site_header($attributes) {
	$icon_url = isset($attributes['iconUrl']) ? esc_url($attributes['iconUrl']) : '';
	$icon_alt = isset($attributes['iconAlt']) ? esc_attr($attributes['iconAlt']) : '';
	if (empty($icon_url) && function_exists('onepoint_header_icon_url')) {
		$icon_url = onepoint_header_icon_url();
	}

	$html = '<header id="masthead" class="site-header onepoint-site-header" role="banner">';
	$html .= '<div class="onepoint-site-header__inner">';
	$html .= '<div class="onepoint-site-header__brand">';
	$html .= '<a href="' . esc_url(home_url('/')) . '" class="onepoint-site-header__logo" rel="home">';
	if (has_custom_logo()) {
		$html .= get_custom_logo();
	} else {
		$html .= '<span class="onepoint-site-header__name">' . esc_html(get_bloginfo('name') ?: 'ONEPOINT') . '</span>';
	}
	$html .= '</a></div>';
	$html .= '<button type="button" class="onepoint-site-header__toggle" aria-controls="onepoint-primary-menu" aria-expanded="false" aria-label="' . esc_attr__('Toggle menu', 'onepoint-custom-blocks') . '">';
	$html .= '<span class="onepoint-site-header__hamburger" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<nav id="site-navigation" class="onepoint-site-header__nav" aria-label="' . esc_attr__('Primary', 'onepoint-custom-blocks') . '">';
	$html .= wp_nav_menu(array(
		'theme_location' => 'primary',
		'menu_id'        => 'onepoint-primary-menu',
		'menu_class'     => 'onepoint-site-header__menu',
		'container'      => false,
		'echo'           => false,
		'fallback_cb'    => function () {
			$items = array(
				array('label' => __('Architect for outcomes', 'onepoint-custom-blocks'), 'url' => home_url('/')),
				array('label' => __('Do data better', 'onepoint-custom-blocks'), 'url' => home_url('/')),
				array('label' => __('Innovate AI & more', 'onepoint-custom-blocks'), 'url' => home_url('/')),
			);
			$out = '<ul id="onepoint-primary-menu" class="onepoint-site-header__menu">';
			foreach ($items as $item) {
				$out .= '<li class="menu-item"><a href="' . esc_url($item['url']) . '">' . esc_html($item['label']) . '</a></li>';
			}
			$out .= '</ul>';
			return $out;
		},
	));
	$html .= '</nav>';
	$html .= '<div class="onepoint-site-header__icons" aria-hidden="true">';
	if ($icon_url) {
		for ($i = 0; $i < 3; $i++) {
			$html .= '<span class="onepoint-site-header__icon"><img src="' . $icon_url . '" alt="' . $icon_alt . '" width="24" height="24" loading="lazy" /></span>';
		}
	} else {
		$flask_svg = '<svg class="icon-flask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 2v5M15 2v5"/><path d="M8 7h8l2.5 14H5.5L8 7z"/><line x1="8" y1="14" x2="16" y2="14"/><circle cx="10" cy="15" r="1"/><circle cx="14" cy="16" r="1"/></svg>';
		$html .= '<span class="onepoint-site-header__icon onepoint-site-header__icon--flask">' . $flask_svg . '</span>';
		$html .= '<span class="onepoint-site-header__icon onepoint-site-header__icon--flask">' . $flask_svg . '</span>';
		$html .= '<span class="onepoint-site-header__icon onepoint-site-header__icon--flask">' . $flask_svg . '</span>';
	}
	$html .= '</div></div></header>';

	return $html;
}

/**
 * Render the Onepoint Header block (frontend). Logo, menu, icons. Used in theme header template.
 *
 * @param array $attributes Block attributes (siteName, menuLocation, iconUrl).
 * @return string HTML output.
 */
function onepoint_render_header( $attributes ) {
	$site_name     = isset( $attributes['siteName'] ) ? wp_kses_post( $attributes['siteName'] ) : '';
	$menu_location = isset( $attributes['menuLocation'] ) ? sanitize_key( $attributes['menuLocation'] ) : 'primary';
	$icon_url      = isset( $attributes['iconUrl'] ) ? esc_url( $attributes['iconUrl'] ) : '';
	if ( empty( $icon_url ) && function_exists( 'onepoint_header_icon_url' ) ) {
		$icon_url = onepoint_header_icon_url();
	}
	$logo_name = $site_name !== '' ? $site_name : ( get_bloginfo( 'name' ) ?: 'ONEPOINT' );

	$html  = '<header id="masthead" class="site-header onepoint-site-header" role="banner">';
	$html .= '<div class="onepoint-site-header__inner">';
	$html .= '<div class="onepoint-site-header__brand">';
	$html .= '<a href="' . esc_url( home_url( '/' ) ) . '" class="onepoint-site-header__logo" rel="home">';
	if ( has_custom_logo() && $site_name === '' ) {
		$html .= get_custom_logo();
	} else {
		$html .= '<span class="onepoint-site-header__name">' . esc_html( $logo_name ) . '</span>';
	}
	$html .= '</a></div>';
	$html .= '<button type="button" class="onepoint-site-header__toggle header-toggle" aria-controls="onepoint-primary-menu" aria-expanded="false" aria-label="' . esc_attr__( 'Toggle menu', 'onepoint-custom-blocks' ) . '">';
	$html .= '<span class="onepoint-site-header__hamburger" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<nav id="site-navigation" class="onepoint-site-header__nav header-nav" aria-label="' . esc_attr__( 'Primary', 'onepoint-custom-blocks' ) . '">';
	$html .= wp_nav_menu( array(
		'theme_location' => $menu_location,
		'menu_id'        => 'onepoint-primary-menu',
		'menu_class'     => 'onepoint-site-header__menu',
		'container'      => false,
		'echo'           => false,
		'fallback_cb'    => function () {
			$items = array(
				array( 'label' => __( 'Architect for outcomes', 'onepoint-custom-blocks' ), 'url' => home_url( '/' ) ),
				array( 'label' => __( 'Do data better', 'onepoint-custom-blocks' ), 'url' => home_url( '/' ) ),
				array( 'label' => __( 'Innovate AI & more', 'onepoint-custom-blocks' ), 'url' => home_url( '/' ) ),
			);
			$out = '<ul id="onepoint-primary-menu" class="onepoint-site-header__menu">';
			foreach ( $items as $item ) {
				$out .= '<li class="menu-item"><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['label'] ) . '</a></li>';
			}
			$out .= '</ul>';
			return $out;
		},
	) );
	$html .= '</nav>';
	$html .= '<div class="onepoint-site-header__icons" aria-hidden="true">';
	if ( $icon_url ) {
		for ( $i = 0; $i < 3; $i++ ) {
			$html .= '<span class="onepoint-site-header__icon"><img src="' . $icon_url . '" alt="" width="24" height="24" loading="lazy" /></span>';
		}
	} else {
		$flask_svg = '<svg class="icon-flask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 2v5M15 2v5"/><path d="M8 7h8l2.5 14H5.5L8 7z"/><line x1="8" y1="14" x2="16" y2="14"/><circle cx="10" cy="15" r="1"/><circle cx="14" cy="16" r="1"/></svg>';
		$html .= '<span class="onepoint-site-header__icon onepoint-site-header__icon--flask">' . $flask_svg . '</span>';
		$html .= '<span class="onepoint-site-header__icon onepoint-site-header__icon--flask">' . $flask_svg . '</span>';
		$html .= '<span class="onepoint-site-header__icon onepoint-site-header__icon--flask">' . $flask_svg . '</span>';
	}
	$html .= '</div></div></header>';

	return $html;
}

/**
 * Handle contact form submission (admin-post.php action=onepoint_contact_submit).
 * Verifies nonce, sanitizes/validates input, sends email, redirects with success/error.
 */
function onepoint_handle_contact_form() {
	$nonce = isset( $_POST['onepoint_contact_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['onepoint_contact_nonce'] ) ) : '';
	if ( ! $nonce || ! wp_verify_nonce( $nonce, 'onepoint_contact_submit' ) ) {
		$redirect = wp_get_referer() ?: home_url( '/' );
		wp_safe_redirect( add_query_arg( 'onepoint_contact', 'error', $redirect ) );
		exit;
	}

	$name     = isset( $_POST['onepoint_name'] ) ? sanitize_text_field( wp_unslash( $_POST['onepoint_name'] ) ) : '';
	$email    = isset( $_POST['onepoint_email'] ) ? sanitize_email( wp_unslash( $_POST['onepoint_email'] ) ) : '';
	$company  = isset( $_POST['onepoint_company'] ) ? sanitize_text_field( wp_unslash( $_POST['onepoint_company'] ) ) : '';
	$linkedin = isset( $_POST['onepoint_linkedin'] ) ? esc_url_raw( wp_unslash( $_POST['onepoint_linkedin'] ) ) : '';
	$message  = isset( $_POST['onepoint_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['onepoint_message'] ) ) : '';
	$recipient = isset( $_POST['onepoint_recipient'] ) ? sanitize_email( wp_unslash( $_POST['onepoint_recipient'] ) ) : '';
	if ( empty( $recipient ) || ! is_email( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$redirect = wp_get_referer() ?: home_url( '/' );
	if ( empty( $name ) || empty( $email ) || ! is_email( $email ) || empty( $message ) ) {
		wp_safe_redirect( add_query_arg( 'onepoint_contact', 'error', $redirect ) );
		exit;
	}

	$subject = sprintf(
		/* translators: %s: site name */
		__( '[%s] Contact form submission', 'onepoint-custom-blocks' ),
		get_bloginfo( 'name' )
	);
	/* translators: %s: sender name. */
	$body = sprintf( __( 'Name: %s', 'onepoint-custom-blocks' ), $name ) . "\n";
	/* translators: %s: sender email address. */
	$body .= sprintf( __( 'Email: %s', 'onepoint-custom-blocks' ), $email ) . "\n";
	if ( $company ) {
		/* translators: %s: company name. */
		$body .= sprintf( __( 'Company: %s', 'onepoint-custom-blocks' ), $company ) . "\n";
	}
	if ( $linkedin ) {
		/* translators: %s: LinkedIn profile URL. */
		$body .= sprintf( __( 'LinkedIn: %s', 'onepoint-custom-blocks' ), $linkedin ) . "\n";
	}
	$body .= "\n" . __( 'Message:', 'onepoint-custom-blocks' ) . "\n" . $message . "\n";

	$headers = array( 'Content-Type: text/plain; charset=UTF-8' );
	$sent   = wp_mail( $recipient, $subject, $body, $headers );

	$status = $sent ? 'success' : 'error';
	wp_safe_redirect( add_query_arg( 'onepoint_contact', $status, $redirect ) );
	exit;
}

/**
 * Render the Contact Form block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_contact_form( $attributes ) {
	$section_label   = isset( $attributes['sectionLabel'] ) ? wp_kses_post( $attributes['sectionLabel'] ) : 'Contact us';
	$heading         = isset( $attributes['heading'] ) ? wp_kses_post( $attributes['heading'] ) : "Let's build something together";
	$description     = isset( $attributes['description'] ) ? wp_kses_post( $attributes['description'] ) : '';
	$button_text     = isset( $attributes['buttonText'] ) ? wp_kses_post( $attributes['buttonText'] ) : 'Get in touch';
	$recipient_email = isset( $attributes['recipientEmail'] ) ? sanitize_email( $attributes['recipientEmail'] ) : '';
	$success_message = isset( $attributes['successMessage'] ) ? wp_kses_post( $attributes['successMessage'] ) : "Thank you! We'll get back to you soon.";

	if ( empty( $recipient_email ) ) {
		$recipient_email = get_option( 'admin_email' );
	}

	$nonce_action = 'onepoint_contact_submit';
	$nonce_name   = 'onepoint_contact_nonce';

	$html  = '<div class="onepoint-contact-form">';
	$html .= '<div class="onepoint-contact-form__header">';
	if ( $section_label ) {
		$html .= '<div class="onepoint-contact-form__label-wrap"><p class="onepoint-contact-form__label">' . $section_label . '</p></div>';
	}
	if ( $heading ) {
		$html .= '<h2 class="onepoint-contact-form__heading">' . $heading . '</h2>';
	}
	if ( $description ) {
		$html .= '<p class="onepoint-contact-form__description">' . $description . '</p>';
	}
	$html .= '</div>';
	$html .= '<div class="onepoint-contact-form__card">';
	$html .= '<form class="onepoint-contact-form__fields" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '">';
	$html .= '<input type="hidden" name="action" value="onepoint_contact_submit" />';
	$html .= wp_nonce_field( $nonce_action, $nonce_name, true, false );
	$html .= '<input type="hidden" name="onepoint_recipient" value="' . esc_attr( $recipient_email ) . '" />';
	$html .= '<div class="onepoint-contact-form__row">';
	$html .= '<div class="onepoint-contact-form__field"><label for="onepoint-name">' . esc_html__( 'Name', 'onepoint-custom-blocks' ) . '</label><input type="text" id="onepoint-name" name="onepoint_name" required /></div>';
	$html .= '<div class="onepoint-contact-form__field"><label for="onepoint-email">' . esc_html__( 'Business email', 'onepoint-custom-blocks' ) . '</label><input type="email" id="onepoint-email" name="onepoint_email" required /></div>';
	$html .= '</div>';
	$html .= '<div class="onepoint-contact-form__row">';
	$html .= '<div class="onepoint-contact-form__field"><label for="onepoint-company">' . esc_html__( 'Company', 'onepoint-custom-blocks' ) . '</label><input type="text" id="onepoint-company" name="onepoint_company" /></div>';
	$html .= '<div class="onepoint-contact-form__field"><label for="onepoint-linkedin">' . esc_html__( 'LinkedIn link', 'onepoint-custom-blocks' ) . '</label><input type="url" id="onepoint-linkedin" name="onepoint_linkedin" placeholder="https://" /></div>';
	$html .= '</div>';
	$html .= '<div class="onepoint-contact-form__field onepoint-contact-form__field--message">';
	$html .= '<label for="onepoint-message">' . esc_html__( 'How can we help you?', 'onepoint-custom-blocks' ) . '</label>';
	$html .= '<textarea id="onepoint-message" name="onepoint_message" rows="5" required></textarea>';
	$html .= '</div>';
	$html .= '<div class="onepoint-contact-form__button-wrap">';
	$html .= '<button type="submit" class="onepoint-contact-form__button">' . $button_text . '</button>';
	$html .= '</div></form></div></div>';

	return $html;
}

/**
 * Render the Client Stories Carousel block (frontend).

 * Fallback link lists for footer columns (plugin fallback when theme helpers not used).
 */
function onepoint_plugin_footer_fallback($location) {
	$home = home_url('/');
	$lists = array(
		'footer_what_we_do' => array(
			array('label' => 'Architect for outcomes', 'url' => $home),
			array('label' => 'Do data better', 'url' => $home),
			array('label' => 'Innovate with AI & more', 'url' => $home),
			array('label' => 'Springboard™ Workshop', 'url' => $home),
			array('label' => 'Onepoint Labs', 'url' => $home),
		),
		'footer_resources' => array(
			array('label' => 'Onepoint Data Wellness™ Suite', 'url' => $home),
			array('label' => 'Onepoint Res-AI™', 'url' => $home),
			array('label' => 'Onepoint TechTalk', 'url' => $home),
			array('label' => 'Onepoint Oneness', 'url' => $home),
		),
		'footer_about' => array(
			array('label' => 'Discover Onepoint', 'url' => $home),
			array('label' => 'Client stories', 'url' => $home),
			array('label' => 'Careers', 'url' => $home),
			array('label' => 'Contact us', 'url' => $home),
		),
		'footer_more_info' => array(
			array('label' => 'Boomi', 'url' => $home),
			array('label' => 'Client stories', 'url' => $home),
			array('label' => 'Careers', 'url' => $home),
			array('label' => 'Contact us', 'url' => $home),
		),
	);
	return isset($lists[ $location ]) ? $lists[ $location ] : array();
}

/**
 * Render one footer column (menu or fallback). Used by onepoint_render_footer.
 */
function onepoint_plugin_footer_column($location, $title, $highlight = '') {
	$fallback = function_exists('onepoint_footer_what_we_do_fallback') ? call_user_func('onepoint_footer_' . str_replace('footer_', '', $location) . '_fallback') : onepoint_plugin_footer_fallback($location);
	$html = '<div class="footer-col">';
	$html .= '<h3 class="footer-col__title">' . esc_html($title) . '</h3>';
	if (has_nav_menu($location)) {
		ob_start();
		wp_nav_menu(array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => 'footer-col__list',
			'fallback_cb'    => false,
		));
		$html .= ob_get_clean();
	} else {
		$html .= '<ul class="footer-col__list">';
		foreach ($fallback as $item) {
			$label = isset($item['label']) ? $item['label'] : '';
			$url   = isset($item['url']) ? $item['url'] : home_url('/');
			$class = ($highlight && $label === $highlight) ? ' is-active' : '';
			$html .= '<li><a href="' . esc_url($url) . '" class="' . esc_attr($class) . '">' . esc_html($label) . '</a></li>';
		}
		$html .= '</ul>';
	}
	$html .= '</div>';
	return $html;
}

/**
 * Sanitize a single client story item from block attributes.
 *
 * @param array $item Raw item from block attributes.
 * @return array Sanitized item.
 */
function onepoint_sanitize_client_story_item( $item ) {
	if ( ! is_array( $item ) ) {
		return array();
	}
	$tags = array();
	if ( isset( $item['tags'] ) && is_array( $item['tags'] ) ) {
		foreach ( $item['tags'] as $tag ) {
			$tags[] = sanitize_text_field( $tag );
		}
	}
	$metrics = array();
	if ( isset( $item['metrics'] ) && is_array( $item['metrics'] ) ) {
		foreach ( $item['metrics'] as $m ) {
			$metrics[] = array(
				'value' => isset( $m['value'] ) ? sanitize_text_field( $m['value'] ) : '',
				'label' => isset( $m['label'] ) ? sanitize_text_field( $m['label'] ) : '',
			);
		}
	}
	return array(
		'tabTitle'          => isset( $item['tabTitle'] ) ? sanitize_text_field( $item['tabTitle'] ) : '',
		'tabSubtitle'       => isset( $item['tabSubtitle'] ) ? sanitize_text_field( $item['tabSubtitle'] ) : '',
		'headline'          => isset( $item['headline'] ) ? wp_kses_post( $item['headline'] ) : '',
		'description'      => isset( $item['description'] ) ? wp_kses_post( $item['description'] ) : '',
		'buttonText'        => isset( $item['buttonText'] ) ? sanitize_text_field( $item['buttonText'] ) : '',
		'buttonUrl'         => isset( $item['buttonUrl'] ) ? esc_url_raw( $item['buttonUrl'] ) : '',
		'tags'              => $tags,
		'metrics'            => $metrics,
		'backgroundImageUrl' => isset( $item['backgroundImageUrl'] ) ? esc_url_raw( $item['backgroundImageUrl'] ) : '',
	);
}

/**
 * Render the Client Stories Carousel block (frontend) – tabs, progress, play/pause.

 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_client_stories_carousel( $attributes ) {

	$heading   = isset( $attributes['heading'] ) ? sanitize_text_field( $attributes['heading'] ) : 'Client stories';
	$raw_items = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
	$autoplay  = isset( $attributes['autoplay'] ) ? (bool) $attributes['autoplay'] : true;
	$interval  = isset( $attributes['interval'] ) ? max( 3, min( 15, (int) $attributes['interval'] ) ) : 6;

	$default_item = array(
		'tabTitle'          => 'Robotic Process Automation',
		'tabSubtitle'       => 'SolarCo',
		'headline'          => 'Integrating massive volumes of solar farm',
		'description'       => 'Manual reporting and approval processes created bottlenecks, consumed significant time, and increased error risks in finance operations.',
		'buttonText'        => 'Discover the outcomes',
		'buttonUrl'         => '',
		'tags'              => array( 'Low-code development' ),
		'metrics'           => array(
			array( 'value' => '~2000', 'label' => 'person hours saved monthly' ),
		),
		'backgroundImageUrl' => '',
	);

	if ( empty( $raw_items ) ) {
		$items = array( $default_item );
	} else {
		$items = array_map( 'onepoint_sanitize_client_story_item', $raw_items );
		$items = array_filter( $items );
		if ( empty( $items ) ) {
			$items = array( $default_item );
		}
	}

	$unique_id = 'onepoint-client-stories-' . uniqid();
	$html      = '<div class="onepoint-client-stories" id="' . esc_attr( $unique_id ) . '" data-autoplay="' . ( $autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( $interval ) . '">';
	$html     .= '<div class="onepoint-client-stories__header"><h3 class="onepoint-client-stories__heading">' . esc_html( $heading ) . '</h3></div>';
	$html     .= '<div class="onepoint-client-stories__card">';
	$html     .= '<div class="onepoint-client-stories__list" role="tablist" aria-label="' . esc_attr__( 'Client story tabs', 'onepoint-custom-blocks' ) . '">';

	foreach ( $items as $i => $s ) {
		$tab_title    = isset( $s['tabTitle'] ) ? esc_html( $s['tabTitle'] ) : '';
		$tab_subtitle = isset( $s['tabSubtitle'] ) ? esc_html( $s['tabSubtitle'] ) : '';
		$is_active    = $i === 0;
		$html        .= '<div class="onepoint-client-stories__item">';
		$html        .= '<button type="button" role="tab" aria-selected="' . ( $is_active ? 'true' : 'false' ) . '" aria-label="' . esc_attr( $tab_title . ' – ' . $tab_subtitle ) . '" class="onepoint-client-stories__tab' . ( $is_active ? ' is-active' : '' ) . '">';
		$html        .= '<span class="onepoint-client-stories__tab-title">' . $tab_title . '</span>';
		$html        .= '<span class="onepoint-client-stories__tab-subtitle">' . $tab_subtitle . '</span>';
		$html        .= '<span class="onepoint-client-stories__tab-progress"><span class="onepoint-client-stories__tab-progress-fill"></span></span>';
		$html        .= '</button>';
		$html        .= '<div class="onepoint-client-stories__content">';
		$headline    = isset( $s['headline'] ) ? wp_kses_post( $s['headline'] ) : '';
		$description = isset( $s['description'] ) ? wp_kses_post( $s['description'] ) : '';
		$btn_text    = isset( $s['buttonText'] ) ? wp_kses_post( $s['buttonText'] ) : '';
		$btn_url     = isset( $s['buttonUrl'] ) ? esc_url( $s['buttonUrl'] ) : '';
		$tags        = isset( $s['tags'] ) && is_array( $s['tags'] ) ? array_values( array_filter( array_map( 'trim', $s['tags'] ) ) ) : array();
		$metrics_raw = isset( $s['metrics'] ) && is_array( $s['metrics'] ) ? $s['metrics'] : array();
		$metrics     = array_values( array_filter( $metrics_raw, function ( $m ) {
			$v = isset( $m['value'] ) ? trim( (string) $m['value'] ) : '';
			$l = isset( $m['label'] ) ? trim( (string) $m['label'] ) : '';
			return $v !== '' || $l !== '';
		} ) );
		$bg_url     = isset( $s['backgroundImageUrl'] ) ? esc_url( $s['backgroundImageUrl'] ) : '';
		$is_active   = $i === 0;

		$html .= '<div class="onepoint-client-stories__slide' . ( $is_active ? ' is-active' : '' ) . '" aria-hidden="' . ( $is_active ? 'false' : 'true' ) . '" role="tabpanel">';
		$html .= '<div class="onepoint-client-stories__slide-bg" style="' . ( $bg_url ? 'background-image:url(' . $bg_url . ');' : '' ) . '"></div>';

		$html .= '<div class="onepoint-client-stories__slide-inner">';
		$html .= '<div class="onepoint-client-stories__slide-left">';
		if ( $headline ) {
			$html .= '<h2 class="onepoint-client-stories__slide-headline">' . $headline . '</h2>';
		}
		if ( $description ) {
			$html .= '<p class="onepoint-client-stories__slide-desc">' . $description . '</p>';
		}
		if ( $btn_text ) {
			$href = $btn_url ? $btn_url : '#';
			$html .= '<a href="' . $href . '" class="onepoint-client-stories__slide-cta">' . $btn_text . '</a>';
		}
		if ( ! empty( $tags ) ) {
			$html .= '<div class="onepoint-client-stories__slide-tags">';
			foreach ( $tags as $tag ) {
				$html .= '<span class="onepoint-client-stories__tag"><span class="onepoint-client-stories__tag-chevron" aria-hidden="true">&#8249;</span>' . esc_html( $tag ) . '<span class="onepoint-client-stories__tag-chevron" aria-hidden="true">&#8250;</span></span>';
			}
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '<div class="onepoint-client-stories__slide-right">';
		$html .= '<div class="onepoint-client-stories__metrics">';
		foreach ( array_filter( $metrics, function ( $m ) { return ! empty( $m['value'] ) || ! empty( $m['label'] ); } ) as $m ) {
			$val = isset( $m['value'] ) ? esc_html( $m['value'] ) : '';
			$lbl = isset( $m['label'] ) ? esc_html( $m['label'] ) : '';
			$html .= '<div class="onepoint-client-stories__metric"><span class="onepoint-client-stories__metric-value">' . $val . '</span><span class="onepoint-client-stories__metric-label">' . $lbl . '</span></div>';
		}
		$html .= '</div></div></div></div></div></div>';
	}

	$html .= '</div></div>';
	$html .= '<div class="onepoint-client-stories__controls">';
	$html .= '<button type="button" class="onepoint-client-stories__arrow onepoint-client-stories__arrow--prev" aria-label="' . esc_attr__( 'Previous slide', 'onepoint-custom-blocks' ) . '"></button>';
	$html .= '<button type="button" class="onepoint-client-stories__play-pause' . ( $autoplay ? ' is-playing' : ' is-paused' ) . '" aria-label="' . esc_attr( $autoplay ? __( 'Pause carousel', 'onepoint-custom-blocks' ) : __( 'Play carousel', 'onepoint-custom-blocks' ) ) . '">';
	$html .= '<span class="onepoint-client-stories__play-icon" aria-hidden="true"></span>';
	$html .= '<span class="onepoint-client-stories__pause-icon" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<button type="button" class="onepoint-client-stories__arrow onepoint-client-stories__arrow--next" aria-label="' . esc_attr__( 'Next slide', 'onepoint-custom-blocks' ) . '"></button>';
	$html .= '</div></div>';


	return $html;
}

/**
 * Render the Onepoint Footer block (frontend).
 */
function onepoint_render_footer($attributes) {
	$col1   = isset($attributes['col1Title']) ? $attributes['col1Title'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_col1_title', 'What we do') : 'What we do' );
	$col2   = isset($attributes['col2Title']) ? $attributes['col2Title'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_col2_title', 'Resources') : 'Resources' );
	$col3   = isset($attributes['col3Title']) ? $attributes['col3Title'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_col3_title', 'About us') : 'About us' );
	$col4   = isset($attributes['col4Title']) ? $attributes['col4Title'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_col4_title', 'More info') : 'More info' );
	$btn_col = isset($attributes['btnCollapse']) ? $attributes['btnCollapse'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_btn_collapse', 'Hide full footer') : 'Hide full footer' );
	$btn_exp = isset($attributes['btnExpand']) ? $attributes['btnExpand'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_btn_expand', 'Show full footer') : 'Show full footer' );
	$terms_url = isset($attributes['termsUrl']) && $attributes['termsUrl'] !== '' ? $attributes['termsUrl'] : home_url('/terms');
	$cook_url  = isset($attributes['cookiesUrl']) && $attributes['cookiesUrl'] !== '' ? $attributes['cookiesUrl'] : home_url('/cookies');
	$pol_label = isset($attributes['policiesLabel']) ? $attributes['policiesLabel'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_policies_label', 'Policies') : 'Policies' );
	$terms_label = isset($attributes['termsLabel']) ? $attributes['termsLabel'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_terms_label', 'Terms and conditions') : 'Terms and conditions' );
	$cook_label  = isset($attributes['cookiesLabel']) ? $attributes['cookiesLabel'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_cookies_label', 'Cookies') : 'Cookies' );
	$copyright   = isset($attributes['copyright']) ? $attributes['copyright'] : ( function_exists('get_theme_mod') ? get_theme_mod('footer_copyright', 'Onepoint Consulting Ltd') : 'Onepoint Consulting Ltd' );

	$use_theme_column = function_exists('onepoint_footer_column');

	ob_start();
	?>
	<footer id="site-footer" class="site-footer" role="contentinfo">
		<div class="footer-inner">
			<div class="footer-top" id="footer-full">
				<div class="footer-cols">
					<?php
					if ($use_theme_column) {
						onepoint_footer_column('footer_what_we_do', $col1, onepoint_footer_what_we_do_fallback(), 'Innovate with AI & more');
						onepoint_footer_column('footer_resources', $col2, onepoint_footer_resources_fallback());
						onepoint_footer_column('footer_about', $col3, onepoint_footer_about_fallback());
						onepoint_footer_column('footer_more_info', $col4, onepoint_footer_more_info_fallback());
					} else {
						echo wp_kses_post( onepoint_plugin_footer_column( 'footer_what_we_do', $col1, 'Innovate with AI & more' ) );
						echo wp_kses_post( onepoint_plugin_footer_column( 'footer_resources', $col2 ) );
						echo wp_kses_post( onepoint_plugin_footer_column( 'footer_about', $col3 ) );
						echo wp_kses_post( onepoint_plugin_footer_column( 'footer_more_info', $col4 ) );
					}
					?>
				</div>
			</div>
			<div class="footer-mobile-accordion" aria-hidden="true">
				<?php
				$sections = array(
					'footer_what_we_do' => array('title' => $col1, 'fallback' => $use_theme_column ? onepoint_footer_what_we_do_fallback() : onepoint_plugin_footer_fallback('footer_what_we_do'), 'highlight' => 'Innovate with AI & more'),
					'footer_resources'  => array('title' => $col2, 'fallback' => $use_theme_column ? onepoint_footer_resources_fallback() : onepoint_plugin_footer_fallback('footer_resources'), 'highlight' => ''),
					'footer_about'      => array('title' => $col3, 'fallback' => $use_theme_column ? onepoint_footer_about_fallback() : onepoint_plugin_footer_fallback('footer_about'), 'highlight' => ''),
					'footer_more_info'  => array('title' => $col4, 'fallback' => $use_theme_column ? onepoint_footer_more_info_fallback() : onepoint_plugin_footer_fallback('footer_more_info'), 'highlight' => ''),
				);
				foreach ($sections as $loc => $args) :
					$fallback = $args['fallback'];
				?>
				<div class="footer-accordion-item">
					<button type="button" class="footer-accordion-trigger" aria-expanded="false" aria-controls="footer-acc-<?php echo esc_attr($loc); ?>" id="footer-acc-btn-<?php echo esc_attr($loc); ?>">
						<span class="footer-accordion-trigger-text"><?php echo esc_html($args['title']); ?></span>
						<span class="footer-accordion-trigger-icon" aria-hidden="true"></span>
					</button>
					<div class="footer-accordion-panel" id="footer-acc-<?php echo esc_attr($loc); ?>" role="region" aria-labelledby="footer-acc-btn-<?php echo esc_attr($loc); ?>" hidden>
						<ul class="footer-accordion-list">
							<?php foreach ($fallback as $item) :
								$label = isset($item['label']) ? $item['label'] : '';
								$url   = isset($item['url']) ? $item['url'] : home_url('/');
								$cls   = ( ! empty( $args['highlight'] ) && $label === $args['highlight'] ) ? 'is-active' : '';
							?>
							<li><a href="<?php echo esc_url( $url ); ?>"<?php echo $cls ? ' class="' . esc_attr( $cls ) . '"' : ''; ?>><?php echo esc_html( $label ); ?></a></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<div class="footer-bottom">
				<button type="button" class="footer-toggle-full" id="footer-toggle-full" aria-expanded="true" aria-controls="footer-full" aria-label="<?php echo esc_attr($btn_col); ?>" data-label-collapse="<?php echo esc_attr($btn_col); ?>" data-label-expand="<?php echo esc_attr($btn_exp); ?>">
					<?php echo esc_html($btn_col); ?>
				</button>
				<div class="footer-brand-row">
					<div class="footer-brand">
						<a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" rel="home">
							<?php if (has_custom_logo()) : ?>
								<?php the_custom_logo(); ?>
							<?php else : ?>
								<span class="footer-logo-text"><?php echo esc_html(get_bloginfo('name')); ?></span>
							<?php endif; ?>
						</a>
					</div>
				</div>
				<div class="footer-legal">
					<nav class="footer-policies" aria-label="<?php echo esc_attr($pol_label); ?>">
						<span class="footer-policies-label"><?php echo esc_html($pol_label); ?></span>
						<a href="<?php echo esc_url($terms_url); ?>"><?php echo esc_html($terms_label); ?></a>
						<a href="<?php echo esc_url($cook_url); ?>"><?php echo esc_html($cook_label); ?></a>
					</nav>
					<p class="footer-copyright">© <?php echo esc_html(gmdate('Y')); ?> <?php echo esc_html($copyright); ?></p>
				</div>
			</div>
		</div>
	</footer>
	<?php
	return ob_get_clean();
}

/**
 * Sanitize a single purpose card item from block attributes.
 *
 * @param array $item Raw item from block attributes.
 * @return array Sanitized item.
 */
function onepoint_sanitize_purpose_card_item( $item ) {
	if ( ! is_array( $item ) ) {
		return array();
	}
	return array(
		'imageUrl'     => isset( $item['imageUrl'] ) ? esc_url_raw( $item['imageUrl'] ) : '',
		'imageAlt'     => isset( $item['imageAlt'] ) ? sanitize_text_field( $item['imageAlt'] ) : '',
		'brand'        => isset( $item['brand'] ) ? sanitize_text_field( $item['brand'] ) : '',
		'title'        => isset( $item['title'] ) ? sanitize_text_field( $item['title'] ) : '',
		'heading'      => isset( $item['heading'] ) ? wp_kses_post( $item['heading'] ) : '',
		'description'  => isset( $item['description'] ) ? wp_kses_post( $item['description'] ) : '',
		'accentColor'  => isset( $item['accentColor'] ) ? sanitize_hex_color( $item['accentColor'] ) : '#00D3BA',
	);
}

/**
 * Render the Purpose Cards Carousel block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_purpose_cards_carousel( $attributes ) {

	$section_label     = isset( $attributes['sectionLabel'] ) ? sanitize_text_field( $attributes['sectionLabel'] ) : '';
	$section_heading   = isset( $attributes['sectionHeading'] ) ? sanitize_text_field( $attributes['sectionHeading'] ) : '';
	$section_desc      = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';
	$cta_text          = isset( $attributes['ctaText'] ) ? sanitize_text_field( $attributes['ctaText'] ) : '';
	$cta_url           = isset( $attributes['ctaUrl'] ) ? esc_url( $attributes['ctaUrl'] ) : '';
	$raw_items         = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

	$default_item = array(
		'imageUrl'    => '',
		'imageAlt'    => '',
		'brand'       => 'ONEPOINT',
		'title'       => 'AppShip',
		'heading'     => "Investing in young people's futures",
		'description' => '',
		'accentColor' => '#00D3BA',
	);

	if ( empty( $raw_items ) ) {
		$items = array( $default_item );
	} else {
		$items = array_map( 'onepoint_sanitize_purpose_card_item', $raw_items );
		$items = array_filter( $items );
		if ( empty( $items ) ) {
			$items = array( $default_item );
		}
	}

	$closed_bg_url = isset( $attributes['closedCardBackgroundUrl'] ) ? esc_url( $attributes['closedCardBackgroundUrl'] ) : '';
	$wrapper_style = $closed_bg_url ? ' style="--purpose-cards-closed-bg: url(\'' . $closed_bg_url . '\');"' : '';
	$unique_id     = 'onepoint-purpose-cards-' . uniqid();

	$html  = '<div class="onepoint-purpose-cards" id="' . esc_attr( $unique_id ) . '"' . $wrapper_style . '>';
	$html .= '<div class="onepoint-purpose-cards__header">';
	if ( $section_label ) {
		$html .= '<div class="onepoint-purpose-cards__label-wrap"><p class="onepoint-purpose-cards__label">' . esc_html( $section_label ) . '</p></div>';
	}
	if ( $section_heading ) {
		$html .= '<h2>' . esc_html( $section_heading ) . '</h2>';
	}
	if ( $section_desc ) {
		$html .= '<p class="onepoint-purpose-cards__description">' . $section_desc . '</p>';
	}
	$html .= '</div>';
	$html .= '<div class="onepoint-purpose-cards__track"><div class="onepoint-purpose-cards__track-inner">';

	foreach ( $items as $i => $s ) {
		$img_url    = isset( $s['imageUrl'] ) ? esc_url( $s['imageUrl'] ) : '';
		$img_alt    = isset( $s['imageAlt'] ) ? esc_attr( $s['imageAlt'] ) : '';
		$brand      = isset( $s['brand'] ) ? esc_html( $s['brand'] ) : '';
		$title      = isset( $s['title'] ) ? esc_html( $s['title'] ) : '';
		$heading    = isset( $s['heading'] ) ? wp_kses_post( $s['heading'] ) : '';
		$desc       = isset( $s['description'] ) ? wp_kses_post( $s['description'] ) : '';
		$accent     = isset( $s['accentColor'] ) ? esc_attr( sanitize_hex_color( $s['accentColor'] ) ?: '#00D3BA' ) : '#00D3BA';
		$is_active  = $i === 0;
		/* translators: %d: card number (1-based). */
		$card_name  = $title ? $title : ( $brand ? $brand : sprintf( __( 'Card %d', 'onepoint-custom-blocks' ), $i + 1 ) );

		$html .= '<button type="button" class="onepoint-purpose-cards__card' . ( $is_active ? ' is-active' : '' ) . '" aria-label="' . esc_attr( $card_name ) . '" style="--onepoint-card-accent:' . $accent . '">';
		$html .= '<div class="onepoint-purpose-cards__card-inner">';
		if ( $img_url || $brand || $title ) {
			$html .= '<div class="onepoint-purpose-cards__card-header">';
			if ( $img_url ) {
				$html .= '<div class="onepoint-purpose-cards__card-icon"><img src="' . $img_url . '" alt="' . $img_alt . '" loading="lazy" /></div>';
			} else {
				$html .= '<div class="onepoint-purpose-cards__card-icon-placeholder" aria-hidden="true"></div>';
			}
			$html .= '<div class="onepoint-purpose-cards__card-branding">';
			if ( $brand ) {
				$html .= '<span class="onepoint-purpose-cards__card-brand">' . $brand . '</span>';
			}
			if ( $title ) {
				$html .= '<span class="onepoint-purpose-cards__card-title">' . $title . '</span>';
			}
			$html .= '</div></div>';
		}
		if ( $heading ) {
			$html .= '<h3 class="onepoint-purpose-cards__card-heading">' . $heading . '</h3>';
		}
		if ( $desc ) {
			$html .= '<p class="onepoint-purpose-cards__card-desc">' . $desc . '</p>';
		}
		$html .= '</div></button>';
	}

	$html .= '</div></div>';
	if ( $cta_text ) {
		$html .= '<div class="onepoint-purpose-cards__cta-wrap">';
		$html .= '<a href="' . ( $cta_url ?: '#' ) . '" class="onepoint-purpose-cards__cta">' . esc_html( $cta_text ) . '</a>';
		$html .= '</div>';
	}
	$html .= '</div>';

	return $html;
}