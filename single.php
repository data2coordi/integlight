<?php

/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Integlight
 */

get_header();

?>

<div class="ly_site_content">
	<main id="primary" class="site-main ly_site_content_main">

		<?php do_action('after_header'); ?>

		<?php



		while (have_posts()) :
			the_post();

			get_template_part('template-parts/content', get_post_type());

			the_post_navigation(
				array(
					'prev_text' => '<i class="fa-regular fa-square-caret-left"></i> %title',
					'next_text' => '%title <i class="fa-regular fa-square-caret-right"></i>',
				)
			);

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