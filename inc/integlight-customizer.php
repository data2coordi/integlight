<?php


// 見出しセクション作成クラス _s ////////////////////////////////////////////////////////////////////////////////

/**
 * カスタムカスタマイザーコントロールクラスを定義する関数
 * customize_register フックの早い段階で実行される
 */
function integlight_define_custom_controls()
{
	// integlight_customizer_creBigTitle クラスがまだ定義されていない場合のみ定義
	if (! class_exists('integlight_customizer_creBigTitle')) {
		// この時点では customize_register フック内なので WP_Customize_Control は存在するはず
		// 念のため存在確認を追加しても良い
		if (class_exists('WP_Customize_Control')) {
			class integlight_customizer_creBigTitle extends WP_Customize_Control
			{
				public $type = 'heading';
				public function render_content()
				{
					if (! empty($this->label)) {
						echo '<h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px;">' . esc_html($this->label) . '</h3>';
					}
					if (!empty($this->description)) {
						echo '<span class="description customize-control-description">' . esc_html($this->description) . '</span>';
					}
				}
			}
		} else {
			// WP_Customize_Control が見つからない場合のエラーログ（通常は発生しないはず）
			trigger_error('WP_Customize_Control not found when trying to define integlight_customizer_creBigTitle.', E_USER_WARNING);
		}
	}
	// 他にもカスタムコントロールがあればここに追加定義できます
}
// setting メソッドよりも早い優先度 (例: 9) でクラス定義関数をフック
add_action('customize_register', 'integlight_define_custom_controls', 9);
// 見出しセクション作成クラス _e ////////////////////////////////////////////////////////////////////////////////



// パネル上のセクションボタンに説明追加クラス _s ////////////////////////////////////////////////////////////////////////////////

class Integlight_Customizer_Section_Description
{
	private string $section_id;
	private string $description;

	public function __construct(string $section_id, string $description)
	{
		$this->section_id = $section_id;
		$this->description = $description;

		// PHP 側：既存セクションの description を空にする
		add_action('customize_register', [$this, 'clear_section_description']);

		// JS 側：タイトル上に説明文を挿入
		add_action('customize_controls_print_footer_scripts', [$this, 'inject_description_script']);
	}

	public function clear_section_description($wp_customize)
	{
		if ($wp_customize->get_section($this->section_id)) {
			$wp_customize->get_section($this->section_id)->description = '';
		}
	}

	public function inject_description_script()
	{
?>
		<script>
			jQuery(document).ready(function($) {
				var section = $('#accordion-section-<?php echo esc_js($this->section_id); ?>');
				if (section.length && !section.find('.custom-section-description').length) {
					var descriptionHTML = '<div class="custom-section-description" style="margin-bottom:6px;color:#555;font-size:13px;"><?php echo wp_kses_post($this->description); ?></div>';
					section.find('h3').first().before(descriptionHTML);
				}
			});
		</script>
		<style>
			#accordion-section-<?php echo esc_js($this->section_id); ?>.custom-section-description {
				line-height: 1.4;
			}
		</style>
<?php
	}
}
// パネル上のセクションボタンに説明追加クラス _e ////////////////////////////////////////////////////////////////////////////////


/**
 * Integlight Theme Customizer
 *
 * @package Integlight
 */


// side bar position _s ////////////////////////////////////////////////////////////////////////////////
class integlight_customizer_sidebar
{

	public function __construct()
	{
		add_action('customize_register', array($this, 'customize_register_sidebar'));
	}

	private function helper_setting($wp_customize, $no, $defPosition)
	{

		// サイドバー位置設定の追加
		$wp_customize->add_setting('integlight_sidebar' . $no . '_position', array(
			'default' => $defPosition,
			'sanitize_callback' => array($this, 'sanitize_sidebar_position'),
		));

		// サイドバー位置オプションの追加
		$wp_customize->add_control('integlight_sidebar' . $no . '_position_control', array(
			'label' => __('Sidebar', 'integlight') . $no . ' ' . __('Position', 'integlight'),
			'section' => 'integlight_sidebar_section',
			'settings' => 'integlight_sidebar' . $no . '_position',
			'type' => 'radio',
			'choices' => array(
				'right' => __('Right', 'integlight'),
				'left' => __('Left', 'integlight'),
				'bottom' => __('Bottom', 'integlight'),
				'none' => __('None', 'integlight'),
			),
		));
	}

	public function customize_register_sidebar($wp_customize)
	{



		// サイドバー位置セクションの追加
		$wp_customize->add_section('integlight_sidebar_section', array(
			'title' => __('Sidebar Position Settings', 'integlight'),
			'priority' => 30,
			'panel' => 'integlight_sidebar_panel'
		));

		$this->helper_setting($wp_customize, '1', 'right');
		$this->helper_setting($wp_customize, '2', 'none');
	}


	// サイドバー位置の入力を検証する
	public function sanitize_sidebar_position($input)
	{
		return $input;
	}
}

new integlight_customizer_sidebar();


// side bar position _e ////////////////////////////////////////////////////////////////////////////////


// ## 配色カスタマイズ _s /////////////////////////////////////////////

class integlight_customizer_themeColor
{
	public function __construct()
	{
		add_action('customize_register', [$this, 'customize_register']);
	}

	public function customize_register($wp_customize)
	{

		// Setting
		$wp_customize->add_setting('integlight_base_color_setting', array(
			'type'              => 'theme_mod',
			'default'           => 'pattern8',
			'sanitize_callback' => [$this, 'sanitize_choices'],
		));

		// Control
		$wp_customize->add_control('integlight_base_color_setting', array(
			'section'     => 'colors', //既存の色セクションに追加
			'settings'    => 'integlight_base_color_setting',
			'label'       => __('Accent color setting', 'integlight'),
			'description' => __('Select favorite accent color', 'integlight'),
			'type'        => 'radio',
			'choices'     => array(
				'pattern8' => __('Navy', 'integlight'),
				'pattern7' => __('Khaki', 'integlight'),
				'pattern5' => __('Purple', 'integlight'),
				'pattern2' => __('Blue', 'integlight'),
				'pattern3' => __('Green', 'integlight'),
				'pattern4' => __('Orange', 'integlight'),
				'pattern6' => __('Pink', 'integlight'),
				'pattern1' => __('None', 'integlight'),
			),
		));
	}

	public function sanitize_choices($input, $setting)
	{
		global $wp_customize;
		$control = $wp_customize->get_control($setting->id);
		if (array_key_exists($input, $control->choices)) {
			return $input;
		} else {
			return $setting->default;
		}
	}
}

// インスタンスを作成して初期化
new integlight_customizer_themeColor();


class integlight_customizer_HomeType
{
	public function __construct()
	{
		add_action('customize_register', [$this, 'customize_register']);
	}

	public function customize_register($wp_customize)
	{

		// サイドバー位置セクションの追加
		$wp_customize->add_section('integlight_hometype_section', array(
			'title' => __('Site Type Settings', 'integlight'),
			'priority' => 29,
			'panel' => 'integlight_site_panel'
		));

		// Setting
		$wp_customize->add_setting('integlight_hometype_setting', array(
			'type'              => 'theme_mod',
			'default'           => 'home1',
			'sanitize_callback' => [$this, 'sanitize_choices'],
		));

		// Control
		$wp_customize->add_control('integlight_hometype_setting', array(
			'section'     => 'integlight_hometype_section',
			'settings'    => 'integlight_hometype_setting',
			'label'       => __('Site Type Settings', 'integlight'),
			'description' => __('Select favorite site type', 'integlight'),
			'type'        => 'radio',
			'choices'     => array(
				'home1' => __('Elegant', 'integlight'),
				'home2' => __('Pop', 'integlight'),
				//'home3' => __('home3', 'integlight'),
				//'home4' => __('home4', 'integlight'),
			),
		));
	}

	public function sanitize_choices($input, $setting)
	{
		global $wp_customize;
		$control = $wp_customize->get_control($setting->id);
		if (array_key_exists($input, $control->choices)) {
			return $input;
		} else {
			return $setting->default;
		}
	}
}

// インスタンスを作成して初期化
new integlight_customizer_HomeType();




// ## フッター クレジット設定 _s /////////////////////////////////////////////
class Integlight_Customizer_Footer
{
	public function __construct()
	{
		add_action('customize_register', array($this, 'register'));
	}



	private function copy_right($wp_customize)
	{

		// サイドバー位置セクションの追加
		$wp_customize->add_section('integlight_copyright_section', array(
			'title' => __('コピーライト設定', 'integlight'),
			'priority' => 29,
			'panel' => 'integlight_footer_panel'
		));
		// コピーライト設定
		$wp_customize->add_setting('integlight_footer_copy_right', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$wp_customize->add_control('integlight_footer_copy_right', array(
			'label'    => __('Copyright Settings', 'integlight'),
			'section'  => 'integlight_copyright_section',
			'type'     => 'text',
		));

		// クレジット表示チェックボックス
		$wp_customize->add_setting('integlight_footer_show_credit', array(
			'default'           => true,
			'sanitize_callback' => array($this, 'sanitize_checkbox'),
		));

		$wp_customize->add_control('integlight_footer_show_credit', array(
			'label'    => __("Display 'Powered by WordPress' and theme author credit", 'integlight'),
			'section'  => 'integlight_copyright_section',
			'type'     => 'checkbox',
		));
	}
	public function register($wp_customize)
	{

		$this->copy_right($wp_customize);
	}

	public function sanitize_checkbox($checked)
	{
		return (isset($checked) && true == $checked) ? true : false;
	}
}

// 初期化
new Integlight_Customizer_Footer();

// ## フッター クレジット設定 _e /////////////////////////////////////////////

//////////////////////////////////////////
//////////////////////////////////////////
//////////////////////////////////////////
//////////////////////////////////////////
//////////////////////////////////////////

/**
 * Integlight: Simple customizer rearrange
 * シンプル化版：コアのセクションを独自パネルに移動する（子テーマ/プラグインは下に追加される前提）
 */

class Integlight_Customizer_Simple
{
	/** @var array */
	private $panels;

	/** @var array section_id => [ 'panel' => panel_id, 'title' => '任意名' ] */
	private $map;

	public function __construct()
	{
		$this->panels = $this->default_panels();
		$this->map = $this->default_map();

		add_action('customize_register', [$this, 'register_panels'], 15);
		add_action('customize_register', [$this, 'apply_mapping'], 20);
	}

	private function default_panels()
	{
		$site_panel_desc = __('サイト全体の設定', 'integlight');
		return [
			'integlight_site_panel'   => ['title' => __('サイト設定', 'integlight'), 'priority' => 10, 'description' => $site_panel_desc],
			'integlight_menu_panel'   => ['title' => __('メニュー設定', 'integlight'), 'priority' => 20],
			'integlight_header_panel' => ['title' => __('ヘッダー設定', 'integlight'), 'priority' => 30],
			'integlight_sidebar_panel' => ['title' => __('サイドバー設定', 'integlight'), 'priority' => 40],
			'integlight_footer_panel' => ['title' => __('フッター設定', 'integlight'), 'priority' => 50],
			'integlight_design_panel'  => ['title' => __('デザイン設定', 'integlight'), 'priority' => 400],
			'integlight_perf_panel'   => ['title' => __('パフォーマンス設定', 'integlight'), 'priority' => 500],
		];
	}

	private function default_map()
	{
		return [
			// コアの section を独自パネルへ移動（ID は変えない）
			'title_tagline'      => ['panel' => 'integlight_site_panel',   'title' => __('サイト基本情報', 'integlight')],
			'static_front_page'  => ['panel' => 'integlight_site_panel',   'title' => __('ホームページ設定', 'integlight')],
			'colors'             => ['panel' => 'integlight_design_panel',  'title' => __('配色', 'integlight')],
			'background_image'   => ['panel' => 'integlight_design_panel',  'title' => __('背景画像', 'integlight')],
			'custom_css'         => ['panel' => 'integlight_design_panel',  'title' => __('追加CSS', 'integlight')],
			// nav_menus等はパネル -> それに属するセクションを移動する形で扱う
			'header_image'       => ['panel' => 'integlight_header_panel', 'title' => __('ヘッダー画像', 'integlight')],
			'nav_menus'          => ['panel' => 'integlight_menu_panel',   'title' => __('メニュー', 'integlight')],
			'widgets'            => ['panel' => 'integlight_sidebar_panel', 'title' => __('ウィジェット', 'integlight')],
		];
	}

	public function register_panels($wp_customize)
	{
		foreach ($this->panels as $id => $args) {
			if (! $wp_customize->get_panel($id)) {
				$wp_customize->add_panel($id, array_merge(['capability' => 'edit_theme_options'], $args));
			}
		}
	}
	public function apply_mapping($wp_customize)
	{
		foreach ($this->map as $core_id => $target) {
			// セクションが存在する場合
			$section = $wp_customize->get_section($core_id);
			if ($section) {
				$section->panel = $target['panel'];
				$section->title = $target['title'];
				continue;
			}

			// パネル（メニュー）の場合
			$panel = $wp_customize->get_panel($core_id);
			if ($panel) {
				foreach ($wp_customize->sections() as $s_id => $s_obj) {
					if (isset($s_obj->panel) && $s_obj->panel === $core_id) {
						$s_obj->panel = $target['panel'];
					}
				}
				if ($core_id != 'nav_menus') { //'nav_menus'を削除すると全て消えるため。おそらく不具合
					$wp_customize->remove_panel($core_id);
				}
				continue;
			}
		}
	}
}

// init
new Integlight_Customizer_Simple();

// class Integlight_Customizer_Widgets
// {
// 	/** @var string 移動先パネルID */
// 	private $panel_id;

// 	public function __construct($target_panel_id)
// 	{
// 		$this->panel_id = $target_panel_id;
// 		add_action('customize_register', [$this, 'move_widgets_panel'], 20);
// 	}

// 	public function move_widgets_panel($wp_customize)
// 	{
// 		// widgets パネルが存在する場合
// 		$widgets_panel = $wp_customize->get_panel('widgets');
// 		if ($widgets_panel) {
// 			// widgets パネル配下のすべてのセクションを移動
// 			foreach ($wp_customize->sections() as $section) {
// 				if (isset($section->panel) && $section->panel === 'widgets') {
// 					//	$section->panel = $this->panel_id;
// 				}
// 			}

// 			// widgets パネル自体を非表示にする
// 			//$wp_customize->remove_panel('widgets');
// 		}
// 	}
// }

// 使用例
//new Integlight_Customizer_Widgets('integlight_sidebar_panel');
