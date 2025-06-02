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
/**css,js読み込み s************************ */
/***************************************** */


require get_template_directory() . '/inc/integlight-functions-outerAssets.php';

class InteglightCommonCssAssets
{
	private static $styles = [
		'integlight-awesome' => '/css/awesome-all.min.css',
		'integlight-base-style-plus' => '/css/base-style.css',
		'integlight-style-plus' => '/css/integlight-style.css',
		'integlight-sp-style' => '/css/integlight-sp-style.css',
		'integlight-layout' => '/css/layout.css',
		'integlight-integlight-menu' => '/css/integlight-menu.css',
		'integlight-post' => '/css/post.css',
		'integlight-page' => '/css/page.css',
		'integlight-front' => '/css/front.css',
		'integlight-home' => '/css/home.css',
		'integlight-module' => '/css/module.css',
		'integlight-block-module' => '/css/block-module.css',
		'integlight-helper' => '/css/helper.css',
	];


	private static $deferredStyles = [
		'integlight-awesome',
		'integlight-sp-style',
		'integlight-block-module'
	];


	public static function init()
	{
		// スタイルリストを設定（追記可能）
		InteglightFrontendStyles::add_styles(self::$styles);

		$excluded_key = 'integlight-sp-style';
		// $styles から $excluded_key を除外してコピー
		$EditorStyles = array_filter(self::$styles, function ($key) use ($excluded_key) {
			return $key !== $excluded_key;
		}, ARRAY_FILTER_USE_KEY);

		InteglightEditorStyles::add_styles($EditorStyles);

		// 遅延対象のスタイルを登録
		InteglightDeferCss::add_deferred_styles(self::$deferredStyles);
	}
}

// 初期化処理（ルートで実行）
add_action('after_setup_theme', ['InteglightCommonCssAssets', 'init']);





//js 移動　PF対策	
class InteglightCommonJsAssets
{


	public static function init()
	{

		//js 読み込み
		$scripts = [
			'integlight-navigation' =>  ['path' => '/js/build/navigation.js', 'deps' => []],
		];
		InteglightFrontendScripts::add_scripts($scripts);

		// フッターに移動するスクリプトを登録
		$footerScripts = [
			'jquery'   => includes_url('/js/jquery/jquery.min.js')
		];
		InteglightMoveScripts::add_scripts($footerScripts);


		//js 読み込み　WPデフォルトのコメント用
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_comment_reply_script']);
	}

	public static function enqueue_comment_reply_script()
	{
		if (is_singular() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}
	}
}

// 初期化処理
add_action('after_setup_theme', ['InteglightCommonJsAssets', 'init']);


/***************************************** */
/**css,js読み込み e***************************** */
/***************************************** */




// デフォルトから追加するテーマサポート _s ///////////////////////////////////////////////
function integlight_setup_plus()
{


	// resolve of  theme check _s
	add_theme_support("wp-block-styles");
	add_theme_support("responsive-embeds");
	add_theme_support("align-wide");
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
		$ul .= '<a href="' . home_url() . '">HOME</a>';
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
			$output .= '<li>ページが見つかりません</li>';
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
			<label for="custom_meta_title"><strong><?php echo esc_html__('Meta Title', 'integlight'); ?></strong></label><br>
			<input type="text" name="<?php echo esc_attr($this->meta_key_title); ?>" id="custom_meta_title" value="<?php echo esc_attr($custom_meta_title); ?>" style="width:100%;" placeholder="<?php echo esc_attr__('ex) Improve Your English Speaking | 5 Easy & Effective Tips', 'integlight'); ?>">
		</p>
		<p>
			<label for="custom_meta_description"><strong><?php echo esc_html__('Meta Description', 'integlight'); ?></strong></label><br>
			<textarea name="<?php echo esc_attr($this->meta_key_description); ?>" id="custom_meta_description" rows="4" style="width:100%;" placeholder="<?php echo esc_attr__('ex) Struggling with English speaking? Learn 5 simple and practical tips to boost your fluency and confidence in conversations. Perfect for beginners and intermediate learners!', 'integlight'); ?>"><?php echo esc_textarea($custom_meta_description); ?></textarea>
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
			} elseif (has_excerpt($post->ID)) {
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



require get_template_directory() . '/inc/integlight-functions-block.php';



/********************************************************************/
/* ブロックテーマへの適用s*/
/********************************************************************/


/**
 * Class Integlight_Block_Assets
 *
 * Registers custom block styles and patterns for the theme.
 */
class Integlight_Block_Assets
{

	/**
	 * Constructor. Hooks into WordPress init action.
	 */
	public function __construct()
	{
		// Both styles and patterns should be registered during the 'init' action.
		add_action('init', [$this, 'register_assets']);
	}

	/**
	 * Registers both block styles and patterns.
	 * Action: init
	 */
	public function register_assets()
	{
		$this->register_block_styles();
		$this->register_block_patterns();
	}

	/**
	 * Registers custom block styles.
	 * Called by register_assets during the 'init' action.
	 */
	private function register_block_styles()
	{
		register_block_style(
			'core/heading',
			[
				'name'         => 'no-underline',
				'label'        => __('No Underline', 'integlight'), // Use __() for translatable strings
				'inline_style' => '.wp-block-heading.is-style-no-underline::after { display: none !important; }',
			]
		);
		// Add more block styles here if needed
	}

	/**
	 * Registers custom block patterns.
	 * Called by register_assets during the 'init' action.
	 */
	private function register_block_patterns()
	{
		// Check if the function exists before calling it (good practice)
		if (function_exists('register_block_pattern')) {
			register_block_pattern(
				'integlight/two-columns',
				[
					'title'       => __('Two Columns', 'integlight'),
					'description' => _x('A layout with two columns for content.', 'Block pattern description', 'integlight'),
					'categories'  => ['columns'], // Use array() or [] consistently
					'content'     => "<!-- wp:columns -->\n<div class=\"wp-block-columns\">\n    <!-- wp:column -->\n    <div class=\"wp-block-column\"><p>" . esc_html__('Column one', 'integlight') . "</p></div>\n    <!-- /wp:column -->\n    <!-- wp:column -->\n    <div class=\"wp-block-column\"><p>" . esc_html__('Column two', 'integlight') . "</p></div>\n    <!-- /wp:column -->\n</div>\n<!-- /wp:columns -->",
				]
			);
			// Add more block patterns here if needed
		}
	}
}

// Instantiate the class to initialize the functionality
new Integlight_Block_Assets();




/********************************************************************/
/* ブロックテーマへの適用e*/
/********************************************************************/


/********************************************************************/
/* 投稿の画像を取得するページネーション s*/
/********************************************************************/
/**
 * 投稿のサムネイル画像があればそのURLを、
 * なければ本文の最初の画像URLを返す。
 */
class Integlight_PostHelper
{
	/**
	 * 投稿の画像を取得する（アイキャッチ or 本文の最初の画像）
	 */
	private static function get_post_image($post_id)
	{
		$thumb_url = get_the_post_thumbnail_url($post_id, 'full');
		if ($thumb_url) {
			return $thumb_url;
		}

		$post_content = get_post_field('post_content', $post_id);
		$first_img_url = self::get_first_image_url_from_content($post_content);
		if ($first_img_url) {
			return $first_img_url;
		}

		return ''; // 画像がない場合のデフォルト処理（必要なら設定）
	}

	/**
	 * 本文から最初の画像URLを抽出する
	 */
	private static function get_first_image_url_from_content($content)
	{
		if (preg_match('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $content, $matches)) {
			return $matches[1];
		}
		return '';
	}

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
		$post_title = (strlen($post_title) > 14) ? wp_html_excerpt($post_title, 14) . '...' : $post_title;
		$post_img   = self::get_post_image($post_id);
		$post_url   = get_permalink($post_id);

	?>
		<div class="<?php echo esc_attr($class); ?>" style="background-image: url('<?php echo esc_url($post_img); ?>');">
			<a href="<?php echo esc_url($post_url); ?>">
				<?php if ($class === 'nav-previous') : ?>
					<i class="fa-regular fa-square-caret-left"></i>
				<?php endif; ?>
				<?php echo esc_html($post_title); ?>
				<?php if ($class === 'nav-next') : ?>
					<i class="fa-regular fa-square-caret-right"></i>
				<?php endif; ?>
			</a>
		</div>
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

	?>
		<nav class="post-navigation" role="navigation">
			<?php
			self::get_post_navigation_item($prev_post, 'nav-previous', '<i class="fa-regular fa-square-caret-left"></i>');
			self::get_post_navigation_item($next_post, 'nav-next', '<i class="fa-regular fa-square-caret-right"></i>');
			?>
		</nav>
<?php
	}
}



/********************************************************************/
/* 投稿の画像を取得 e	*/
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
