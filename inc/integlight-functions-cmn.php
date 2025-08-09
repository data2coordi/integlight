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
 * integlight_post_thumbnail
 * サムネイルを付与
 * @package Integlight
 */
if (! function_exists('integlight_post_thumbnail')) :
	function integlight_post_thumbnail()
	{
		if (post_password_required() || is_attachment() || ! has_post_thumbnail()) {
			return;
		}

		if (is_singular()) :
?>

			<div class="post-thumbnail">
				<?php the_post_thumbnail(
					'full',
					[
						'class' => 'responsive-img',
						'loading' => 'eager',
						'decoding' => 'async',
						'sizes' => '(max-width: 480px) 20vw, 800px', //PF対応!!!：20vwとすることで、srcsetで低解像度を選択させる。
						'fetchpriority' => 'high'  // PF対応!!!

					]
				);
				/*
				PF対応!!!の前提：20vwとすることで下記のsrcsetの300w用画像を選択させる。
				sizes="(max-width: 850px) 20vw, 900px"
				srcset="
					https://.../Firefly_882369-300x171.webp 300w,
					https://.../Firefly_882369-768x439.webp 768w,
					https://.../Firefly_882369.webp 900w

				*/

				?>
			</div><!-- .post-thumbnail -->




		<?php else : ?>

			<a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
				<?php
				the_post_thumbnail(
					'post-thumbnail',
					[
						'alt' => the_title_attribute(['echo' => false]),
						'fetchpriority' => 'high', // PF対応!!!
					]
				);
				?>
			</a>

<?php
		endif; // End is_singular().
	}
endif;


/**
 * wp_body_open
 * @package Integlight
 */
if (! function_exists('integlight_wp_body_open')) :
	function integlight_wp_body_open()
	{
		do_action('wp_body_open');
	}
endif;




/********************************************************************/
/* サムネイル取得(存在しなければ、本文の画像、デフォルト画像を取得) s	*/
/********************************************************************/

class Integlight_PostThumbnail
{

	private static function get_thumbnail_url($post_id = null, $size = 'medium', $default_url = '')
	{


		if (is_null($post_id)) {
			$post_id = get_the_ID();
		}

		// アイキャッチ画像がある場合
		if (has_post_thumbnail($post_id)) {
			$thumbnail_url = get_the_post_thumbnail_url($post_id, $size);
			return esc_url($thumbnail_url);
		};

		// 本文から最初の画像を抽出
		$content = get_post_field('post_content', $post_id);
		preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);

		if (!empty($image['src'])) {
			return esc_url($image['src']);
		}

		// デフォルト画像（未指定時は /assets/default.webp）
		if (empty($default_url)) {
			$default_url = get_template_directory_uri() . '/assets/default.webp';
			return esc_url($default_url);
		}
	}

	/**
	 * 指定投稿の表示用サムネイルHTMLを出力する。
	 * @param int|null $post_id 投稿ID（省略時は現在の投稿）
	 * @param string $size アイキャッチ画像のサイズ（デフォルト: 'medium'）
	 * @param string $default_url デフォルト画像のURL（空なら /assets/default.webp）
	 */
	public static function render($post_id = null, $size = 'medium', $default_url = '')
	{
		echo '<img src="' . self::get_thumbnail_url($post_id, $size, $default_url) . '" alt="">';

		return;
	}

	public static function getUrl($post_id = null, $size = 'medium', $default_url = '')
	{
		return self::get_thumbnail_url($post_id, $size, $default_url);
	}
}
/********************************************************************/
/* サムネイル取得(存在しなければ、本文の画像、デフォルト画像を取得) e	*/
/********************************************************************/

/**
 * 簡易モバイル判定（User-Agentをもとに）
 *
 * @return bool true=モバイル、false=PC
 */
function my_is_mobile_simple()
{
	if (empty($_SERVER['HTTP_USER_AGENT'])) {
		return false;
	}
	$ua = $_SERVER['HTTP_USER_AGENT'];

	// iPhone, Android, iPad, Mobile などの文字列で判定（必要に応じて追加可能）
	$mobile_agents = [
		'iPhone',
		'iPod',
		'Android',
		'Mobile',
		'webOS',
		'BlackBerry',
		'Opera Mini',
		'IEMobile',
		'Windows Phone',
	];

	foreach ($mobile_agents as $agent) {
		if (stripos($ua, $agent) !== false) {
			return true;
		}
	}
	return false;
}
