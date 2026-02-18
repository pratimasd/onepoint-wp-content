<?php
/**
 * Plugin Name: Onepoint Custom Blocks
 * Description: Gutenberg blocks POC (Plugin vs Theme approach)
 * Version: 0.2.0
 */

defined('ABSPATH') || exit;

/**
 * Register all blocks in blocks/ folder. Dynamic blocks get render_callback from mapping.
 */
function onepoint_register_blocks() {
	$blocks_dir = plugin_dir_path(__FILE__) . 'blocks';
	$render_callbacks = array(
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
	$items   = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();
	$speed   = isset( $attributes['speed'] ) ? absint( $attributes['speed'] ) : 14;
	$speed   = $speed < 8 ? 8 : ( $speed > 45 ? 45 : $speed );
	$count   = count( $items );

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
		$html .= '<div class="onepoint-client-stories__slide-inner">';
		$html .= '<div class="onepoint-client-stories__slide-left">';
		if ( $headline ) {
			$html .= '<h2 class="onepoint-client-stories__slide-headline">' . $headline . '</h2>';
		}
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
	$html .= '<button type="button" class="onepoint-client-stories__play-pause is-' . ( $autoplay ? 'playing' : 'paused' ) . '" aria-label="' . esc_attr__( 'Play/Pause', 'onepoint-custom-blocks' ) . '">';
	$html .= '<span class="onepoint-client-stories__play-icon" aria-hidden="true"></span>';
	$html .= '<span class="onepoint-client-stories__pause-icon" aria-hidden="true"></span>';
	$html .= '</button>';
	$html .= '<button type="button" class="onepoint-client-stories__arrow onepoint-client-stories__arrow--next" aria-label="' . esc_attr__( 'Next slide', 'onepoint-custom-blocks' ) . '"></button>';
	$html .= '</div></div>';

	return $html;
}

/**
 * Render the Purpose Cards Carousel block (frontend).
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function onepoint_render_purpose_cards_carousel( $attributes ) {
	$section_label      = isset( $attributes['sectionLabel'] ) ? wp_kses_post( $attributes['sectionLabel'] ) : 'Purpose beyond profit';
	$section_heading    = isset( $attributes['sectionHeading'] ) ? wp_kses_post( $attributes['sectionHeading'] ) : 'Always doing right by every stakeholder';
	$section_description = isset( $attributes['sectionDescription'] ) ? wp_kses_post( $attributes['sectionDescription'] ) : '';
	$cta_text           = isset( $attributes['ctaText'] ) ? wp_kses_post( $attributes['ctaText'] ) : 'Learn more about Purpose beyond profit';
	$cta_url            = isset( $attributes['ctaUrl'] ) ? esc_url( $attributes['ctaUrl'] ) : '';
	$items              = isset( $attributes['items'] ) && is_array( $attributes['items'] ) ? $attributes['items'] : array();

	$default_item = array(
		'imageUrl'    => '',
		'imageAlt'    => '',
		'brand'       => 'ONEPOINT',
		'title'       => 'AppShip',
		'heading'     => "Investing in young people's futures",
		'description' => '',
		'accentColor' => '#00D3BA',
	);

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
		$html .= '<a href="' . ( $cta_url ?: '#' ) . '" class="onepoint-purpose-cards__cta">' . $cta_text . '</a>';
		$html .= '</div>';
	}
	$html .= '</div>';

	return $html;
}

/**
 * Render the Footer block (frontend). Uses theme .site-footer styles.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
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

	return $html;
}

