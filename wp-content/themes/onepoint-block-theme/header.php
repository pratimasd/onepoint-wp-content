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
// Header is provided by the plugin (onepoint/header block). Template uses the block.
echo do_blocks( '<!-- wp:onepoint/header /-->' );
?>

<main id="content" class="site-content">
