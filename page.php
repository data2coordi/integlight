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
<?php if (!is_home() && is_front_page()) : ?>

	<div class="slider">
		<div class="slide"><img src="<?php echo get_template_directory_uri(); ?>/img/concept_img.jpg" alt="Slide 1"></div>
		<div class="slide"><img src="<?php echo get_template_directory_uri(); ?>/img/headder_img.jpg" alt="Slide 2"></div>
	</div>
<?php endif; ?>
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
