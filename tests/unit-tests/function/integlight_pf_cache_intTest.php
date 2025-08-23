<?php

use function cli\err;

/**
 * @package Integlight
 * @group cache
 * @group integration
 */

class integlight_pf_cache_intTest extends WP_UnitTestCase
{
    /** @var Integlight_Cache_Menu */
    protected $cache_menu;

    protected function setUp(): void
    {
        parent::setUp();


        // テスト用に 'primary' ロケーションを登録しておく（テーマに依存しないように）
        //register_nav_menu('primary', 'Primary Menu');

        // デフォルトでキャッシュ有効（必要に応じて各テストで上書き）
        set_theme_mod('integlight_cache_enable', true);

        // テスト開始時はログアウト状態にしておく
        wp_logout();
    }

    protected function tearDown(): void
    {
        // テスト終了時はログアウトして後片付け
        wp_logout();
        parent::tearDown();
    }



    /**
     * header.php を実際にロードしてメニューキャッシュが保存されることを検証する統合テスト
     * @group integration
     * @group header-full-load
     */
    public function test_headerMenu_saveCache()
    {
        // --- 【前提条件】テスト環境の準備 ---
        // 非ログインユーザーとしてテストを実行
        wp_logout();

        // カスタマイザーでキャッシュを有効にする
        set_theme_mod('integlight_cache_enable', true);

        // テストで使用するメニューのキーとトランジェントキーを定義
        $key  = 'main_menu';
        $tkey = 'integlight_' . $key;

        // テスト前に、既存のキャッシュを確実に削除
        delete_transient($tkey);

        // --- 【外部システムの状態設定】 ---
        // 'header' テーマロケーションを登録
        //register_nav_menu('header', 'Header Menu');

        // テスト用のメニューを作成
        $menu_id = wp_create_nav_menu('Header Test Menu');
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Header Test Item',
            'menu-item-url'    => 'https://example.com/header-test',
            'menu-item-status' => 'publish',
        ]);

        // 'header' ロケーションにテストメニューを割り当て
        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['header'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);


        // --- 【テスト対象の実行】 ---
        // Integlight_Cache_Menu クラスをインスタンス化
        //$cache_menu = new Integlight_Cache_Menu();
        // get_header() がこのテストクラスのメソッドを呼び出せるように、
        // グローバルスコープにインスタンスをセットアップ
        // (これはあくまで例です。より洗練された方法としては、フックでインスタンスをセットアップする方法もあります。)
        //$GLOBALS['cache_menu_instance'] = $cache_menu;

        // header.php をロードして出力をバッファリング
        ob_start();
        // 実際に get_header() を呼び出すことで header.php がロードされる
        require get_stylesheet_directory() . '/header.php';


        $output_html = ob_get_clean();

        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力されたHTMLが期待通りであるか
        $this->assertStringContainsString('Header Test Item', $output_html, 'メニュー項目がHTMLに出力されていません。');
        $this->assertStringContainsString('id="header-menu"', $output_html, 'header.phpで指定した menu_id がHTMLに出力されていません。');
        $this->assertStringContainsString('class="menuToggle-containerForMenu"', $output_html, 'header.phpで指定した container_class がHTMLに出力されていません。');

        // 2. キャッシュが正しく生成されたか
        $cached = get_transient($tkey);
        $this->assertNotFalse($cached, 'メニューキャッシュがトランジェントに保存されていません。');

        // 3. 保存されたキャッシュの内容が期待通りであるか
        $this->assertStringContainsString('Header Test Item', $cached, '保存されたキャッシュにメニュー項目が含まれていません。');
        $this->assertStringContainsString('id="header-menu"', $cached, '保存されたキャッシュに menu_id が含まれていません。');

        // --- 【後片付け】 ---
        // テストで使用したリソースを削除
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
        unregister_nav_menu('header');
    }

    /**
     * header.phpを実際にロードし、キャッシュが利用されることを検証する統合テスト
     * @group integration
     * @group header-cache-hit
     */
    public function test_headerMenu_cacheHit()
    {
        // --- 【前提条件】テスト環境の準備 ---
        // 非ログインユーザーとしてテストを実行
        wp_logout();

        // カスタマイザーでキャッシュを有効にする
        set_theme_mod('integlight_cache_enable', true);

        // テストで使用するメニューのキーとトランジェントキーを定義
        $key  = 'main_menu';
        $tkey = 'integlight_' . $key;


        // --- 【外部システムの状態設定】 ---
        // キャッシュをテストするために、事前にダミーのキャッシュを保存する
        $dummy_cached_html = '<nav class="integlight-menu"><a>Cached Dummy Item</a></nav>';
        set_transient($tkey, $dummy_cached_html, 300); // 有効期限は5分



        // テスト用メニューを作成
        $menu_id = wp_create_nav_menu('Header Test Menu');
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Header Test Item',
            'menu-item-url'    => 'https://example.com/header-test',
            'menu-item-status' => 'publish',
        ]);

        // 'header' ロケーションにテストメニューを割り当て
        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['header'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);

        // 以下のコードは、キャッシュヒットのテストでは実行する必要がない
        // 新しいメニューを作成したり、ロケーションに割り当てたりする必要はないため、コメントアウト
        // register_nav_menu('header', 'Header Menu');
        // $menu_id = wp_create_nav_menu('Header Test Menu');
        // ...
        // set_theme_mod('nav_menu_locations', $locations);

        // --- 【テスト対象の実行】 ---
        // get_header() が呼び出される前に、キャッシュが有効な状態であることを確認
        $this->assertNotFalse(get_transient($tkey), 'テスト開始前にキャッシュが保存されていません。');


        // header.php をロードし、出力をバッファリング
        ob_start();
        //get_header();
        require get_stylesheet_directory() . '/header.php';

        $output_html = ob_get_clean();


        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力されたHTMLが、事前に保存したダミーのキャッシュと完全に一致することを確認
        $this->assertStringContainsString($dummy_cached_html, $output_html, '出力にキャッシュされたメニューが含まれていません。');

        // 2. キャッシュが上書きされていないことを確認
        $cached_after = get_transient($tkey);
        $this->assertSame($dummy_cached_html, $cached_after, 'キャッシュが上書きされました。');

        // --- 【後片付け】 ---
        // テストで使用したリソースを削除
        delete_transient($tkey);
    }

    /**
     * postのコンテンツキャッシュが利用されることを検証する統合テスト
     * @group post-content-cache-hit
     */
    public function test_postContent_useCache()
    {
        // --- 【前提条件】テスト環境の準備 ---
        // 1. 非ログインユーザーとしてテストを実行
        wp_logout();

        // 2. カスタマイザーでキャッシュを有効にする
        set_theme_mod('integlight_cache_enable', true);

        // 3. テスト対象の投稿を作成
        $post_id = $this->factory->post->create([
            'post_title'   => 'Test Post for Cache',
            'post_content' => 'This is the original post content.',
            'post_status'  => 'publish',
        ]);

        // 4. グローバル変数 $post を設定
        global $post;
        $post = get_post($post_id);
        setup_postdata($post);

        // 5. テストで使用するキャッシュキーを定義
        $key = 'post_content_' . $post->ID;
        $ttkey = 'integlight_' . $key;


        // --- 【外部システムの状態設定】 ---
        // 6. キャッシュをテストするために、事前にダミーのキャッシュを保存
        $dummy_cached_html = '<div class="cached-content">This is a cached dummy post content.</div>';
        set_transient($ttkey, $dummy_cached_html, 300); // 有効期限は5分
        set_transient($ttkey . 'plus', $dummy_cached_html . 'plus', 300); // 有効期限は5分


        $cached_after = get_transient($ttkey);
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey:', $ttkey);
        var_dump('@@@@@@@@@@@@@@@@@@@@@1$dummy_cached_html:', $dummy_cached_html);
        var_dump('@@@@@@@@@@@@@@@@@@@@@1$cached_after:', $cached_after);
        // テスト内：テンプレ読み込みの直前に入れる
        add_action('delete_option', function ($option) {
            if (strpos($option, '_transient_integlight') === 0) {
                error_log("[integlight-debug] delete_option: {$option}");
                var_dump('[integlight-debug] delete_option', $option);
            }
        });
        add_action('updated_option', function ($option) {
            if (strpos($option, '_transient_integlight') === 0) {
                error_log("[integlight-debug] updated_option: {$option}");
                var_dump('[integlight-debug] updated_option', $option);
            }
        });

        add_action('deleted_transient_integlight_post_content_6', function () {
            error_log('[integlight-debug] deleted_transient_integlight_post_content_6 fired');
            var_dump('[integlight-debug] deleted_transient_integlight_post_content_6');
        });


        // --- 【テスト対象の実行】 ---
        // 7. displayPostContent()が呼び出される前に、キャッシュが有効な状態であることを確認
        $this->assertNotFalse(get_transient($ttkey), 'テスト開始前にキャッシュが保存されていません。');


        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey5:', $ttkey);






        global $wpdb;


        $prefix = '_transient_integlight';
        $sql = $wpdb->prepare(
            "SELECT option_name, option_value 
     FROM {$wpdb->options} 
     WHERE option_name LIKE %s",
            $wpdb->esc_like($prefix) . '%'
        );
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey5:', $ttkey);
        $results = $wpdb->get_results($sql, ARRAY_A); // 配列で取得
        var_dump('TRANSIENT_before', $results);

        // 8. ob_start()で出力バッファリングを開始し、テスト対象のメソッドを呼び出す
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey6:', $ttkey);
        ob_start();
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey7:', $ttkey);
        require get_stylesheet_directory() . '/template-parts/content.php';
        $output_html = ob_get_clean();
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey8:', $ttkey);
        $after = $wpdb->get_col($wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like($prefix) . '%'
        ));

        $prefix = '_transient_integlight';
        $sql = $wpdb->prepare(
            "SELECT option_name, option_value 
     FROM {$wpdb->options} 
     WHERE option_name LIKE %s",
            $wpdb->esc_like($prefix) . '%'
        );
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey9:', $ttkey);
        $results = $wpdb->get_results($sql, ARRAY_A); // 配列で取得
        var_dump('TRANSIENT_AFTER', $results);

        // 10. キャッシュが上書きされていないことを確認
        $cached_after = get_transient($ttkey);
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$tkey10:', $ttkey);
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$dummy_cached_html:', $dummy_cached_html);
        var_dump('@@@@@@@@@@@@@@@@@@@@@2$cached_after:', $cached_after);



        // --- 【検証】外部から見える結果のみをアサート ---
        // 9. 出力されたHTMLが、事前に保存したダミーキャッシュと一致することを確認
        $this->assertStringContainsString($dummy_cached_html, $output_html, '出力がキャッシュの内容と一致しません。');


        $this->assertSame($dummy_cached_html, $cached_after, 'キャッシュが上書きされました。');

        // --- 【後片付け】 ---
        // 11. テストで使用したリソースを削除
        delete_transient($ttkey);
        wp_delete_post($post_id, true);
        wp_reset_postdata();
    }
}
