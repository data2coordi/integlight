<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Integlight
 */
?>

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
	<header class="entry-header">
		<?php
		if (is_singular()) :
			the_title('<h1 class="entry-title">', '</h1>');
		else :
			the_title('<h2 class="entry-title"><a href="' . esc_url(get_permalink()) . '" rel="bookmark">', '</a></h2>');
		endif;

		if ('post' === get_post_type()) :
		?>
			<div class="entry-meta">
				<?php
				integlight_posted_on();
				integlight_posted_by();
				?>
			</div><!-- .entry-meta -->
		<?php endif; ?>
	</header><!-- .entry-header -->

	<?php integlight_post_thumbnail(); ?>

	<div class="entry-content">
		<?php

		// 投稿のコンテンツをキャッシュして表示
		global $post;
		$cache_main    = new Integlight_Cache_MainContent();
		$tkey = 'post_content_' . (int) $post->ID; // 必ずIDを含める
		$cache_main->displayPostContent($tkey);






		wp_link_pages(
			array(
				'before' => '<div class="page-links">' . esc_html__('Pages:', 'integlight'),
				'after'  => '</div>',
			)
		);
		?>
	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php integlight_footerEntry(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->