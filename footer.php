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

<a href="#" id="page-top"><i class="fa-solid fa-angle-up"></i></a>

<footer id="colophon" class="site-footer ly_site_footer">
	<div class="site-info">

		<?php echo "<br>" . get_option('copy_right'); ?>
		<br>
		<a href="<?php echo esc_url('https://wordpress.org/'); ?>">
			<?php
			/* translators: %s: CMS name, i.e. WordPress. */
			printf(esc_html('Proudly powered by %s', 'integlight'), 'WordPress');
			?>
		</a>
		<span class="sep"> | </span>
		<?php
		/* translators: 1: Theme name, 2: Theme author. */
		printf(esc_html('Theme: %1$s by %2$s.'), 'Integlight', '<a href="https://color.toshidayurika.com/">Yurika Toshida at Aurora Lab</a>');
		?>
	</div><!-- .site-info -->
</footer><!-- #colophon -->
</div><!-- #page -->

<?php wp_footer(); ?>

</body>

</html>