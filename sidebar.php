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
if (is_page()) {
	return;
}



if (is_active_sidebar('sidebar-1')) {
	if (get_theme_mod('integlight_sidebar1_position', 'right') !== 'none') {
?>
		<aside id="secondary" class="widget-area <?php echo esc_attr("ly_site_content_widgetArea_" . get_theme_mod('integlight_sidebar1_position', 'right')); ?>">


			<?php

			//サイドバー出力
			$cache_sidebar = new Integlight_Cache_Sidebar();
			$cache_sidebar->displaySidebar('sidebar-1', 'sidebar-1', 300);

			?>
		</aside><!-- #secondary -->
<?php
	}
}
?>

<?php
if (is_active_sidebar('sidebar-2')) {
	if (get_theme_mod('integlight_sidebar2_position', 'none') !== 'none') {
?>
		<aside id="third" class="widget-area <?php echo esc_attr("ly_site_content_widgetArea_" . get_theme_mod('integlight_sidebar2_position', 'none'));  ?>">


			<?php
			//サイドバー出力
			$cache_sidebar = new Integlight_Cache_Sidebar();
			$cache_sidebar->displaySidebar('sidebar-2', 'sidebar-2', 300);

			?>

		</aside><!-- #secondary -->
<?php
	}
}
?>