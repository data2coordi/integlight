<?php

/**
 * Integlight functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Integlight
 */


//## スタイルシート、JSファイルの追加 _s //////////////////////////////////////////////////////
/**
 * Enqueue scripts and styles.
 */
class InteglightAssets
{
	private static $styles = [
		'integlight-awesome' => '/css/awesome-all.min.css',
		'integlight-base-style-plus' => '/css/base-style.css',
		'integlight-style-plus' => '/css/integlight-style.css',
		'integlight-sp-style' => '/css/integlight-sp-style.css',
		'integlight-layout' => '/css/layout.css',
		'integlight-integlight-menu' => '/css/integlight-menu.css',
		'integlight-post' => '/css/post.css',
		'integlight-page' => '/css/page.css',
		'integlight-front' => '/css/front.css',
		'integlight-home' => '/css/home.css',
		'integlight-module' => '/css/module.css',
		'integlight-block-module' => '/css/block-module.css',
		'integlight-helper' => '/css/helper.css',
	];

	// 遅延読み込み対象のスタイル
	private static $deferred_styles = [
		'integlight-awesome',
		'integlight-block-module'
	];

	public static function init()
	{
		add_filter('style_loader_tag', [__CLASS__, 'defer_css'], 10, 2);
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_styles']);
		add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_editor_styles']);
	}

	public static function enqueue_frontend_styles()
	{
		self::enqueue_styles();
	}

	public static function enqueue_editor_styles()
	{
		$excluded_styles = ['integlight-sp-style'];
		self::enqueue_styles($excluded_styles);
	}

	private static function enqueue_styles($excluded = [])
	{
		foreach (self::$styles as $handle => $path) {
			if (in_array($handle, $excluded, true)) {
				continue;
			}
			wp_enqueue_style($handle, get_template_directory_uri() . $path, [], _S_VERSION);
		}
	}

	public static function defer_css($tag, $handle)
	{
		// 遅延読み込み対象のスタイルの場合、media='print' を適用し、onload で元に戻す
		if (in_array($handle, self::$deferred_styles, true)) {
			return str_replace("rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $tag);
		}
		return $tag;
	}
}

InteglightAssets::init();
