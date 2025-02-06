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
	<figure class="bl_card_img">
		<a href="<?php the_permalink(); ?>" class="bl_card_link">
			<?php
			$categories = get_the_category();
			if (!empty($categories)) {
				$last_category = end($categories);
				echo '<span class="category-label">' . esc_html($last_category->name) . '</span>';
			}
			?>
			<img src="<?php the_post_thumbnail_url(); ?>" alt="">
		</a>
	</figure>

	<!-- 右側のカラム：本文 -->
	<div class="bl_card_head">

		<a href="<?php the_permalink(); ?>">
			<?php the_title('<h5 class="bl_card_ttl">', '</h5>'); ?>
		</a>
		<span class="entry-date"><i class="fa-solid fa-calendar-days"></i><?php echo get_the_date(); ?></span>
	</div>
	<div class="bl_card_body">
		<p class="bl_card_txt"><?php echo get_the_excerpt(); ?></p>
	</div>
</div>