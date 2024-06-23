<?php

/**
 * Integlight Theme Customizer
 *
 * @package Integlight
 */


/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////



// slide customiser _s ////////////////////////////////////////////////////////////////////////////////

function integlight_customize_register_slide($wp_customize)
{
	// セクションを追加
	$wp_customize->add_section('slider_section', array(
		'title'    => __('Slider Settings', 'integlight'),
		'priority' => 30,
	));



	// 効果設定を追加
	$wp_customize->add_setting('effect', array(
		'default' => 'slide',
		'sanitize_callback' => 'sanitize_text_field',
	));

	// セレクトボックスのコントロールを追加
	$wp_customize->add_control('effect', array(
		'label'    => __('Effect', 'integlight'),
		'section'  => 'slider_section',
		'type'     => 'select',
		'choices'  => array(
			'fade'  => __('Fade', 'integlight'),
			'slide' => __('Slide', 'integlight'),
		),
	));





	// スライダー画像1を追加
	$wp_customize->add_setting('slider_image_1', array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
	));

	$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'slider_image_1', array(
		'label'    => __('Slider Image 1', 'integlight'),
		'section'  => 'slider_section',
		'settings' => 'slider_image_1',
	)));

	// スライダー画像2を追加
	$wp_customize->add_setting('slider_image_2', array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control(new WP_Customize_Image_control($wp_customize, 'slider_image_2', array(
		'label'    => __('Slider Image 2', 'integlight'),
		'section'  => 'slider_section',
		'settings' => 'slider_image_2',
	)));

	// スライダー画像3を追加
	$wp_customize->add_setting('slider_image_3', array(
		'default' => '',
		'sanitize_callback' => 'esc_url_raw',
	));
	$wp_customize->add_control(new WP_Customize_Image_control($wp_customize, 'slider_image_3', array(
		'label'    => __('Slider Image 3', 'integlight'),
		'section'  => 'slider_section',
		'settings' => 'slider_image_3',
	)));


	// スライダーテキスト1を追加
	$wp_customize->add_setting('slider_text_1', array(
		'default' => 'Slide  text',
		'sanitize_callback' => 'sanitize_text_field',
	));
	$wp_customize->add_control('slider_text_1', array(
		'label'    => __('Slider Text', 'integlight'),
		'section'  => 'slider_section',
		'type'     => 'textarea',
	));




	// スライド切り替え時間
	$wp_customize->add_setting('slider_change_duration', array(
		'default' => '1',
		'sanitize_callback' => 'absint', // 数値をサニタイズ
	));

	$wp_customize->add_control('slider_change_duration', array(
		'label'    => __('Slider Change Duration (seconds)', 'integlight'),
		'section'  => 'slider_section',
		'type'     => 'number',
		'input_attrs' => array(
			'min' => 1,
			'step' => 1,
		),
	));



	// フェード時間の設定
	$wp_customize->add_setting('slider_fade_duration', array(
		'default' => '0.8',
		'sanitize_callback' => 'absint', // 数値をサニタイズ
	));

	$wp_customize->add_control('slider_fade_duration', array(
		'label'    => __('Slider Fade Duration (seconds)', 'integlight'),
		'section'  => 'slider_section',
		'type'     => 'number',
		'input_attrs' => array(
			'min' => 0.1,
			'step' => 0.1,
		),
	));
}
add_action('customize_register', 'integlight_customize_register_slide');

// slide customiser _e ////////////////////////////////////////////////////////////////////////////////


// side bar position _s ////////////////////////////////////////////////////////////////////////////////
function integlight_customize_register_sidebar($wp_customize)
{
	// サイドバー位置セクションの追加
	$wp_customize->add_section('integlight_sidebar_section', array(
		'title' => __('Sidebar Settings', 'integlight'),
		'priority' => 30,
	));

	// サイドバー位置設定の追加
	$wp_customize->add_setting('integlight_sidebar1_position', array(
		'default' => 'right',
		'sanitize_callback' => 'integlight_sanitize_sidebar_position',
	));

	// サイドバー位置オプションの追加
	$wp_customize->add_control('integlight_sidebar1_position_control', array(
		'label' => __('Sidebar1 Position', 'integlight'),
		'section' => 'integlight_sidebar_section',
		'settings' => 'integlight_sidebar1_position',
		'type' => 'radio',
		'choices' => array(
			'right' => __('Right', 'integlight'),
			'bottom' => __('Bottom', 'integlight'),
			'none' => __('None', 'integlight'),
		),
	));


	// サイドバー位置設定の追加
	$wp_customize->add_setting('integlight_sidebar2_position', array(
		'default' => 'right',
		'sanitize_callback' => 'integlight_sanitize_sidebar_position',
	));

	// サイドバー位置オプションの追加
	$wp_customize->add_control('integlight_sidebar2_position_control', array(
		'label' => __('Sidebar2 Position', 'integlight'),
		'section' => 'integlight_sidebar_section',
		'settings' => 'integlight_sidebar2_position',
		'type' => 'radio',
		'choices' => array(
			'right' => __('Right', 'integlight'),
			'bottom' => __('Bottom', 'integlight'),
			'none' => __('None', 'integlight'),
		),
	));
}

add_action('customize_register', 'integlight_customize_register_sidebar');

// サイドバー位置の入力を検証する
function integlight_sanitize_sidebar_position($input)
{
	$valid = array('right', 'bottom', 'none');
	if (in_array($input, $valid, true)) {
		return $input;
	}
	return 'right';
}
// side bar position _e ////////////////////////////////////////////////////////////////////////////////



// ## 配色カスタマイズ _s /////////////////////////////////////////////
add_action('customize_register', 'integlight_theme_customize');

function integlight_theme_customize($wp_customize)
{

	$wp_customize->add_section('base_pattern_section', array(
		'title'    => __('Base color pattern', 'integlight'),
		'priority' => 30,
		'description' => __('The base color pattern you select will be reflected throughout the site.', 'integlight'),
	));


	//type theme_modにするとwp_optionsにテーマ設定として値が格納される。
	$wp_customize->add_setting('base_color_setting', array(
		'type'  => 'theme_mod',
		'sanitize_callback' => 'integlight_sanitize_choices',
	));

	$wp_customize->add_control('base_color_setting', array(
		'section' => 'base_pattern_section',
		'settings' => 'base_color_setting',
		'label' => 'Base color setting',
		'description' => 'Select favarite base color',
		'type' => 'radio',
		'choices' => array(
			'pattern1' => 'None',
			'pattern2' => 'Blue',
			'pattern3' => 'Green',
			'pattern4' => 'Orange',
			'pattern5' => 'Red',
			'pattern6' => 'Pink',
		),
	));
}


/* テーマカスタマイザー用のサニタイズ関数
---------------------------------------------------------- */
//ラジオボタン
function integlight_sanitize_choices($input, $setting)
{
	global $wp_customize;
	$control = $wp_customize->get_control($setting->id);
	if (array_key_exists($input, $control->choices)) {
		return $input;
	} else {
		return $setting->default;
	}
}

function integlight_your_theme_enqueue_custom_css()
{
	$base_pattern = get_theme_mod('base_color_setting', 'pattern1');

	// パターンに応じてCSSファイルを読み込む
	wp_enqueue_style('custom-pattern', get_template_directory_uri() . '/css/' . $base_pattern . '.css', array(), '1.0.0');
}

add_action('wp_enqueue_scripts', 'integlight_your_theme_enqueue_custom_css');

// ## 配色カスタマイズ _e /////////////////////////////////////////////
