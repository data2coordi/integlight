<?php



/***************************************** */
/***************************************** */
/***************************************** */
/**共通ss,js読み込み s************************ */
/***************************************** */
/***************************************** */
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


/****************************************************** */
/* ccsの登録                                              */
/****************************************************** */
class Integlight_outerAssets_css_preDetermine
{
    private static $styles = [
        'integlight-base-style-plus' => ['path' => '/css/build/all.cmn.nonLayout0.css', 'deps' => ['integlight-layout']],
        'integlight-style-plus' =>  ['path' => '/css/build/all.cmn.nonLayout1.css', 'deps' => ['integlight-base-style-plus']],
        'integlight-layout' =>  ['path' => '/css/build/all.cmn.layout.css', 'deps' => []],
        'integlight-integlight-menu' =>  ['path' => '/css/build/all.sp.menu.css', 'deps' => ['integlight-style-plus']],
        'integlight-helper' =>  ['path' => '/css/build/all.helper.css', 'deps' => ['integlight-style-plus']],

    ];


    private static $deferredStyles = [
        'integlight-svg-non-home',
        //'wp-block-library'
    ];


    public static function init()
    {
        // --- 1. 共通スタイルの定義 ---
        // is_singular() は is_single(), is_page(), is_attachment() を含む
        if (is_singular() || is_front_page() || is_archive() || is_search() || is_404()) {
            self::$styles['integlight-module'] = ['path' => '/css/build/all.parts.module-forTheme.css', 'deps' => ['wp-block-library']];
            self::$styles['integlight-module_forBlocks'] =  ['path' => '/css/build/all.parts.module-forBlockItem.css', 'deps' => ['wp-block-library']];
        }

        // is_home() 以外のページで読み込むSVGスタイル
        if (!is_home()) {
            self::$styles['integlight-svg-non-home'] = ['path' => '/css/build/all.sp.svg-non-home.css', 'deps' => []];
        }

        // --- 2. ページ固有のスタイルの追加 ---
        if (is_single()) {
            self::$styles['integlight-post'] = ['path' => '/css/build/page.post.css', 'deps' => ['integlight-style-plus']];
        }

        if (is_page()) {
            self::$styles['integlight-page'] = ['path' => '/css/build/page.page.css', 'deps' => ['integlight-style-plus']];
        }

        if (is_front_page() && (!is_home())) {
            self::$styles['integlight-front'] = ['path' => '/css/build/page.front.css', 'deps' => ['integlight-style-plus']];
        }

        if (is_home()) {
            self::$styles['integlight-home'] = ['path' => '/css/build/page.home.css', 'deps' => ['integlight-style-plus']];
            // is_home() の場合、wp-block-library を遅延読み込みの対象に追加
            self::$deferredStyles = array_merge(self::$deferredStyles, [
                'wp-block-library',
            ]);
        }

        // --- 3. 登録処理 ---
        // スタイルリストを設定（追記可能）
        Integlight_outerAssets_css_frontend::add_styles(self::$styles);

        $excluded_key = 'dummy';
        // $styles から $excluded_key を除外してコピー
        $EditorStyles = array_filter(self::$styles, function ($key) use ($excluded_key) {
            return $key !== $excluded_key;
        }, ARRAY_FILTER_USE_KEY);

        Integlight_outerAssets_css_editor::add_styles($EditorStyles);

        // 遅延対象のスタイルを登録
        Integlight_outerAssets_css_defer::add_deferred_styles(self::$deferredStyles);
    }
}

// 初期化処理（ルートで実行）
add_action('wp', ['Integlight_outerAssets_css_preDetermine', 'init']);



/****************************************************** */
/* JSの登録                                              */
/****************************************************** */
//js 移動　PF対応!!!
class Integlight_outerAssets_js_preDetermine
{

    private static $scripts = [
        'integlight-navigation' =>  ['path' => '/js/build/navigation.js', 'deps' => []],
    ];


    public static function init()
    {

        // 以下、必要に応じて追加
        /*
        if (is_single()) {
        }

        if (is_page()) {
        }

        if (is_front_page() && (!is_home())) {
        }

        if (is_home()) {
            
            self::$scripts = array_merge(self::$scripts, [
                'integlight-loadmore' =>  ['path' => '/js/build/loadmore.js', 'deps' => ['jquery']],
            ]);
        }
        */
        Integlight_outerAssets_js_frontend::add_scripts(self::$scripts);

        $deferredScripts = [
            'integlight-navigation',
        ];
        Integlight_outerAssets_js_defer::add_deferred_scripts($deferredScripts); //PF対応!!!

        // フッターに移動するスクリプトを登録
        //$footerScripts = [
        //  'jquery'   => includes_url('/js/jquery/jquery.min.js') jqueryが不要なときもロードされてしまうため廃止
        //];
        //Integlight_outerAssets_js_move::add_scripts($footerScripts);


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
add_action('wp', ['Integlight_outerAssets_js_preDetermine', 'init']);


/***************************************** */
/***************************************** */
/***************************************** */
/**共通css,js読み込み e***************************** */
/***************************************** */
/***************************************** */
/***************************************** */



//////////////////////////////////////////
//カスタムカラー用のcss
//////////////////////////////////////////

class Integlight_outerAssets_themeColorLoader
{
    public function __construct()
    {
        add_action('wp', [$this, 'enqueue_custom_css']);
    }

    public function enqueue_custom_css()
    {
        $base_pattern = Integlight_getThemeMod::getThemeMod('integlight_base_color_setting', 'pattern8');



        $styles = ['integlight-custom-color-pattern' => ['path' => '/css/build/' . 'all.upd.color-' . $base_pattern . '.css', 'deps' => ['integlight-style-plus']]];
        Integlight_outerAssets_css_frontend::add_styles($styles);
        Integlight_outerAssets_css_editor::add_styles($styles);
        Integlight_outerAssets_css_defer::add_deferred_styles(['integlight-custom-color-pattern']); //PF対応!!!
    }
}
new Integlight_outerAssets_themeColorLoader();


//////////////////////////////////////////
//ホームタイプ用のcss
//////////////////////////////////////////
class Integlight_outerAssets_homeTypeLoader
{
    public function __construct()
    {
        add_action('wp', [$this, 'enqueue_hometype_css']);
    }

    public function enqueue_hometype_css()
    {
        $home_type = Integlight_getThemeMod::getThemeMod('integlight_hometype_setting', 'siteType1');

        $tmp_deps = ['integlight-integlight-menu', 'integlight-custom-color-pattern'];
        // slider かつフロントまたは home の場合に追記
        if (
            'slider' === Integlight_getThemeMod::getThemeMod('integlight_display_choice', 'none') &&
            (is_front_page())
        ) {
            $tmp_deps[] = 'integlight-slide';
        }

        //$home_typeから最後の1文字(1or2)を取得するしてcssファイル名を決定
        $styles = ['integlight-home-type' => ['path' => '/css/build/' . 'all.upd.site-type' . substr($home_type, -1) . '.css', 'deps' => $tmp_deps]];
        Integlight_outerAssets_css_frontend::add_styles($styles);
        Integlight_outerAssets_css_editor::add_styles($styles);
    }
}
new Integlight_outerAssets_homeTypeLoader();



//////////////////////////////////////////
//外部から呼び出しでcssのロード
//スライダー
//////////////////////////////////////////
class Integlight_outerAssets_css_forCall
{

    public static function regSliderCss()
    {

        $styles = [
            'integlight-slide' => ['path' => '/css/build/all.sp.slider.css', 'deps' => ['integlight-style-plus']],
        ];
        Integlight_outerAssets_css_frontend::add_styles($styles);
    }

    public static function regHeaderImageCss()
    {

        $styles = [
            'integlight-header-image' => ['path' => '/css/build/all.sp.headerImage.css', 'deps' => ['integlight-style-plus']],
        ];
        Integlight_outerAssets_css_frontend::add_styles($styles);
    }
}


//////////////////////////////////////////
//外部から呼び出しでjsのロード
//スライダー、LoadMore
//////////////////////////////////////////
class Integlight_outerAssets_js_forCall
{

    public static function regSliderScripts()
    {


        $scripts = [
            'integlight_slider-script' =>  ['path' => '/js/build/slider.js'],
            //'integlight_slider-script' =>  ['path' => '/js/build/slider.js', 'deps' => ['jquery']],
        ];
        Integlight_outerAssets_js_frontend::add_scripts($scripts);


        // 遅延対象のスクリプトを登録
        $deferredScripts = [
            'integlight_slider-script',
        ];
        Integlight_outerAssets_js_defer::add_deferred_scripts($deferredScripts);
        /* レンダリングブロック、layout計算増加の防止のためのチューニング e*/
    }
    public static function getSliderScriptsHandleName()
    {
        return 'integlight_slider-script';
    }

    public static function regLoadMoreScripts()
    {

        $scripts = [
            //'integlight-loadmore' =>  ['path' => '/js/build/loadmore.js', 'deps' => ['jquery']],
            'integlight-loadmore' =>  ['path' => '/js/build/loadmore.js'],
        ];
        Integlight_outerAssets_js_frontend::add_scripts($scripts);

        $deferredScripts = [
            'integlight-loadmore',
        ];
        Integlight_outerAssets_js_defer::add_deferred_scripts($deferredScripts); //PF対応!!!

    }
    public static function getLoadMoreScriptsHandleName()
    {
        return 'integlight-loadmore';
    }
}

///////////////////////