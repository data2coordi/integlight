<?php

/********************************************************************/
/*ブロックアイテムの読み込みs*/
/********************************************************************/


function register_theme_blocks()
{
	$blocks = glob(get_template_directory() . '/blocks/*', GLOB_ONLYDIR);
	foreach ($blocks as $block) {
		if (file_exists($block . '/block.json')) {
			register_block_type($block);
		}
	}
}
add_action('init', 'register_theme_blocks');

//Font Awesome 
function add_fontawesome_button_to_toolbar()
{

	wp_enqueue_script(
		'integlight-gfontawesome',
		get_template_directory_uri() . '/blocks/gfontawesome/build/index.js', // ビルドされたファイルを読み込む
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'wp-rich-text'),
		'1.0',
		true
	);
}

add_action('enqueue_block_editor_assets', 'add_fontawesome_button_to_toolbar');


//右寄せサンプルツールバー
/*
function add_right_align_button_to_toolbar()
{
	wp_enqueue_script(
		'custom-right-align-button',
		get_template_directory_uri() . '/blocks/right-align-button/build/index.js', // ビルドされたファイルを読み込む
		array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
		'1.0',
		true
	);
}

add_action('enqueue_block_editor_assets', 'add_right_align_button_to_toolbar');
*/

//ブロックの国際化対応

function my_theme_enqueue_block_assets()
{
	wp_set_script_translations(
		'integlight-speech-bubble-editor-script', // ハンドル名を適切に設定
		'integlight',
		get_template_directory() . '/languages'
	);
}
add_action('enqueue_block_editor_assets', 'my_theme_enqueue_block_assets');

/*
function integlight_enqueue_block_assets()
{
	wp_enqueue_script(
		'integlight-block-script',
		get_template_directory_uri() . '/blocks/speech-bubble/build/index.js',
		array('wp-i18n', 'wp-blocks', 'wp-element', 'wp-editor'),
		get_template_directory() . '/blocks/speech-bubble/build/index.js'
	);
	wp_set_script_translations('integlight-block-script', 'integlight', get_template_directory() . '/languages');
}
add_action('enqueue_block_assets', 'integlight_enqueue_block_assets');
*/

/* 登録されているブロックのハンドルネーム出力*/
add_action('wp_print_scripts', function () {
	global $wp_scripts;
	foreach ($wp_scripts->registered as $handle => $script) {
		error_log('Registered script: ' . $handle);
	}
});

/********************************************************************/
/*ブロックアイテムの読み込みe*/
/********************************************************************/

/********************************************************************/
/*font awesome 用のショートコードs*/
/********************************************************************/
function integlight_render_fontawesome_shortcode($atts)
{
	$atts = shortcode_atts(
		array('icon' => ''),
		$atts,
		'fa'
	);

	if (empty($atts['icon'])) {
		return '';
	}

	return '<i class="fas ' . esc_attr($atts['icon']) . '"></i>';
}
add_shortcode('fontawesome', 'integlight_render_fontawesome_shortcode');

/********************************************************************/
/*font awesome 用のショートコードe*/
/********************************************************************/
