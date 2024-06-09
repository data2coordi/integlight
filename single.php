<?php

/**
 * The template for displaying all single posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#single-post
 *
 * @package Integlight
 */

var_dump('single.php');
get_header();

?>

<main id="primary" class="site-main">


	<?php

	// 目次作成_s /////////////////////
	//$content = get_post_field('post_content', get_the_ID());
	//$contentsTable = new InteglightTableOfContents();
	//$content = $contentsTable.add_toc_to_content($content); 
	//var_dump($content);
	// 目次作成_e ///////////////////


	// パンくず_s //////////////////
	integlight_breadcrumb();
	// パンくず_e //////////////////

	while (have_posts()) :
		the_post();

		get_template_part('template-parts/content', get_post_type());

		the_post_navigation(
			array(
				'prev_text' => '<span class="nav-subtitle">' . esc_html__('Previous:', 'integlight') . '</span> <span class="nav-title">%title</span>',
				'next_text' => '<span class="nav-subtitle">' . esc_html__('Next:', 'integlight') . '</span> <span class="nav-title">%title</span>',
			)
		);

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
