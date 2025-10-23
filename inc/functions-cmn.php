<?php

/**
 * integlight_posted_on
 * 投稿日付を付与
 * @package Integlight
 */
if (! function_exists('integlight_posted_on')) :
	function integlight_posted_on()
	{

		$time_string = '<time class="entry-date published updated" datetime="%1$s">%2$s</time>';
		if (get_the_time('U') !== get_the_modified_time('U')) {

			$time_string = esc_html__('Posted on', 'integlight') . ':<time class="entry-date published" datetime="%1$s">%2$s</time>' . ' ' . esc_html__('Updated on', 'integlight') . ':<time class="updated" datetime="%3$s">%4$s</time>';
		}

		$time_string = sprintf(
			$time_string,
			esc_attr(get_the_date(DATE_W3C)),
			esc_html(get_the_date()),
			esc_attr(get_the_modified_date(DATE_W3C)),
			esc_html(get_the_modified_date())
		);


		/* translators: %s: post date. */

		if (is_single()) {
			// リンクなし
			$posted_on = sprintf(esc_html_x('%s', 'post date', 'integlight'), $time_string);
		} else {
			// 投稿一覧などではリンクをつける
			$posted_on = sprintf(esc_html_x('%s', 'post date', 'integlight'), '<a href="' . esc_url(get_permalink()) . '" rel="bookmark">' . $time_string . '</a>');
		}



		echo '<span class="posted-on">'  . ' <i class="fa-solid fa-calendar-days"></i>' . $posted_on . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped


	}
endif;


/**
 * integlight_posted_by
 * * 投稿作成者を付与
 * @package Integlight
 */
if (! function_exists('integlight_posted_by')) :
	function integlight_posted_by()
	{
		$byline = sprintf(
			/* translators: %s: post author. */
			esc_html_x('by %s', 'post author', 'integlight'),
			'<span class="author vcard"><a class="url fn n" href="' . esc_url(get_author_posts_url(get_the_author_meta('ID'))) . '">' . esc_html(get_the_author()) . '</a></span>'
		);

		echo '<span class="byline"> ' . $byline . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

	}
endif;








/**
 * integlight_entry_footer
 * フッターを付与
 * @package Integlight
 */
if (! function_exists('integlight_entry_footer')) :
	function integlight_entry_footer()
	{
		// Hide category and tag text for pages.
		if ('post' === get_post_type()) {
			/* translators: used between list items, there is a space after the comma */
			$categories_list = get_the_category_list(esc_html__(', ', 'integlight'));
			if ($categories_list) {
				/* translators: 1: list of categories. */
				printf('<span class="cat-links">' . esc_html__('Posted in %1$s', 'integlight') . '</span>', $categories_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}

			/* translators: used between list items, there is a space after the comma */
			$tags_list = get_the_tag_list('', esc_html_x(', ', 'list item separator', 'integlight'));
			if ($tags_list) {
				/* translators: 1: list of tags. */
				printf('<span class="tags-links">' . esc_html__('Tagged %1$s', 'integlight') . '</span>', $tags_list); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}

		if (! is_single() && ! post_password_required() && (comments_open() || get_comments_number())) {
			echo '<span class="comments-link">';
			comments_popup_link(
				sprintf(
					wp_kses(
						/* translators: %s: post title */
						__('Leave a Comment<span class="screen-reader-text"> on %s</span>', 'integlight'),
						array(
							'span' => array(
								'class' => array(),
							),
						)
					),
					wp_kses_post(get_the_title())
				)
			);
			echo '</span>';
		}

		edit_post_link(
			sprintf(
				wp_kses(
					/* translators: %s: Name of current post. Only visible to screen readers */
					__('Edit <span class="screen-reader-text">%s</span>', 'integlight'),
					array(
						'span' => array(
							'class' => array(),
						),
					)
				),
				wp_kses_post(get_the_title())
			),
			'<span class="edit-link">, ',
			'</span>'
		);
	}
endif;



/**
 * 投稿の抜粋を取得する共通関数
 * 抜粋があればそれを使用し、なければ本文から生成
 * HTMLタグやショートコードを除去し、日本語向けに文字幅で切り詰める
 *
 * @param int|null $post_id 投稿ID（省略時はグローバル $post）
 * @param int $length 文字幅の最大長（デフォルト150）
 * @param string $more 省略文字（デフォルト '…'）
 * @return string 安全に整形された抜粋文字列
 */
function integlight_get_trimmed_excerpt($post_id = null, $length = 150, $more = '…')
{
	if (! $post_id) {
		global $post;
		$post_id = $post->ID;
	}

	// 抜粋があれば使用
	if (has_excerpt($post_id)) {
		$excerpt = get_post_field('post_excerpt', $post_id);
	} else {
		// 本文から生成
		$raw     = get_post_field('post_content', $post_id); // フィルタ前の本文
		$excerpt = wp_strip_all_tags(strip_shortcodes($raw)); // ショートコードとHTMLを除去
	}

	// 日本語向けに文字幅で切り詰め
	$excerpt_trimmed = mb_strimwidth($excerpt, 0, $length, $more);

	return $excerpt_trimmed;
}
