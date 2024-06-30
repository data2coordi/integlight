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
$slider_image_1 = get_theme_mod('integlight_slider_image_1');
$slider_image_2 = get_theme_mod('integlight_slider_image_2');
$slider_image_3 = get_theme_mod('integlight_slider_image_3');
$slider_effect = get_theme_mod('integlight_effect');
if (($slider_effect != 'none') && (!empty($slider_image_1) || !empty($slider_image_2) || !empty($slider_image_3))) :
?>
	<div class="slider">
		<div class="slides">

			<!-- slide 1 -->
			<?php if (!empty($slider_image_1)) : ?>
				<div class="slide">
					<img src="<?php echo esc_url($slider_image_1); ?>" alt="Slide 1">
				</div>
			<?php endif; ?>

			<!-- slide 2 -->
			<?php if (!empty($slider_image_2)) : ?>
				<div class="slide">
					<img src="<?php echo esc_url($slider_image_2); ?>" alt="Slide 2">
				</div>
			<?php endif; ?>

			<!-- slide 3 -->
			<?php if (!empty($slider_image_3)) : ?>
				<div class="slide">
					<img src="<?php echo esc_url($slider_image_3); ?>" alt="Slide 3">
				</div>
			<?php endif; ?>

		</div>
		<div class="text-overlay">
			<p><?php echo nl2br(wp_kses_post(get_theme_mod('integlight_slider_text_1', ''))); ?></p>
		</div>
	</div>
<?php
endif;
?>
<!-- slide bar _e //////////////////////////////// -->