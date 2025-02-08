<?php

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

	wp_enqueue_style('integlight-style-plus', get_template_directory_uri() . '/integlight-style.css', array(), _S_VERSION);
	//wp_enqueue_script('integlight-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true);
	wp_enqueue_style('integlight-layout', get_template_directory_uri() . '/css/layout.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-integlight-menu', get_template_directory_uri() . '/css/integlight-menu.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-post', get_template_directory_uri() . '/css/post.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-page', get_template_directory_uri() . '/css/page.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-front', get_template_directory_uri() . '/css/front.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-home', get_template_directory_uri() . '/css/home.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-module', get_template_directory_uri() . '/css/module.css', array(), _S_VERSION);
	wp_enqueue_style('integlight-helper', get_template_directory_uri() . '/css/helper.css', array(), _S_VERSION);

	//web fonts: font awsome
	wp_enqueue_style('integlight-owsome', get_template_directory_uri() . '/css/all.min.css', array(), _S_VERSION);
}
add_action('wp_enqueue_scripts', 'integlight_scripts_plus');
//## スタイルシート、JSファイルの追加 _e //////////////////////////////////////////////////////


//## editor用のスタイルの追加 _s //////////////////////////////////////////////////////////////////////////////////
// 性能劣化のデメリットがあるためOFFにしておくことも検討
function integlight_add_editor_styles()
{
	add_theme_support('editor-styles');
	add_editor_style(get_theme_file_uri('/style.css'));
	add_editor_style(get_theme_file_uri('/integlight-style.css'));
}
add_action('admin_init', 'integlight_add_editor_styles');
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



// ## 横に流れるアニメーションテキスト _s ////////////////////////////////////////////////////



function integlight_register_text_flow_animation_block()
{
	// エディタ用のスクリプトを登録（import文を使わずにグローバル変数経由で記述する場合）
	wp_register_script(
		'text-flow-animation-block',
		get_template_directory_uri() . '/blocks/text-flow-animation/block.js', // スクリプトファイルのパス
		array('wp-blocks', 'wp-element', 'wp-block-editor'), // 必要な依存関係（WPバージョンによっては 'wp-editor'）
		filemtime(get_template_directory() . '/blocks/text-flow-animation/block.js')
	);

	// フロントエンドとエディタ両方で読み込むスタイルを登録
	wp_register_style(
		'text-flow-animation-style',
		get_template_directory_uri() . '/blocks/text-flow-animation/style.css', // CSSファイルのパス
		array(),
		filemtime(get_template_directory() . '/blocks/text-flow-animation/style.css')
	);

	// ブロック自体の登録
	register_block_type('integlight/text-flow-animation', array(
		'editor_script' => 'text-flow-animation-block',
		'style'         => 'text-flow-animation-style',
	));
}
add_action('init', 'integlight_register_text_flow_animation_block');



// ## アニメーションテキスト _e ////////////////////////////////////////////////////

























// ## 横に流れるアニメーション画像 _s ////////////////////////////////////////////////////
function integlight_register_image_flow_animation_block()
{
	// エディタ用スクリプトの登録
	wp_register_script(
		'image-flow-animation-block',
		get_template_directory_uri() . '/blocks/image-flow-animation/block.js', // block.js のパス
		array('wp-blocks', 'wp-element', 'wp-block-editor'),
		filemtime(get_template_directory() . '/blocks/image-flow-animation/block.js')
	);

	// フロントエンド・エディタ共通のスタイル登録
	wp_register_style(
		'image-flow-animation-style',
		get_template_directory_uri() . '/blocks/image-flow-animation/style.css', // style.css のパス
		array(),
		filemtime(get_template_directory() . '/blocks/image-flow-animation/style.css')
	);

	// ブロックの登録
	register_block_type('integlight/image-flow-animation', array(
		'editor_script' => 'image-flow-animation-block',
		'style'         => 'image-flow-animation-style',
	));
}
add_action('init', 'integlight_register_image_flow_animation_block');

// ## 横に流れるアニメーション画像 _e ////////////////////////////////////////////////////