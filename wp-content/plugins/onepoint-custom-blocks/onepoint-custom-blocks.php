<?php
/**
 * Plugin Name: Onepoint Custom Blocks
 * Description: Gutenberg blocks POC (Plugin vs Theme approach)
 * Version: 0.5.0
 */

defined('ABSPATH') || exit;

/**
 * Register all blocks in blocks/ folder. Dynamic blocks get render_callback from mapping.
 */
function onepoint_register_blocks() {
	$blocks_dir = plugin_dir_path(__FILE__) . 'blocks';
	$render_callbacks = array(
<<<<<<< HEAD
		'onepoint/image-carousel'        => 'onepoint_render_image_carousel',
		'onepoint/initiative-card'       => 'onepoint_render_initiative_card',
		'onepoint/hero-banner'           => 'onepoint_render_hero_banner',
		'onepoint/technology-carousel'   => 'onepoint_render_technology_carousel',
		'onepoint/site-header'           => 'onepoint_render_site_header',
		'onepoint/header'                => 'onepoint_render_header',
		'onepoint/contact-form'          => 'onepoint_render_contact_form',
		'onepoint/client-stories-carousel' => 'onepoint_render_client_stories_carousel',
		'onepoint/purpose-cards-carousel' => 'onepoint_render_purpose_cards_carousel',
		'onepoint/footer'                 => 'onepoint_render_footer',
=======
<<<<<<< HEAD
		'onepoint/image-carousel'   => 'onepoint_render_image_carousel',
		'onepoint/initiative-card'  => 'onepoint_render_initiative_card',
		'onepoint/hero-banner'      => 'onepoint_render_hero_banner',
		'onepoint/header'           => 'onepoint_render_header',
		'onepoint/footer'           => 'onepoint_render_footer',
=======
		'onepoint/vision-block'               => 'onepoint_render_vision_block',
		'onepoint/image-carousel'             => 'onepoint_render_image_carousel',
		'onepoint/initiative-card'            => 'onepoint_render_initiative_card',
		'onepoint/hero-banner'                => 'onepoint_render_hero_banner',
		'onepoint/technology-carousel'        => 'onepoint_render_technology_carousel',
		'onepoint/client-stories-carousel'    => 'onepoint_render_client_stories_carousel',
		'onepoint/purpose-cards-carousel'     => 'onepoint_render_purpose_cards_carousel',
		'onepoint/latest-updates-carousel'    => 'onepoint_render_latest_updates_carousel',
		'onepoint/contact-form'               => 'onepoint_render_contact_form',
>>>>>>> f4c6dffb3f041f265141415961791fc8bb2bf198
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
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
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_technology_carousel( $attributes ) {
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

<<<<<<< HEAD
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
=======
	if ( $count === 0 ) {
		$content .= '<div class="onepoint-tech-carousel-wrap" data-speed="' . esc_attr( $speed ) . '" data-count="0"><p class="onepoint-tech-carousel-empty">' . esc_html__( 'Add at least 6 technology logos in the block settings.', 'onepoint-custom-blocks' ) . '</p></div>';
	} else {
		$rows   = onepoint_technology_carousel_chunk_rows( $items, 3 );
		$unique = 'onepoint-tech-carousel-' . uniqid();
		$content .= '<div class="onepoint-tech-carousel-wrap" id="' . esc_attr( $unique ) . '" data-speed="' . esc_attr( $speed ) . '" data-count="' . esc_attr( $count ) . '">';
		$content .= '<div class="onepoint-tech-carousel-track" aria-hidden="true">';

		$track_html = '';
		foreach ( $rows as $row ) {
			$track_html .= onepoint_technology_carousel_render_row( $row );
		}

		// For seamless loop when > 9 items, duplicate the track content (viewport shows 9 cards; carousel slides upward).
		if ( $count > 9 ) {
			$content .= $track_html . $track_html;
		} else {
			$content .= $track_html;
		}

		$content .= '</div></div>';
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
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
			$html .= '<button type="button" role="tab" aria-selected="' . ( $i === 0 ? 'true' : 'false' ) . '" aria-label="' . esc_attr( sprintf( __( 'Slide %d', 'onepoint-custom-blocks' ), $i + 1 ) ) . '" class="onepoint-hero-carousel__dot' . $dot_active . '"></button>';
		}
		$html .= '</div>';
		$html .= '<button type="button" class="onepoint-hero-carousel__play-pause" aria-label="' . esc_attr__( $autoplay ? 'Pause carousel' : 'Play carousel', 'onepoint-custom-blocks' ) . '">';
		$html .= '<span class="onepoint-hero-carousel__play-pause-icon' . ( $autoplay ? '' : ' is-paused' ) . '" aria-hidden="true"></span>';
		$html .= '</button>';
		$html .= '</div>';
	}

	$html .= '</div>';
	return $html;
}

/**
<<<<<<< HEAD
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
	if ( ! isset( $_POST['onepoint_contact_nonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['onepoint_contact_nonce'] ), 'onepoint_contact_submit' ) ) {
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
	$body = sprintf( __( 'Name: %s', 'onepoint-custom-blocks' ), $name ) . "\n";
	$body .= sprintf( __( 'Email: %s', 'onepoint-custom-blocks' ), $email ) . "\n";
	if ( $company ) {
		$body .= sprintf( __( 'Company: %s', 'onepoint-custom-blocks' ), $company ) . "\n";
	}
	if ( $linkedin ) {
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
=======
<<<<<<< HEAD
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
=======
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

	$items    = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
	$heading  = isset( $attributes['heading'] ) ? wp_kses_post( $attributes['heading'] ) : 'Client stories';
	$autoplay = isset( $attributes['autoplay'] ) ? (bool) $attributes['autoplay'] : true;
	$interval = isset( $attributes['interval'] ) ? max( 3, min( 15, (int) $attributes['interval'] ) ) : 6;

	$default_item = array(
		'tabTitle'         => 'Robotic Process Automation',
		'tabSubtitle'      => 'SolarCo',
		'headline'         => 'Integrating massive volumes of solar farm',
		'description'      => '',
		'buttonText'       => 'Discover the outcomes',
		'buttonUrl'        => '',
		'tags'             => array( 'Low-code development' ),
		'metrics'          => array( array( 'value' => '~2000', 'label' => 'person hours saved monthly' ) ),
		'backgroundImageUrl' => '',
	);

	if ( empty( $items ) ) {
		$items = array( $default_item );
	}

	$html  = '<div class="onepoint-client-stories" data-autoplay="' . ( $autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( $interval ) . '">';
	$html .= '<div class="onepoint-client-stories__header">';
	$html .= '<h3 class="onepoint-client-stories__heading">' . $heading . '</h3>';
	$html .= '</div>';
	$html .= '<div class="onepoint-client-stories__card">';
	$html .= '<div class="onepoint-client-stories__list" role="tablist">';

	foreach ( $items as $i => $s ) {
		$s         = wp_parse_args( $s, $default_item );
		$tab_title = isset( $s['tabTitle'] ) ? esc_html( $s['tabTitle'] ) : '';
		$tab_sub   = isset( $s['tabSubtitle'] ) ? esc_html( $s['tabSubtitle'] ) : '';
		$headline  = isset( $s['headline'] ) ? wp_kses_post( $s['headline'] ) : '';
		$desc      = isset( $s['description'] ) ? wp_kses_post( $s['description'] ) : '';
		$btn_text  = isset( $s['buttonText'] ) ? wp_kses_post( $s['buttonText'] ) : '';
		$btn_url   = isset( $s['buttonUrl'] ) ? esc_url( $s['buttonUrl'] ) : '';
		$tags      = isset( $s['tags'] ) && is_array( $s['tags'] ) ? $s['tags'] : array();
		$metrics   = isset( $s['metrics'] ) && is_array( $s['metrics'] ) ? $s['metrics'] : array();
		$bg_url    = isset( $s['backgroundImageUrl'] ) ? esc_url( $s['backgroundImageUrl'] ) : '';
		$active    = $i === 0 ? ' is-active' : '';

		$html .= '<div class="onepoint-client-stories__item">';
		$html .= '<button type="button" role="tab" aria-selected="' . ( $i === 0 ? 'true' : 'false' ) . '" class="onepoint-client-stories__tab' . $active . '">';
		$html .= '<span class="onepoint-client-stories__tab-title">' . $tab_title . '</span>';
		$html .= '<span class="onepoint-client-stories__tab-subtitle">' . $tab_sub . '</span>';
		$html .= '<span class="onepoint-client-stories__tab-progress"><span class="onepoint-client-stories__tab-progress-fill"></span></span>';
		$html .= '</button>';
		$html .= '<div class="onepoint-client-stories__content">';
		$html .= '<div class="onepoint-client-stories__slide' . $active . '" aria-hidden="' . ( $i !== 0 ? 'true' : 'false' ) . '" role="tabpanel">';
		if ( $bg_url ) {
			$html .= '<div class="onepoint-client-stories__slide-bg" style="background-image:url(' . $bg_url . ')"></div>';
		} else {
			$html .= '<div class="onepoint-client-stories__slide-bg"></div>';
		}
=======
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
		'metrics'            => array(
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
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
		$html .= '<div class="onepoint-client-stories__slide-inner">';
		$html .= '<div class="onepoint-client-stories__slide-left">';
		if ( $headline ) {
			$html .= '<h2 class="onepoint-client-stories__slide-headline">' . $headline . '</h2>';
		}
<<<<<<< HEAD
		if ( $desc ) {
			$html .= '<p class="onepoint-client-stories__slide-desc">' . $desc . '</p>';
		}
		if ( $btn_text ) {
			$href = $btn_url ? $btn_url : '#';
			$html .= '<a href="' . $href . '" class="onepoint-client-stories__slide-cta">' . $btn_text . '</a>';
		}
		if ( ! empty( $tags ) ) {
			$html .= '<div class="onepoint-client-stories__slide-tags">';
			foreach ( array_filter( $tags ) as $tag ) {
				$html .= '<span class="onepoint-client-stories__tag"><span class="onepoint-client-stories__tag-chevron" aria-hidden="true">&#8249;</span>' . esc_html( $tag ) . '<span class="onepoint-client-stories__tag-chevron" aria-hidden="true">&#8250;</span></span>';
=======
		if ( $description ) {
			$html .= '<p class="onepoint-client-stories__slide-desc">' . $description . '</p>';
		}
		if ( $btn_text ) {
			$html .= '<a href="' . ( $btn_url ? $btn_url : '#' ) . '" class="onepoint-client-stories__slide-cta">' . $btn_text . '</a>';
		}
		if ( ! empty( $tags ) ) {
			$html .= '<div class="onepoint-client-stories__slide-tags">';
			foreach ( $tags as $tag ) {
				$html .= '<span class="onepoint-client-stories__tag"><span class="onepoint-client-stories__tag-chevron" aria-hidden="true">‹</span>' . esc_html( $tag ) . '<span class="onepoint-client-stories__tag-chevron" aria-hidden="true">›</span></span>';
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
			}
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '<div class="onepoint-client-stories__slide-right">';
		$html .= '<div class="onepoint-client-stories__metrics">';
<<<<<<< HEAD
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
	$html .= '<button type="button" class="onepoint-client-stories__play-pause is-' . ( $autoplay ? 'playing' : 'paused' ) . '" aria-label="' . esc_attr__( 'Play/Pause', 'onepoint-custom-blocks' ) . '">';
=======
		foreach ( $metrics as $m ) {
			$val   = isset( $m['value'] ) ? esc_html( $m['value'] ) : '';
			$label = isset( $m['label'] ) ? esc_html( $m['label'] ) : '';
			$html .= '<div class="onepoint-client-stories__metric"><span class="onepoint-client-stories__metric-value">' . $val . '</span><span class="onepoint-client-stories__metric-label">' . $label . '</span></div>';
		}
		$html .= '</div></div></div></div>';
		$html .= '</div></div>';
	}

	$html .= '</div>';
	$html .= '<div class="onepoint-client-stories__controls">';
	$html .= '<button type="button" class="onepoint-client-stories__arrow onepoint-client-stories__arrow--prev" aria-label="' . esc_attr__( 'Previous slide', 'onepoint-custom-blocks' ) . '"></button>';
	$html .= '<button type="button" class="onepoint-client-stories__play-pause' . ( $autoplay ? ' is-playing' : ' is-paused' ) . '" aria-label="' . esc_attr__( $autoplay ? 'Pause carousel' : 'Play carousel', 'onepoint-custom-blocks' ) . '">';
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
	$html .= '<span class="onepoint-client-stories__play-icon" aria-hidden="true"></span>';
	$html .= '<span class="onepoint-client-stories__pause-icon" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<button type="button" class="onepoint-client-stories__arrow onepoint-client-stories__arrow--next" aria-label="' . esc_attr__( 'Next slide', 'onepoint-custom-blocks' ) . '"></button>';
	$html .= '</div></div>';

<<<<<<< HEAD
=======
>>>>>>> f4c6dffb3f041f265141415961791fc8bb2bf198
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
	return $html;
}

/**
<<<<<<< HEAD
 * Render the Purpose Cards Carousel block (frontend).
=======
<<<<<<< HEAD
 * Render the Onepoint Header block (frontend).
 */
function onepoint_render_header($attributes) {
	$site_name = isset($attributes['siteName']) && $attributes['siteName'] !== '' ? $attributes['siteName'] : ( function_exists('get_theme_mod') && get_theme_mod('header_site_name', '') !== '' ? get_theme_mod('header_site_name') : get_bloginfo('name') );
	$menu_loc  = isset($attributes['menuLocation']) ? sanitize_key($attributes['menuLocation']) : 'primary';
	$icon_url  = isset($attributes['iconUrl']) && $attributes['iconUrl'] !== '' ? $attributes['iconUrl'] : ( function_exists('get_theme_mod') && get_theme_mod('header_icon_url', '') !== '' ? get_theme_mod('header_icon_url') : ( function_exists('onepoint_header_icon_url') ? onepoint_header_icon_url() : '' ) );
	$admin_logo_url = function_exists('get_theme_mod') ? get_theme_mod('header_logo_url', '') : '';
	$default_logo_url = function_exists('onepoint_header_logo_url') ? onepoint_header_logo_url() : '';
	$logo_url = ($admin_logo_url !== '') ? $admin_logo_url : $default_logo_url;

	ob_start();
	?>
	<header id="masthead" class="site-header" role="banner">
		<div class="header-inner">
			<div class="header-brand">
				<a href="<?php echo esc_url(home_url('/')); ?>" class="site-logo" rel="home">
					<?php if ($logo_url !== '') : ?>
						<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr($site_name); ?>" class="site-logo-img" width="180" height="48" />
					<?php elseif (has_custom_logo()) : ?>
						<?php the_custom_logo(); ?>
					<?php else : ?>
						<span class="site-name"><?php echo esc_html($site_name); ?></span>
					<?php endif; ?>
				</a>
			</div>
			<button type="button" class="header-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e('Toggle menu', 'onepoint-custom-blocks'); ?>">
				<span class="hamburger" aria-hidden="true"></span>
			</button>
			<nav id="site-navigation" class="header-nav" aria-label="<?php esc_attr_e('Primary', 'onepoint-custom-blocks'); ?>">
				<?php
				wp_nav_menu(array(
					'theme_location' => $menu_loc,
					'menu_id'        => 'primary-menu',
					'menu_class'     => 'nav-menu',
					'container'      => false,
					'fallback_cb'    => function_exists('onepoint_header_fallback_menu') ? 'onepoint_header_fallback_menu' : null,
				));
				?>
			</nav>
			<div class="header-icons" aria-hidden="true">
				<?php
				if ($icon_url) :
					for ($i = 0; $i < 3; $i++) :
				?>
				<span class="header-icon header-icon-custom">
					<img src="<?php echo esc_url($icon_url); ?>" alt="" width="24" height="24" loading="lazy" />
				</span>
				<?php
					endfor;
				else :
					$flask_svg = '<svg class="icon-flask" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 2v5M15 2v5"/><path d="M8 7h8l2.5 14H5.5L8 7z"/><line x1="8" y1="14" x2="16" y2="14"/><circle cx="10" cy="15" r="1"/><circle cx="14" cy="16" r="1"/></svg>';
				?>
				<span class="header-icon header-icon-flask"><?php echo $flask_svg; ?></span>
				<span class="header-icon header-icon-flask"><?php echo $flask_svg; ?></span>
				<span class="header-icon header-icon-flask"><?php echo $flask_svg; ?></span>
				<?php endif; ?>
			</div>
		</div>
	</header>
	<?php
	return ob_get_clean();
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
						echo onepoint_plugin_footer_column('footer_what_we_do', $col1, 'Innovate with AI & more');
						echo onepoint_plugin_footer_column('footer_resources', $col2);
						echo onepoint_plugin_footer_column('footer_about', $col3);
						echo onepoint_plugin_footer_column('footer_more_info', $col4);
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
								$cls   = ($args['highlight'] && $label === $args['highlight']) ? ' class="is-active"' : '';
							?>
							<li><a href="<?php echo esc_url($url); ?>"<?php echo $cls; ?>><?php echo esc_html($label); ?></a></li>
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
=======
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
 * Render the Purpose Cards Accordion block (frontend) – horizontal accordion, click to expand.
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_purpose_cards_carousel( $attributes ) {
<<<<<<< HEAD
	$section_label      = isset( $attributes['sectionLabel'] ) ? wp_kses_post( $attributes['sectionLabel'] ) : 'Purpose beyond profit';
	$section_heading    = isset( $attributes['sectionHeading'] ) ? wp_kses_post( $attributes['sectionHeading'] ) : 'Always doing right by every stakeholder';
	$section_description = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';
	$cta_text           = isset( $attributes['ctaText'] ) ? wp_kses_post( $attributes['ctaText'] ) : 'Learn more about Purpose beyond profit';
	$cta_url            = isset( $attributes['ctaUrl'] ) ? esc_url( $attributes['ctaUrl'] ) : '';
	$items              = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
=======
	$section_label     = isset( $attributes['sectionLabel'] ) ? sanitize_text_field( $attributes['sectionLabel'] ) : '';
	$section_heading   = isset( $attributes['sectionHeading'] ) ? sanitize_text_field( $attributes['sectionHeading'] ) : '';
	$section_desc     = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';
	$cta_text         = isset( $attributes['ctaText'] ) ? sanitize_text_field( $attributes['ctaText'] ) : '';
	$cta_url          = isset( $attributes['ctaUrl'] ) ? esc_url( $attributes['ctaUrl'] ) : '';
	$raw_items        = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7

	$default_item = array(
		'imageUrl'    => '',
		'imageAlt'    => '',
		'brand'       => 'ONEPOINT',
		'title'       => 'AppShip',
		'heading'     => "Investing in young people's futures",
		'description' => '',
		'accentColor' => '#00D3BA',
	);

<<<<<<< HEAD
	if ( empty( $items ) ) {
		$items = array( $default_item );
	}

	$closed_bg_url = isset( $attributes['closedCardBackgroundUrl'] ) ? esc_url( $attributes['closedCardBackgroundUrl'] ) : '';
	$wrapper_style = $closed_bg_url ? ' style="--purpose-cards-closed-bg: url(\'' . $closed_bg_url . '\');"' : '';

	$html  = '<div class="onepoint-purpose-cards"' . $wrapper_style . '>';
	$html .= '<div class="onepoint-purpose-cards__header">';
	if ( $section_label ) {
		$html .= '<div class="onepoint-purpose-cards__label-wrap"><p class="onepoint-purpose-cards__label">' . $section_label . '</p></div>';
	}
	if ( $section_heading ) {
		$html .= '<h2>' . $section_heading . '</h2>';
	}
	if ( $section_description ) {
		$html .= '<p class="onepoint-purpose-cards__description">' . $section_description . '</p>';
	}
	$html .= '</div>';
	$html .= '<div class="onepoint-purpose-cards__track">';
	$html .= '<div class="onepoint-purpose-cards__track-inner">';

	foreach ( $items as $i => $s ) {
		$s         = wp_parse_args( $s, $default_item );
		$image_url = isset( $s['imageUrl'] ) ? esc_url( $s['imageUrl'] ) : '';
		$image_alt = isset( $s['imageAlt'] ) ? esc_attr( $s['imageAlt'] ) : '';
		$brand     = isset( $s['brand'] ) ? esc_html( $s['brand'] ) : '';
		$title     = isset( $s['title'] ) ? esc_html( $s['title'] ) : '';
		$heading   = isset( $s['heading'] ) ? wp_kses_post( $s['heading'] ) : '';
		$desc      = isset( $s['description'] ) ? wp_kses_post( $s['description'] ) : '';
		$accent_raw = isset( $s['accentColor'] ) ? $s['accentColor'] : '#00D3BA';
		$accent    = esc_attr( sanitize_hex_color( $accent_raw ) ?: '#00D3BA' );
		$active    = $i === 0 ? ' is-active' : '';

		$html .= '<button type="button" class="onepoint-purpose-cards__card' . $active . '" aria-label="' . esc_attr( $title ?: $brand ?: __( 'Card', 'onepoint-custom-blocks' ) . ' ' . ( $i + 1 ) ) . '" style="--onepoint-card-accent:' . $accent . '">';
		$html .= '<div class="onepoint-purpose-cards__card-inner">';
		if ( $image_url || $brand || $title ) {
			$html .= '<div class="onepoint-purpose-cards__card-header">';
			if ( $image_url ) {
				$html .= '<div class="onepoint-purpose-cards__card-icon"><img src="' . $image_url . '" alt="' . $image_alt . '" loading="lazy" /></div>';
=======
	if ( empty( $raw_items ) ) {
		$items = array( $default_item );
	} else {
		$items = array_map( 'onepoint_sanitize_purpose_card_item', $raw_items );
		$items = array_filter( $items );
		if ( empty( $items ) ) {
			$items = array( $default_item );
		}
	}

	$unique_id = 'onepoint-purpose-cards-' . uniqid();
	$html      = '<div class="onepoint-purpose-cards" id="' . esc_attr( $unique_id ) . '">';
	$html     .= '<div class="onepoint-purpose-cards__header">';
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
		$accent     = isset( $s['accentColor'] ) ? esc_attr( $s['accentColor'] ) : '#00D3BA';
		$is_active  = $i === 0;
		$card_name  = $title ? $title : ( $brand ? $brand : sprintf( __( 'Card %d', 'onepoint-custom-blocks' ), $i + 1 ) );

		$html .= '<button type="button" class="onepoint-purpose-cards__card' . ( $is_active ? ' is-active' : '' ) . '" aria-label="' . esc_attr( $card_name ) . '" style="--onepoint-card-accent:' . $accent . '">';
		$html .= '<div class="onepoint-purpose-cards__card-inner">';
		if ( $img_url || $brand || $title ) {
			$html .= '<div class="onepoint-purpose-cards__card-header">';
			if ( $img_url ) {
				$html .= '<div class="onepoint-purpose-cards__card-icon"><img src="' . $img_url . '" alt="' . $img_alt . '" loading="lazy" /></div>';
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
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
<<<<<<< HEAD
		$html .= '<a href="' . ( $cta_url ?: '#' ) . '" class="onepoint-purpose-cards__cta">' . $cta_text . '</a>';
		$html .= '</div>';
=======
		$href   = $cta_url ? $cta_url : '#';
		$html  .= '<a href="' . $href . '" class="onepoint-purpose-cards__cta">' . esc_html( $cta_text ) . '</a>';
		$html  .= '</div>';
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
	}
	$html .= '</div>';

	return $html;
}

/**
<<<<<<< HEAD
 * Render the Footer block (frontend). Uses theme .site-footer styles.
=======
 * Sanitize a single latest updates card item from block attributes.
 *
 * @param array $item Raw item from block attributes.
 * @return array Sanitized item.
 */
function onepoint_sanitize_latest_updates_item( $item ) {
	if ( ! is_array( $item ) ) {
		return array();
	}
	return array(
		'categoryTag' => isset( $item['categoryTag'] ) ? sanitize_text_field( $item['categoryTag'] ) : '',
		'imageUrl'    => isset( $item['imageUrl'] ) ? esc_url_raw( $item['imageUrl'] ) : '',
		'imageAlt'    => isset( $item['imageAlt'] ) ? sanitize_text_field( $item['imageAlt'] ) : '',
		'title'       => isset( $item['title'] ) ? wp_kses_post( $item['title'] ) : '',
		'buttonText'  => isset( $item['buttonText'] ) ? sanitize_text_field( $item['buttonText'] ) : '',
		'buttonUrl'   => isset( $item['buttonUrl'] ) ? esc_url_raw( $item['buttonUrl'] ) : '',
	);
}

/**
 * Render the Latest Updates Carousel block (frontend) – 3 visible cards, arrows, auto-slide.
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
<<<<<<< HEAD
function onepoint_render_footer( $attributes ) {
	$col1_title   = isset( $attributes['col1Title'] ) ? wp_kses_post( $attributes['col1Title'] ) : 'What we do';
	$col2_title   = isset( $attributes['col2Title'] ) ? wp_kses_post( $attributes['col2Title'] ) : 'Resources';
	$col3_title   = isset( $attributes['col3Title'] ) ? wp_kses_post( $attributes['col3Title'] ) : 'About us';
	$col4_title   = isset( $attributes['col4Title'] ) ? wp_kses_post( $attributes['col4Title'] ) : 'More info';
	$btn_collapse = isset( $attributes['btnCollapse'] ) ? wp_kses_post( $attributes['btnCollapse'] ) : 'Hide full footer';
	$btn_expand   = isset( $attributes['btnExpand'] ) ? wp_kses_post( $attributes['btnExpand'] ) : 'Show full footer';
	$policies_label = isset( $attributes['policiesLabel'] ) ? wp_kses_post( $attributes['policiesLabel'] ) : 'Policies';
	$terms_label  = isset( $attributes['termsLabel'] ) ? wp_kses_post( $attributes['termsLabel'] ) : 'Terms and conditions';
	$terms_url    = isset( $attributes['termsUrl'] ) ? esc_url( $attributes['termsUrl'] ) : home_url( '/terms' );
	$cookies_label = isset( $attributes['cookiesLabel'] ) ? wp_kses_post( $attributes['cookiesLabel'] ) : 'Cookies';
	$cookies_url  = isset( $attributes['cookiesUrl'] ) ? esc_url( $attributes['cookiesUrl'] ) : home_url( '/cookies' );
	$copyright    = isset( $attributes['copyright'] ) ? wp_kses_post( $attributes['copyright'] ) : 'Onepoint Consulting Ltd';
	$footer_logo_url = isset( $attributes['footerLogoUrl'] ) ? esc_url( $attributes['footerLogoUrl'] ) : '';
	$footer_logo_alt = isset( $attributes['footerLogoAlt'] ) ? esc_attr( $attributes['footerLogoAlt'] ) : '';
	$privacy_label = isset( $attributes['privacyLabel'] ) ? wp_kses_post( $attributes['privacyLabel'] ) : '';
	$privacy_url   = isset( $attributes['privacyUrl'] ) ? esc_url( $attributes['privacyUrl'] ) : '';

	$col_titles = array( $col1_title, $col2_title, $col3_title, $col4_title );
	$menu_locations = array( 'footer-col-1', 'footer-col-2', 'footer-col-3', 'footer-col-4' );
	$year = gmdate( 'Y' );

	$html  = '<footer id="colophon" class="site-footer" role="contentinfo">';
	$html .= '<div class="footer-inner">';
	$html .= '<div class="footer-top" id="footer-top">';
	$html .= '<div class="footer-cols">';

	$fallback_links = array(
		array( __( 'Services', 'onepoint-custom-blocks' ), home_url( '/services' ) ),
		array( __( 'Industries', 'onepoint-custom-blocks' ), home_url( '/industries' ) ),
		array( __( 'Case studies', 'onepoint-custom-blocks' ), home_url( '/case-studies' ) ),
		array( __( 'Blog', 'onepoint-custom-blocks' ), home_url( '/blog' ) ),
		array( __( 'Careers', 'onepoint-custom-blocks' ), home_url( '/careers' ) ),
		array( __( 'Contact', 'onepoint-custom-blocks' ), home_url( '/contact' ) ),
	);
	foreach ( $col_titles as $i => $title ) {
		$loc = $menu_locations[ $i ];
		$html .= '<div class="footer-col">';
		$html .= '<h3 class="footer-col__title">' . $title . '</h3>';
		$menu_html = wp_nav_menu( array(
			'theme_location' => $loc,
			'menu_class'     => 'footer-col__list',
			'container'      => false,
			'echo'           => false,
			'fallback_cb'    => false,
		) );
		if ( $menu_html ) {
			$html .= $menu_html;
		} else {
			$html .= '<ul class="footer-col__list">';
			$take = 2 + ( $i % 2 );
			foreach ( array_slice( $fallback_links, $i * 2, $take ) as $link ) {
				$html .= '<li class="menu-item"><a href="' . esc_url( $link[1] ) . '">' . esc_html( $link[0] ) . '</a></li>';
			}
			$html .= '</ul>';
		}
		$html .= '</div>';
	}

	$html .= '</div></div>';
	$html .= '<div class="footer-bottom">';
	$html .= '<button type="button" class="footer-toggle-full" id="footer-toggle-full" aria-expanded="true" aria-controls="footer-top" data-label-collapse="' . esc_attr( $btn_collapse ) . '" data-label-expand="' . esc_attr( $btn_expand ) . '">';
	$html .= '<span class="footer-toggle-full-text">' . $btn_collapse . '</span>';
	$html .= '<span class="footer-toggle-full-icon" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<div class="footer-brand-row">';
	$html .= '<div class="footer-brand">';
	if ( $footer_logo_url ) {
		$html .= '<a href="' . esc_url( home_url( '/' ) ) . '" class="footer-logo" rel="home"><img src="' . $footer_logo_url . '" alt="' . $footer_logo_alt . '" /></a>';
	} elseif ( has_custom_logo() ) {
		$html .= '<a href="' . esc_url( home_url( '/' ) ) . '" class="footer-logo" rel="home">' . get_custom_logo() . '</a>';
	} else {
		$html .= '<a href="' . esc_url( home_url( '/' ) ) . '" class="footer-logo footer-logo-text" rel="home">' . esc_html( get_bloginfo( 'name' ) ?: 'ONEPOINT' ) . '</a>';
	}
	$html .= '</div></div>';
	$html .= '<div class="footer-legal">';
	$html .= '<div class="footer-policies">';
	$html .= '<span class="footer-policies-label">' . $policies_label . ':</span>';
	$html .= '<a href="' . $terms_url . '">' . $terms_label . '</a>';
	if ( $privacy_label && $privacy_url ) {
		$html .= '<a href="' . $privacy_url . '">' . $privacy_label . '</a>';
	}
	$html .= '<a href="' . $cookies_url . '">' . $cookies_label . '</a>';
	$html .= '</div>';
	$html .= '<p class="footer-copyright">© ' . esc_html( $year ) . ' ' . $copyright . '</p>';
	$html .= '</div></div>';
	$html .= '</div></footer>';
=======
function onepoint_render_latest_updates_carousel( $attributes ) {
	$section_label     = isset( $attributes['sectionLabel'] ) ? sanitize_text_field( $attributes['sectionLabel'] ) : '';
	$section_heading   = isset( $attributes['sectionHeading'] ) ? sanitize_text_field( $attributes['sectionHeading'] ) : '';
	$section_desc      = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';
	$raw_items         = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
	$autoplay          = isset( $attributes['autoplay'] ) ? (bool) $attributes['autoplay'] : true;
	$interval          = isset( $attributes['interval'] ) ? max( 3, min( 15, (int) $attributes['interval'] ) ) : 6;

	$default_item = array(
		'categoryTag' => 'Press - release',
		'imageUrl'    => '',
		'imageAlt'    => '',
		'title'       => '',
		'buttonText'  => '',
		'buttonUrl'   => '',
	);

	if ( empty( $raw_items ) ) {
		$items = array( $default_item );
	} else {
		$items = array_map( 'onepoint_sanitize_latest_updates_item', $raw_items );
		$items = array_filter( $items );
		if ( empty( $items ) ) {
			$items = array( $default_item );
		}
	}

	$count    = count( $items );
	$unique_id = 'onepoint-latest-updates-' . uniqid();

	/* For infinite loop: triple the cards when count > 1; view.js starts at middle set and jumps seamlessly. */
	$total_cards = ( $count > 1 ) ? $count * 3 : $count;

	$html = '<div class="onepoint-latest-updates" id="' . esc_attr( $unique_id ) . '" data-autoplay="' . ( $autoplay ? '1' : '0' ) . '" data-interval="' . esc_attr( $interval ) . '" data-original-count="' . esc_attr( $count ) . '" style="--lu-items:' . esc_attr( $total_cards ) . '">';
	$html .= '<div class="onepoint-latest-updates__header">';
	$html .= '<div class="onepoint-latest-updates__header-text">';
	if ( $section_label ) {
		$html .= '<div class="onepoint-latest-updates__label-wrap"><p class="onepoint-latest-updates__label">' . esc_html( $section_label ) . '</p></div>';
	}
	if ( $section_heading ) {
		$html .= '<h2>' . esc_html( $section_heading ) . '</h2>';
	}
	if ( $section_desc ) {
		$html .= '<p class="onepoint-latest-updates__description">' . $section_desc . '</p>';
	}
	$html .= '</div>';
	if ( $count > 1 ) {
		$html .= '<div class="onepoint-latest-updates__arrows">';
		$html .= '<button type="button" class="onepoint-latest-updates__arrow onepoint-latest-updates__arrow--prev" aria-label="' . esc_attr__( 'Previous', 'onepoint-custom-blocks' ) . '"></button>';
		$html .= '<button type="button" class="onepoint-latest-updates__arrow onepoint-latest-updates__arrow--next" aria-label="' . esc_attr__( 'Next', 'onepoint-custom-blocks' ) . '"></button>';
		$html .= '</div>';
	}
	$html .= '</div>';
	$html .= '<div class="onepoint-latest-updates__track">';
	$html .= '<div class="onepoint-latest-updates__track-inner">';

	$copies = ( $count > 1 ) ? 3 : 1;
	for ( $copy = 0; $copy < $copies; $copy++ ) {
		foreach ( $items as $s ) {
			$cat_tag    = isset( $s['categoryTag'] ) ? esc_html( $s['categoryTag'] ) : '';
			$img_url    = isset( $s['imageUrl'] ) ? esc_url( $s['imageUrl'] ) : '';
			$img_alt    = isset( $s['imageAlt'] ) ? esc_attr( $s['imageAlt'] ) : '';
			$title      = isset( $s['title'] ) ? wp_kses_post( $s['title'] ) : '';
			$btn_text   = isset( $s['buttonText'] ) ? esc_html( $s['buttonText'] ) : '';
			$btn_url    = isset( $s['buttonUrl'] ) ? esc_url( $s['buttonUrl'] ) : '';

			$html .= '<div class="onepoint-latest-updates__card">';
			$html .= '<div class="onepoint-latest-updates__card-image-wrap">';
			if ( $img_url ) {
				$html .= '<img src="' . $img_url . '" alt="' . $img_alt . '" class="onepoint-latest-updates__card-image" loading="lazy" />';
			} else {
				$html .= '<div class="onepoint-latest-updates__card-image-placeholder" aria-hidden="true"></div>';
			}
			if ( $cat_tag ) {
				$html .= '<span class="onepoint-latest-updates__card-tag">' . $cat_tag . '</span>';
			}
			$html .= '</div>';
			$html .= '<div class="onepoint-latest-updates__card-content">';
			if ( $title ) {
				$html .= '<h3 class="onepoint-latest-updates__card-title">' . $title . '</h3>';
			}
			if ( $btn_text ) {
				$href = $btn_url ? $btn_url : '#';
				$html .= '<a href="' . $href . '" class="onepoint-latest-updates__card-cta">' . $btn_text . '</a>';
			}
			$html .= '</div></div>';
		}
	}

	$html .= '</div></div></div>';
	return $html;
}

/**
 * Render the Contact Form block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_contact_form( $attributes ) {
	$section_label   = isset( $attributes['sectionLabel'] ) ? sanitize_text_field( $attributes['sectionLabel'] ) : '';
	$heading         = isset( $attributes['heading'] ) ? sanitize_text_field( $attributes['heading'] ) : '';
	$description     = isset( $attributes['description'] ) ? wp_kses_post( $attributes['description'] ) : '';
	$button_text     = isset( $attributes['buttonText'] ) ? sanitize_text_field( $attributes['buttonText'] ) : __( 'Get in touch', 'onepoint-custom-blocks' );
	$recipient       = isset( $attributes['recipientEmail'] ) ? sanitize_email( $attributes['recipientEmail'] ) : '';
	$success_message = isset( $attributes['successMessage'] ) ? sanitize_text_field( $attributes['successMessage'] ) : __( "Thank you! We'll get back to you soon.", 'onepoint-custom-blocks' );

	if ( empty( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$form_action = admin_url( 'admin-post.php' );
	$nonce       = wp_create_nonce( 'onepoint_contact_form' );
	$redirect    = esc_url( get_permalink() ?: home_url( '/' ) );

	$success = isset( $_GET['onepoint_contact_success'] ) && $_GET['onepoint_contact_success'] === '1';
	$error   = isset( $_GET['onepoint_contact_error'] ) ? sanitize_text_field( wp_unslash( $_GET['onepoint_contact_error'] ) ) : '';

	$html = '<div class="onepoint-contact-form">';
	$html .= '<div class="onepoint-contact-form__header">';
	if ( $section_label ) {
		$html .= '<div class="onepoint-contact-form__label-wrap"><p class="onepoint-contact-form__label">' . esc_html( $section_label ) . '</p></div>';
	}
	if ( $heading ) {
		$html .= '<h2>' . esc_html( $heading ) . '</h2>';
	}
	if ( $description ) {
		$html .= '<p class="onepoint-contact-form__description">' . $description . '</p>';
	}
	$html .= '</div>';

	$html .= '<div class="onepoint-contact-form__card">';
	if ( $success ) {
		$html .= '<div class="onepoint-contact-form__message onepoint-contact-form__message--success">' . esc_html( $success_message ) . '</div>';
	}
	if ( $error ) {
		$html .= '<div class="onepoint-contact-form__message onepoint-contact-form__message--error">' . esc_html( $error ) . '</div>';
	}

	$html .= '<form class="onepoint-contact-form__fields" method="post" action="' . esc_url( $form_action ) . '">';
	$html .= '<input type="hidden" name="action" value="onepoint_contact_form" />';
	$html .= '<input type="hidden" name="onepoint_contact_nonce" value="' . esc_attr( $nonce ) . '" />';
	$html .= '<input type="hidden" name="onepoint_recipient" value="' . esc_attr( $recipient ) . '" />';
	$html .= '<input type="hidden" name="_wp_http_referer" value="' . esc_attr( $redirect ) . '" />';

	$html .= '<div class="onepoint-contact-form__row">';
	$html .= '<div class="onepoint-contact-form__field">';
	$html .= '<label for="onepoint_contact_name">' . esc_html__( 'Name', 'onepoint-custom-blocks' ) . '</label>';
	$html .= '<input type="text" id="onepoint_contact_name" name="onepoint_name" required />';
	$html .= '</div>';
	$html .= '<div class="onepoint-contact-form__field">';
	$html .= '<label for="onepoint_contact_email">' . esc_html__( 'Business email', 'onepoint-custom-blocks' ) . '</label>';
	$html .= '<input type="email" id="onepoint_contact_email" name="onepoint_email" required />';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="onepoint-contact-form__row">';
	$html .= '<div class="onepoint-contact-form__field">';
	$html .= '<label for="onepoint_contact_company">' . esc_html__( 'Company', 'onepoint-custom-blocks' ) . '</label>';
	$html .= '<input type="text" id="onepoint_contact_company" name="onepoint_company" />';
	$html .= '</div>';
	$html .= '<div class="onepoint-contact-form__field">';
	$html .= '<label for="onepoint_contact_linkedin">' . esc_html__( 'LinkedIn link', 'onepoint-custom-blocks' ) . '</label>';
	$html .= '<input type="url" id="onepoint_contact_linkedin" name="onepoint_linkedin" placeholder="https://" />';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="onepoint-contact-form__field onepoint-contact-form__field--message">';
	$html .= '<label for="onepoint_contact_message">' . esc_html__( 'How can we help you?', 'onepoint-custom-blocks' ) . '</label>';
	$html .= '<textarea id="onepoint_contact_message" name="onepoint_message" rows="5"></textarea>';
	$html .= '</div>';

	$html .= '<div class="onepoint-contact-form__button-wrap">';
	$html .= '<input type="submit" class="onepoint-contact-form__button" value="' . esc_attr( $button_text ) . '" />';
	$html .= '</div>';
	$html .= '</form>';
	$html .= '</div></div>';
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7

	return $html;
}

<<<<<<< HEAD
=======
/**
 * Handle contact form submission (admin-post).
 */
function onepoint_handle_contact_form_submit() {
	if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'onepoint_contact_form' ) {
		return;
	}
	if ( ! isset( $_POST['onepoint_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['onepoint_contact_nonce'] ) ), 'onepoint_contact_form' ) ) {
		wp_safe_redirect( add_query_arg( 'onepoint_contact_error', rawurlencode( __( 'Security check failed. Please try again.', 'onepoint-custom-blocks' ) ), wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	$name     = isset( $_POST['onepoint_name'] ) ? sanitize_text_field( wp_unslash( $_POST['onepoint_name'] ) ) : '';
	$email    = isset( $_POST['onepoint_email'] ) ? sanitize_email( wp_unslash( $_POST['onepoint_email'] ) ) : '';
	$company  = isset( $_POST['onepoint_company'] ) ? sanitize_text_field( wp_unslash( $_POST['onepoint_company'] ) ) : '';
	$linkedin = isset( $_POST['onepoint_linkedin'] ) ? esc_url_raw( wp_unslash( $_POST['onepoint_linkedin'] ) ) : '';
	$message  = isset( $_POST['onepoint_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['onepoint_message'] ) ) : '';
	$recipient = isset( $_POST['onepoint_recipient'] ) ? sanitize_email( wp_unslash( $_POST['onepoint_recipient'] ) ) : get_option( 'admin_email' );

	if ( empty( $name ) || empty( $email ) ) {
		wp_safe_redirect( add_query_arg( 'onepoint_contact_error', rawurlencode( __( 'Name and email are required.', 'onepoint-custom-blocks' ) ), wp_get_referer() ?: home_url( '/' ) ) );
		exit;
	}

	if ( empty( $recipient ) ) {
		$recipient = get_option( 'admin_email' );
	}

	$subject = sprintf( /* translators: %s: site name */ __( '[%s] Contact form submission', 'onepoint-custom-blocks' ), get_bloginfo( 'name' ) );
	$body    = sprintf( __( 'Name: %s', 'onepoint-custom-blocks' ), $name ) . "\n";
	$body   .= sprintf( __( 'Email: %s', 'onepoint-custom-blocks' ), $email ) . "\n";
	$body   .= sprintf( __( 'Company: %s', 'onepoint-custom-blocks' ), $company ) . "\n";
	$body   .= sprintf( __( 'LinkedIn: %s', 'onepoint-custom-blocks' ), $linkedin ) . "\n\n";
	$body   .= sprintf( __( 'Message:', 'onepoint-custom-blocks' ) ) . "\n" . $message;

	$headers = array( 'Content-Type: text/plain; charset=UTF-8', 'Reply-To: ' . $name . ' <' . $email . '>' );
	$sent    = wp_mail( $recipient, $subject, $body, $headers );

	$redirect = isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : ( wp_get_referer() ?: home_url( '/' ) );
	if ( $sent ) {
		wp_safe_redirect( add_query_arg( 'onepoint_contact_success', '1', $redirect ) );
	} else {
		wp_safe_redirect( add_query_arg( 'onepoint_contact_error', rawurlencode( __( 'Failed to send. Please try again later.', 'onepoint-custom-blocks' ) ), $redirect ) );
	}
	exit;
}
add_action( 'admin_post_onepoint_contact_form', 'onepoint_handle_contact_form_submit' );
add_action( 'admin_post_nopriv_onepoint_contact_form', 'onepoint_handle_contact_form_submit' );

>>>>>>> f4c6dffb3f041f265141415961791fc8bb2bf198
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
