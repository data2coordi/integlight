<?php

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

new integlight_customizer_sidebar();


// side bar position _e ////////////////////////////////////////////////////////////////////////////////


// ## 配色カスタマイズ _s /////////////////////////////////////////////

class integlight_customizer_themeColor
{
	public function __construct()
	{
		add_action('customize_register', [$this, 'customize_register']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_css']);
	}

	public function customize_register($wp_customize)
	{

		// Setting
		$wp_customize->add_setting('base_color_setting', array(
			'type'              => 'theme_mod',
			'sanitize_callback' => [$this, 'sanitize_choices'],
		));

		// Control
		$wp_customize->add_control('base_color_setting', array(
			'section'     => 'colors', //既存の色セクションに追加
			'settings'    => 'base_color_setting',
			'label'       => __('Accent color setting', 'integlight'),
			'description' => __('Select favorite accent color', 'integlight'),
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
new integlight_customizer_themeColor();


// ## 配色カスタマイズ _e /////////////////////////////////////////////



function customize_register($wp_customize)
{
	// 🟢 新しいパネルを作成（これが「セクションの親」のような役割）
	$wp_customize->add_panel('integlight_panel', array(
		'title'       => __('Integlight カスタム設定', 'integlight'),
		'priority'    => 10,
	));

	// 🔹 サブセクション1を作成（このセクションはパネルの下に入る）
	$wp_customize->add_section('integlight_section1', array(
		'title'       => __('ヘッダー設定', 'integlight'),
		'priority'    => 20,
		'panel'       => 'integlight_panel', // パネルの下に配置
	));

	// 🔹 サブセクション2を作成
	$wp_customize->add_section('integlight_section2', array(
		'title'       => __('フッター設定', 'integlight'),
		'priority'    => 30,
		'panel'       => 'integlight_panel',
	));

	// 🎨 サブセクション1に「背景色」コントロールを追加
	$wp_customize->add_setting('header_bg_color', array(
		'default'    => '#ffffff',
		'transport'  => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'header_bg_color', array(
		'label'       => __('ヘッダー背景色', 'integlight'),
		'section'     => 'integlight_section1', // 🔹 サブセクション1に追加
		'settings'    => 'header_bg_color',
	)));

	// 🎨 サブセクション2に「フッター背景色」コントロールを追加
	$wp_customize->add_setting('footer_bg_color', array(
		'default'    => '#000000',
		'transport'  => 'refresh',
	));

	$wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'footer_bg_color', array(
		'label'       => __('フッター背景色', 'integlight'),
		'section'     => 'integlight_section2', // 🔹 サブセクション2に追加
		'settings'    => 'footer_bg_color',
	)));
}
add_action('customize_register', 'customize_register');
