<?php
defined('ABSPATH') || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php
// Header block from plugin – edit via Appearance → Editor → Template parts → Header, or in any page where the block is added.
echo onepoint_render_template_part('header');
?>

<main id="content" class="site-content">
