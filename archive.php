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

<main id="primary" class="site-main">

	<?php
	// パンくず_s //////////////////
	integlight_breadcrumb();
	// パンくず_e //////////////////
	?>




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
				'prev_next'     => true, // 「前へ」「次へ」のリンクを表示する場合はtrue
				'prev_text'     => '<i class="fa-regular fa-circle-left"></i>prev', // 「前へ」リンクのテキスト
				'next_text'     => 'next<i class="fa-regular fa-circle-right"></i>', // 「次へ」リンクのテキスト
				'type'          => 'plain', // 戻り値の指定 (plain/list)
			)
		);



	else :

		get_template_part('template-parts/content', 'none');

	endif;
	?>

</main><!-- #main -->

<?php
get_sidebar();
get_footer();
