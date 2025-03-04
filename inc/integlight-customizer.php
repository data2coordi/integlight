<?php

/**
 * Integlight Theme Customizer
 *
 * @package Integlight
 */


// top header slider or Image select  _s ////////////////////////////////////////////////////////////////////////////////
function integlight_customize_register_topHeader($wp_customize)
{
	// 新しいセクションを追加（カスタマイザメニューのトップに表示されるように優先度を低く設定）
	$wp_customize->add_section('integlight_custom_section', array(
		'title'    => __('Top Header:[Select - Slider or Image]', 'integlight'),
		'priority' => 28, // 優先度を1にしてトップに表示
	));

	// 選択ボックスを追加
	$wp_customize->add_setting('display_choice', array(
		'default' => 'header',
		'sanitize_callback' => 'sanitize_text_field',
	));

	$wp_customize->add_control('display_choice', array(
		'label'    => __('Display Slider or Image', 'integlight'),
		'section'  => 'integlight_custom_section', // 先ほど追加したセクションに追加
		'settings' => 'display_choice',
		'type'     => 'select',
		'choices'  => array(
			'slider' => __('Slider', 'integlight'),
			'image' => __('Image', 'integlight'),
		),
	));
}
add_action('customize_register', 'integlight_customize_register_topHeader');



function integlight_display_slider_or_image()
{
	$choice = get_theme_mod('display_choice', 'slider');

	if ('slider' === $choice) {
		// スライダーを表示
		get_template_part('template-parts/content', 'slide');
	} else {
		// ヘッダー画像を表示
		if (get_header_image()) {
			echo '<img src="' . esc_url(get_header_image()) . '" class="topImage" ' .  ' alt="' . esc_attr(get_bloginfo('name')) . '">';
		}
	}
}

// top header select  _e ////////////////////////////////////////////////////////////////////////////////



// ヘッダー画像セクションのプライオリティをアップする関数 _s 
function integlight_customize_header_priority($wp_customize)
{
	if ($wp_customize->get_section('header_image')) {
		$wp_customize->get_section('header_image')->title = __('Top Header:[Image settings]', 'integlight');
		$wp_customize->get_section('header_image')->priority = 30; // 上に配置される
		$wp_customize->get_section('header_image')->active_callback = function () {
			return get_theme_mod('display_choice', 'slider') === 'image';
		};
	}
}
add_action('customize_register', 'integlight_customize_header_priority');

// ヘッダー画像セクションのプライオリティをアップする関数 _e



// slide customiser _s ////////////////////////////////////////////////////////////////////////////////

if (class_exists('WP_Customize_Control') && ! class_exists('Simple_Customize_Heading_Control')) {
	class Simple_Customize_Heading_Control extends WP_Customize_Control
	{
		public $type = 'heading';
		public function render_content()
		{
			if (! empty($this->label)) {
				echo '<h3 style="border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px;">' . esc_html($this->label) . '</h3>';
			}
		}
	}
}

/* スライダーに表示するテキストs */
class Integlight_Slider_Customizer_Style
{

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		// wp_head に出力するためのフックを登録
		add_action('wp_head', array($this, 'output_custom_slider_styles'));
	}

	/**
	 * カスタマイザーの設定値に基づき、.slider .text-overlay のスタイルを出力
	 */
	public function output_custom_slider_styles()
	{
		// カスタマイザーから値を取得。未設定の場合はデフォルト値を使用
		$color = get_theme_mod('integlight_slider_text_color', '#ffffff'); // デフォルトは白
		$left  = get_theme_mod('integlight_slider_text_left', 30);      // デフォルト 30px
		$top   = get_theme_mod('integlight_slider_text_top', 300);       // デフォルト 300px
		$left_mobile  = get_theme_mod('integlight_slider_text_left_mobile', 20);      // デフォルト 30px
		$top_mobile   = get_theme_mod('integlight_slider_text_top_mobile', 200);       // デフォルト 300px
		// フォント選択の取得（デフォルトは 'yu_gothic'）

		$font = get_theme_mod('integlight_slider_text_font', 'yu_gothic');
		switch ($font) {
			case 'yu_mincho':
				// 游明朝の場合の font-family
				$font_family = 'Yu Mincho, 游明朝体, serif';
				break;
			case 'yu_gothic':
			default:
				// 游ゴシックの場合の font-family
				$font_family = 'Yu Gothic, 游ゴシック体, sans-serif';
				break;
		}


?>
		<style type="text/css">
			.slider .text-overlay {
				position: absolute;
				left: <?php echo absint($left); ?>px;
				top: <?php echo absint($top); ?>px;
				color: <?php echo esc_attr($color); ?>;
			}

			.slider .text-overlay h1 {
				font-family: <?php echo esc_attr($font_family); ?>;
			}

			@media only screen and (max-width: 767px) {
				.slider .text-overlay {
					position: absolute;
					left: <?php echo absint($left_mobile); ?>px;
					top: <?php echo absint($top_mobile); ?>px;
				}
			}
		</style>
<?php
	}
}
/* スライダーに表示するテキストe */



class InteglightSlide
{


	private $pInteglight_slider_settings;

	public function __construct()
	{
		add_action('customize_register', array($this, 'setting'));

		add_action('wp_enqueue_scripts', array($this, 'init_in_wp_enqueue_scripts'));
		$GLOBALS['Integlight_slider_settings'] = new stdClass();
		$GLOBALS['Integlight_slider_settings']->optionValueName_fade = 'fade';
		$GLOBALS['Integlight_slider_settings']->optionValueName_slide = 'slide';
		$GLOBALS['Integlight_slider_settings']->optionValueName_none = 'none';
		global $Integlight_slider_settings;
		$this->pInteglight_slider_settings = $Integlight_slider_settings;
		// クラスのインスタンスを生成して処理を開始
		new Integlight_Slider_Customizer_Style();
	}

	public function init_in_wp_enqueue_scripts()
	{

		wp_enqueue_style('integlight-slide', get_template_directory_uri() . '/css/integlight-slide-style.css', array(), _S_VERSION);
		wp_enqueue_script('jquery');
		wp_enqueue_script('integlight_slider-script', get_template_directory_uri() . '/js/integlight-scripts.js', array('jquery'), _S_VERSION, true);
		// カスタマイザーの設定値をJavaScriptに渡す
		wp_localize_script('integlight_slider-script', 'integlight_sliderSettings', array(
			'changeDuration' => get_theme_mod('integlight_slider_change_duration', '3'),
			'effect' => get_theme_mod('integlight_slider_effect', $this->pInteglight_slider_settings->optionValueName_none),
			'fade' => $this->pInteglight_slider_settings->optionValueName_fade,
			'slide' => $this->pInteglight_slider_settings->optionValueName_slide
		));
	}


	private function effect($customize)
	{
		// 効果設定を追加
		$customize->add_setting('integlight_slider_effect', array(
			'default' => 'slide',
			'sanitize_callback' => 'sanitize_text_field',
		));

		// セレクトボックスのコントロールを追加
		$customize->add_control('integlight_slider_effect', array(
			'label'    => __('Effect', 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'select',
			'choices'  => array(
				$this->pInteglight_slider_settings->optionValueName_fade  => __('Fade', 'integlight'),
				$this->pInteglight_slider_settings->optionValueName_slide => __('Slide', 'integlight'),
				$this->pInteglight_slider_settings->optionValueName_none => __('None', 'integlight'),
			),
		));
	}

	private function image($customize, $settingName, $label)
	{
		// スライダー画像1を追加
		$customize->add_setting($settingName, array(
			'default' => '',
			'sanitize_callback' => 'esc_url_raw',
		));

		$customize->add_control(new WP_Customize_Image_Control($customize, $settingName, array(
			'label'    => __($label, 'integlight'),
			'section'  => 'slider_section',
			'settings' => $settingName,
		)));
	}

	private function text($customize, $settingName, $label)
	{
		// スライダーテキスト1を追加
		$customize->add_setting($settingName, array(
			'default' => $label,
			'sanitize_callback' => 'sanitize_textarea_field',
		));
		$customize->add_control($settingName, array(
			'label'    => __($label, 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'textarea',
		));
	}

	private function number($customize, $settingName, $label, $min, $step)
	{

		// スライド切り替え時間
		$customize->add_setting($settingName, array(
			'default' => '1',
			'sanitize_callback' => 'absint', // 数値をサニタイズ
		));

		$customize->add_control($settingName, array(
			'label'    => __($label, 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'number',
			'input_attrs' => array(
				'min' => $min,
				'step' => $step,
			),
		));
	}

	//利用しないように変更
	private function fadeDurationTime($customize)
	{
		// フェード時間の設定
		$customize->add_setting('integlight_slider_fade_duration', array(
			'default' => '0.8',
			'sanitize_callback' => 'absint', // 数値をサニタイズ
		));

		$customize->add_control('integlight_slider_fade_duration', array(
			'label'    => __('Slider Fade Duration (seconds)', 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'number',
			'input_attrs' => array(
				'min' => 0.1,
				'step' => 0.1,
			),
		));
	}

	private function label($customize, $settingName, $label)
	{

		// --- 親ラベル（見出し）を追加s ---
		$customize->add_setting($settingName, array(
			'sanitize_callback' => 'sanitize_text_field',
		));
		$customize->add_control(new Simple_Customize_Heading_Control(
			$customize,
			$settingName,
			array(
				'label'   => __($label, 'integlight'),
				'section' => 'slider_section',
			)
		));
		// --- 親ラベル（見出し）を追加e ---

	}

	private function fonttype($customize)
	{

		// カスタマイザー設定追加の例（既存のカスタマイザー登録コードに追加）
		$customize->add_setting('integlight_slider_text_font', array(
			'default'           => 'yu_gothic',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$customize->add_control('integlight_slider_text_font', array(
			'label'    => __('Slider Text Font', 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'select',
			'choices'  => array(
				'yu_gothic' => __('yu gothic', 'integlight'),
				'yu_mincho' => __('yu mincho', 'integlight'),
			),
		));
	}
	private function color($customize, $settingName, $label)
	{

		// 色選択の設定を追加
		$customize->add_setting($settingName, array(
			'default'           => '#000000', // デフォルトは黒
			'sanitize_callback' => 'sanitize_hex_color', // HEX形式の文字列をサニタイズ
		));

		$customize->add_control(new WP_Customize_Color_Control(
			$customize,
			$settingName,
			array(
				'label'    => __($label, 'integlight'),
				'section'  => 'slider_section',
				'settings' => 'integlight_slider_text_color',
			)
		));
	}


	public function setting($wp_customize)
	{



		// セクションを追加
		$wp_customize->add_section('slider_section', array(
			'title'    => __('Top Header:[Slider Settings]', 'integlight'),
			'priority' => 29,
			'active_callback' => function () {
				return get_theme_mod('display_choice', 'slider') === 'slider';
			},
		));

		/*画像*/
		$this->effect($wp_customize);
		$this->image($wp_customize, 'integlight_slider_image_1', __('Slider Image 1', 'integlight'));
		$this->image($wp_customize, 'integlight_slider_image_2', __('Slider Image 2', 'integlight'));
		$this->image($wp_customize, 'integlight_slider_image_3', __('Slider Image 3', 'integlight'));
		$this->number($wp_customize, 'integlight_slider_change_duration', __('Slider Change Duration (seconds)', 'integlight'), 1, 1);

		/*テキスト*/
		$this->label($wp_customize, 'integlight_slider_text_heading', __('Slider Text', 'integlight'));
		$this->text($wp_customize, 'integlight_slider_text_1', __('Slider Text Main', 'integlight'));
		$this->text($wp_customize, 'integlight_slider_text_2', __('Slider Text Sub', 'integlight'));
		$this->color($wp_customize, 'integlight_slider_text_color', __('Slider Text color', 'integlight'));
		$this->fonttype($wp_customize);
		$this->label($wp_customize, 'integlight_slider_text_position_heading', __('Slider Text Position', 'integlight'));
		$this->number($wp_customize, 'integlight_slider_text_top', __('Slider Text Position Top (px)', 'integlight'), 0, 1);
		$this->number($wp_customize, 'integlight_slider_text_left', __('Slider Text Position Left (px)', 'integlight'), 0, 1);
		$this->label($wp_customize, 'integlight_slider_text_position_heading_mobile', __('Slider Text Position Mobile', 'integlight'));
		$this->number($wp_customize, 'integlight_slider_text_top_mobile', __('Slider Text Position Top Mobile (px)', 'integlight'), 0, 1);
		$this->number($wp_customize, 'integlight_slider_text_left_mobile', __('Slider Text Position Left Mobile (px)', 'integlight'), 0, 1);
		//利用しないように変更
		//$this->fadeDurationTime($wp_customize);

	}
}

$InteglightSlide = new InteglightSlide();

// slide customiser _e ////////////////////////////////////////////////////////////////////////////////



// side bar position _s ////////////////////////////////////////////////////////////////////////////////
class integlightCustomizeRegisterSidebar
{

	public function __construct()
	{
		add_action('customize_register', array($this, 'customize_register_sidebar'));
	}

	private function helper_setting($wp_customize, $no)
	{

		// サイドバー位置設定の追加
		$wp_customize->add_setting('integlight_sidebar' . $no . '_position', array(
			'default' => 'right',
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
			'title' => __('Sidebar Settings', 'integlight'),
			'priority' => 30,
		));

		$this->helper_setting($wp_customize, '1');
		$this->helper_setting($wp_customize, '2');
	}


	// サイドバー位置の入力を検証する
	public function sanitize_sidebar_position($input)
	{
		return $input;
	}
}

new integlightCustomizeRegisterSidebar();


// side bar position _e ////////////////////////////////////////////////////////////////////////////////


// ## 配色カスタマイズ _s /////////////////////////////////////////////

class InteglightThemeCustomize
{
	public function __construct()
	{
		add_action('customize_register', [$this, 'customize_register']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_css']);
	}

	public function customize_register($wp_customize)
	{
		$wp_customize->add_section('base_pattern_section', array(
			'title'       => __('Base color pattern', 'integlight'),
			'priority'    => 30,
			'description' => __('The base color pattern you select will be reflected throughout the site.', 'integlight'),
		));

		// Setting
		$wp_customize->add_setting('base_color_setting', array(
			'type'              => 'theme_mod',
			'sanitize_callback' => [$this, 'sanitize_choices'],
		));

		// Control
		$wp_customize->add_control('base_color_setting', array(
			'section'     => 'base_pattern_section',
			'settings'    => 'base_color_setting',
			'label'       => 'Base color setting',
			'description' => 'Select favorite base color',
			'type'        => 'radio',
			'choices'     => array(
				'pattern1' => 'None',
				'pattern2' => 'Blue',
				'pattern3' => 'Green',
				'pattern4' => 'Orange',
				'pattern5' => 'Red',
				'pattern6' => 'Pink',
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

	public function enqueue_custom_css()
	{
		$base_pattern = get_theme_mod('base_color_setting', 'pattern1');
		wp_enqueue_style('custom-pattern', get_template_directory_uri() . '/css/' . $base_pattern . '.css', array(), '1.0.0');
	}
}

// インスタンスを作成して初期化
new InteglightThemeCustomize();


// ## 配色カスタマイズ _e /////////////////////////////////////////////
