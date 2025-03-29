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
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<!-- OGP Meta Tags s -->
	<!-- OGP Meta Tags -->
	<meta property="og:title" content="<?php echo esc_attr(get_the_title()); ?>" />
	<meta property="og:description" content="<?php
												// 抜粋があればそれを使用、なければ本文から最初の100文字を取得
												$excerpt = get_the_excerpt();
												if (empty($excerpt)) {
													$content = get_the_content();
													// 100文字に制限
													$excerpt = wp_trim_words(strip_tags($content), 25, '...'); // ここで100文字程度の制限をかける
												}
												echo esc_attr($excerpt);
												?>" />
	<meta property="og:type" content="website" />
	<meta property="og:url" content="<?php echo esc_url(get_permalink()); ?>" />
	<meta property="og:image" content="<?php
										if (has_post_thumbnail()) {
											echo esc_url(get_the_post_thumbnail_url(null, 'full'));
										} else {
										}
										?>" />

	<meta property="og:site_name" content="<?php echo esc_attr(get_bloginfo('name')); ?>" />
	<meta property="og:locale" content="<?php echo esc_attr(get_locale()); ?>" />
	<!-- OGP Meta Tags e -->


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

				<input type="checkbox" id="menuToggle-checkbox" class="menuToggle-checkbox" />
				<label for="menuToggle-checkbox" class="menuToggle-label"><span></span></label>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'menu-1',
						'menu_id'        => 'primary-menu',
						'menu_class'     => 'menu',
						'container_class'		=> 'menuToggle-containerForMenu',
					)
				);
				?>
			</nav><!-- #site-navigation -->
		</header><!-- #masthead -->