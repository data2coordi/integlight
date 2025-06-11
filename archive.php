<?php

/**
 * The template for displaying archive pages
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Integlight
 */

get_header();
?>

<div class="ly_site_content">
	<main id="primary" class="site-main  ly_site_content_main">


		<?php do_action('after_header'); ?>


		<?php if (have_posts()) : ?>

			<header class="page-header">
				<?php
				the_archive_title('<h1 class="page-title">', '</h1>');
				the_archive_description('<div class="archive-description">', '</div>');
				?>
			</header><!-- .page-header -->

		<?php

			/* Start the Loop */
			while (have_posts()) :
				the_post();

				/*
				 * Include the Post-Type-specific template for the content.
				 * If you want to override this in a child theme, then include a file
				 * called content-___.php (where ___ is the Post Type name) and that will be used instead.
				 */
				//echo 'template-parts/content:' + 'get_post_type';
				get_template_part('template-parts/content', 'arc');

			endwhile;

			the_posts_pagination( //ページャーを出力
				array(
					'mid_size'      => 2, // 現在ページの左右に表示するページ番号の数
					'prev_next'     => true,
					'prev_text'     => '<i class="fa-regular fa-square-caret-left"></i>  ' . esc_html__('prev', 'integlight'),
					'next_text'     => esc_html__('next', 'integlight') . '  <i class="fa-regular fa-square-caret-right"></i>',
					'type'          => 'plain', // 戻り値の指定 (plain/list)
				)
			);

		else :

			get_template_part('template-parts/content', 'none');

		endif;
		?>

	</main><!-- #main -->


	<?php get_sidebar(); ?>
</div> <!-- site-content-->
<?php get_footer(); ?>