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
		$wp_customize->add_setting('integlight_base_color_setting', array(
			'type'              => 'theme_mod',
			'sanitize_callback' => [$this, 'sanitize_choices'],
		));

		// Control
		$wp_customize->add_control('integlight_base_color_setting', array(
			'section'     => 'colors', //既存の色セクションに追加
			'settings'    => 'integlight_base_color_setting',
			'label'       => integlight_g('Accent color setting'),
			'description' => integlight_g('Select favorite accent color'),
			'type'        => 'radio',
			'choices'     => array(
				'pattern1' => integlight_g('None'),
				'pattern2' => integlight_g('Blue'),
				'pattern3' => integlight_g('Green'),
				'pattern4' => integlight_g('Orange'),
				'pattern5' => integlight_g('Red'),
				'pattern6' => integlight_g('Pink'),
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
		$base_pattern = get_theme_mod('integlight_base_color_setting', 'pattern1');
		wp_enqueue_style('custom-pattern', get_template_directory_uri() . '/css/' . $base_pattern . '.css', array(), '1.0.0');
	}
}

// インスタンスを作成して初期化
new integlight_customizer_themeColor();


// ## 配色カスタマイズ _e /////////////////////////////////////////////


// ## Google_Analytics _s /////////////////////////////////////////////

class integlight_customizer_ga
{

	// コンストラクタ：カスタマイザー設定の登録
	public function __construct()
	{
		add_action('customize_register', array($this, 'regSettings'));
		add_action('wp_head', array($this, 'outCode'));
	}

	// カスタマイザーに設定項目を登録
	public function regSettings($wp_customize)
	{
		// Google Analytics 設定セクションを追加
		$wp_customize->add_section('integlight_ga_section', array(
			'title' => integlight_g('Google Analytics Setting'),
			'priority' => 1000,
		));

		// Google Analytics トラッキングコードを入力する設定を追加
		$wp_customize->add_setting('integlight_ga_trackingCode', array(
			'default' => ''
		));

		// トラッキングコード入力フィールドを追加
		$wp_customize->add_control('integlight_ga_trackingCode', array(
			'label' => integlight_g('Google Analytics Tracking Code'),
			'section' => 'integlight_ga_section',
			'type' => 'textarea', // 複数行のテキストエリアを使用
			'description' => integlight_g('Please paste the entire tracking code provided by Google Analytics.'),

		));
	}

	// Google アナリティクスコードをサイトの <head> に出力
	public function outCode()
	{
		$tracking_code = get_theme_mod('integlight_ga_trackingCode');
		if ($tracking_code) {
			echo $tracking_code; // HTMLをそのまま出力
		}
	}
}

// クラスをインスタンス化して処理を開始
new integlight_customizer_ga();
// ## Google_Analytics _e /////////////////////////////////////////////


// ## Google_GTM _s /////////////////////////////////////////////
class integlight_customizer_gtm
{

	// コンストラクタ：カスタマイザー設定の登録
	public function __construct()
	{
		add_action('customize_register', array($this, 'regSettings'));
		add_action('wp_head', array($this, 'outCode'));
		add_action('wp_body_open', array($this, 'outNoscriptCode')); // body開始直後に追加
	}

	// カスタマイザーに設定項目を登録
	public function regSettings($wp_customize)
	{
		// Google Tag Manager 設定セクションを追加
		$wp_customize->add_section('integlight_gtm_section', array(
			'title' => integlight_g('Google Tag Manager Setting'),
			'priority' => 1000,
		));

		// Google Tag Manager トラッキングコードを入力する設定を追加
		$wp_customize->add_setting('integlight_gtm_trackingCode', array(
			'default' => ''
		));

		// GTM トラッキングコード入力フィールドを追加
		$wp_customize->add_control('integlight_gtm_trackingCode', array(
			'label' => integlight_g('Code to output in the <head> tag'),
			'section' => 'integlight_gtm_section',
			'type' => 'textarea', // 複数行のテキストエリアを使用
			'description' => integlight_g('Please paste the code provided by Google Tag Manager.'),
		));

		// Google Tag Manager noscript バックアップコードを入力する設定を追加
		$wp_customize->add_setting('integlight_gtm_noscriptCode', array(
			'default' => ''
		));


		// noscript トラッキングコード入力フィールドを追加
		$wp_customize->add_control('integlight_gtm_noscriptCode', array(
			'label' => integlight_g('Code to output immediately after the opening <body> tag'),
			'section' => 'integlight_gtm_section',
			'type' => 'textarea',
			'description' => integlight_g('Please paste the code provided by Google Tag Manager.'),
		));
	}

	// Google Tag Manager コードをサイトの <head> に出力
	public function outCode()
	{
		$tracking_code = get_theme_mod('integlight_gtm_trackingCode');
		if ($tracking_code) {
			echo $tracking_code; // HTMLをそのまま出力
		}
	}

	// Google Tag Manager noscript バックアップコードを <body> タグ直後に出力
	public function outNoscriptCode()
	{
		$noscript_code = get_theme_mod('integlight_gtm_noscriptCode');
		if ($noscript_code) {
			echo $noscript_code; // noscriptタグを出力
		}
	}
}

// クラスをインスタンス化して処理を開始
new integlight_customizer_gtm();
// ## Google_GTM _e /////////////////////////////////////////////
