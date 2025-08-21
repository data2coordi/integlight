<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Integlight
 */

?>

<?php
if (!function_exists('integlight_custom_fallback_menu_simple')) {

	function integlight_custom_fallback_menu_simple()
	{
		$pages = get_pages();

		if (empty($pages)) {
			return;
		}

		// ここで必ずメニューのラッパーを出す（メニューがある場合と同じ構造に）
		echo '<div class="menuToggle-containerForMenu">';
		echo '<ul id="primary-menu" class="menu">';

		foreach ($pages as $page) {
			$classes = ['menu-item', 'page-item-' . $page->ID];
			if (is_page($page->ID)) {
				$classes[] = 'current-menu-item';
			}
			echo '<li class="' . esc_attr(implode(' ', $classes)) . '">';
			echo '<a href="' . esc_url(get_permalink($page->ID)) . '">' . esc_html($page->post_title) . '</a>';
			echo '</li>';
		}

		echo '</ul>';
		echo '</div>';
	}
}
?>





<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">




	<?php wp_head(); ?>
</head>
<?php
$frontPage = '';
if (!is_home() && is_front_page()) {
	$frontPage = 'integlight_front_page';
}
?>

<body <?php body_class(array('integlight_pt', $frontPage)); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'integlight'); ?></a>

		<header id="masthead" class="site-header ly_site_header">
			<div class="site-branding">
				<?php
				the_custom_logo();
				if (is_front_page() && is_home()) :
				?>
					<h1 class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></h1>
				<?php
				else :
				?>
					<p class="site-title"><a href="<?php echo esc_url(home_url('/')); ?>" rel="home"><?php bloginfo('name'); ?></a></p>
				<?php
				endif;
				$integlight_description = get_bloginfo('description', 'display');
				if ($integlight_description || is_customize_preview()) :
				?>
					<p class="site-description"><?php echo $integlight_description; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
												?></p>
				<?php endif; ?>
			</div><!-- .site-branding -->

			<nav id="site-navigation" class="main-navigation">

				<input type="checkbox" id="menuToggle-checkbox" class="menuToggle-checkbox" aria-hidden="true" />
				<button id="menuToggle-button" class="menuToggle-label" aria-controls="primary-menu-container" aria-expanded="false">
					<span class="screen-reader-text"><?php esc_html_e('Menu', 'integlight'); ?></span>
					<span></span>
				</button>
				<?php
				// wp_nav_menu(
				// 	array(
				// 		'theme_location' => 'header',
				// 		'menu_id'        => 'header-menu',
				// 		'container_class' => 'menuToggle-containerForMenu',
				// 		'container_id'    => 'primary-menu-container', // ここにIDを追加
				// 		'fallback_cb'     => 'integlight_custom_fallback_menu_simple',
				// 	)
				// );
				// wp_nav_menu()用の引数
				$menu_args = array(
					'theme_location'  => 'header',
					'menu_id'         => 'header-menu',
					'container_class' => 'menuToggle-containerForMenu',
					'container_id'    => 'primary-menu-container',
					'fallback_cb'     => 'integlight_custom_fallback_menu_simple'
				);

				// 共通関数を呼び出し、引数として関数名と引数配列を渡す
				integlight_display_cached_content(
					'wp_nav_menu',
					'wp_nav_menu_main',
					array($menu_args), // wp_nav_menuの引数を配列でラップ
				);

				?>
			</nav><!-- #site-navigation -->
		</header><!-- #masthead -->