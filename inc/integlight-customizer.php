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
			'title' => __('Sidebar Settings', 'integlight'),
			'priority' => 30,
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
		add_action('wp_enqueue_scripts', [$this, 'enqueue_custom_css']);
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

	public function enqueue_custom_css()
	{

		$base_pattern = get_theme_mod('integlight_base_color_setting', 'pattern8');

		$styles = ['custom-pattern' => '/css/' . $base_pattern . '.css'];
		InteglightFrontendStyles::add_styles($styles);
		InteglightEditorStyles::add_styles($styles);
		InteglightDeferCss::add_deferred_styles(['custom-pattern']); //PF対応!!!
	}
}

// インスタンスを作成して初期化
new integlight_customizer_themeColor();


// ## フッター クレジット設定 _s /////////////////////////////////////////////
class Integlight_Customizer_Footer
{
	public function __construct()
	{
		add_action('customize_register', array($this, 'register'));
	}

	public function register($wp_customize)
	{
		// セクション追加
		$wp_customize->add_section('integlight_footer_section', array(
			'title'    => __('Footer Settings', 'integlight'),
			'priority' => 160,
		));

		// コピーライト設定
		$wp_customize->add_setting('integlight_footer_copy_right', array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		));

		$wp_customize->add_control('integlight_footer_copy_right', array(
			'label'    => __('Copyright Settings', 'integlight'),
			'section'  => 'integlight_footer_section',
			'type'     => 'text',
		));

		// クレジット表示チェックボックス
		$wp_customize->add_setting('integlight_footer_show_credit', array(
			'default'           => true,
			'sanitize_callback' => array($this, 'sanitize_checkbox'),
		));

		$wp_customize->add_control('integlight_footer_show_credit', array(
			'label'    => __("Display 'Powered by WordPress' and theme author credit", 'integlight'),
			'section'  => 'integlight_footer_section',
			'type'     => 'checkbox',
		));
	}

	public function sanitize_checkbox($checked)
	{
		return (isset($checked) && true == $checked) ? true : false;
	}
}

// 初期化
new Integlight_Customizer_Footer();

// ## フッター クレジット設定 _e /////////////////////////////////////////////
