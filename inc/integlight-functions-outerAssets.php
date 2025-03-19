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
/********************************************************* */
/* cssファイル s  **********************************/
/********************************************************* */


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
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_styles'], 20);
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
/********************************************************* */
/* cssファイル e  **********************************/
/********************************************************* */



/********************************************************* */
/* jsファイル s***********************************************/
/********************************************************* */


class InteglightRegScripts
{
	private static $scripts = [];
	private static $excluded_scripts = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_frontend_scripts']);
	}

	/**
	 * スクリプトを追加
	 * @param array $scripts ['handle' => ['path' => 'パス', 'deps' => ['依存スクリプト']]]
	 */
	public static function add_scripts(array $scripts)
	{
		self::$scripts = array_merge(self::$scripts, $scripts);
	}

	/**
	 * 除外するスクリプトを追加
	 * @param array $excluded_scripts 除外するスクリプトのハンドル
	 */
	public static function add_excluded_scripts(array $excluded_scripts)
	{
		self::$excluded_scripts = array_merge(self::$excluded_scripts, $excluded_scripts);
	}

	/**
	 * フロントエンド用スクリプトの登録
	 */
	public static function enqueue_frontend_scripts()
	{
		foreach (self::$scripts as $handle => $data) {
			if (in_array($handle, self::$excluded_scripts, true)) {
				continue;
			}

			$path = $data['path'];
			$deps = isset($data['deps']) ? $data['deps'] : [];

			wp_enqueue_script($handle,  get_template_directory_uri() . $path, $deps, _S_VERSION, true);
		}
	}
}




// スクリプト登録の初期化
InteglightRegScripts::init();



class InteglightDeferJs
{
	private static $deferred_scripts = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_filter('script_loader_tag', [__CLASS__, 'defer_js'], 10, 2);
	}

	/**
	 * 遅延対象のスクリプトを追加する
	 * @param array $scripts 追加するスクリプトのハンドル名の配列
	 */
	public static function add_deferred_scripts(array $scripts)
	{
		self::$deferred_scripts = array_merge(self::$deferred_scripts, $scripts);
		self::$deferred_scripts = array_unique(self::$deferred_scripts); // 重複を排除
	}

	/**
	 * JSの遅延読み込み処理
	 * @param string $tag    スクリプトタグ
	 * @param string $handle スクリプトのハンドル名
	 * @return string 修正後のタグ
	 */
	public static function defer_js($tag, $handle)
	{
		if (in_array($handle, self::$deferred_scripts, true)) {
			return str_replace(' src', ' defer src', $tag);
		}
		return $tag;
	}
}

// フィルタの登録を実行
InteglightDeferJs::init();




class InteglightMoveScripts
{
	private static $scripts = [];

	/**
	 * 初期化メソッド
	 */
	public static function init()
	{
		add_action('wp_enqueue_scripts', [__CLASS__, 'move_scripts_to_footer'], 1);
	}

	/**
	 * フッターに移動するスクリプトを登録
	 * @param array $scripts ['handle' => 'パス'] の配列
	 */
	public static function add_scripts(array $scripts)
	{
		self::$scripts = array_merge(self::$scripts, $scripts);
	}

	/**
	 * 指定したスクリプトをフッターに移動
	 */
	public static function move_scripts_to_footer()
	{
		if (!is_admin()) {
			foreach (self::$scripts as $handle => $path) {
				wp_deregister_script($handle);
				wp_register_script($handle, includes_url($path), [], _S_VERSION, true);
				wp_enqueue_script($handle);
			}
		}
	}
}

// 初期化処理
InteglightMoveScripts::init();

class InteglightMoveScriptsMain
{
	private static $footerScripts = [
		'jquery'   => '/js/jquery/jquery.min.js',
	];

	public static function init()
	{
		// フッターに移動するスクリプトを登録
		InteglightMoveScripts::add_scripts(self::$footerScripts);
	}
}

// 初期化処理
InteglightMoveScriptsMain::init();
/********************************************************* */
/* jsファイル e***********************************************/
/********************************************************* */
