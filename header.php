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

	<?php

	if (is_singular()) {
		global $post;

		// カスタムフィールドから値を取得
		$custom_title       = get_post_meta($post->ID, '_custom_meta_title', true);
		$custom_description = get_post_meta($post->ID, '_custom_meta_description', true);

		// Meta Title の処理：入力があればその値、なければ投稿タイトル + サイトタイトル
		if ($custom_title) {
			$meta_title = $custom_title;
		} else {
			$meta_title = get_the_title($post->ID) . ' | ' . get_bloginfo('name');
		}

		// Meta Description の処理：入力があればその値、なければ抜粋または本文から抽出
		if ($custom_description) {
			$meta_description = $custom_description;
		} else {
			if (has_excerpt($post->ID)) {
				$meta_description = get_the_excerpt($post->ID);
			} else {
				// 投稿本文からHTMLタグを除去し、先頭155文字を抽出
				$content = strip_tags($post->post_content);
				$meta_description = mb_substr($content, 0, 155, 'UTF-8');
			}
		}
	} else {
		// シングルページ以外（アーカイブページなど）の場合は、サイト情報を利用
		$meta_title = get_bloginfo('name');
		$meta_description = get_bloginfo('description');
	}
	?>
	<!-- meta タグの出力 -->
	<title><?php echo esc_html($meta_title); ?></title>
	<meta name="description" content="<?php echo esc_attr($meta_description); ?>">



	<?php wp_head(); ?>
</head>
<?php
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