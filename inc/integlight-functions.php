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


//## スタイルシート、JSファイルの追加 _s //////////////////////////////////////////////////////
/**
 * Enqueue scripts and styles.
 */
function integlight_scripts_plus()
{

	wp_enqueue_style('integlight-base-style-plus', get_template_directory_uri() . '/css/base-style.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-style-plus', get_template_directory_uri() . '/css/integlight-style.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-sp-style', get_template_directory_uri() . '/css/integlight-sp-style.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-layout', get_template_directory_uri() . '/css/layout.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-integlight-menu', get_template_directory_uri() . '/css/integlight-menu.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-post', get_template_directory_uri() . '/css/post.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-page', get_template_directory_uri() . '/css/page.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-front', get_template_directory_uri() . '/css/front.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-home', get_template_directory_uri() . '/css/home.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-module', get_template_directory_uri() . '/css/module.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-helper', get_template_directory_uri() . '/css/helper.css', array(), _S_VERSION);

	//web fonts: font awsome
	wp_enqueue_style('integlight-awesome', get_template_directory_uri() . '/css/awesome-all.min.css', array(), _S_VERSION);
}
add_action('wp_enqueue_scripts', 'integlight_scripts_plus');
//## スタイルシート、JSファイルの追加 _e //////////////////////////////////////////////////////

//## editor用のスタイルの追加 _s //////////////////////////////////////////////////////////////////////////////////
// 性能劣化のデメリットがあるためOFFにしておくことも検討
function integlight_enqueue_editor_styles()
{


	wp_enqueue_style('integlight-base-style-plus', get_template_directory_uri() . '/css/base-style.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-style-plus', get_template_directory_uri() . '/css/integlight-style.css', array(), _S_VERSION);
	/*
	wp_enqueue_style('integlight-sp-style', get_template_directory_uri() . '/css/integlight-sp-style.css', array(), _S_VERSION);
	*/
	wp_enqueue_style('integlight-layout', get_template_directory_uri() . '/css/layout.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-integlight-menu', get_template_directory_uri() . '/css/integlight-menu.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-post', get_template_directory_uri() . '/css/post.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-page', get_template_directory_uri() . '/css/page.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-front', get_template_directory_uri() . '/css/front.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-home', get_template_directory_uri() . '/css/home.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-module', get_template_directory_uri() . '/css/module.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-helper', get_template_directory_uri() . '/css/helper.css', array(), _S_VERSION);
	//web fonts: font awsome
	wp_enqueue_style('integlight-awesome', get_template_directory_uri() . '/css/awesome-all.min.css', array(), _S_VERSION);
}
add_action('enqueue_block_editor_assets', 'integlight_enqueue_editor_styles');


//editor用のスタイルの追加 _e ////////////////////////////////////////////////////////////////////////////////

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

/********************************************************************/
/*ブロックアイテムの読み込みs*/
/********************************************************************/
function register_theme_blocks()
{
	$blocks = glob(get_template_directory() . '/blocks/*', GLOB_ONLYDIR);
	foreach ($blocks as $block) {
		if (file_exists($block . '/block.json')) {
			register_block_type($block);
		}
	}
}
add_action('init', 'register_theme_blocks');

//Font Awesome 
function add_fontawesome_button_to_toolbar()
{

	wp_enqueue_script(
		'integlight-gfontawesome',
		get_template_directory_uri() . '/blocks/gfontawesome/build/index.js', // ビルドされたファイルを読み込む
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-rich-text'),
		'1.0',
		true
	);
}

add_action('enqueue_block_editor_assets', 'add_fontawesome_button_to_toolbar');


//右寄せサンプルツールバー
function add_right_align_button_to_toolbar()
{
	wp_enqueue_script(
		'custom-right-align-button',
		get_template_directory_uri() . '/blocks/right-align-button/build/index.js', // ビルドされたファイルを読み込む
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
		'1.0',
		true
	);
}

add_action('enqueue_block_editor_assets', 'add_right_align_button_to_toolbar');

/********************************************************************/
/*ブロックアイテムの読み込みe*/
/********************************************************************/

/********************************************************************/
/*font awesome 用のショートコードs*/
/********************************************************************/
function integlight_render_fontawesome_shortcode($atts)
{
	$atts = shortcode_atts(
		array('icon' => ''),
		$atts,
		'fa'
	);

	if (empty($atts['icon'])) {
		return '';
	}

	return '<i class="fas ' . esc_attr($atts['icon']) . '"></i>';
}
add_shortcode('fontawesome', 'integlight_render_fontawesome_shortcode');

/********************************************************************/
/*font awesome 用のショートコードe*/
/********************************************************************/












// ## コピーライト対応 _s//////////////////////////////////////////////////////////////////////////////////
class InteglightCopyRight
{

	public function __construct()
	{
		add_filter('admin_menu', array($this, 'setting'));
	}

	public function setting()
	{
		add_submenu_page('themes.php', 'フッダー設定', 'フッダー', 'manage_options', 'custom_menu_page', array($this, 'setting_menuPage'),  6);
		add_action('admin_init', array($this, 'setting_db'));
	}

	public function setting_db()
	{
		register_setting('custom-menu-group', 'copy_right');
	}


	public function setting_menuPage()
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
}

new InteglightCopyRight();



// ## コピーライト対応 _e//////////////////////////////////////////////////////////////////////////////////




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

				// 見出しにIDを追加
				$id = sanitize_title_with_dashes($heading_text);

				// 目次を作成
				$toc .= '<li class="toc-' . strtolower($heading_tag) . '"><a href="#' . $id . '">' . strip_tags($heading_text) . '</a></li>';

				// HタグにIDを追加してクラスを維持
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
			<?php _e('Hide TOC', 'integlight'); ?>
		</label>
	<?php

	}

	public  function save_toc_visibility_meta_box_data($post_id)
	{
		if (!isset($_POST['toc_visibility_nonce'])) {
			return;
		}
		if (!wp_verify_nonce($_POST['toc_visibility_nonce'], 'toc_visibility_nonce_action')) {
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
		$ul .= '<i class="fa-solid fa-house"></i>';
		$ul .= '<li><a href="' . home_url() . '">HOME</a></li>';
		$ul .= '<i class="fa-solid fa-angle-right"></i>';

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
				$output .= '<li><a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a></li>';
				$output .= '<i class="fa-solid fa-angle-right"></i>';
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
			'Meta data setting(optional)',                 // メタボックスのタイトル（管理画面に表示される）
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
		<label for="custom_meta_title"><strong>Meta Title</strong></label><br>
		<input type="text" name="custom_meta_title" id="custom_meta_title" value="<?php echo esc_attr($custom_meta_title); ?>" style="width:100%;" placeholder="ex) Meta Title">
	</p>
	<p>
		<label for="custom_meta_description"><strong>Meta Description</strong></label><br>
		<textarea name="custom_meta_description" id="custom_meta_description" rows="4" style="width:100%;" placeholder="ex) Meta Description"><?php echo esc_textarea($custom_meta_description); ?></textarea>
	</p>
<?php
}

// ③ メタボックスに入力されたデータを保存する
function save_seo_meta_box_data($post_id)
{
	// セキュリティチェック：nonceの存在と検証
	if (! isset($_POST['seo_meta_box_nonce']) || ! wp_verify_nonce($_POST['seo_meta_box_nonce'], 'seo_meta_box_nonce_action')) {
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
		update_post_meta($post_id, '_custom_meta_title', sanitize_text_field($_POST['custom_meta_title']));
	}

	// Meta Description の保存
	if (isset($_POST['custom_meta_description'])) {
		update_post_meta($post_id, '_custom_meta_description', sanitize_textarea_field($_POST['custom_meta_description']));
	}
}
add_action('save_post', 'save_seo_meta_box_data');

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
			// 入力がなければ投稿タイトル＋サイトタイトルにするなど、自由に処理可能
			$title_parts['title'] = get_the_title($post->ID);
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

		if ($custom_description) {
			// ユーザーがカスタムフィールドに入力している場合、その値を利用
			$meta_description = $custom_description;
		} else {
			// 入力がない場合は、抜粋があれば抜粋を利用、なければ本文から先頭155文字を抽出
			if (has_excerpt($post->ID)) {
				$meta_description = get_the_excerpt($post->ID);
			} else {
				$content = strip_tags($post->post_content);
				$meta_description = mb_substr($content, 0, 155, 'UTF-8');
			}
		}
	} else {
		// 投稿や固定ページ以外の場合はサイト説明を利用
		$meta_description = get_bloginfo('description');
	}

	// meta タグとして出力
	echo '<meta name="description" content="' . esc_attr($meta_description) . '">' . "\n";
}
add_action('wp_head', 'my_custom_meta_description');





/********************************************************************/
/* SEO用カスタムフィールド（Meta Title / Meta Description）を追加するe*/
/********************************************************************/
