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
                        'sizes' => '(max-width: 480px) 20vw, 800px', //PF対応!!!：20vwとすることで、srcsetで低解像度を選択させる。
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
function integlight_getAttr_byImageCount(
    int $current_post,
    int $ctPerlineForPc_withHeader,
    int $ctPerlineForPc_noHeader,
    int $ctPerlineForSp_withHeader,
    int $ctPerlineForSp_noHeader
): string {
    $hasHeader = (get_theme_mod('integlight_display_choice', 'none') !== 'none');

    if (wp_is_mobile()) {
        $ct = $hasHeader ? $ctPerlineForSp_withHeader : $ctPerlineForSp_noHeader;
    } else {
        $ct = $hasHeader ? $ctPerlineForPc_withHeader : $ctPerlineForPc_noHeader;
    }

    return ($current_post < $ct) ? ' fetchpriority="high" decoding="async" ' : ' loading="lazy" decoding="async" ';
}

/********************************************************************/
/* fetchprioryにする上位画像数を計算e	*/
/********************************************************************/
