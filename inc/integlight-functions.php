<?php
/* テスト領域 s*/
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
InteglightCommonCssAssets::init();





//js 移動　PF対策	
class InteglightCommonJsAssets
{


	public static function init()
	{

		//js 読み込み
		$scripts = [
			'integlight-navigation' =>  ['path' => '/js/navigation.js', 'deps' => []],
		];
		InteglightFrontendScripts::add_scripts($scripts);

		// フッターに移動するスクリプトを登録
		$footerScripts = [
			'jquery'   => includes_url('/js/jquery/jquery.min.js')
		];
		InteglightMoveScripts::add_scripts($footerScripts);


		//js 読み込み　WPデフォルトのコメント用
		if (is_singular() && comments_open() && get_option('thread_comments')) {
			wp_enqueue_script('comment-reply');
		}
	}
}

// 初期化処理
InteglightCommonJsAssets::init();


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













class InteglightFooterSettings
{
	public function __construct()
	{
		add_filter('admin_menu', array($this, 'setting'));
		add_action('admin_init', array($this, 'setting_db'));
	}

	public function setting()
	{
		add_theme_page(
			__('Footer Settings', 'integlight'),
			__('Footer', 'integlight'),
			'manage_options',
			'custom_footer_page',
			array($this, 'setting_menuPage'),
			6
		);
	}

	public function setting_db()
	{
		register_setting('custom-menu-group', 'copy_right');
		register_setting('custom-menu-group', 'integlight_show_footer_credit');
	}

	public function setting_menuPage()
	{
		$show_credit = get_option('integlight_show_footer_credit');
		$copy_right = get_option('copy_right');
?>
		<div class="wrap">
			<h2><?php echo __("Footer Settings", "integlight") ?></h2>
			<form method="post" action="options.php" enctype="multipart/form-data" encoding="multipart/form-data">
				<?php
				settings_fields('custom-menu-group');
				do_settings_sections('custom-menu-group');
				?>
				<div class="metabox-holder">
					<!-- コピーライト設定 -->
					<p><?php echo __("Please enter the Copyright information.", "integlight"); ?></p>
					<p>
						<input type="text" id="copy_right" name="copy_right" value="<?php echo esc_attr($copy_right); ?>" class="regular-text">
					</p>

					<!-- クレジット表示チェック -->
					<p>
						<label>
							<input type="checkbox" name="integlight_show_footer_credit" value="1" <?php checked(1, $show_credit, true); ?> />
							<?php echo __("Display 'Powered by WordPress' and theme author credit", "integlight"); ?>
						</label>
					</p>
				</div>

				<?php submit_button(); ?>
			</form>
		</div>
	<?php
	}
}

new InteglightFooterSettings();


// ## クレジット対応 _e//////////////////////////////////////////////////////////////////////////////////

















// 目次_s ////////////////////////////////////////////////////////////////////////////////
// 目次を生成するクラスを定義
class InteglightTableOfContents
{

	// コンストラクタ
	public function __construct()
	{
		add_filter('the_content', array($this, 'add_toc_to_content'));
		add_action('add_meta_boxes', array($this, 'add_toc_visibility_meta_box'));
		add_action('save_post', array($this, 'save_toc_visibility_meta_box_data'));
	}

	// 投稿コンテンツに目次を追加するメソッド
	public function add_toc_to_content($content)
	{



		$hide_toc = get_post_meta(get_the_ID(), 'hide_toc', true);

		if ($hide_toc == '1') {
			return $content;
		}



		// H1, H2, H3タグを抽出
		preg_match_all('/<(h[1-3])([^>]*)>(.*?)<\/\1>/', $content, $matches, PREG_SET_ORDER);

		if (!empty($matches)) {
			// 目次を生成
			$toc = '<div class="post-toc"><B>Index</B><ul>';
			foreach ($matches as $match) {
				$heading_tag = $match[1]; // h1, h2, h3
				$heading_attributes = $match[2]; // クラスやIDなどの属性
				$heading_text = $match[3]; // 見出しのテキスト
				// HタグにIDを追加してクラスを維持
				$id = sanitize_title_with_dashes($heading_text);


				// 目次を作成
				// インデント調整（追加部分）
				$indent = '';
				if ($heading_tag === 'h2') {
					$indent = '&nbsp;&nbsp;'; // H2ならインデント1つ
				} elseif ($heading_tag === 'h3') {
					$indent = '&nbsp;&nbsp;&nbsp;&nbsp;'; // H3ならインデント2つ
				}

				// 目次を作成
				$toc .= '<li class="toc-' . strtolower($heading_tag) . '">' . $indent . '<a href="#' . $id . '">' . strip_tags($heading_text) . '</a></li>';


				$content = str_replace(
					$match[0],
					'<' . $heading_tag . $heading_attributes . ' id="' . $id . '">' . $heading_text . '</' . $heading_tag . '>',
					$content
				);
			}
			$toc .= '</ul></div>';

			// 目次をコンテンツの最初に追加
			$content = $toc . $content;
		}

		return $content;
	}


	public function add_toc_visibility_meta_box()
	{
		$screens = ['post', 'page'];
		add_meta_box(
			'toc_visibility_meta_box', // ID
			__('TOC Visibility', 'integlight'), // タイトル
			array($this, 'render_toc_visibility_meta_box'), // コールバック関数
			$screens, // 投稿タイプ
			'side', // コンテキスト
			'default' // 優先度
		);
	}

	public  function render_toc_visibility_meta_box($post)
	{
		$value = get_post_meta($post->ID, 'hide_toc', true);
		wp_nonce_field('toc_visibility_nonce_action', 'toc_visibility_nonce');
	?>
		<label for="hide_toc">
			<input type="checkbox" name="hide_toc" id="hide_toc" value="1" <?php checked($value, '1'); ?> />
			<?php echo __('Hide TOC', 'integlight'); ?>
		</label>
	<?php

	}

	public  function save_toc_visibility_meta_box_data($post_id)
	{
		if (!isset($_POST['toc_visibility_nonce'])) {
			return;
		}
		if (!wp_verify_nonce(wp_unslash($_POST['toc_visibility_nonce']), 'toc_visibility_nonce_action')) {
			return;
		}
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		if (!current_user_can('edit_post', $post_id)) {
			return;
		}

		$hide_toc = isset($_POST['hide_toc']) ? '1' : '0';
		update_post_meta($post_id, 'hide_toc', $hide_toc);
	}
}

// インスタンスを作成して目次生成を初期化
new InteglightTableOfContents();

// 目次_e ////////////////////////////////////////////////////////////////////////////////



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
		$ul .= '<i class="fa-solid fa-house"></i>';
		$ul .= '<a href="' . home_url() . '">HOME</a>';
		$ul .= '<i class="fa-solid fa-angle-right"></i>';
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
			$output .= '<i class="fa-solid fa-angle-right"></i>';
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
				$output .= '<i class="fa-solid fa-angle-right"></i>'; // ✅ <li> の中ならOK
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
/* SEO用カスタムフィールド（Meta Title / Meta Description）を追加するs*/
/********************************************************************/

// ① 管理画面にメタボックスを追加する
function add_custom_seo_meta_box()
{
	// 対象の投稿タイプを配列で指定（例：投稿と固定ページ）
	$post_types = array('post', 'page');
	foreach ($post_types as $post_type) {
		add_meta_box(
			'seo_meta_box',            // メタボックスのID
			__('Meta data setting(optional)', 'integlight'),                 // メタボックスのタイトル（管理画面に表示される）
			'display_seo_meta_box',    // コールバック関数（メタボックス内のHTML出力）
			$post_type,                // 対象の投稿タイプ
			'normal',                  // 表示位置（normal, side, advanced など）
			'high'                     // 優先度
		);
	}
}
add_action('add_meta_boxes', 'add_custom_seo_meta_box');

// ② メタボックスの内容（入力欄）の出力
function display_seo_meta_box($post)
{
	// セキュリティ用のnonceフィールドを出力
	wp_nonce_field('seo_meta_box_nonce_action', 'seo_meta_box_nonce');

	// 既に保存されている値を取得（なければ空文字）
	$custom_meta_title       = get_post_meta($post->ID, '_custom_meta_title', true);
	$custom_meta_description = get_post_meta($post->ID, '_custom_meta_description', true);
	?>
	<p>
		<label for="custom_meta_title"><strong><?php echo __('Meta Title', 'integlight') ?></strong></label><br>
		<input type="text" name="custom_meta_title" id="custom_meta_title" value="<?php echo esc_attr($custom_meta_title); ?>" style="width:100%;" placeholder="<?php echo __('ex) Improve Your English Speaking | 5 Easy & Effective Tips', 'integlight') ?>">
	</p>
	<p>
		<label for="custom_meta_description"><strong><?php echo __('Meta Description', 'integlight') ?></strong></label><br>
		<textarea name="custom_meta_description" id="custom_meta_description" rows="4" style="width:100%;" placeholder="<?php echo __('ex) Struggling with English speaking? Learn 5 simple and practical tips to boost your fluency and confidence in conversations. Perfect for beginners and intermediate learners!', 'integlight') ?>"><?php echo esc_textarea($custom_meta_description); ?></textarea>
	</p>
<?php
}

// ③ メタボックスに入力されたデータを保存する
function save_seo_meta_box_data($post_id)
{
	// セキュリティチェック：nonceの存在と検証
	if (! isset($_POST['seo_meta_box_nonce']) || ! wp_verify_nonce($wp_unslash(_POST['seo_meta_box_nonce']), 'seo_meta_box_nonce_action')) {
		return;
	}

	// 自動保存時は何もしない
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}

	// ユーザー権限の確認（投稿と固定ページで分岐）
	if (isset($_POST['post_type']) && 'page' === $_POST['post_type']) {
		if (! current_user_can('edit_page', $post_id)) {
			return;
		}
	} else {
		if (! current_user_can('edit_post', $post_id)) {
			return;
		}
	}

	// Meta Title の保存（入力があれば更新、空の場合は空文字でも更新される）
	if (isset($_POST['custom_meta_title'])) {
		update_post_meta($post_id, '_custom_meta_title', sanitize_text_field(wp_unslash($_POST['custom_meta_title'])));
	}

	// Meta Description の保存
	if (isset($_POST['custom_meta_description'])) {
		update_post_meta($post_id, '_custom_meta_description', sanitize_textarea_field(wp_unslash($_POST['custom_meta_description'])));
	}
}
add_action('save_post', 'save_seo_meta_box_data');




/*******title設定***********/
//デフォルト
////シングルサイト：ページのタイトル - 一般設定サイトタイトル
////それ以外：一般設定サイトタイトル - 一般設定キャッチフレーズ
//変更後
////シングルサイト：
///////設定あり：設定値
///////設定なし：何もしない（デフォルト）
////それ以外：何もしない（デフォルト）
function my_custom_document_title($title_parts)
{
	if (is_singular()) {
		global $post;
		// カスタムフィールドから値を取得
		$custom_title = get_post_meta($post->ID, '_custom_meta_title', true);
		if ($custom_title) {
			// ここでカスタムフィールドの値を優先して設定
			$title_parts['title'] = $custom_title;
		} else {
		}
	}
	return $title_parts;
}
add_filter('document_title_parts', 'my_custom_document_title');





/**
 * ヘッダーに meta description タグを出力する関数
 */
function my_custom_meta_description()
{
	if (is_singular()) {
		global $post;
		// カスタムフィールドから値を取得
		$custom_description = get_post_meta($post->ID, '_custom_meta_description', true);

		if (! empty($custom_description)) {
			$meta_description = $custom_description;
		} elseif (has_excerpt($post->ID)) {
			$meta_description = get_the_excerpt($post->ID);
		} else {
			// ユーザー入力も抜粋もない場合は meta description タグを出力しない
			return;
		}
		echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
	}
}
add_action('wp_head', 'my_custom_meta_description');




/********************************************************************/
/* SEO用カスタムフィールド（Meta Title / Meta Description）を追加するe*/
/********************************************************************/



function add_preload_images()
{
?>


	<?php

}
add_action('wp_head', 'add_preload_images');


require get_template_directory() . '/inc/integlight-functions-block.php';
require get_template_directory() . '/inc/integlight-functions-init.php';



/********************************************************************/
/* ブロックテーマへの適用s*/
/********************************************************************/

function integlight_register_block_styles()
{
	register_block_style(
		'core/heading',
		array(
			'name'  => 'no-underline',
			'label' => 'No Underline',
			'inline_style' => '.wp-block-heading.is-style-no-underline::after { display: none !important; }'
		)
	);
}
add_action('init', 'integlight_register_block_styles');


function integlight_register_block_patterns()
{
	if (function_exists('register_block_pattern')) {
		register_block_pattern(
			'integlight/two-columns',
			array(
				'title'       => __('Two Columns', 'integlight'),
				'description' => _x('A layout with two columns for content.', 'Block pattern description', 'integlight'),
				'categories'  => array('columns'),
				'content'     => "<!-- wp:columns -->
<div class=\"wp-block-columns\">
    <!-- wp:column -->
    <div class=\"wp-block-column\"><p>" . __('Column one', 'integlight') . "</p></div>
    <!-- /wp:column -->
    <!-- wp:column -->
    <div class=\"wp-block-column\"><p>" . __('Column two', 'integlight') . "</p></div>
    <!-- /wp:column -->
</div>
<!-- /wp:columns -->",
			)
		);
	}
}
add_action('init', 'integlight_register_block_patterns');

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
class PostHelper
{
	/**
	 * 投稿の画像を取得する（アイキャッチ or 本文の最初の画像）
	 */
	public static function get_post_image($post_id)
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
		$post_title = (mb_strlen($post_title, 'UTF-8') > 14) ? mb_substr($post_title, 0, 14, 'UTF-8') . '...' : $post_title;
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
