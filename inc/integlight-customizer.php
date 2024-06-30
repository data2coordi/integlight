<?php

/**
 * Integlight Theme Customizer
 *
 * @package Integlight
 */




// slide customiser _s ////////////////////////////////////////////////////////////////////////////////


class InteglightSlide
{

	private $themePrefix = 'integlight_slide_';
	public function __construct()
	{
		add_action('customize_register', array($this, 'setting'));

		add_action('wp_enqueue_scripts', array($this, 'init_in_wp_enqueue_scripts'));
	}



	public function init_in_wp_enqueue_scripts()
	{

		wp_enqueue_style('integlight-slide', get_template_directory_uri() . '/css/integlight-slide-style.css', array(), _S_VERSION);
		wp_enqueue_script('jquery');
		wp_enqueue_script('integlight_slider-script', get_template_directory_uri() . '/js/integlight-scripts.js', array('jquery'), _S_VERSION, true);
		// カスタマイザーの設定値をJavaScriptに渡す
		wp_localize_script('integlight_slider-script', 'integlight_sliderSettings', array(
			//利用しないように変更
			//'fadeDuration' => get_theme_mod('integlight_slider_fade_duration', '3'),
			'changeDuration' => get_theme_mod('integlight_slider_change_duration', '3'),
			'effect' => get_theme_mod('integlight_effect', 'fade')
		));
	}





	private function effect($customize)
	{
		// 効果設定を追加
		$customize->add_setting('integlight_effect', array(
			'default' => 'slide',
			'sanitize_callback' => 'sanitize_text_field',
		));

		// セレクトボックスのコントロールを追加
		$customize->add_control('integlight_effect', array(
			'label'    => __('Effect', 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'select',
			'choices'  => array(
				'fade'  => __('Fade', 'integlight'),
				'slide' => __('Slide', 'integlight'),
				'none' => __('None', 'integlight'),
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
			'sanitize_callback' => 'sanitize_text_field',
		));
		$customize->add_control($settingName, array(
			'label'    => __($label, 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'textarea',
		));
	}

	private function changingTime($customize)
	{

		// スライド切り替え時間
		$customize->add_setting('integlight_slider_change_duration', array(
			'default' => '1',
			'sanitize_callback' => 'absint', // 数値をサニタイズ
		));

		$customize->add_control('integlight_slider_change_duration', array(
			'label'    => __('Slider Change Duration (seconds)', 'integlight'),
			'section'  => 'slider_section',
			'type'     => 'number',
			'input_attrs' => array(
				'min' => 1,
				'step' => 1,
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

	public function setting($wp_customize)
	{

		// セクションを追加
		$wp_customize->add_section('slider_section', array(
			'title'    => __('Slider Settings', 'integlight'),
			'priority' => 30,
		));

		$this->effect($wp_customize);
		$this->image($wp_customize, 'integlight_slider_image_1', 'Slider Image 1');
		$this->image($wp_customize, 'integlight_slider_image_2', 'Slider Image 2');
		$this->image($wp_customize, 'integlight_slider_image_3', 'Slider Image 3');
		$this->text($wp_customize, 'integlight_slider_text_1', 'Slider Text');
		$this->changingTime($wp_customize);
		//利用しないように変更
		//$this->fadeDurationTime($wp_customize);
	}
}
new InteglightSlide();

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
			'label' => __('Sidebar' . $no . ' Position', 'integlight'),
			'section' => 'integlight_sidebar_section',
			'settings' => 'integlight_sidebar' . $no . '_position',
			'type' => 'radio',
			'choices' => array(
				'right' => __('Right', 'integlight'),
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
		return true;	
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
