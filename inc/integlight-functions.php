<?php

/**
 * Integlight functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Integlight
 */


// デフォルトから追加するテーマサポート _s ///////////////////////////////////////////////
function integlight_setup_plus()
{


	// resolve of  theme check _s
	add_theme_support("wp-block-styles");
	add_theme_support("responsive-embeds");
	add_theme_support('border');


	// エディターでテーマのスタイルを反映（別途CSSが必要）
	add_theme_support('editor-styles');

	// 余白（マージン・パディング）のUIを有効化
	add_theme_support('custom-spacing');

	// 行間（line-height）のUIを有効化
	add_theme_support('custom-line-height');

	// 単位（px, %, em など）をユーザーが選べるように
	add_theme_support('custom-units');

	//add_theme_support('editor-color-palette');

	// resolve of  theme check _e




}
add_action('after_setup_theme', 'integlight_setup_plus');
// デフォルトから追加するテーマサポート _e /////////////////////////////////////////////




















// ## パンくずリスト _s //////////////////////////////////////////////////////////
class InteglightBreadcrumb
{



	public function __construct()
	{
		add_action('after_header', [$this, 'add_breadcrumb'], 10, 2);
	}

	public function add_breadcrumb()
	{

		if (!is_front_page()) {
			echo  $this->generate_breadcrumb();
		}
	}

	private function helper_addUl($breadcrumbData)
	{
		$ul = '<ul class="create_bread">';
		$ul .= '<li>';
		// CSS mask方式に合わせて<span>でアイコンを表現
		$ul .= '<span class="icon-home"></span>';
		$ul .= '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('HOME', 'integlight') . '</a>';
		$ul .= '<span class="icon-arrow"></span>';
		$ul .= '</li>';

		return $ul . $breadcrumbData . '</ul>';
	}

	private function generate_breadcrumb()
	{
		$output = '';
		if (is_category() || is_tag()) {
			$output .= $this->get_category_tag_breadcrumb();
		} elseif (is_archive()) {
			$output .= '<li>' . single_term_title('', false) . '</li>';
		} elseif (is_single()) {
			$output .= $this->get_single_breadcrumb();
		} elseif (is_page()) {
			$output .= '<li>' . get_the_title() . '</li>';
		} elseif (is_404()) {
			$output .= '<li>' . esc_html__('Page not found', 'integlight') . '</li>';
		}

		return $this->helper_addUl($output);
	}


	private function get_category_tag_breadcrumb()
	{
		$output = '';
		$queried_object = get_queried_object();
		$cat_list = array();

		while ($queried_object->parent != 0) {
			$queried_object = get_category($queried_object->parent);
			$cat_link = get_category_link($queried_object->term_id);
			array_unshift($cat_list, '<li><a href="' . esc_url($cat_link) . '">' . esc_html($queried_object->name) . '</a></li>');
		}

		foreach ($cat_list as $value) {
			$output .= $value;
			$output .= $this->awesome_rightArrow;
		}

		$output .= '<li>' . single_term_title('', false) . '</li>';
		return $output;
	}

	private function get_single_breadcrumb()
	{
		$output = '';
		$categories = get_the_category();

		if (!empty($categories)) {
			foreach ($categories as $category) {
				$output .= '<li>';
				$output .= '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
				$output .= '<span class="icon-arrow"></span>';
				$output .= '</li>';
			}
		}

		return $output . '<li>' . get_the_title()  . '</li>';
	}
}

// インスタンスを作成して初期化
new InteglightBreadcrumb();

// ## パンくずリスト _e //////////////////////////////////////////////////////////



/********************************************************************/
/* タイトル用カスタムフィールド（Meta Title / Meta Description）を追加するs*/
/********************************************************************/


/**
 * Class Integlight_SEO_Meta
 *
 * Handles custom SEO meta fields (title and description) for posts and pages.
 */
class Integlight_SEO_Meta
{

	/**
	 * The meta key for the custom title.
	 * @var string
	 */
	private $meta_key_title = '_custom_meta_title';

	/**
	 * The meta key for the custom description.
	 * @var string
	 */
	private $meta_key_description = '_custom_meta_description';

	/**
	 * The nonce action name.
	 * @var string
	 */
	private $nonce_action = 'seo_meta_box_nonce_action';

	/**
	 * The nonce field name.
	 * @var string
	 */
	private $nonce_name = 'seo_meta_box_nonce';

	/**
	 * Constructor. Hooks into WordPress actions and filters.
	 */
	public function __construct()
	{
		add_action('add_meta_boxes', [$this, 'add_meta_box']);
		add_action('save_post', [$this, 'save_meta_data']);
		add_filter('document_title_parts', [$this, 'filter_document_title']);
		add_action('wp_head', [$this, 'output_meta_description']);
	}

	/**
	 * Adds the meta box to specified post types.
	 * Action: add_meta_boxes
	 */
	public function add_meta_box()
	{
		// Target post types
		$post_types = ['post', 'page'];
		foreach ($post_types as $post_type) {
			add_meta_box(
				'seo_meta_box',                         // Meta box ID
				__('Meta data setting(optional)', 'integlight'), // Meta box title
				[$this, 'display_meta_box_content'],  // Callback function for content
				$post_type,                             // Target post type
				'normal',                               // Context (normal, side, advanced)
				'high'                                  // Priority
			);
		}
	}

	/**
	 * Displays the content of the meta box (input fields).
	 * Callback for add_meta_box.
	 *
	 * @param WP_Post $post The current post object.
	 */
	public function display_meta_box_content($post)
	{
		// Add a nonce field for security
		wp_nonce_field($this->nonce_action, $this->nonce_name);

		// Get existing values
		$custom_meta_title       = get_post_meta($post->ID, $this->meta_key_title, true);
		$custom_meta_description = get_post_meta($post->ID, $this->meta_key_description, true);
?>
		<p>
			<label for="custom_meta_title"><strong><?php esc_html_e('Meta Title', 'integlight'); ?></strong></label><br>
			<input type="text" name="<?php echo esc_attr($this->meta_key_title); ?>" id="custom_meta_title" value="<?php echo esc_attr($custom_meta_title); ?>" style="width:100%;" placeholder="<?php esc_attr_e('ex) Improve Your English Speaking | 5 Easy & Effective Tips', 'integlight'); ?>">
		</p>
		<p>
			<label for="custom_meta_description"><strong><?php esc_html_e('Meta Description', 'integlight'); ?></strong></label><br>
			<textarea name="<?php echo esc_attr($this->meta_key_description); ?>" id="custom_meta_description" rows="4" style="width:100%;" placeholder="<?php esc_attr_e('ex) Struggling with English speaking? Learn 5 simple and practical tips to boost your fluency and confidence in conversations. Perfect for beginners and intermediate learners!', 'integlight'); ?>"><?php echo esc_textarea($custom_meta_description); ?></textarea>
		</p>
	<?php
	}

	/**
	 * Saves the data entered in the meta box.
	 * Action: save_post
	 *
	 * @param int $post_id The ID of the post being saved.
	 */
	public function save_meta_data($post_id)
	{
		// Security check: Verify nonce
		if (! isset($_POST[$this->nonce_name]) || ! wp_verify_nonce(wp_unslash($_POST[$this->nonce_name]), $this->nonce_action)) {
			return;
		}

		// Do nothing during autosave
		if (wp_is_post_autosave($post_id)) {
			return;
		}

		// Check user permissions
		$post_type = isset($_POST['post_type']) ? sanitize_key($_POST['post_type']) : get_post_type($post_id);
		if ('page' === $post_type) {
			if (! current_user_can('edit_page', $post_id)) {
				return;
			}
		} else {
			if (! current_user_can('edit_post', $post_id)) {
				return;
			}
		}

		// Save Meta Title
		if (isset($_POST[$this->meta_key_title])) {
			$title_value = sanitize_text_field(wp_unslash($_POST[$this->meta_key_title]));
			update_post_meta($post_id, $this->meta_key_title, $title_value);
		}

		// Save Meta Description
		if (isset($_POST[$this->meta_key_description])) {
			$description_value = sanitize_textarea_field(wp_unslash($_POST[$this->meta_key_description]));
			update_post_meta($post_id, $this->meta_key_description, $description_value);
		}
	}

	/**
	 * Filters the document title parts to use the custom meta title if set.
	 * Filter: document_title_parts
	 *
	 * @param array $title_parts The parts of the document title.
	 * @return array The potentially modified title parts.
	 */
	public function filter_document_title($title_parts)
	{
		if (is_singular()) {
			global $post;
			if ($post) { // Ensure $post is available
				$custom_title = get_post_meta($post->ID, $this->meta_key_title, true);
				if (! empty($custom_title)) {
					// Use the custom title, overriding the default post title part
					$title_parts['title'] = $custom_title;
					// Optionally remove other parts like site name for SEO titles
					// unset($title_parts['site']);
					// unset($title_parts['tagline']);
				}
			}
		}
		return $title_parts;
	}

	/**
	 * Outputs the meta description tag in the header.
	 * Uses the custom meta description if set, otherwise falls back to the excerpt.
	 * Action: wp_head
	 */
	public function output_meta_description()
	{
		$meta_description = '';

		if (is_singular()) {
			global $post;
			if ($post) {
				$custom_description = get_post_meta($post->ID, $this->meta_key_description, true);
				if (!empty($custom_description)) {
					$meta_description = $custom_description;
				} else {
					$meta_description = get_the_excerpt($post->ID);
				}
			}
		} elseif (is_front_page() || is_home()) {
			$meta_description = get_bloginfo('description');
		} elseif (is_category()) {
			$meta_description = single_cat_title('', false) . 'の記事一覧｜' . get_bloginfo('description');
		} elseif (is_tag()) {
			$meta_description = single_tag_title('', false) . 'の記事一覧｜' . get_bloginfo('description');
		} elseif (is_post_type_archive()) {
			$meta_description = post_type_archive_title('', false) . '一覧｜' . get_bloginfo('description');
		} else {
			$meta_description = get_bloginfo('description');
		}

		if (!empty($meta_description)) {
			echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
		}
	}
}

// Instantiate the class to initialize the functionality
new Integlight_SEO_Meta();






/********************************************************************/
/* タイトル用カスタムフィールド（Meta Title / Meta Description）を追加するe*/
/********************************************************************/






/********************************************************************/
/* 次へ＆前へのページネーション s*/
/********************************************************************/
class Integlight_PostNavigations
{
	/**
	 * 投稿の画像を取得する（アイキャッチ or 本文の最初の画像）
	 */



	/**
	 * ナビゲーションの共通HTMLを出力
	 */
	private static function get_post_navigation_item($post, $class, $icon)
	{
		if (!$post) {
			return;
		}

		$post_id    = $post->ID;
		$post_title = get_the_title($post_id);
		$post_title = (strlen($post_title) > 17) ? wp_html_excerpt($post_title, 17) . esc_html__('...', 'integlight') : $post_title;
		$post_url   = get_permalink($post_id);

	?>
		<a href="<?php echo esc_url($post_url); ?>" class="<?php echo esc_attr($class); ?>">
			<div class="nav-image-wrapper">
				<img loading="lazy" fetchpriority="low" src="<?php echo esc_url(Integlight_PostThumbnail::getUrl($post_id)); ?>"
					alt="">
				<span class="nav-label">
					<?php if ($class === 'nav-previous') : ?>
						<?php echo $icon; ?>
					<?php endif; ?>
					<?php echo esc_html($post_title); ?>
					<?php if ($class === 'nav-next') : ?>
						<?php echo $icon; ?>
					<?php endif; ?>
				</span>
			</div>
		</a>

	<?php
	}

	/**
	 * 前後の投稿ナビゲーションを表示する
	 */
	public static function get_post_navigation()
	{
		$prev_post = get_previous_post();
		$next_post = get_next_post();


		if (!$prev_post && !$next_post) {
			return;
		}

		$icon_prev = '<span class="icon-prev"></span>';

		$icon_next = '<span class="icon-next"></span>';


	?>
		<nav class="post-navigation" role="navigation">
			<?php
			self::get_post_navigation_item($prev_post, 'nav-previous', $icon_prev);
			self::get_post_navigation_item($next_post, 'nav-next', $icon_next);
			?>
		</nav>
<?php
	}
}
/********************************************************************/
/* 次へ＆前へのページネーション e*/
/********************************************************************/


/********************************************************************/
/* 抜粋関数の文字数を変更 s	*/
/********************************************************************/

/**
 * Class Integlight_Excerpt_Customizer
 *
 * Customizes the excerpt length and cleans up the automatically generated excerpt.
 */
class Integlight_Excerpt_Customizer
{
	/**
	 * The desired excerpt length in words.
	 * @var int
	 */
	private $excerpt_length = 200; // 語数を200語に設定

	/**
	 * Constructor. Hooks the methods into WordPress filters.
	 */
	public function __construct()
	{
		add_filter('excerpt_length', [$this, 'custom_excerpt_length']);
		add_filter('wp_trim_excerpt', [$this, 'clean_auto_excerpt'], 20); // 優先度 20 を維持
	}

	/**
	 * Sets the custom excerpt length.
	 * Filter: excerpt_length
	 *
	 * @param int $length Default excerpt length.
	 * @return int Custom excerpt length.
	 */
	public function custom_excerpt_length($length)
	{
		return $this->excerpt_length;
	}

	/**
	 * Cleans the automatically generated excerpt.
	 * Removes the Table of Contents and replaces non-breaking spaces.
	 * Filter: wp_trim_excerpt
	 *
	 * @param string $excerpt The automatically generated excerpt.
	 * @return string The cleaned excerpt.
	 */
	public function clean_auto_excerpt($excerpt)
	{

		// ② &nbsp; を通常の空白に変換
		$excerpt = str_replace('&nbsp;', ' ', $excerpt);

		// ③ HTMLタグを除去 (wp_trim_excerpt は通常タグを除去しますが、念のため)
		$excerpt = strip_tags($excerpt);

		// ④ 不要な空白や改行を整理
		$excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));

		return $excerpt;
	}
}

// Instantiate the class to initialize the functionality
new Integlight_Excerpt_Customizer();



/********************************************************************/
/* 抜粋関数の文字数を変更 e	*/
/********************************************************************/
