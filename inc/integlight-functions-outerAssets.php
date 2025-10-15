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
class InteglightPreDetermineCssAssets
{
    private static $styles = [
        'integlight-base-style-plus' => ['path' => '/css/build/base-style.css', 'deps' => ['integlight-layout']],
        'integlight-style-plus' =>  ['path' => '/css/build/integlight-style.css', 'deps' => ['integlight-base-style-plus']],
        'integlight-sp-style' => ['path' =>  '/css/build/integlight-sp-style.css', 'deps' => ['integlight-style-plus']],
        'integlight-layout' =>  ['path' => '/css/build/layout.css', 'deps' => []],
        'integlight-integlight-menu' =>  ['path' => '/css/build/integlight-menu.css', 'deps' => ['integlight-style-plus']],
        'integlight-helper' =>  ['path' => '/css/build/helper.css', 'deps' => ['integlight-style-plus']],

    ];


    private static $deferredStyles = [
        'integlight-sp-style',
        'integlight-svg-non-home',
        //'wp-block-library'
    ];


    public static function init()
    {

        // 以下、必要に応じて追加
        if (is_single()) {
            self::$styles = array_merge(self::$styles, [
                'integlight-post' => ['path' => '/css/build/post.css', 'deps' => ['integlight-style-plus']],
                'integlight-module' =>  ['path' => '/css/build/module.css', 'deps' => ['wp-block-library']],
                'integlight-svg-non-home' =>  ['path' => '/css/build/svg-non-home.css', 'deps' => []],
            ]);
        }

        if (is_page()) {
            self::$styles = array_merge(self::$styles, [
                'integlight-page' => ['path' => '/css/build/page.css', 'deps' => ['integlight-style-plus']],
                'integlight-module' =>  ['path' => '/css/build/module.css', 'deps' => ['wp-block-library']],
                'integlight-svg-non-home' =>  ['path' => '/css/build/svg-non-home.css', 'deps' => []],
            ]);
        }

        if (is_front_page() && (!is_home())) {
            self::$styles = array_merge(self::$styles, [
                'integlight-front' => ['path' => '/css/build/front.css', 'deps' => ['integlight-style-plus']],
                'integlight-module' =>  ['path' => '/css/build/module.css', 'deps' => ['wp-block-library']],
                'integlight-svg-non-home' =>  ['path' => '/css/build/svg-non-home.css', 'deps' => []],
            ]);
        }

        if (is_archive() || is_search() || is_404()) {
            // 漏れているページ用の CSS をここで追加
            self::$styles = array_merge(self::$styles, [
                'integlight-module' =>  ['path' => '/css/build/module.css', 'deps' => ['wp-block-library']],
                'integlight-svg-non-home' =>  ['path' => '/css/build/svg-non-home.css', 'deps' => []],
            ]);
        }

        if (is_home()) {
            self::$styles = array_merge(self::$styles, [
                'integlight-home' => ['path' => '/css/build/home.css', 'deps' => ['integlight-style-plus']],
                //上書き。依存関係をはずす。homeはブロックアイテムはないため依存は不要
                //'integlight-base-style-plus' => ['path' => '/css/build/base-style.css', 'deps' => []],
            ]);
            self::$deferredStyles = array_merge(self::$deferredStyles, [
                'wp-block-library',
            ]);
        }

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
add_action('wp', ['InteglightPreDetermineCssAssets', 'init']);



/****************************************************** */
/* JSの登録                                              */
/****************************************************** */

//js 移動　PF対応!!!
class InteglightPreDetermineJsAssets
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
        InteglightFrontendScripts::add_scripts(self::$scripts);

        $deferredScripts = [
            'integlight-navigation',
        ];
        InteglightDeferJs::add_deferred_scripts($deferredScripts); //PF対応!!!

        // フッターに移動するスクリプトを登録
        //$footerScripts = [
        //  'jquery'   => includes_url('/js/jquery/jquery.min.js') jqueryが不要なときもロードされてしまうため廃止
        //];
        //InteglightMoveScripts::add_scripts($footerScripts);


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
add_action('wp', ['InteglightPreDetermineJsAssets', 'init']);


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

class InteglightThemeColorLoader
{
    public function __construct()
    {
        add_action('wp', [$this, 'enqueue_custom_css']);
    }

    public function enqueue_custom_css()
    {
        $base_pattern = get_theme_mod('integlight_base_color_setting', 'pattern8');



        $styles = ['integlight-custom-color-pattern' => ['path' => '/css/build/' . $base_pattern . '.css', 'deps' => ['integlight-style-plus']]];
        InteglightFrontendStyles::add_styles($styles);
        InteglightEditorStyles::add_styles($styles);
        InteglightDeferCss::add_deferred_styles(['integlight-custom-color-pattern']); //PF対応!!!
    }
}
new InteglightThemeColorLoader();


//////////////////////////////////////////
//ホームタイプ用のcss
//////////////////////////////////////////
class InteglightHomeTypeLoader
{
    public function __construct()
    {
        add_action('wp', [$this, 'enqueue_hometype_css']);
    }

    public function enqueue_hometype_css()
    {
        $home_type = get_theme_mod('integlight_hometype_setting', 'home1');

        $tmp_deps = ['integlight-integlight-menu', 'integlight-custom-color-pattern'];
        // slider かつフロントまたは home の場合に追記
        if (
            'slider' === get_theme_mod('integlight_display_choice', 'none') &&
            (is_front_page())
        ) {
            $tmp_deps[] = 'integlight-slide';
        }

        $styles = ['integlight-home-type' => ['path' => '/css/build/' . $home_type . '.css', 'deps' => $tmp_deps]];
        InteglightFrontendStyles::add_styles($styles);
        InteglightEditorStyles::add_styles($styles);
    }
}
new InteglightHomeTypeLoader();



//////////////////////////////////////////
//スライダーcssのロード
//////////////////////////////////////////
class integlight_load_css
{

    public static function regSliderCss()
    {

        $styles = [
            'integlight-slide' => ['path' => '/css/build/integlight-slide-style.css', 'deps' => ['integlight-style-plus']],
        ];
        InteglightFrontendStyles::add_styles($styles);
    }
}


//////////////////////////////////////////
//jsのロード
//スライダー、LoadMore
//////////////////////////////////////////
class integlight_load_scripts
{

    public static function regSliderScripts()
    {


        $scripts = [
            'integlight_slider-script' =>  ['path' => '/js/build/slider.js'],
            //'integlight_slider-script' =>  ['path' => '/js/build/slider.js', 'deps' => ['jquery']],
        ];
        InteglightFrontendScripts::add_scripts($scripts);


        // 遅延対象のスクリプトを登録
        $deferredScripts = [
            'integlight_slider-script',
        ];
        InteglightDeferJs::add_deferred_scripts($deferredScripts);
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
        InteglightFrontendScripts::add_scripts($scripts);

        $deferredScripts = [
            'integlight-loadmore',
        ];
        InteglightDeferJs::add_deferred_scripts($deferredScripts); //PF対応!!!

    }
    public static function getLoadMoreScriptsHandleName()
    {
        return 'integlight-loadmore';
    }
}

///////////////////////