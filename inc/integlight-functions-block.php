<?php

/********************************************************************/
/*ブロックアイテムの読み込みs*/
/********************************************************************/

//Font Awesome 
function integlight_add_fontawesome_button_to_toolbar()
{
	//js 読み込み
	$scripts = [
		'integlight-gfontawesome' =>  ['path' => '/blocks/gfontawesome/build/index.js', 'deps' => ['wp-blocks', 'wp-i18n', 'wp-element',  'wp-rich-text']],
	];
	InteglightEditorScripts::add_scripts($scripts);
	$deferredScripts = ['integlight-gfontawesome'];
	InteglightDeferJs::add_deferred_scripts($deferredScripts);
}
integlight_add_fontawesome_button_to_toolbar();


/********************************************************************/
/*ブロックアイテムの読み込みe*/
/********************************************************************/

/********************************************************************/
/*font awesome 用フィルター置換*/
/********************************************************************/

function integlight_replace_fontawesome_icons($content)
{
	return preg_replace_callback(
		'/\[fontawesome icon=([a-z0-9-]+)\]/i',
		function ($matches) {
			$icon = $matches[1];
			if (empty($icon)) {
				return '';
			}
			return '<i class="fas ' . esc_attr($icon) . '"></i>';
		},
		$content
	);
}
add_filter('the_content', 'integlight_replace_fontawesome_icons', 10);

/********************************************************************/
/*font awesome 用のショートコードe*/
/********************************************************************/




/********************************************************************/
/*ブロックの国際化対応s*/
/********************************************************************/

function integlight_enqueue_block_assets()
{


	wp_set_script_translations(
		'integlight-gfontawesome-block-editor-script',
		'integlight',
		get_template_directory() . '/languages'
	);
}
add_action('enqueue_block_editor_assets', 'integlight_enqueue_block_assets');




/********************************************************************/
/*ブロックの国際化対応e*/
/********************************************************************/
