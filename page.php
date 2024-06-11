<?php

/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site may use a
 * different template.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Integlight
 */
echo ("page.php");
get_header();
?>

<!-- lide bar _s //////////////////////////////// -->
<?php if (!is_home() && is_front_page()) :
	$slider_image_1 = get_theme_mod('slider_image_1');
	$slider_image_2 = get_theme_mod('slider_image_2');
	if (!empty($slider_image_1) || !empty($slider_image_2)) :

?>
		<div class="slider">
			<?php if (!empty($slider_image_1)) : ?>
				<div class="slide"><img src="<?php echo esc_url($slider_image_1); ?>" alt="Slide 1"></div>
			<?php endif; ?>
			<?php if (!empty($slider_image_2)) : ?>
				<div class="slide"><img src="<?php echo esc_url($slider_image_2); ?>" alt="Slide 2"></div>
			<?php endif; ?>
		</div>
<?php endif;
endif;
?>
<!-- slide bar _e //////////////////////////////// -->


<main id="primary" class="site-main">

	<?php
	while (have_posts()) :
		the_post();

		get_template_part('template-parts/content', 'page');

		// If comments are open or we have at least one comment, load up the comment template.
		if (comments_open() || get_comments_number()) :
			comments_template();
		endif;

	endwhile; // End of the loop.
	?>

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
