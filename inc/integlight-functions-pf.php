<?php










/***************************************** */
/**css,js読み込み s************************ */
/***************************************** */

//wp-block-libraryは遅延できないため、フロントでは除外する。 
//遅延もさせているがPSIで指摘されるため、除外も実施
/*
add_action('wp_enqueue_scripts', function () {
    if (!is_admin()) {
        wp_dequeue_style('wp-block-library');
    }
}, 20);
*/




class InteglightCommonCssAssets
{
    private static $styles = [
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
        'integlight-helper' => '/css/helper.css',
    ];


    private static $deferredStyles = [
        'integlight-sp-style',
        'wp-block-library' /*ブロックエディタ用css*/
    ];


    public static function init()
    {
        // スタイルリストを設定（追記可能）
        InteglightFrontendStyles::add_styles(self::$styles);

        $excluded_key = 'integlight-sp-style';
        // $styles から $excluded_key を除外してコピー
        $EditorStyles = array_filter(self::$styles, function ($key) use ($excluded_key) {
            return $key !== $excluded_key;
        }, ARRAY_FILTER_USE_KEY);

        InteglightEditorStyles::add_styles($EditorStyles);

        // 遅延対象のスタイルを登録
        InteglightDeferCss::add_deferred_styles(self::$deferredStyles);
    }
}

// 初期化処理（ルートで実行）
add_action('after_setup_theme', ['InteglightCommonCssAssets', 'init']);





//js 移動　PF対策	
class InteglightCommonJsAssets
{


    public static function init()
    {

        //js 読み込み
        $scripts = [
            'integlight-navigation' =>  ['path' => '/js/build/navigation.js', 'deps' => []],
        ];
        InteglightFrontendScripts::add_scripts($scripts);

        // フッターに移動するスクリプトを登録
        $footerScripts = [
            'jquery'   => includes_url('/js/jquery/jquery.min.js')
        ];
        InteglightMoveScripts::add_scripts($footerScripts);


        //js 読み込み　WPデフォルトのコメント用
        add_action('wp_enqueue_scripts', [__CLASS__, 'enqueue_comment_reply_script']);
    }

    public static function enqueue_comment_reply_script()
    {
        if (is_singular() && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }
}

// 初期化処理
add_action('after_setup_theme', ['InteglightCommonJsAssets', 'init']);


/***************************************** */
/**css,js読み込み e***************************** */
/***************************************** */

/********************************************************************/
/* 検索ボックスにラベル追加 ユーザビリティ対応s	*/
/********************************************************************/
add_filter('render_block', 'add_label_to_wp_block_search', 10, 2);

function add_label_to_wp_block_search($block_content, $block)
{
    if ($block['blockName'] !== 'core/search') {
        return $block_content;
    }

    $label = '<label for="wp-block-search__input-1" class="screen-reader-text">検索</label>';

    $block_content = preg_replace(
        '/(<input[^>]*id="wp-block-search__input-1"[^>]*>)/',
        $label . '$1',
        $block_content,
        1
    );

    return $block_content;
}
/********************************************************************/
/* 検索ボックスにラベル追加 ユーザビリティ対応e	*/
/********************************************************************/
