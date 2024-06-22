<?php

/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Integlight
 */
?>

<?php
if (is_active_sidebar('sidebar-1')) {
	if (get_theme_mod('integlight_sidebar1_position') !== 'none') {
?>
		<aside id="secondary" class="widget-area <?php echo get_theme_mod('integlight_sidebar1_position'); ?>">
			<?php dynamic_sidebar('sidebar-1'); ?>
		</aside><!-- #secondary -->
<?php
	}
}
?>

<?php
if (is_active_sidebar('sidebar-2')) {
	if (get_theme_mod('integlight_sidebar2_position') !== 'none') {
?>
		<aside id="third" class="widget-area <?php echo get_theme_mod('integlight_sidebar2_position'); ?>">
			<?php dynamic_sidebar('sidebar-2'); ?>
		</aside><!-- #secondary -->
<?php
	}
}
?>