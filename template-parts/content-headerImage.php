<?php

/**
 * Template part for displaying slide on top page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Integlight
 */

?>

<?php



if (!get_header_image()) {

	return;
}

$texts  = Integlight_customizer_headerImage_displayContent::getTexts();

?>

<div class="header-image">
	<?php
	echo '<img src="' . esc_url(get_header_image()) . '" class="topImage" ' .  ' alt="' . esc_attr(get_bloginfo('name')) . '">';
	?>
	<div class="text-overlay">
		<div class="text-overlay1">
			<h1><?php echo wp_kses_post($texts[0]); ?></h1>
		</div>
		<div class="text-overlay2">
			<h2><?php echo wp_kses_post($texts[1]); ?></h2>
		</div>
	</div>
</div>