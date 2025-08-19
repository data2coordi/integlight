<?php

/**
 * Template part for displaying slide on top page
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Integlight
 */

?>

<!-- slide bar _s //////////////////////////////// -->
<?php

global $Integlight_slider_settings;

$display_choice = get_theme_mod('integlight_display_choice');
if ($display_choice != $Integlight_slider_settings->headerTypeName_slider) return;



if (wp_is_mobile()) {
	$slider_image_1 = get_theme_mod('integlight_slider_image_mobile_1');
	if (empty($slider_image_1)) {
		$slider_image_1 = get_theme_mod('integlight_slider_image_1'); // PCの画像
	}

	$slider_image_2 = get_theme_mod('integlight_slider_image_mobile_2');
	if (empty($slider_image_2)) {
		$slider_image_2 = get_theme_mod('integlight_slider_image_2'); // PCの画像
	}

	$slider_image_3 = get_theme_mod('integlight_slider_image_mobile_3');
	if (empty($slider_image_3)) {
		$slider_image_3 = get_theme_mod('integlight_slider_image_3'); // PCの画像
	}
} else {
	//PF対応!!!
	$slider_image_1 = get_theme_mod('integlight_slider_image_1');
	$slider_image_2 = get_theme_mod('integlight_slider_image_2');
	$slider_image_3 = get_theme_mod('integlight_slider_image_3');
}

if (!empty($slider_image_1) || !empty($slider_image_2) || !empty($slider_image_3)) {

?>
	<div class="slider">
		<div class="slides">

			<!-- slide 1 -->
			<?php if (!empty($slider_image_1)) : ?>
				<div class="slide">

					<?php
					$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(0);
					echo wp_get_attachment_image($slider_image_1, 'full',  false, $attr);
					?>
				</div>
			<?php endif; ?>

			<!-- slide 2 -->
			<?php if (!empty($slider_image_2)) : ?>
				<div class="slide">
					<?php
					$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(1);
					echo wp_get_attachment_image($slider_image_2, 'full', false, $attr); ?>

				</div>
			<?php endif; ?>

			<!-- slide 3 -->
			<?php if (!empty($slider_image_3)) : ?>
				<div class="slide">

					<?php
					$attr = Integlight_getAttr_byImageCount::getHeaderImageAttr(2);
					echo wp_get_attachment_image($slider_image_3, 'full', false, $attr); ?>
				</div>
			<?php endif; ?>

		</div>
		<div class="text-overlay">
			<div class="text-overlay1">
				<h1><?php echo nl2br(wp_kses_post(get_theme_mod('integlight_slider_text_1', ''))); ?></h1>
			</div>
			<div class="text-overlay2">
				<h2><?php echo nl2br(wp_kses_post(get_theme_mod('integlight_slider_text_2', ''))); ?></h2>
			</div>
		</div>
	</div>
<?php
};
?>
<!-- slide bar _e //////////////////////////////// -->