<?php
/*  テスト領域 s*/
/////////////////////////////////////////////










/////////////////////////////////////////////
/* テスト領域 e*/


/**
 * Integlight functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Integlight
 */


/***************************************** */
/**php読み込み s************************ */
/***************************************** */

require get_template_directory() . '/inc/integlight-functions-outerAssets.php';

require get_template_directory() . '/inc/starter-content.php';
require get_template_directory() . '/inc/integlight-functions-cmn.php';
require get_template_directory() . '/inc/integlight-functions-pf.php';
require get_template_directory() . '/inc/integlight-functions-widgets.php';
require get_template_directory() . '/inc/integlight-functions-pattern.php';

/***************************************** */
/**php読み込み e************************ */
/***************************************** */






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

	private $awesome_home = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M575.8 255.5c0 18-15 32.1-32 32.1l-32 0 .7 160.2c0 2.7-.2 5.4-.5 8.1l0 16.2c0 22.1-17.9 40-40 40l-16 0c-1.1 0-2.2 0-3.3-.1c-1.4 .1-2.8 .1-4.2 .1L416 512l-24 0c-22.1 0-40-17.9-40-40l0-24 0-64c0-17.7-14.3-32-32-32l-64 0c-17.7 0-32 14.3-32 32l0 64 0 24c0 22.1-17.9 40-40 40l-24 0-31.9 0c-1.5 0-3-.1-4.5-.2c-1.2 .1-2.4 .2-3.6 .2l-16 0c-22.1 0-40-17.9-40-40l0-112c0-.9 0-1.9 .1-2.8l0-69.7-32 0c-18 0-32-14-32-32.1c0-9 3-17 10-24L266.4 8c7-7 15-8 22-8s15 2 21 7L564.8 231.5c8 7 12 15 11 24z"/></svg>';
	private $awesome_rightArrow = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M310.6 233.4c12.5 12.5 12.5 32.8 0 45.3l-192 192c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L242.7 256 73.4 86.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l192 192z"/></svg>';


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
		$ul .= $this->awesome_home;
		$ul .= '<a href="' . esc_url(home_url('/')) . '">' . esc_html__('HOME', 'integlight') . '</a>';
		$ul .= $this->awesome_rightArrow;
		$ul .= '</li>';

		return $ul . $breadcrumbData .  '</ul>';
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
				$output .= $this->awesome_rightArrow;
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
		if (is_singular()) {
			global $post;
			if (! $post) { // Ensure $post is available
				return;
			}

			$meta_description = '';
			// Get custom meta description
			$custom_description = get_post_meta($post->ID, $this->meta_key_description, true);

			if (! empty($custom_description)) {
				$meta_description = $custom_description;
			} else {
				// Fallback to excerpt if custom description is empty
				$meta_description = get_the_excerpt($post->ID);
			}

			// Output the meta tag only if we have a description
			if (! empty($meta_description)) {
				echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
			}
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
		$post_title = (strlen($post_title) > 14) ? wp_html_excerpt($post_title, 14) . esc_html__('...', 'integlight') : $post_title;
		$post_url   = get_permalink($post_id);

	?>
		<a href="<?php echo esc_url($post_url); ?>" class="<?php echo esc_attr($class); ?>" style="display: block; background-image: url('<?php echo Integlight_PostThumbnail::getUrl($post_id); ?>'); background-size: cover; background-position: center;">
			<span class="nav-label">
				<?php if ($class === 'nav-previous') : ?>
					<?php echo $icon; ?>
				<?php endif; ?>
				<?php echo esc_html($post_title); ?>
				<?php if ($class === 'nav-next') : ?>
					<?php echo $icon; ?>
				<?php endif; ?>
			</span>
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

		$icon_prev = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="24" height="24" aria-hidden="true" focusable="false">
  <path d="M144 160C144 151.2 151.2 144 160 144L480 144C488.8 144 496 151.2 496 160L496 480C496 488.8 488.8 496 480 496L160 496C151.2 496 144 488.8 144 480L144 160zM160 96C124.7 96 96 124.7 96 160L96 480C96 515.3 124.7 544 160 544L480 544C515.3 544 544 515.3 544 480L544 160C544 124.7 515.3 96 480 96L160 96zM224 320C224 313.3 226.8 307 231.7 302.4L343.7 198.4C350.7 191.9 360.9 190.2 369.6 194C378.3 197.8 384 206.5 384 216L384 424C384 433.5 378.3 442.2 369.6 446C360.9 449.8 350.7 448.1 343.7 441.6L231.7 337.6C226.8 333.1 224 326.7 224 320z"/>
</svg>
SVG;

		$icon_next = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" width="24" height="24" aria-hidden="true" focusable="false">
  <path d="M496 160C496 151.2 488.8 144 480 144L160 144C151.2 144 144 151.2 144 160L144 480C144 488.8 151.2 496 160 496L480 496C488.8 496 496 488.8 496 480L496 160zM480 96C515.3 96 544 124.7 544 160L544 480C544 515.3 515.3 544 480 544L160 544C124.7 544 96 515.3 96 480L96 160C96 124.7 124.7 96 160 96L480 96zM416 320C416 326.7 413.2 333 408.3 337.6L296.3 441.6C289.3 448.1 279.1 449.8 270.4 446C261.7 442.2 256 433.5 256 424L256 216C256 206.5 261.7 197.8 270.4 194C279.1 190.2 289.3 191.9 296.3 198.4L408.3 302.4C413.2 306.9 416 313.3 416 320z"/>
</svg>
SVG;


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
