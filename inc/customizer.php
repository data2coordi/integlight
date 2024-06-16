<?php
/**
 * Integlight Theme Customizer
 *
 * @package Integlight
 */

/**
 * Add postMessage support for site title and description for the Theme Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
 */
function integlight_customize_register( $wp_customize ) {
	$wp_customize->get_setting( 'blogname' )->transport         = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport  = 'postMessage';
	$wp_customize->get_setting( 'header_textcolor' )->transport = 'postMessage';

	if ( isset( $wp_customize->selective_refresh ) ) {
		$wp_customize->selective_refresh->add_partial(
			'blogname',
			array(
				'selector'        => '.site-title a',
				'render_callback' => 'integlight_customize_partial_blogname',
			)
		);
		$wp_customize->selective_refresh->add_partial(
			'blogdescription',
			array(
				'selector'        => '.site-description',
				'render_callback' => 'integlight_customize_partial_blogdescription',
			)
		);
	}
}
add_action( 'customize_register', 'integlight_customize_register' );

/**
 * Render the site title for the selective refresh partial.
 *
 * @return void
 */
function integlight_customize_partial_blogname() {
	bloginfo( 'name' );
}

/**
 * Render the site tagline for the selective refresh partial.
 *
 * @return void
 */
function integlight_customize_partial_blogdescription() {
	bloginfo( 'description' );
}

/**
 * Binds JS handlers to make Theme Customizer preview reload changes asynchronously.
 */
function integlight_customize_preview_js() {
	wp_enqueue_script( 'integlight-customizer', get_template_directory_uri() . '/js/customizer.js', array( 'customize-preview' ), _S_VERSION, true );
}
add_action( 'customize_preview_init', 'integlight_customize_preview_js' );


/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////
/////////////////////////////////////////////



// slide customiser _s ////////////////////////////////////////////////////////////////////////////////

function integlight_customize_register_plus($wp_customize)
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
add_action('customize_register', 'integlight_customize_register_plus');

// slide customiser _e ////////////////////////////////////////////////////////////////////////////////