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
/***************************************** */
/**php読み込み e************************ */
/***************************************** */


/***************************************** */
/**css,js読み込み s************************ */
/***************************************** */



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
	add_theme_support('border');


	// エディターでテーマのスタイルを反映（別途CSSが必要）
	add_theme_support('editor-styles');

	// 余白（マージン・パディング）のUIを有効化
	add_theme_support('custom-spacing');

	// 行間（line-height）のUIを有効化
	add_theme_support('custom-line-height');

	// 単位（px, %, em など）をユーザーが選べるように
	add_theme_support('custom-units');

	add_theme_support('editor-color-palette');

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
				'integlight/media-and-text-pattern',
				array(
					'title'       => __('media and text', 'integlight'),
					'categories'  => array('featured'),
					'content'     => '
<!-- wp:media-text {"mediaPosition":"left","mediaType":"image","mediaLink":"","isStackedOnMobile":true,"verticalAlignment":"center"} -->
<div class="wp-block-media-text is-stacked-on-mobile is-vertically-aligned-center">
  <figure class="wp-block-media-text__media">
    <img src="' . esc_url(get_template_directory_uri() . '/assets/pattern-woman1.webp') . '" alt="Firefly image" />
  </figure>
  <div class="wp-block-media-text__content">
    <!-- wp:heading {"level":4} -->
    <h4 class="wp-block-heading">Director</h4>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>"Less experienced and uneasy about design and development, struggling to keep projects on track." <br />
    One common challenge for less experienced directors is lacking sufficient knowledge in specialized design.</p>
    <!-- /wp:paragraph -->
  </div>
</div>
<!-- /wp:media-text -->
        ',
				)
			);
			// Add more block patterns here if needed




			register_block_pattern(
				'integlight/text and media',
				array(
					'title'       => __('text and media', 'integlight'),
					'categories'  => array('featured'),
					'content'     => '
<!-- wp:media-text {"mediaType":"image","mediaPosition":"right","mediaId":0,"mediaUrl":"/assets/pattern-woman2.webp","isStackedOnMobile":true} -->
<div class="wp-block-media-text has-media-on-the-right is-stacked-on-mobile">
  <div class="wp-block-media-text__content">
    <!-- wp:heading {"level":4} -->
    <h4 class="wp-block-heading">For Production Agencies</h4>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>
      "We want to deliver WordPress sites tailored to our clients\' needs, but lack the in-house expertise and technical skills to do so."<br><br>
      Many production agencies face the challenge 	.
    </p>
    <!-- /wp:paragraph -->
  </div>
  <figure class="wp-block-media-text__media">
<img src="' . esc_url(get_template_directory_uri() . '/assets/pattern-woman2.webp') . '" alt="Firefly image" />  </figure>
</div>
<!-- /wp:media-text -->
        ',
				)
			);













			register_block_pattern(
				'integlight/big-quote',
				array(
					'title'       => __('big quote', 'integlight'),
					'categories'  => array('featured'),

					'content'     => '
<!-- wp:quote -->
<blockquote class="wp-block-quote">
  <!-- wp:paragraph -->
  <p><strong>"When I first used [integlight], I was amazed. It’s intuitive to use, yet the site loads incredibly fast. This ensures readers can enjoy articles without any stress."</strong></p>
  <!-- /wp:paragraph -->

  <!-- wp:paragraph -->
  <p><strong>"What I especially like is the 8 color variations. They are all sophisticated, and just by choosing one, my blog\'s impression becomes much more stylish. Even someone like me, who lacks design confidence, could easily create a consistent site."</strong></p>
  <!-- /wp:paragraph -->

  <!-- wp:paragraph -->
  <p><strong>"Moreover, by adding [aurora-design-blocks], I could use sliders and balloon features, which greatly enhanced article expressiveness. And it only costs 1,980 yen — truly amazing. I’m glad I found it!"</strong></p>
  <!-- /wp:paragraph -->
</blockquote>
<!-- /wp:quote -->
',
				)
			);

			register_block_pattern(
				'integlight/strong-table',
				array(
					'title'       => __('strong table', 'integlight'),
					'categories'  => array('featured'),

					'content'     => '
<!-- wp:table {"hasFixedLayout":true} -->
<figure class="wp-block-table">
<table class="has-fixed-layout">
  <thead>
    <tr>
      <th>Common Mistake</th>
      <th>Solution</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>No event is triggered</td>
      <td>Check for <code>Click ID</code> typos or insufficient tag wait time</td>
    </tr>
    <tr>
      <td>Event not showing</td>
      <td>Twitter embed buttons cannot be captured by GTM</td>
    </tr>
    <tr>
      <td>No click event detected</td>
      <td>Likely blocked by JavaScript or iframe handling</td>
    </tr>
  </tbody>
</table>
</figure>
<!-- /wp:table -->
',
				)
			);

			register_block_pattern(
				'integlight/post-columns',
				array(
					'title'       => __('Post Columns – Morning Routine', 'integlight'),
					'categories'  => array('featured'),

					'content'     => <<<HTML
<!-- wp:columns -->
<div class="wp-block-columns">
  <!-- wp:column {"style":{"color":{"background":"#7fdde7"},"border":{"radius":"10px"},"spacing":{"padding":{"right":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-column has-background" style="border-radius:10px;background-color:#7fdde7;padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
    <!-- wp:heading {"className":"is-style-no-underline"} -->
    <h2 class="wp-block-heading is-style-no-underline">Post 01</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>On Sunday mornings, I wake up a bit later than on weekdays. No alarm. Just the soft morning light filtering through the curtains, gently saying “you can wake up now.”</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:column -->

  <!-- wp:column {"style":{"color":{"background":"#7fdde7"},"border":{"radius":"10px"},"spacing":{"padding":{"right":"var:preset|spacing|40","left":"var:preset|spacing|40"}}}} -->
  <div class="wp-block-column has-background" style="border-radius:10px;background-color:#7fdde7;padding-right:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
    <!-- wp:heading {"className":"is-style-no-underline"} -->
    <h2 class="wp-block-heading is-style-no-underline">Post 02</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph -->
    <p>I move to the living room and boil some water. Since I’ve been cutting back on caffeine, I started drinking rooibos tea instead. It’s mild, and feels healthy somehow—just the way I like it.</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:column -->
</div>
<!-- /wp:columns -->
HTML
				)
			);

			register_block_pattern(
				'integlight/promo-box',
				array(
					'title'       => __('Integlight Promo Box', 'integlight'),
					'categories'  => array('featured'),

					'content'     => <<<HTML
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
  <!-- wp:group {"style":{"color":{"background":"#d9ffed","text":"#566b65"},"border":{"radius":"10px","color":"#566b65","width":"1px"},"spacing":{"padding":{"top":"0","bottom":"0","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}},"elements":{"link":{"color":{"text":"#566b65"}}}},"layout":{"type":"constrained"}} -->
  <div class="wp-block-group has-border-color has-text-color has-background has-link-color" style="border-color:#566b65;border-width:1px;border-radius:10px;color:#566b65;background-color:#d9ffed;padding-top:0;padding-right:var(--wp--preset--spacing--50);padding-bottom:0;padding-left:var(--wp--preset--spacing--50)">
    <!-- wp:image {"id":6407,"width":"100px","height":"100px","scale":"cover","sizeSlug":"full","linkDestination":"none","align":"center"} -->
    <figure class="wp-block-image aligncenter size-full is-resized"><img src="https://integlight.auroralab-design.com/wp-content/uploads/2025/07/cropped-ファビコン.webp" alt="" class="wp-image-6407" style="object-fit:cover;width:100px;height:100px"/></figure>
    <!-- /wp:image -->

    <!-- wp:heading {"textAlign":"center","className":"is-style-no-underline"} -->
    <h2 class="wp-block-heading has-text-align-center is-style-no-underline">Integlight</h2>
    <!-- /wp:heading -->

    <!-- wp:paragraph {"align":"center"} -->
    <p class="has-text-align-center">Build a professional website for free.<br>The “Integlight” theme, officially approved by WordPress, combines sleek design with lightning-fast performance.<br>No complex settings. No hassle. Just launch your SEO-ready digital asset today.</p>
    <!-- /wp:paragraph -->
  </div>
  <!-- /wp:group -->
</div>
<!-- /wp:group -->
HTML
				)
			);
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
class Integlight_PostNavigations
{
	/**
	 * 投稿の画像を取得する（アイキャッチ or 本文の最初の画像）
	 */

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
		$post_title = (strlen($post_title) > 14) ? wp_html_excerpt($post_title, 14) . esc_html__('...', 'integlight') : $post_title;
		$post_url   = get_permalink($post_id);

	?>
		<div class="<?php echo esc_attr($class); ?>" style="background-image: url('<?php echo Integlight_PostThumbnail::getUrl($post_id); ?>');">
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
