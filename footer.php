<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Integlight
 */
?>

<a href="#" id="page-top">
	<i class="fa-solid fa-angle-up"></i>
	<span class="screen-reader-text">To Page Top</span>
</a>

<footer id="colophon" class="site-footer ly_site_footer">

	<?php if (is_active_sidebar('footer-1')) : ?>
		<div id="footer-widget-area" class="footer-widgets-wrapper">
			<?php dynamic_sidebar('footer-1'); ?>
		</div>
	<?php endif; ?>
	<?php
	wp_nav_menu(array(
		'theme_location'  => 'footer',
		'menu_id'      => 'footer-menu',
		'menu_class'      => 'footer-menu',
		'container'       => 'nav',
		'container_class' => 'footer-nav',
		'fallback_cb'     => false, // メニューが設定されていない場合に何も出さない
	));
	?>

	<div class="site-info">

		<?php echo esc_html(get_theme_mod('integlight_footer_copy_right', '')); ?>
		<br>

		<?php if (get_theme_mod('integlight_footer_show_credit', true)) :  ?>

			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: %s: WordPress link. */
					__('Proudly powered by %s', 'integlight'),
					'<a href="' . esc_url('https://wordpress.org/') . '">' . esc_html__('WordPress', 'integlight') . '</a>'
				)
			);
			?>
			<span class="sep"> | </span>
			<?php
			echo wp_kses_post(
				sprintf(
					/* translators: 1: Theme name, 2: Theme author. */
					__('Theme: %1$s by %2$s.', 'integlight'),
					esc_html_x('Integlight', 'Theme name', 'integlight'),
					'<a href="' . esc_url("https://color.toshidayurika.com/") . '">' . esc_html__('Yurika Toshida at Aurora Lab', 'integlight') . '</a>'
				)
			);
			?>
		<?php endif; ?>
	</div><!-- .site-info -->
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>