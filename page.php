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

<main id="primary" class="site-main">

	<?php
	// パンくず_s //////////////////
	integlight_breadcrumb();
	// パンくず_e //////////////////
	?>

	<?php
	// slide 
	if (!is_home() && is_front_page()) :
		get_template_part('template-parts/content', 'slide');
	endif
	?>



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
