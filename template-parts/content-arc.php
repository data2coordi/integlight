<?php

/**
 * Template part for displaying posts
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Integlight
 */
?>

<div class="bl_card_container">
	<!-- 左側のカラム：画像 -->
	<div class="bl_card_column">
		<a href="<?php the_permalink(); ?>" class="bl_card_link">
			<figure class="bl_card_imgWrapper">
				<img src="<?php the_post_thumbnail_url(); ?>" alt="">
				<?php
				$categories = get_the_category();
				if (!empty($categories)) {
					$last_category = end($categories);
					echo '<span class="category-label">' . esc_html($last_category->name) . '</span>';
				}
				?>
			</figure>
		</a>
	</div>

	<!-- 右側のカラム：本文 -->
	<div class="bl_card_column">
		<article class="bl_card">
			<?php the_title('<h3 class="bl_card_ttl">', '</h3>'); ?>
			<div class="bl_card_body">
				<span class="entry-date"><i class="fa-solid fa-calendar-days"></i><?php echo get_the_date(); ?></span>
				<p class="bl_card_txt"><?php echo get_the_excerpt(); ?></p>
				<a href="<?php the_permalink(); ?>"><i class="fa-solid fa-up-right-from-square"></i> continue</a>
			</div>
		</article>
	</div>
</div>