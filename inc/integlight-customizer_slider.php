<?php

// ヘッダー画像セクションのプライオリティをアップする関数 _s 
function integlight_customizer_headerImage_updPriority($wp_customize)
{
	if ($wp_customize->get_section('header_image')) {
		$wp_customize->get_section('header_image')->title = __('Top Header:[Image settings]', 'integlight');
		$wp_customize->get_section('header_image')->priority = 30; // 上に配置される
		$wp_customize->get_section('header_image')->active_callback = function () {
			return get_theme_mod('display_choice', 'slider') === 'image';
		};
	}
}
add_action('customize_register', 'integlight_customizer_headerImage_updPriority');

// ヘッダー画像セクションのプライオリティをアップする関数 _e



// top header slider or Image select  _s ////////////////////////////////////////////////////////////////////////////////


//フロントエンドでの表示制御用
function integlight_display_headerContents()
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


function integlight_customizer_HeaderTypeSelecter($wp_customize)
{
	// 新しいセクションを追加（カスタマイザメニューのトップに表示されるように優先度を低く設定）
	$wp_customize->add_section('integlight_header_section', array(
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
		'section'  => 'integlight_header_section', // 先ほど追加したセクションに追加
		'settings' => 'display_choice',
		'type'     => 'select',
		'choices'  => array(
			'slider' => __('Slider', 'integlight'),
			'image' => __('Image', 'integlight'),
		),
	));
}
add_action('customize_register', 'integlight_customizer_HeaderTypeSelecter');



// top header select  _e ////////////////////////////////////////////////////////////////////////////////







// 見出しセクション作成クラス _s ////////////////////////////////////////////////////////////////////////////////

if (class_exists('WP_Customize_Control') && ! class_exists('integlight_customizer_creBigSection')) {
	class integlight_customizer_creBigSection extends WP_Customize_Control
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
// 見出しセクション作成クラス _e ////////////////////////////////////////////////////////////////////////////////

/* スライダーに表示するテキストにカスタマイザーでユーザーがセットしたスタイルを適用するs */
class integlight_customizer_slider_applyHeaderTextStyle
{

	/**
	 * コンストラクタ
	 */
	public function __construct()
	{
		// wp_head に出力するためのフックを登録
		add_action('wp_head', array($this, 'integlight_slider_applyTextStyles'));
	}

	/**
	 * カスタマイザーの設定値に基づき、.slider .text-overlay のスタイルを出力
	 */
	public function integlight_slider_applyTextStyles()
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


class integlight_customizer_slider_outerAssets
{

	private $pInteglight_slider_settings;

	public function __construct($slider_settings)
	{
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('wp_enqueue_scripts', array($this, 'init_in_wp_enqueue_scripts'));
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
}


class integlight_customizer_slider_setting
{

	private $pInteglight_slider_settings;

	public function __construct($slider_settings)
	{
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('customize_register', array($this, 'setting'));
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

		/* 効果 */
		$this->labelSetting($wp_customize, 'integlight_slider_Animation_heading', __('Slider Animation', 'integlight'));
		$this->effectSetting($wp_customize);
		$this->numberSetting($wp_customize, 'integlight_slider_change_duration', __('Slider Change Duration (seconds)', 'integlight'), 1, 1);

		/* 画像 */
		$this->labelSetting($wp_customize, 'integlight_slider_image_heading', __('Slider Image', 'integlight'));
		$this->imageSetting($wp_customize, 'integlight_slider_image_1', 'Slider Image 1');
		$this->imageSetting($wp_customize, 'integlight_slider_image_2', __('Slider Image 2', 'integlight'));
		$this->imageSetting($wp_customize, 'integlight_slider_image_3', __('Slider Image 3', 'integlight'));

		/* テキスト */
		$this->labelSetting($wp_customize, 'integlight_slider_text_heading', __('Slider Text', 'integlight'));
		$this->textSetting($wp_customize, 'integlight_slider_text_1', __('Slider Text Main', 'integlight'));
		$this->textSetting($wp_customize, 'integlight_slider_text_2', __('Slider Text Sub', 'integlight'));
		$this->colorSetting($wp_customize, 'integlight_slider_text_color', __('Slider Text color', 'integlight'));
		$this->fonttypeSetting($wp_customize);
		$this->labelSetting($wp_customize, 'integlight_slider_text_position_heading', __('Slider Text Position', 'integlight'));
		$this->numberSetting($wp_customize, 'integlight_slider_text_top', __('Slider Text Position Top (px)', 'integlight'), 0, 1);
		$this->numberSetting($wp_customize, 'integlight_slider_text_left', __('Slider Text Position Left (px)', 'integlight'), 0, 1);
		$this->labelSetting($wp_customize, 'integlight_slider_text_position_heading_mobile', __('Slider Text Position Mobile', 'integlight'));
		$this->numberSetting($wp_customize, 'integlight_slider_text_top_mobile', __('Slider Text Position Top Mobile (px)', 'integlight'), 0, 1);
		$this->numberSetting($wp_customize, 'integlight_slider_text_left_mobile', __('Slider Text Position Left Mobile (px)', 'integlight'), 0, 1);
	}

	private function effectSetting($customize)
	{
		$customize->add_setting('integlight_slider_effect', array(
			'default' => 'slide',
			'sanitize_callback' => 'sanitize_text_field',
		));

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

	private function imageSetting($customize, $settingName, $label)
	{
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

	private function textSetting($customize, $settingName, $label)
	{
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

	private function numberSetting($customize, $settingName, $label, $min, $step)
	{
		$customize->add_setting($settingName, array(
			'default' => '1',
			'sanitize_callback' => 'absint',
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

	private function labelSetting($customize, $settingName, $label)
	{
		$customize->add_setting($settingName, array(
			'sanitize_callback' => 'sanitize_text_field',
		));
		$customize->add_control(new integlight_customizer_creBigSection(
			$customize,
			$settingName,
			array(
				'label'   => __($label, 'integlight'),
				'section' => 'slider_section',
			)
		));
	}

	private function fonttypeSetting($customize)
	{
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

	private function colorSetting($customize, $settingName, $label)
	{
		$customize->add_setting($settingName, array(
			'default'           => '#000000',
			'sanitize_callback' => 'sanitize_hex_color',
		));

		$customize->add_control(new WP_Customize_Color_Control(
			$customize,
			$settingName,
			array(
				'label'    => __($label, 'integlight'),
				'section'  => 'slider_section',
				'settings' => $settingName,
			)
		));
	}
}


class integlight_customizer_slider
{


	private $pInteglight_slider_settings;

	public function __construct()
	{


		//グローバルで使う定数を定義
		$GLOBALS['Integlight_slider_settings'] = new stdClass();
		$GLOBALS['Integlight_slider_settings']->optionValueName_fade = 'fade';
		$GLOBALS['Integlight_slider_settings']->optionValueName_slide = 'slide';
		$GLOBALS['Integlight_slider_settings']->optionValueName_none = 'none';

		global $Integlight_slider_settings;
		$this->pInteglight_slider_settings = $Integlight_slider_settings;
		// クラスのインスタンスを生成して処理を開始
		new integlight_customizer_slider_applyHeaderTextStyle();
		new integlight_customizer_slider_outerAssets($this->pInteglight_slider_settings);
		new integlight_customizer_slider_setting($this->pInteglight_slider_settings);
	}
}

$InteglightSlider = new integlight_customizer_slider();

// slide customiser _e ////////////////////////////////////////////////////////////////////////////////
