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

<div class="site-content">
	<main id="primary" class="site-main">

		<?php do_action('after_header'); ?>

		<?php



		while (have_posts()) :
			the_post();

			get_template_part('template-parts/content', get_post_type());

			the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__('', 'integlight') . '<i class="fa-regular fa-circle-left"></i><span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__('', 'integlight') . '<span class="nav-title">%title</span><i class="fa-regular fa-circle-right"></i>',
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