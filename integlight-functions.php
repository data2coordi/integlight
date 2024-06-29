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
	wp_enqueue_style('integlight-post', get_template_directory_uri() . '/css/post.css', array(), _S_VERSION);

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
	private $pTitle;

	public function __construct()
	{
		add_filter('the_title', [$this, 'add_breadcrumb_to_title'], 10, 2);
		add_filter('get_the_archive_title', [$this, 'add_breadcrumb_to_archive_title']);
	}

	public function add_breadcrumb_to_title($title)
	{

		if (!is_front_page() && (is_page() || is_single()) && in_the_loop() && is_main_query()) {
			return $this->generate_breadcrumb($title) ;
		}
		return $title;
	}

	public function add_breadcrumb_to_archive_title($title)
	{
		if (!is_front_page() && is_archive() && is_main_query()) {
			return $this->generate_breadcrumb($title) ;
		}
		return $title;
	}


	private function helper_addUl($breadcrumbData)
	{
		$ul = '<ul class="create_bread">';
		$ul .= '<i class="fa-solid fa-house"></i>';
		$ul .= '<li><a href="' . home_url() . '">HOME</a></li>';
		$ul .= '<i class="fa-solid fa-angle-right"></i>';

		return $ul . $breadcrumbData .  '</ul>';
	}

	private function generate_breadcrumb($title)
	{
		$this->pTitle = $title;
		$output = '';
		if (is_category() || is_tag()) {
			$output .= $this->get_category_tag_breadcrumb();
		} elseif (is_archive()) {
			$output .= '<li>' . single_term_title('', false) . '</li>';
		} elseif (is_single()) {
			$output .= $this->get_single_breadcrumb();
		} elseif (is_page()) {
			$output .= '<li>' . $this->pTitle . '</li>';
		} elseif (is_404()) {
			$output .= '<li>ページが見つかりません</li>';
		}

		return $this->helper_addUl($output) . $title;
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

		return $output . '<li>' . $this->pTitle . '</li>';
	}
}

// インスタンスを作成して初期化
new InteglightBreadcrumb();

// ## パンくずリスト _e //////////////////////////////////////////////////////////
