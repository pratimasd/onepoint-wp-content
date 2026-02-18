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
<<<<<<< HEAD
		'primary'      => __('Primary Menu', 'onepoint-block-theme'),
		'footer-col-1' => __('Footer Column 1', 'onepoint-block-theme'),
		'footer-col-2' => __('Footer Column 2', 'onepoint-block-theme'),
		'footer-col-3' => __('Footer Column 3', 'onepoint-block-theme'),
		'footer-col-4' => __('Footer Column 4', 'onepoint-block-theme'),
=======
		'primary' => __('Primary Menu', 'onepoint-block-theme'),
		'footer_what_we_do' => __('Footer: What we do', 'onepoint-block-theme'),
		'footer_resources'  => __('Footer: Resources', 'onepoint-block-theme'),
		'footer_about'      => __('Footer: About us', 'onepoint-block-theme'),
		'footer_more_info'  => __('Footer: More info', 'onepoint-block-theme'),
>>>>>>> 6144bd12d1c6612064f8d78635778521282760d7
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
 * Render a block template part (header or footer). Uses customized content from DB if edited in Site Editor, else theme file.
 */
function onepoint_render_template_part($slug) {
	if ( ! in_array($slug, array('header', 'footer'), true) ) {
		return '';
	}
	$content = '';
	if (function_exists('get_block_template')) {
		$template = get_block_template(get_stylesheet() . '//' . $slug, 'wp_template_part');
		if ($template && ! is_wp_error($template) && ! empty($template->content)) {
			$content = $template->content;
		}
	}
	if ($content === '') {
		$path = get_theme_file_path("parts/{$slug}.html");
		if (file_exists($path)) {
			$content = file_get_contents($path);
		}
	}
	if ($content !== '') {
		return do_blocks($content);
	}
	if ($slug === 'header') {
		return do_blocks('<!-- wp:onepoint/header /-->');
	}
	if ($slug === 'footer') {
		return do_blocks('<!-- wp:onepoint/footer /-->');
	}
	return '';
}

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
/**
 * Fallback menu for footer "What we do" column.
 */
function onepoint_footer_what_we_do_fallback() {
	$items = array(
		array('label' => __('Architect for outcomes', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Do data better', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Innovate with AI & more', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Springboard™ Workshop', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Onepoint Labs', 'onepoint-block-theme'), 'url' => home_url('/')),
	);
	return $items;
}

/**
 * Fallback menu for footer "Resources" column.
 */
function onepoint_footer_resources_fallback() {
	return array(
		array('label' => __('Onepoint Data Wellness™ Suite', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Onepoint Res-AI™', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Onepoint TechTalk', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Onepoint Oneness', 'onepoint-block-theme'), 'url' => home_url('/')),
	);
}

/**
 * Fallback menu for footer "About us" column.
 */
function onepoint_footer_about_fallback() {
	return array(
		array('label' => __('Discover Onepoint', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Client stories', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Careers', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Contact us', 'onepoint-block-theme'), 'url' => home_url('/')),
	);
}

/**
 * Fallback menu for footer "More info" column.
 */
function onepoint_footer_more_info_fallback() {
	return array(
		array('label' => __('Boomi', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Client stories', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Careers', 'onepoint-block-theme'), 'url' => home_url('/')),
		array('label' => __('Contact us', 'onepoint-block-theme'), 'url' => home_url('/')),
	);
}

/**
 * Output footer column: nav menu or fallback list. Used by footer.php.
 *
 * @param string $location   Menu location.
 * @param string $title      Column heading.
 * @param array  $fallback   Array of [ 'label' => '', 'url' => '' ].
 * @param string $highlight  Optional. Label to add .is-active (e.g. 'Innovate with AI & more').
 */
function onepoint_footer_column($location, $title, $fallback, $highlight = '') {
	echo '<div class="footer-col">';
	echo '<h3 class="footer-col__title">' . esc_html($title) . '</h3>';
	if (has_nav_menu($location)) {
		wp_nav_menu(array(
			'theme_location' => $location,
			'container'      => false,
			'menu_class'     => 'footer-col__list',
			'fallback_cb'    => false,
		));
	} else {
		echo '<ul class="footer-col__list">';
		foreach ($fallback as $item) {
			$class = ($highlight && $item['label'] === $highlight) ? ' is-active' : '';
			echo '<li><a href="' . esc_url($item['url']) . '" class="' . esc_attr($class) . '">' . esc_html($item['label']) . '</a></li>';
		}
		echo '</ul>';
	}
	echo '</div>';
}

/**
 * URL for custom header icon (theme assets folder). Returns empty if no icon file present.
 * Used by header.php to decide whether to show custom icon or fallback flask SVG.
 * Checked: g629.png, header-icon.png, header-icon.svg
 */
/**
 * URL for default header logo (theme assets folder). Used when no custom logo is set.
 * Checked: onepoint-logo.png, onepoint-logo.svg
 */
function onepoint_header_logo_url() {
	$dir = get_template_directory();
	$uri = get_template_directory_uri();
	$candidates = array('onepoint-logo.png', 'onepoint-logo.svg');
	foreach ($candidates as $file) {
		if (file_exists($dir . '/assets/' . $file)) {
			return $uri . '/assets/' . $file;
		}
	}
	return '';
}

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

/**
 * Ensure Header and Footer sections are visible in the Customizer (even when theme is treated as block theme).
 */
function onepoint_customize_section_active($active, $section) {
	if ( ! is_object($section) || ! isset($section->id) ) {
		return $active;
	}
	if (in_array($section->id, array('onepoint_header', 'onepoint_footer'), true)) {
		return true;
	}
	return $active;
}
add_filter('customize_section_active', 'onepoint_customize_section_active', 10, 2);

/**
 * Header Customizer – site name override and icon URL for the header block.
 */
function onepoint_header_customize_register($wp_customize) {
	$wp_customize->add_section('onepoint_header', array(
		'title'    => __('Header', 'onepoint-block-theme'),
		'priority' => 45,
	));
	$wp_customize->add_setting('header_site_name', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('header_site_name', array(
		'label'       => __('Site name (header)', 'onepoint-block-theme'),
		'description' => __('Leave blank to use the site title from Settings → General.', 'onepoint-block-theme'),
		'section'     => 'onepoint_header',
		'type'        => 'text',
	));
	$wp_customize->add_setting('header_icon_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control('header_icon_url', array(
		'label'       => __('Header icon URL', 'onepoint-block-theme'),
		'description' => __('Optional. URL of the small icon shown in the header (replaces default).', 'onepoint-block-theme'),
		'section'     => 'onepoint_header',
		'type'        => 'url',
	));
}
add_action('customize_register', 'onepoint_header_customize_register');

/**
 * Footer Customizer – makes all footer labels, links and copyright editable.
 */
function onepoint_footer_customize_register($wp_customize) {
	$wp_customize->add_section('onepoint_footer', array(
		'title'    => __('Footer', 'onepoint-block-theme'),
		'priority' => 50,
	));

	// Column titles
	$cols = array(
		'footer_col1_title' => array('label' => __('Column 1 title (What we do)', 'onepoint-block-theme'), 'default' => __('What we do', 'onepoint-block-theme')),
		'footer_col2_title' => array('label' => __('Column 2 title (Resources)', 'onepoint-block-theme'), 'default' => __('Resources', 'onepoint-block-theme')),
		'footer_col3_title' => array('label' => __('Column 3 title (About us)', 'onepoint-block-theme'), 'default' => __('About us', 'onepoint-block-theme')),
		'footer_col4_title' => array('label' => __('Column 4 title (More info)', 'onepoint-block-theme'), 'default' => __('More info', 'onepoint-block-theme')),
	);
	foreach ($cols as $id => $args) {
		$wp_customize->add_setting($id, array(
			'default'           => $args['default'],
			'sanitize_callback' => 'sanitize_text_field',
		));
		$wp_customize->add_control($id, array(
			'label'   => $args['label'],
			'section' => 'onepoint_footer',
			'type'    => 'text',
		));
	}

	// Toggle button labels
	$wp_customize->add_setting('footer_btn_collapse', array(
		'default'           => __('Hide full footer', 'onepoint-block-theme'),
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('footer_btn_collapse', array(
		'label'   => __('Button label (when footer is visible)', 'onepoint-block-theme'),
		'section' => 'onepoint_footer',
		'type'    => 'text',
	));
	$wp_customize->add_setting('footer_btn_expand', array(
		'default'           => __('Show full footer', 'onepoint-block-theme'),
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('footer_btn_expand', array(
		'label'   => __('Button label (when footer is collapsed)', 'onepoint-block-theme'),
		'section' => 'onepoint_footer',
		'type'    => 'text',
	));

	// Policies
	$wp_customize->add_setting('footer_policies_label', array(
		'default'           => __('Policies', 'onepoint-block-theme'),
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('footer_policies_label', array(
		'label'   => __('Policies section label', 'onepoint-block-theme'),
		'section' => 'onepoint_footer',
		'type'    => 'text',
	));
	$wp_customize->add_setting('footer_terms_label', array(
		'default'           => __('Terms and conditions', 'onepoint-block-theme'),
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('footer_terms_label', array(
		'label'   => __('Terms link text', 'onepoint-block-theme'),
		'section' => 'onepoint_footer',
		'type'    => 'text',
	));
	$wp_customize->add_setting('footer_terms_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control('footer_terms_url', array(
		'label'       => __('Terms URL', 'onepoint-block-theme'),
		'description' => __('Leave blank for home/terms', 'onepoint-block-theme'),
		'section'     => 'onepoint_footer',
		'type'        => 'url',
	));
	$wp_customize->add_setting('footer_cookies_label', array(
		'default'           => __('Cookies', 'onepoint-block-theme'),
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('footer_cookies_label', array(
		'label'   => __('Cookies link text', 'onepoint-block-theme'),
		'section' => 'onepoint_footer',
		'type'    => 'text',
	));
	$wp_customize->add_setting('footer_cookies_url', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control('footer_cookies_url', array(
		'label'       => __('Cookies URL', 'onepoint-block-theme'),
		'description' => __('Leave blank for home/cookies', 'onepoint-block-theme'),
		'section'     => 'onepoint_footer',
		'type'        => 'url',
	));

	// Copyright
	$wp_customize->add_setting('footer_copyright', array(
		'default'           => __('Onepoint Consulting Ltd', 'onepoint-block-theme'),
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('footer_copyright', array(
		'label'       => __('Copyright line (company name)', 'onepoint-block-theme'),
		'description' => __('Shown as: © [year] [this text]', 'onepoint-block-theme'),
		'section'     => 'onepoint_footer',
		'type'        => 'text',
	));
}
add_action('customize_register', 'onepoint_footer_customize_register');

/**
 * Add "Header & Footer" under Appearance so content editors have one place to go.
 */
function onepoint_add_header_footer_menu() {
	$hook = add_submenu_page(
		'themes.php',
		__('Header & Footer', 'onepoint-block-theme'),
		__('Header & Footer', 'onepoint-block-theme'),
		'edit_theme_options',
		'onepoint-header-footer',
		'onepoint_header_footer_admin_page'
	);
	if ($hook) {
		add_action('load-' . $hook, 'onepoint_header_footer_enqueue_media');
	}
}
add_action('admin_menu', 'onepoint_add_header_footer_menu', 20);

/**
 * Enqueue media uploader on Header & Footer page.
 */
function onepoint_header_footer_enqueue_media() {
	wp_enqueue_media();
}

/**
 * Admin page: edit Header and Footer with form fields (saves to same theme_mod as Customizer).
 */
function onepoint_header_footer_admin_page() {
	if (isset($_POST['onepoint_header_footer_nonce']) && wp_verify_nonce($_POST['onepoint_header_footer_nonce'], 'onepoint_header_footer_save')) {
		$keys = array(
			'header_logo_url', 'header_site_name', 'header_icon_url',
			'footer_col1_title', 'footer_col2_title', 'footer_col3_title', 'footer_col4_title',
			'footer_btn_collapse', 'footer_btn_expand',
			'footer_policies_label', 'footer_terms_label', 'footer_terms_url', 'footer_cookies_label', 'footer_cookies_url',
			'footer_copyright',
		);
		foreach ($keys as $key) {
			if (array_key_exists($key, $_POST)) {
				$val = wp_unslash($_POST[$key]);
				if (strpos($key, '_url') !== false) {
					set_theme_mod($key, esc_url_raw($val));
				} else {
					set_theme_mod($key, sanitize_text_field($val));
				}
			}
		}
		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Header & Footer saved.', 'onepoint-block-theme') . '</p></div>';
	}

	$h_logo   = get_theme_mod('header_logo_url', '');
	$h_name   = get_theme_mod('header_site_name', '');
	$h_icon   = get_theme_mod('header_icon_url', '');
	$f_col1   = get_theme_mod('footer_col1_title', __('What we do', 'onepoint-block-theme'));
	$f_col2   = get_theme_mod('footer_col2_title', __('Resources', 'onepoint-block-theme'));
	$f_col3   = get_theme_mod('footer_col3_title', __('About us', 'onepoint-block-theme'));
	$f_col4   = get_theme_mod('footer_col4_title', __('More info', 'onepoint-block-theme'));
	$f_btn_c  = get_theme_mod('footer_btn_collapse', __('Hide full footer', 'onepoint-block-theme'));
	$f_btn_e  = get_theme_mod('footer_btn_expand', __('Show full footer', 'onepoint-block-theme'));
	$f_pol    = get_theme_mod('footer_policies_label', __('Policies', 'onepoint-block-theme'));
	$f_terms  = get_theme_mod('footer_terms_label', __('Terms and conditions', 'onepoint-block-theme'));
	$f_terms_u = get_theme_mod('footer_terms_url', '');
	$f_cook   = get_theme_mod('footer_cookies_label', __('Cookies', 'onepoint-block-theme'));
	$f_cook_u = get_theme_mod('footer_cookies_url', '');
	$f_copy   = get_theme_mod('footer_copyright', __('Onepoint Consulting Ltd', 'onepoint-block-theme'));
	?>
	<div class="wrap">
		<h1><?php esc_html_e('Header & Footer', 'onepoint-block-theme'); ?></h1>
		<p><?php esc_html_e('Upload the header logo and icon, edit footer text, then save. Menus (main nav and footer columns) are managed via the link below.', 'onepoint-block-theme'); ?></p>

		<form method="post" action="" id="onepoint-header-footer-form">
			<?php wp_nonce_field('onepoint_header_footer_save', 'onepoint_header_footer_nonce'); ?>

			<table class="form-table" role="presentation">
				<tr><th colspan="2"><h2><?php esc_html_e('Header', 'onepoint-block-theme'); ?></h2></th></tr>
				<tr>
					<th scope="row"><?php esc_html_e('Header logo', 'onepoint-block-theme'); ?></th>
					<td>
						<input name="header_logo_url" id="header_logo_url" type="hidden" value="<?php echo esc_attr($h_logo); ?>" />
						<div id="header_logo_preview" style="margin-bottom:8px;min-height:40px;"><?php if ($h_logo) : ?><img src="<?php echo esc_url($h_logo); ?>" alt="" style="max-height:48px;width:auto;" /><?php endif; ?></div>
						<button type="button" class="button" id="header_logo_upload"><?php echo $h_logo ? esc_html__('Change logo', 'onepoint-block-theme') : esc_html__('Upload logo', 'onepoint-block-theme'); ?></button>
						<?php if ($h_logo) : ?><button type="button" class="button" id="header_logo_remove"><?php esc_html_e('Remove logo', 'onepoint-block-theme'); ?></button><?php endif; ?>
						<br><span class="description"><?php esc_html_e('Logo shown in the header. Used if no logo is set under Appearance → Customize → Site Identity.', 'onepoint-block-theme'); ?></span>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Site name (fallback)', 'onepoint-block-theme'); ?></th>
					<td><input name="header_site_name" id="header_site_name" type="text" value="<?php echo esc_attr($h_name); ?>" class="regular-text" /><br><span class="description"><?php esc_html_e('Shown only when no logo is set. Leave blank to use Settings → General → Site title.', 'onepoint-block-theme'); ?></span></td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e('Header icon', 'onepoint-block-theme'); ?></th>
					<td>
						<input name="header_icon_url" id="header_icon_url" type="hidden" value="<?php echo esc_attr($h_icon); ?>" />
						<div id="header_icon_preview" style="margin-bottom:8px;min-height:24px;"><?php if ($h_icon) : ?><img src="<?php echo esc_url($h_icon); ?>" alt="" style="max-height:24px;width:auto;" /><?php endif; ?></div>
						<button type="button" class="button" id="header_icon_upload"><?php echo $h_icon ? esc_html__('Change icon', 'onepoint-block-theme') : esc_html__('Upload icon', 'onepoint-block-theme'); ?></button>
						<?php if ($h_icon) : ?><button type="button" class="button" id="header_icon_remove"><?php esc_html_e('Remove icon', 'onepoint-block-theme'); ?></button><?php endif; ?>
						<br><span class="description"><?php esc_html_e('Small icon repeated three times on the right of the header. Recommended: 24×24 px.', 'onepoint-block-theme'); ?></span>
					</td>
				</tr>

				<tr><th colspan="2"><h2><?php esc_html_e('Footer – Column titles', 'onepoint-block-theme'); ?></h2></th></tr>
				<tr><th scope="row"><label for="footer_col1_title"><?php esc_html_e('Column 1', 'onepoint-block-theme'); ?></label></th><td><input name="footer_col1_title" id="footer_col1_title" type="text" value="<?php echo esc_attr($f_col1); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_col2_title"><?php esc_html_e('Column 2', 'onepoint-block-theme'); ?></label></th><td><input name="footer_col2_title" id="footer_col2_title" type="text" value="<?php echo esc_attr($f_col2); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_col3_title"><?php esc_html_e('Column 3', 'onepoint-block-theme'); ?></label></th><td><input name="footer_col3_title" id="footer_col3_title" type="text" value="<?php echo esc_attr($f_col3); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_col4_title"><?php esc_html_e('Column 4', 'onepoint-block-theme'); ?></label></th><td><input name="footer_col4_title" id="footer_col4_title" type="text" value="<?php echo esc_attr($f_col4); ?>" class="regular-text" /></td></tr>

				<tr><th colspan="2"><h2><?php esc_html_e('Footer – Toggle button', 'onepoint-block-theme'); ?></h2></th></tr>
				<tr><th scope="row"><label for="footer_btn_collapse"><?php esc_html_e('Label (when visible)', 'onepoint-block-theme'); ?></label></th><td><input name="footer_btn_collapse" id="footer_btn_collapse" type="text" value="<?php echo esc_attr($f_btn_c); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_btn_expand"><?php esc_html_e('Label (when collapsed)', 'onepoint-block-theme'); ?></label></th><td><input name="footer_btn_expand" id="footer_btn_expand" type="text" value="<?php echo esc_attr($f_btn_e); ?>" class="regular-text" /></td></tr>

				<tr><th colspan="2"><h2><?php esc_html_e('Footer – Policies & legal', 'onepoint-block-theme'); ?></h2></th></tr>
				<tr><th scope="row"><label for="footer_policies_label"><?php esc_html_e('Policies label', 'onepoint-block-theme'); ?></label></th><td><input name="footer_policies_label" id="footer_policies_label" type="text" value="<?php echo esc_attr($f_pol); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_terms_label"><?php esc_html_e('Terms link text', 'onepoint-block-theme'); ?></label></th><td><input name="footer_terms_label" id="footer_terms_label" type="text" value="<?php echo esc_attr($f_terms); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_terms_url"><?php esc_html_e('Terms URL', 'onepoint-block-theme'); ?></label></th><td><input name="footer_terms_url" id="footer_terms_url" type="url" value="<?php echo esc_attr($f_terms_u); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_cookies_label"><?php esc_html_e('Cookies link text', 'onepoint-block-theme'); ?></label></th><td><input name="footer_cookies_label" id="footer_cookies_label" type="text" value="<?php echo esc_attr($f_cook); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_cookies_url"><?php esc_html_e('Cookies URL', 'onepoint-block-theme'); ?></label></th><td><input name="footer_cookies_url" id="footer_cookies_url" type="url" value="<?php echo esc_attr($f_cook_u); ?>" class="regular-text" /></td></tr>
				<tr><th scope="row"><label for="footer_copyright"><?php esc_html_e('Copyright (company name)', 'onepoint-block-theme'); ?></label></th><td><input name="footer_copyright" id="footer_copyright" type="text" value="<?php echo esc_attr($f_copy); ?>" class="regular-text" /><br><span class="description"><?php esc_html_e('Shown as © [year] [this text].', 'onepoint-block-theme'); ?></span></td></tr>
			</table>

			<p class="submit"><button type="submit" class="button button-primary"><?php esc_html_e('Save Header & Footer', 'onepoint-block-theme'); ?></button></p>
		</form>

		<hr>
		<p><strong><?php esc_html_e('Menus', 'onepoint-block-theme'); ?></strong><br>
			<?php esc_html_e('Main navigation (header) and footer link columns are managed here:', 'onepoint-block-theme'); ?>
			<a href="<?php echo esc_url(admin_url('nav-menus.php')); ?>"><?php esc_html_e('Appearance → Menus', 'onepoint-block-theme'); ?></a>.
			<?php esc_html_e('Assign menus to “Primary Menu” (header) and the four “Footer: …” locations.', 'onepoint-block-theme'); ?>
		</p>
	</div>
	<script>
	(function() {
		function openMedia(fieldId, previewId, uploadBtnId, removeBtnId, maxH, changeTxt, uploadTxt, removeTxt) {
			var input = document.getElementById(fieldId);
			var preview = document.getElementById(previewId);
			var btn = document.getElementById(uploadBtnId);
			if (!window.wp || !window.wp.media) return;
			var frame = window.wp.media({ library: { type: 'image' }, multiple: false });
			frame.on('select', function() {
				var att = frame.state().get('selection').first().toJSON();
				if (att && att.url) {
					input.value = att.url;
					preview.innerHTML = '<img src="' + att.url + '" alt="" style="max-height:' + maxH + 'px;width:auto;" />';
					btn.textContent = changeTxt;
					var rb = document.getElementById(removeBtnId);
					if (!rb) {
						rb = document.createElement('button');
						rb.type = 'button';
						rb.className = 'button';
						rb.id = removeBtnId;
						rb.textContent = removeTxt;
						btn.parentNode.insertBefore(rb, btn.nextSibling);
						rb.onclick = function() {
							input.value = '';
							preview.innerHTML = '';
							btn.textContent = uploadTxt;
							rb.remove();
						};
					}
				}
			});
			frame.open();
		}
		var logoUpload = '<?php echo esc_js(__('Upload logo', 'onepoint-block-theme')); ?>';
		var logoChange = '<?php echo esc_js(__('Change logo', 'onepoint-block-theme')); ?>';
		var logoRemove = '<?php echo esc_js(__('Remove logo', 'onepoint-block-theme')); ?>';
		var iconUpload = '<?php echo esc_js(__('Upload icon', 'onepoint-block-theme')); ?>';
		var iconChange = '<?php echo esc_js(__('Change icon', 'onepoint-block-theme')); ?>';
		var iconRemove = '<?php echo esc_js(__('Remove icon', 'onepoint-block-theme')); ?>';
		document.getElementById('header_logo_upload').onclick = function() { openMedia('header_logo_url', 'header_logo_preview', 'header_logo_upload', 'header_logo_remove', 48, logoChange, logoUpload, logoRemove); };
		document.getElementById('header_icon_upload').onclick = function() { openMedia('header_icon_url', 'header_icon_preview', 'header_icon_upload', 'header_icon_remove', 24, iconChange, iconUpload, iconRemove); };
		var rLogo = document.getElementById('header_logo_remove');
		if (rLogo) rLogo.onclick = function() { document.getElementById('header_logo_url').value = ''; document.getElementById('header_logo_preview').innerHTML = ''; this.remove(); document.getElementById('header_logo_upload').textContent = logoUpload; };
		var rIcon = document.getElementById('header_icon_remove');
		if (rIcon) rIcon.onclick = function() { document.getElementById('header_icon_url').value = ''; document.getElementById('header_icon_preview').innerHTML = ''; this.remove(); document.getElementById('header_icon_upload').textContent = iconUpload; };
	})();
	</script>
	<?php
}
