<?php

/**
 * Integlight functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Integlight
 */

if (!defined('_S_VERSION')) {
	// Replace the version number of the theme on each release.
	define('_S_VERSION', '1.0.1');
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function integlight_setup()
{
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Integlight, use a find and replace
		* to change 'integlight' to the name of your theme in all the template files.
		*/
	load_theme_textdomain('integlight', get_template_directory() . '/languages');

	// Add default posts and comments RSS feed links to head.
	add_theme_support('automatic-feed-links');

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support('title-tag');

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support('post-thumbnails');

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__('Primary', 'integlight'),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	// Set up the WordPress core custom background feature.
	add_theme_support(
		'custom-background',
		apply_filters(
			'integlight_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	// Add theme support for selective refresh for widgets.
	add_theme_support('customize-selective-refresh-widgets');

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action('after_setup_theme', 'integlight_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function integlight_content_width()
{
	$GLOBALS['content_width'] = apply_filters('integlight_content_width', 640);
}
add_action('after_setup_theme', 'integlight_content_width', 0);

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function integlight_widgets_init()
{
	register_sidebar(
		array(
			'name'          => esc_html__('Sidebar', 'integlight'),
			'id'            => 'sidebar-1',
			'description'   => esc_html__('Add widgets here.', 'integlight'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'integlight_widgets_init');

/**
 * Enqueue scripts and styles.
 */
function integlight_scripts()
{
	wp_enqueue_style('integlight-style', get_stylesheet_uri(), array(), _S_VERSION);
	wp_style_add_data('integlight-style', 'rtl', 'replace');

	wp_enqueue_script('integlight-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);

	if (is_singular() && comments_open() && get_option('thread_comments')) {
		wp_enqueue_script('comment-reply');
	}
}
add_action('wp_enqueue_scripts', 'integlight_scripts');

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if (defined('JETPACK__VERSION')) {
	require get_template_directory() . '/inc/jetpack.php';
}

// 以下は初期構成からの追加分	////////////////////////////////


// ## パンくずリスト
//////////////////////////////////////////////////////////
function integlight_breadcrumb()
{
	// HOMEリンク
	$home = '<li><a href="' . home_url() . '" >HOME</a></li>';

	echo '<ul class="create_bread">';
	if (!is_front_page()) {
		// カテゴリページまたはタグページ
		if (is_category() || is_tag()) {
			$queried_object = get_queried_object();
			$cat_list = array();
			// 親カテゴリを取得
			while ($queried_object->parent != 0) {
				$queried_object = get_category($queried_object->parent);
				$cat_link = get_category_link($queried_object->term_id);
				array_unshift($cat_list, '<li><a href="' . esc_url($cat_link) . '">' . esc_html($queried_object->name) . '</a></li>');
			}
			// リンクを出力
			foreach ($cat_list as $value) {
				echo $value;
			}
			// カテゴリまたはタグ名を表示
			echo '<li>' . single_term_title('', false) . '</li>';
		}
		// それ以外のアーカイブページ
		elseif (is_archive()) {
			echo $home;
			echo '<li>' . get_the_archive_title() . '</li>';
		}
		// 投稿ページ
		elseif (is_single()) {
			// カテゴリリンクを表示
			$categories = get_the_category();
			if (!empty($categories)) {
				foreach ($categories as $category) {
					echo '<li><a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a></li>';
				}
			}
			// 記事のタイトルを表示
			echo '<li>' . get_the_title() . '</li>';
		}
		// 固定ページ
		elseif (is_page()) {
			echo $home;
			echo '<li>' . get_the_title() . '</li>';
		}
		// 404ページ
		elseif (is_404()) {
			echo $home;
			echo '<li>ページが見つかりません</li>';
		}
	}
	echo "</ul>";
}

//////////////////////////////////////////////////////////



///////////////////////////////////////////////////////////////////////////////
//## スタイルシート、JSファイルの追加
/**
 * Enqueue scripts and styles.
 */
function integlight_scripts_plus()
{

	wp_enqueue_style('integlight-style-plus', get_template_directory_uri() . '/integlight-style.css', array(), _S_VERSION);
	//wp_enqueue_script('integlight-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);
	wp_enqueue_style('integlight-post', get_template_directory_uri() . '/css/post.css', array(), _S_VERSION);

	//web fonts: font awsome
	wp_enqueue_style('integlight-post', get_template_directory_uri() . '/css/all.min.css', array(), _S_VERSION);

	/////// slider _s
	wp_enqueue_script('jquery');
	wp_enqueue_script('integlight_slider-script', get_template_directory_uri() . '/js/integlight-scripts.js', array('jquery'), _S_VERSION, true);
	// カスタマイザーの設定値をJavaScriptに渡す
	wp_localize_script('integlight_slider-script', 'sliderSettings', array(
		'fadeDuration' => get_theme_mod('slider_fade_duration', '0.8'),
		'changeDuration' => get_theme_mod('slider_change_duration', '1')
	));
	/*
	wp_localize_script('integlight_slider-script', 'sliderSettings', array(
		'changeDuration' => get_theme_mod('slider_change_duration', '1')
	));
	*/
	/////// slider _e


}
add_action('wp_enqueue_scripts', 'integlight_scripts_plus');
/////////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////
//## editor用のスタイルの追加
/**
 * Enqueue scripts and styles.
 */
// 性能劣化のデメリットがあるためOFFにしておくことも検討
function integlight_add_editor_styles()
{
	add_theme_support('editor-styles');
	add_editor_style(get_theme_file_uri('/style.css'));
	add_editor_style(get_theme_file_uri('/integlight-style.css'));
}
add_action('admin_init', 'integlight_add_editor_styles');
//////////////////////////////////////////////////////////////////////////////////

///////////////////////////////////////////////
/*
	デフォルトから追加するテーマサポート
*/
function integlight_setup_plus()
{

	// resolve of  theme check _s
	add_theme_support("wp-block-styles");
	add_theme_support("responsive-embeds");
	add_theme_support("align-wide");
	// resolve of  theme check _e

}
add_action('after_setup_theme', 'integlight_setup_plus');
///////////////////////////////////////////////


// ## コピーライト対応
//////////////////////////////////////////////////////////////////////////////////
function integlight_add_custom_menu_page()
{
?>
	<div class="wrap">
		<h2>Copy Rightの設定</h2>
		<form method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
			<?php
			settings_fields('custom-menu-group');
			do_settings_sections('custom-menu-group'); ?>
			<div class="metabox-holder">
				<p>Copy Rightを入力してください。</p>
				<p><input type="text" id="copy_right" name="copy_right" value="<?php echo get_option('copy_right'); ?>"></p>
			</div>
			<?php submit_button(); ?>
		</form>
	</div>
<?php
}

function integlight_register_custom_setting()
{
	register_setting('custom-menu-group', 'copy_right');
}


function integlight_custom_menu_page()
{
	add_submenu_page('themes.php', 'フッダー設定', 'フッダー', 'manage_options', 'custom_menu_page', 'integlight_add_custom_menu_page',  5);
	add_action('admin_init', 'integlight_register_custom_setting');
}

add_action('admin_menu', 'integlight_custom_menu_page');

//////////////////////////////////////////////////////////////////////////////////



// 目次_s ////////////////////////////////////////////////////////////////////////////////
// 目次を生成するクラスを定義
class InteglightTableOfContents
{

	// コンストラクタ
	public function __construct()
	{
		add_filter('the_content', array($this, 'add_toc_to_content'));
	}

	// 投稿コンテンツに目次を追加するメソッド
	public function add_toc_to_content($content)
	{
		// H1, H2, H3タグを抽出
		preg_match_all('/<(h[1-3]).*?>(.*?)<\/\1>/', $content, $matches, PREG_SET_ORDER);

		if (!empty($matches)) {
			// 目次を生成
			$toc = '<div class="post-toc"><h2>INDEX</h2><ul>';
			foreach ($matches as $match) {
				$heading_tag = $match[1];
				$heading_text = $match[2];
				$id = sanitize_title_with_dashes($heading_text);
				$toc .= '<li class="toc-' . strtolower($heading_tag) . '"><a href="#' . $id . '">' . strip_tags($heading_text) . '</a></li>';
				// 投稿コンテンツ内のH1, H2, H3タグにIDを追加
				$content = str_replace($match[0], '<' . $heading_tag . ' id="' . $id . '">' . $heading_text . '</' . $heading_tag . '>', $content);
			}
			$toc .= '</ul></div>';

			// 目次をコンテンツの最初に追加
			$content = $toc . $content;
		}

		return $content;
	}
}

// インスタンスを作成して目次生成を初期化
new InteglightTableOfContents();
// 目次_e ////////////////////////////////////////////////////////////////////////////////
