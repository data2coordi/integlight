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
class InteglightDeferCss
{
	private static $deferred_styles = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_filter('style_loader_tag', [__CLASS__, 'defer_css'], 10, 2);
	}

	/**
	 * 遅延対象のスタイルを追加する
	 * @param array $styles 追加するスタイルのハンドル名の配列
	 */
	public static function add_deferred_styles(array $styles)
	{
		self::$deferred_styles = array_merge(self::$deferred_styles, $styles);
		self::$deferred_styles = array_unique(self::$deferred_styles); // 重複を排除
	}

	/**
	 * CSSの遅延読み込み処理
	 * @param string $tag    スタイルタグ
	 * @param string $handle スタイルのハンドル名
	 * @return string 修正後のタグ
	 */
	public static function defer_css($tag, $handle)
	{
		if (in_array($handle, self::$deferred_styles, true)) {
			return str_replace("rel='stylesheet'", "rel='stylesheet' media='print' onload=\"this.media='all'\"", $tag);
		}
		return $tag;
	}
}

// フィルタの登録を実行
InteglightDeferCss::init();



class InteglightRegStyles
{
	private static $styles = [];
	private static $excluded_styles = [];

	public static function init()
	{
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_styles']);
		add_action('enqueue_block_editor_assets', [__CLASS__, 'enqueue_editor_styles']);
	}

	public static function add_styles(array $styles)
	{
		self::$styles = array_merge(self::$styles, $styles);
	}

	public static function add_excluded_styles(array $excluded_styles)
	{
		self::$excluded_styles = array_merge(self::$excluded_styles, $excluded_styles);
	}

	public static function enqueue_frontend_styles()
	{
		self::enqueue_styles();
	}

	public static function enqueue_editor_styles()
	{
		self::enqueue_styles(self::$excluded_styles);
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
}
InteglightRegStyles::init();

class InteglightBaseAssets
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

	private static $excludedStyles = [
		'integlight-sp-style',
	];

	private static $deferredStyles = [
		'integlight-sp-style' => '/css/integlight-awesome',
		'integlight-sp-style' => '/css/integlight-block-module'
	];


	public static function init()
	{
		// スタイルリストを設定（追記可能）
		InteglightRegStyles::add_styles(self::$styles);
		InteglightRegStyles::add_excluded_styles(self::$excludedStyles);
		// 遅延対象のスタイルを登録
		InteglightDeferCss::add_deferred_styles(self::$deferredStyles);
	}
}


// 初期化処理（ルートで実行）
InteglightBaseAssets::init();
