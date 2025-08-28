<?php



/*
add_action('wp_footer', function () {

	global $wpdb;

	$queries = $wpdb->queries;

	// 全クエリの合計時間
	$total_time = array_sum(array_column($queries, 1));

	// 個別クエリを実行時間で降順ソート
	usort($queries, function ($a, $b) {
		return $b[1] <=> $a[1]; // $query[1] = 実行時間（秒）
	});

	echo '<pre>';
	echo "=== 全クエリ合計時間: " . number_format($total_time, 6) . " sec ===\n\n";

	foreach ($queries as $query) {
		echo $query[0] . " — " . number_format($query[1], 6) . " sec — " . $query[2] . "\n\n";
	}
	echo '</pre>';
});
*/


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


/****************************************************** */
/* ccsの登録                                              */
/****************************************************** */
class InteglightPreDetermineCssAssets
{
    private static $styles = [
        'integlight-base-style-plus' => '/css/build/base-style.css',
        'integlight-style-plus' => '/css/build/integlight-style.css',
        'integlight-sp-style' => '/css/build/integlight-sp-style.css',
        'integlight-layout' => '/css/build/layout.css',
        'integlight-integlight-menu' => '/css/build/integlight-menu.css',
        'integlight-module' => '/css/build/module.css',
        'integlight-helper' => '/css/build/helper.css',
    ];


    private static $deferredStyles = [
        'integlight-sp-style',
        'wp-block-library' /*ブロックアイテム用css*/
    ];


    public static function init()
    {

        // 以下、必要に応じて追加
        if (is_single()) {
            self::$styles = array_merge(self::$styles, [
                'integlight-post' => '/css/build/post.css',
            ]);
        }

        if (is_page()) {
            self::$styles = array_merge(self::$styles, [
                'integlight-page' => '/css/build/page.css',
            ]);
        }

        if (is_front_page() && (!is_home())) {
            self::$styles = array_merge(self::$styles, [
                'integlight-front' => '/css/build/front.css',
            ]);
        }

        if (is_home()) {
            self::$styles = array_merge(self::$styles, [
                'integlight-home' => '/css/build/home.css',
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


/********************************************************************/
/* カスタムロゴ画像取得 s	*/
/********************************************************************/

/**
 * カスタムロゴのsizes属性を変更するフィルター
 *
 * @param string $sizes 生成されたsizes属性の文字列。
 * @return string 変更後のsizes属性の文字列。
 */
function integlight_custom_logo_sizes($attr, $attachment_id, $size)
{
    // sizes 属性を上書き
    $attr['sizes'] = '(max-width: 450px) 20vw, 700px';

    return $attr;
}
add_filter('get_custom_logo_image_attributes', 'integlight_custom_logo_sizes', 10, 3);


/********************************************************************/
/* カスタムロゴ画像取得 e	*/
/*******************************************************************



/********************************************************************/
/* アイキャッチ画像取得(存在しなければ、本文の画像、デフォルト画像を取得) s	*/
/********************************************************************/


/**
 * integlight_post_thumbnail
 * サムネイルを付与
 * @package Integlight
 */
if (! function_exists('integlight_post_thumbnail')) :
    function integlight_post_thumbnail()
    {
        if (post_password_required() || is_attachment() || ! has_post_thumbnail()) {
            return;
        }

        if (is_singular()) :
?>

            <div class="post-thumbnail">
                <?php the_post_thumbnail(
                    'full',
                    [
                        'class' => 'responsive-img',
                        'loading' => 'eager',
                        'decoding' => 'async',
                        'sizes' => '(max-width: 480px) 80vw, 800px', //PF対応!!!：20vwとすることで、srcsetで低解像度を選択させる。
                        'fetchpriority' => 'high'  // PF対応!!!

                    ]
                );
                /*
				PF対応!!!の前提：20vwとすることで下記のsrcsetの300w用画像を選択させる。
				sizes="(max-width: 850px) 20vw, 900px"
				srcset="
					https://.../Firefly_882369-300x171.webp 300w,
					https://.../Firefly_882369-768x439.webp 768w,
					https://.../Firefly_882369.webp 900w

				*/

                ?>
            </div><!-- .post-thumbnail -->




        <?php else : ?>

            <a class="post-thumbnail" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
                <?php
                the_post_thumbnail(
                    'post-thumbnail',
                    [
                        'alt' => the_title_attribute(['echo' => false]),
                        'fetchpriority' => 'high', // PF対応!!!
                    ]
                );
                ?>
            </a>

<?php
        endif; // End is_singular().
    }
endif;



/********************************************************************/
/* アイキャッチ画像取得(存在しなければ、本文の画像、デフォルト画像を取得) e	*/
/********************************************************************/


/********************************************************************/
/* サムネイル取得(存在しなければ、本文の画像、デフォルト画像を取得) s	*/
/********************************************************************/

class Integlight_PostThumbnail
{

    private static function get_thumbnail_url($post_id = null, $size = 'medium', $default_url = '')
    {


        if (is_null($post_id)) {
            $post_id = get_the_ID();
        }

        // アイキャッチ画像がある場合
        if (has_post_thumbnail($post_id)) {
            $thumbnail_url = get_the_post_thumbnail_url($post_id, $size);
            return esc_url($thumbnail_url);
        };

        // 本文から最初の画像を抽出
        $content = get_post_field('post_content', $post_id);
        preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $image);

        if (!empty($image['src'])) {
            return esc_url($image['src']);
        }

        // デフォルト画像（未指定時は /assets/default.webp）
        if (empty($default_url)) {
            $default_url = get_template_directory_uri() . '/assets/default.webp';
            return esc_url($default_url);
        }
    }

    /**
     * 指定投稿の表示用サムネイルHTMLを出力する。
     * @param int|null $post_id 投稿ID（省略時は現在の投稿）
     * @param string $size アイキャッチ画像のサイズ（デフォルト: 'medium'）
     * @param string $default_url デフォルト画像のURL（空なら /assets/default.webp）
     */

    public static function render($post_id = null, $size = 'medium', $default_url = '', $attr = ' loading="lazy" decoding="async" ')
    {
        $url = self::get_thumbnail_url($post_id, $size, $default_url);
        echo '<img src="' . $url . '" alt=""' . $attr . '>';
    }

    public static function getUrl($post_id = null, $size = 'thumbnail', $default_url = '')
    {
        return self::get_thumbnail_url($post_id, $size, $default_url);
    }
}
/********************************************************************/
/* サムネイル取得(存在しなければ、本文の画像、デフォルト画像を取得) e	*/
/********************************************************************/



/********************************************************************/
/* fetchpriorityにする上位画像数を計算s	*/
/********************************************************************/




class Integlight_getAttr_byImageCount
{
    private static array $patterns = [
        ['env' => 'SP', 'homeType' => 'home1', 'header' => 'none', 'headCt' => 0, 'bodyCt' => 1],
        ['env' => 'PC', 'homeType' => 'home1', 'header' => 'none', 'headCt' => 0, 'bodyCt' => 3],
        ['env' => 'SP', 'homeType' => 'home2', 'header' => 'none', 'headCt' => 0, 'bodyCt' => 2],
        ['env' => 'PC', 'homeType' => 'home2', 'header' => 'none', 'headCt' => 0, 'bodyCt' => 4],
        ['env' => 'SP', 'homeType' => 'home1', 'header' => 'exist', 'headCt' => 1, 'bodyCt' => 0],
        ['env' => 'PC', 'homeType' => 'home1', 'header' => 'exist', 'headCt' => 1, 'bodyCt' => 0],
        ['env' => 'SP', 'homeType' => 'home2', 'header' => 'exist', 'headCt' => 1, 'bodyCt' => 1],
        ['env' => 'PC', 'homeType' => 'home2', 'header' => 'exist', 'headCt' => 3, 'bodyCt' => 2],
    ];

    public static function getCurrentPattern(): ?array
    {
        $env = wp_is_mobile() ? 'SP' : 'PC';
        $homeType = get_theme_mod('integlight_hometype_setting', 'home1');
        $display_choice = get_theme_mod('integlight_display_choice');
        $header = ($display_choice === 'none') ? 'none' : 'exist';

        foreach (self::$patterns as $pattern) {
            if ($pattern['env'] === $env && $pattern['homeType'] === $homeType && $pattern['header'] === $header) {
                return $pattern;
            }
        }

        return null;
    }

    public static function getBodyImageAttr(int $current_post): string
    {
        $pattern = self::getCurrentPattern();
        if (!$pattern) {
            return ' loading="lazy" decoding="async" ';
        }

        $priorityCount = $pattern['bodyCt'];

        return ($current_post < $priorityCount)
            ? ' fetchpriority="high"  loading="eager" decoding="async" '
            : ' fetchpriority="low"  loading="lazy" decoding="async" ';
    }

    /**
     * ヘッダー画像用の属性を返す
     *
     * @param int $current_post 現在のヘッダー画像の順番（0スタート）
     * @return string HTML属性
     */
    public static function getHeaderImageAttr(int $current_post): array
    {
        $pattern = self::getCurrentPattern();
        if (!$pattern) {
            return ['loading' => 'lazy', 'decoding' => 'async'];
        }

        $priorityCount = $pattern['headCt']; // header用の優先読み込み数

        return ($current_post < $priorityCount)
            ? ['fetchpriority' => 'high', 'loading' => 'eager', 'decoding' => 'async']
            : ['fetchpriority' => 'low', 'loading' => 'lazy', 'decoding' => 'async'];
    }
}


/********************************************************************/
/* fetchprioryにする上位画像数を計算e	*/
/********************************************************************/

/********************************************************************/
/* 本文画像の遅延機能 s	*/
/********************************************************************/

/*シンプル版*/
// function add_lazy_and_low_priority_to_content_images_final($content)
// {
//     // 投稿本文に画像がなければ処理を終了
//     if (false === strpos($content, '<img')) {
//         return $content;
//     }

//     // 正規表現で<img>タグを検索し、属性を追加・変更する
//     // これにより、他のHTML構造を壊すことなく、安全に処理を行う
//     $content = preg_replace_callback('/<img([^>]+?)>/i', function ($matches) {
//         $img_tag = $matches[0];
//         $attributes = $matches[1];


//         // loading="lazy" を追加
//         if (strpos($attributes, ' loading=') === false) {
//             $attributes .= ' loading="lazy"';
//         }

//         // fetchpriority="low" を追加
//         if (strpos($attributes, ' fetchpriority=') === false) {
//             $attributes .= ' fetchpriority="low"';
//         }

//         return '<img' . $attributes . '>';
//     }, $content);

//     return $content;
// }

// the_contentフィルターにフック
// 優先度を99に設定することで、他のプラグインの後に処理を実行させる
//add_filter('the_content', 'add_lazy_and_low_priority_to_content_images_final', 99);

/*安全版*/
// function add_lazy_and_low_priority_to_content_images_safe_fragment($content)
// {
//     if (false === strpos($content, '<img')) {
//         return $content;
//     }

//     // <img> タグだけを対象にする正規表現（堅牢なワード境界）
//     $content = preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
//         $img_html = $matches[0];

//         // 小さな DOMDocument を作ってこの <img> フラグメントだけを扱う
//         libxml_use_internal_errors(true);
//         $dom = new DOMDocument('1.0', 'UTF-8');

//         // フラグメント単位なら wrapper を使う（エンティティの影響が小さい）
//         $wrapped = '<!DOCTYPE html><html><body>' . $img_html . '</body></html>';
//         $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

//         $imgs = $dom->getElementsByTagName('img');
//         if ($imgs->length === 0) {
//             // 予期せぬケース（パースできなかった）→元のまま返す
//             libxml_clear_errors();
//             return $img_html;
//         }

//         $img = $imgs->item(0);

//         // 既存属性は壊さず、なければ追加する
//         if (!$img->hasAttribute('loading')) {
//             $img->setAttribute('loading', 'lazy');
//         }
//         if (!$img->hasAttribute('fetchpriority')) {
//             $img->setAttribute('fetchpriority', 'low');
//         }

//         // 変更した <img> ノードのみをシリアライズして返す
//         $new_tag = $dom->saveHTML($img);

//         libxml_clear_errors();
//         return $new_tag !== null ? $new_tag : $img_html;
//     }, $content);

//     return $content;
// }

//add_filter('the_content', 'add_lazy_and_low_priority_to_content_images_safe_fragment', 99);

//安全版の高速化対応
function add_lazy_and_low_priority_to_content_images_static($content)
{
    global $post;

    if (empty($post) || !has_post_thumbnail($post->ID)) {
        return $content;
    }

    if (false === strpos($content, '<img')) {
        return $content;
    }

    return preg_replace_callback('/<img\b[^>]*>/i', function ($matches) {
        $img_html = $matches[0];

        $dom = new DOMDocument();
        $prev = libxml_use_internal_errors(true);

        $dom->loadHTML(
            '<?xml encoding="UTF-8"><html><body>' . $img_html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        $imgs = $dom->getElementsByTagName('img');
        if ($imgs->length === 0) return $img_html;

        $img = $imgs->item(0);
        if (!$img->hasAttribute('loading'))       $img->setAttribute('loading', 'lazy');
        if (!$img->hasAttribute('fetchpriority')) $img->setAttribute('fetchpriority', 'low');

        return $dom->saveHTML($img);
    }, $content);
}

add_filter('the_content', 'add_lazy_and_low_priority_to_content_images_static', 99);



/********************************************************************/
/* 本文画像の遅延機能 e	*/
/********************************************************************/




/********************************************************************/
/* コンテンツのキャッシュ機能s	*/
/********************************************************************/

// ==============================
// 親クラス（共通処理）
// ==============================
abstract class Integlight_Cache_Base
{
    protected static $prefix = 'integlight_';
    protected $default_expiration;

    public function __construct($default_expiration = 300)
    {
        $this->default_expiration = (int) $default_expiration;
    }

    protected function transientKey($key)

    {
        $device = wp_is_mobile() ? 'sp' : 'pc';
        return static::$prefix . $device . '_' . (string) $key;
    }



    protected function isLoggedInUser()
    {
        return is_user_logged_in();
    }

    protected function getExpiration($expiration)
    {
        return ($expiration === null) ? $this->default_expiration : (int) $expiration;
    }

    /**
     * カスタマイザでキャッシュが有効か確認
     */
    protected function isCacheEnabled()
    {
        // カスタマイザ設定 'integlight_cache_enable' を想定
        // デフォルトは true（有効）
        return get_theme_mod('integlight_cache_enable', true);
    }

    /**
     * 汎用：コールバックを実行して出力をキャッシュ
     * サブクラスはコールバックと引数だけを渡せばOK
     */
    public function display($callback, $key, $args = [], $expiration = null)
    {
        $is_logged_in = $this->isLoggedInUser();
        $cache_enabled = $this->isCacheEnabled(); // ここでカスタマイザ設定を確認

        $tkey = $this->transientKey($key);
        $expiration = $this->getExpiration($expiration);

        $cached = (!$is_logged_in && $cache_enabled) ? get_transient($tkey) : false;

        if ($cached === false) {
            ob_start();
            call_user_func_array($callback, $args); // 出力をコールバックで生成
            $cached = ob_get_clean();

            if (!$is_logged_in) {
                set_transient($tkey, $cached, $expiration);
            }
        }

        echo $cached;
    }



    public function delete($key)
    {
        delete_transient($this->transientKey($key));
    }


    /**
     * すべてのキャッシュを効率的に削除（静的メソッド）
     */
    public static function clearAll($dummy = null)
    {
        global $wpdb;
        $prefix = static::$prefix;

        $sql = "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_{$prefix}%'";

        // 安全装置: SQL に "integlight" が含まれていなければ処理を中止
        if (strpos($sql, 'integlight') === false) {
            return;
        }
        $wpdb->query($sql);
    }

    /**
     * キャッシュクリアのためのフックをすべて登録
     */
    public static function registerHooks()
    {
        // 投稿・カテゴリの変更
        add_action('save_post', [static::class, 'clearAll']);
        add_action('edited_term', [static::class, 'clearAll']);

        // プラグイン・テーマの変更
        add_action('upgrader_process_complete', [static::class, 'clearAll']);
        add_action('activate_plugin', [static::class, 'clearAll']);
        add_action('deactivate_plugin', [static::class, 'clearAll']);

        // カスタマイザーの変更
        add_action('customize_save_after', [static::class, 'clearAll']);

        // メニューの変更
        add_action('wp_update_nav_menu', [static::class, 'clearAll']);
        add_action('wp_delete_nav_menu', [static::class, 'clearAll']);

        // ウィジェット内容の変更
        add_action('updated_option', function ($option) {
            if (strpos($option, 'widget_') === 0) {
                static::clearAll();
            }
        }, 10, 1);

        // ウィジェット配置の変更
        add_action('update_option_sidebars_widgets', [static::class, 'clearAll']);
    }
}


class Integlight_Cache_Sidebar extends Integlight_Cache_Base
{
    public function displaySidebar($key, $sidebar_id = 'sidebar-1', $expiration = null)
    {
        $this->display('dynamic_sidebar', $key, [$sidebar_id], $expiration);
    }
}

class Integlight_Cache_Menu extends Integlight_Cache_Base
{
    public function displayMenu($key, $wp_nav_args = [], $expiration = null)
    {
        $this->display('wp_nav_menu', $key, [$wp_nav_args], $expiration);
    }
}

class Integlight_Cache_MainContent extends Integlight_Cache_Base
{
    public function displayPostContent($key, $expiration = null)
    {
        $this->display('the_content', $key, [], $expiration);
    }
}

class Integlight_Cache_TemplatePart extends Integlight_Cache_Base
{
    public function displayTemplatePart($key, $slug, $name = null, $expiration = null)
    {
        $this->display(
            'get_template_part',  // コールバックは文字列で渡す
            $key,
            [$slug, $name],       // 引数は配列で渡す
            $expiration
        );
    }
}


if (class_exists('Integlight_Cache_Base')) {
    Integlight_Cache_Base::registerHooks();
}
/********************************************************************/
/* コンテンツのキャッシュ機能e	*/
/********************************************************************/


// functions.php に追加

// 1. カスタム画像サイズを登録
// add_action('wp_head', function () {
//     if (is_single() && has_post_thumbnail()) {
//         $thumb_id = get_post_thumbnail_id();

//         // medium サイズを使用（幅約300px）
//         $preload_src = wp_get_attachment_image_url($thumb_id, 'medium_large');

//         if ($preload_src) {
//             echo '<link rel="preload" as="image" href="' . esc_url($preload_src) . '" media="(max-width: 600px)" fetchpriority="high">' . "\n";
//         }
//     }
// }, 1);
