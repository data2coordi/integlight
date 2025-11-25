<?php

/**
 * The sidebar containing the main widget area
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Integlight
 */
?>

<?php if (is_active_sidebar('sidebar-4')) : ?>
	<aside id="secondary" class="widget-area ly_site_content_widgetArea_top">
		<?php
		//サイドバー出力
		$cache_sidebar = new Integlight_Cache_Sidebar();
		$cache_sidebar->displaySidebar('sidebar-4', 'sidebar-4', 300);
		?>
	</aside><!-- #secondary -->
<?php endif; ?>


<?php if (is_active_sidebar('sidebar-3')) : ?>
	<aside id="secondary" class="widget-area ly_site_content_widgetArea_right">
		<?php
		//サイドバー出力
		$cache_sidebar = new Integlight_Cache_Sidebar();
		$cache_sidebar->displaySidebar('sidebar-3', 'sidebar-3', 300);
		?>
	</aside><!-- #secondary -->
<?php endif; ?>