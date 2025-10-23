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










$slides = Integlight_customizer_slider_display_sliderContent::getImages();
$texts  = Integlight_customizer_slider_display_sliderContent::getTexts();


if (empty(array_filter($slides))) {
	return;
}
?>

<div class="slider">
	<div class="slides">
		<?php if (!empty($slides[0])) : ?>
			<div class="slide"><?= $slides[0] ?></div>
		<?php endif; ?>

		<?php if (!empty($slides[1])) : ?>
			<div class="slide"><?= $slides[1] ?></div>
		<?php endif; ?>

		<?php if (!empty($slides[2])) : ?>
			<div class="slide"><?= $slides[2] ?></div>
		<?php endif; ?>
	</div>

	<div class="text-overlay">
		<div class="text-overlay1">
			<h1><?= $texts[0] ?></h1>
		</div>
		<div class="text-overlay2">
			<h2><?= $texts[1] ?></h2>
		</div>
	</div>
</div>