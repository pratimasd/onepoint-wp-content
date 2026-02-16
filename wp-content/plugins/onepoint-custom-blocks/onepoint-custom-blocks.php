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
		'onepoint/initiative-card'  => 'onepoint_render_initiative_card',
		'onepoint/hero-banner'      => 'onepoint_render_hero_banner',
		'onepoint/header'           => 'onepoint_render_header',
		'onepoint/footer'           => 'onepoint_render_footer',
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
