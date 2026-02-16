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
		'onepoint/vision-block'               => 'onepoint_render_vision_block',
		'onepoint/image-carousel'             => 'onepoint_render_image_carousel',
		'onepoint/initiative-card'            => 'onepoint_render_initiative_card',
		'onepoint/hero-banner'                => 'onepoint_render_hero_banner',
		'onepoint/technology-carousel'        => 'onepoint_render_technology_carousel',
		'onepoint/client-stories-carousel'    => 'onepoint_render_client_stories_carousel',
		'onepoint/purpose-cards-carousel'     => 'onepoint_render_purpose_cards_carousel',
		'onepoint/latest-updates-carousel'    => 'onepoint_render_latest_updates_carousel',
		'onepoint/contact-form'               => 'onepoint_render_contact_form',
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
	}

	$content .= '</div></div>';
	return $content;
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
			$html .= '<p class="onepoint-hero-banner__greeting">' . $greeting . '</p>';
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
		$html .= '<div class="onepoint-client-stories__slide-inner">';
		$html .= '<div class="onepoint-client-stories__slide-left">';
		if ( $headline ) {
			$html .= '<h2 class="onepoint-client-stories__slide-headline">' . $headline . '</h2>';
		}
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
			}
			$html .= '</div>';
		}
		$html .= '</div>';
		$html .= '<div class="onepoint-client-stories__slide-right">';
		$html .= '<div class="onepoint-client-stories__metrics">';
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
	$html .= '<span class="onepoint-client-stories__play-icon" aria-hidden="true"></span>';
	$html .= '<span class="onepoint-client-stories__pause-icon" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<button type="button" class="onepoint-client-stories__arrow onepoint-client-stories__arrow--next" aria-label="' . esc_attr__( 'Next slide', 'onepoint-custom-blocks' ) . '"></button>';
	$html .= '</div></div>';

	return $html;
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
 * Render the Purpose Cards Accordion block (frontend) – horizontal accordion, click to expand.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_purpose_cards_carousel( $attributes ) {
	$section_label     = isset( $attributes['sectionLabel'] ) ? sanitize_text_field( $attributes['sectionLabel'] ) : '';
	$section_heading   = isset( $attributes['sectionHeading'] ) ? sanitize_text_field( $attributes['sectionHeading'] ) : '';
	$section_desc     = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';
	$cta_text         = isset( $attributes['ctaText'] ) ? sanitize_text_field( $attributes['ctaText'] ) : '';
	$cta_url          = isset( $attributes['ctaUrl'] ) ? esc_url( $attributes['ctaUrl'] ) : '';
	$raw_items        = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

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
		$href   = $cta_url ? $cta_url : '#';
		$html  .= '<a href="' . $href . '" class="onepoint-purpose-cards__cta">' . esc_html( $cta_text ) . '</a>';
		$html  .= '</div>';
	}
	$html .= '</div>';

	return $html;
}

/**
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
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
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

	return $html;
}

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

