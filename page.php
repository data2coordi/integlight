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
get_header();
?>

<?php
// slide 
if (!is_home() && is_front_page()) :
	get_template_part('template-parts/content', 'slide');
endif
?>

<div class="ly_site_content">
	<main id="primary" class="site-main ly_site_content_main">

		<?php do_action('after_header'); ?>

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

	<?php get_sidebar(); ?>
</div> <!-- site-content-->
<?php get_footer(); ?>