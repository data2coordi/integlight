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

        //$this->setExpectedDeprecated([]); // 既知のdeprecatedを無視

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
        $ttkey = 'integlight_' . $key;

        // テスト前に、既存のキャッシュを確実に削除
        delete_transient($ttkey);

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
        $cached = get_transient($ttkey);
        $this->assertNotFalse($cached, 'メニューキャッシュがトランジェントに保存されていません。');

        // 3. 保存されたキャッシュの内容が期待通りであるか
        $this->assertStringContainsString('Header Test Item', $cached, '保存されたキャッシュにメニュー項目が含まれていません。');
        $this->assertStringContainsString('id="header-menu"', $cached, '保存されたキャッシュに menu_id が含まれていません。');

        // --- 【後片付け】 ---
        // テストで使用したリソースを削除
        delete_transient($ttkey);
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
        $ttkey = 'integlight_' . $key;


        // --- 【外部システムの状態設定】 ---
        // キャッシュをテストするために、事前にダミーのキャッシュを保存する
        $dummy_cached_html = '<nav class="integlight-menu"><a>Cached Dummy Item</a></nav>';
        set_transient($ttkey, $dummy_cached_html, 300); // 有効期限は5分



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
        $this->assertNotFalse(get_transient($ttkey), 'テスト開始前にキャッシュが保存されていません。');


        // header.php をロードし、出力をバッファリング
        ob_start();
        //get_header();
        require get_stylesheet_directory() . '/header.php';

        $output_html = ob_get_clean();


        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力されたHTMLが、事前に保存したダミーのキャッシュと完全に一致することを確認
        $this->assertStringContainsString($dummy_cached_html, $output_html, '出力にキャッシュされたメニューが含まれていません。');

        // 2. キャッシュが上書きされていないことを確認
        $cached_after = get_transient($ttkey);
        $this->assertSame($dummy_cached_html, $cached_after, 'キャッシュが上書きされました。');

        // --- 【後片付け】 ---
        // テストで使用したリソースを削除
        delete_transient($ttkey);
    }


    public function test_postContent_saveCache()
    {
        // --- 【前提条件】テスト環境の準備 ---
        // 非ログインユーザーとしてテストを実行
        wp_logout();

        // カスタマイザーでキャッシュを有効にする
        set_theme_mod('integlight_cache_enable', true);

        // テスト対象の投稿を作成
        $post_id = $this->factory->post->create([
            'post_title'   => 'Test Post for Cache',
            'post_content' => 'This is the original post content.',
            'post_status'  => 'publish',
        ]);

        // グローバル変数 $post を設定
        global $post;
        $post = get_post($post_id);
        setup_postdata($post);

        // テストで使用するキャッシュキーを定義
        $key  = 'post_content_' . $post->ID;
        $ttkey = 'integlight_' . $key;

        // --- 【外部システムの状態設定】 ---
        // 既存のキャッシュを確実に削除
        delete_transient($ttkey);

        // --- 【テスト対象の実行】 ---
        ob_start();
        require get_stylesheet_directory() . '/template-parts/content.php';
        $output_html = ob_get_clean();

        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力されたHTMLに投稿本文が含まれていることを確認
        $this->assertStringContainsString(
            'This is the original post content.',
            $output_html,
            '投稿本文がHTMLに出力されていません。'
        );

        // 2. キャッシュが新しく保存されたことを確認
        $cached = get_transient($ttkey);
        $this->assertNotFalse($cached, 'ポストキャッシュがトランジェントに保存されていません。');

        // 3. 保存されたキャッシュ内容が期待通りであることを確認
        $this->assertStringContainsString(
            'This is the original post content.',
            $cached,
            '保存されたキャッシュに投稿本文が含まれていません。'
        );

        // --- 【後片付け】 ---
        delete_transient($ttkey);
        wp_delete_post($post_id, true);
        wp_reset_postdata();
    }

    /**
     * postのコンテンツキャッシュが利用されることを検証する統合テスト
     * @group post-content-cache-hit
     */
    public function test_postContent_useCache()
    {
        // --- 【前提条件】テスト環境の準備 ---
        //  テスト対象の投稿を作成
        $post_id = $this->factory->post->create([
            'post_title'   => 'Test Post for Cache',
            'post_content' => 'This is the original post content.',
            'post_status'  => 'publish',
        ]);
        //  グローバル変数 $post を設定
        global $post;
        $post = get_post($post_id);
        setup_postdata($post);

        //  テストで使用するキャッシュキーを定義
        $key = 'post_content_' . $post->ID;
        $ttkey = 'integlight_' . $key;
        // --- 【外部システムの状態設定】 ---
        // 6. キャッシュをテストするために、事前にダミーのキャッシュを保存
        $dummy_cached_html = '<div class="cached-content">This is a cached dummy post content.</div>';
        set_transient($ttkey, $dummy_cached_html, 300); // 有効期限は5分
        // 7. displayPostContent()が呼び出される前に、キャッシュが有効な状態であることを確認
        $this->assertNotFalse(get_transient($ttkey), 'テスト開始前にキャッシュが保存されていません。');

        // --- 【テスト対象の実行】 ---
        ob_start();
        require get_stylesheet_directory() . '/template-parts/content.php';
        $output_html = ob_get_clean();


        $cached_after = get_transient($ttkey);
        // --- 【検証】外部から見える結果のみをアサート ---
        // 9. 出力されたHTMLが、事前に保存したダミーキャッシュと一致することを確認
        $this->assertStringContainsString($dummy_cached_html, $output_html, '出力がキャッシュの内容と一致しません。');

        // 10. キャッシュが上書きされていないことを確認
        $this->assertSame($dummy_cached_html, $cached_after, 'キャッシュが上書きされました。');

        // --- 【後片付け】 ---
        // 11. テストで使用したリソースを削除
        delete_transient($ttkey);
        wp_delete_post($post_id, true);
        wp_reset_postdata();
    }

    /**
     * home.php を実際にロードして、キャッシュが保存されることを検証する統合テスト
     * @group integration
     * @group home-content-save-cache
     * @expectedDeprecation the_block_template_skip_link
     */
    public function test_homeContent_saveCache()
    {

        $this->setExpectedDeprecated('the_block_template_skip_link');


        // --- 【前提条件】テスト環境の準備 ---

        // テスト用の home_type を設定
        set_theme_mod('integlight_hometype_setting', 'home1');
        $home_type = get_theme_mod('integlight_hometype_setting', 'home1');

        // テストで使用するキャッシュキーを定義
        $key  = 'home_content_' . $home_type;
        $ttkey = 'integlight_' . $key;

        // 既存キャッシュを削除
        delete_transient($ttkey);

        // カテゴリを作成
        $cat_id = wp_insert_category([
            'cat_name' => 'Test Category',
            'category_nicename' => 'test-category',
            'category_parent' => 0,
        ]);
        // 投稿を作成し、カテゴリを割り当て
        $post_id = $this->factory->post->create([
            'post_title'   => 'Home Test Post',
            'post_content' => 'This is a post for testing home.php output.',
            'post_status'  => 'publish',
            'post_category' => [$cat_id],
        ]);
        // WP_Query を正しくセットアップ
        $this->go_to(home_url());

        // --- 【テスト対象の実行】 ---
        ob_start();
        require get_stylesheet_directory() . '/home.php';
        $output_html = ob_get_clean();

        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力されたHTMLにhome1用のコンテンツが含まれていることを確認
        $this->assertStringContainsString(
            'This is a post for testing home.php output.',
            $output_html,
            'home.php 出力に期待値が含まれていません。'
        );

        // 2. キャッシュが新しく保存されたことを確認
        $cached = get_transient($ttkey);
        $this->assertNotFalse($cached, 'ホームキャッシュがトランジェントに保存されていません。');

        // 3. 保存されたキャッシュ内容が期待通りであることを確認
        $this->assertStringContainsString(
            'This is a post for testing home.php output.',
            $cached,
            '保存されたキャッシュに期待値が含まれていません。'
        );

        // --- 【後片付け】 ---
        delete_transient($ttkey);
    }

    public function test_homeContent_useCache()
    {
        //$this->setExpectedDeprecated('the_block_template_skip_link');



        // WP_Query をセットアップするため、カテゴリ・投稿を作成（実際にはキャッシュが使われるので出力には影響しない）
        $cat_id = wp_insert_category([
            'cat_name' => 'Dummy Category',
            'category_nicename' => 'dummy-category',
            'category_parent' => 0,
        ]);
        $post_id = $this->factory->post->create([
            'post_title'   => 'Dummy Post',
            'post_content' => 'This is a dummy post.',
            'post_status'  => 'publish',
            'post_category' => [$cat_id],
        ]);



        // --- 【前提条件】テスト環境の準備 ---
        // テスト用の home_type を設定
        set_theme_mod('integlight_hometype_setting', 'home1');
        $home_type = get_theme_mod('integlight_hometype_setting', 'home1');

        // テストで使用するキャッシュキーを定義
        $key  = 'home_content_' . $home_type;
        $ttkey = 'integlight_' . $key;

        // --- 【外部システムの状態設定】 ---
        // キャッシュヒット用のダミーHTMLを保存
        $dummy_cached_html = '<div class="cached-home-content">Cached home1 content</div>';
        set_transient($ttkey, $dummy_cached_html, 300); // 有効期限は5分

        // キャッシュが保存されていることを確認
        $this->assertNotFalse(get_transient($ttkey), 'テスト開始前にキャッシュが存在しません。');


        $this->go_to(home_url());

        // --- 【テスト対象の実行】 ---
        ob_start();
        require get_stylesheet_directory() . '/home.php';
        $output_html = ob_get_clean();

        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力に事前に保存したキャッシュ内容が含まれていることを確認
        $this->assertStringContainsString(
            $dummy_cached_html,
            $output_html,
            'home.php 出力にキャッシュが反映されていません。'
        );

        // 2. キャッシュが上書きされていないことを確認
        $cached_after = get_transient($ttkey);
        $this->assertSame(
            $dummy_cached_html,
            $cached_after,
            'キャッシュが上書きされました。'
        );

        // --- 【後片付け】 ---
        delete_transient($ttkey);
        wp_delete_post($post_id, true);
    }


    public function test_sidebar_saveCache()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $GLOBALS['post'] = get_post($post_id);
        setup_postdata($GLOBALS['post']);

        add_filter('is_active_sidebar', function ($active, $index) {
            if ($index === 'sidebar-1') return true;
            return $active;
        }, 10, 2);

        set_theme_mod('integlight_sidebar1_position', 'right');

        $key = 'sidebar-1';
        $ttkey = 'integlight_' . $key;
        delete_transient($ttkey);

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        // <aside> は出力されるけど、キャッシュは <section> のみ
        $this->assertStringContainsString('secondary', $output);

        $cached = get_transient($ttkey);
        $this->assertNotFalse($cached);

        // キャッシュにはウィジェットの <section> が含まれていること
        $this->assertStringContainsString('widget_search', $cached);
        $this->assertStringContainsString('widget_block', $cached);

        delete_transient($ttkey);
    }
    public function test_sidebar_uses_cache_if_exists()
    {
        $post_id = $this->factory->post->create([
            'post_type' => 'post',
        ]);
        $GLOBALS['post'] = get_post($post_id);
        setup_postdata($GLOBALS['post']);

        add_filter('is_active_sidebar', function ($active, $index) {
            if ($index === 'sidebar-1') return true;
            return $active;
        }, 10, 2);

        set_theme_mod('integlight_sidebar1_position', 'right');

        $key = 'sidebar-1';
        $ttkey = 'integlight_' . $key;

        // キャッシュをあらかじめセット
        $dummy_cache = '<section class="cached-widget">Dummy Cached Widget</section>';
        set_transient($ttkey, $dummy_cache);

        ob_start();
        include get_template_directory() . '/sidebar.php';
        $output = ob_get_clean();

        // キャッシュがそのまま出力されているか確認
        $this->assertStringContainsString('Dummy Cached Widget', $output);

        // キャッシュはまだ残っていること
        $this->assertSame($dummy_cache, get_transient($ttkey));

        delete_transient($ttkey);
    }

    /**
     * save_post 発火時に clearAll が呼ばれ、キャッシュが削除されることを検証する統合テスト
     */
    public function xtest_clearAll_on_save_post()
    {
        // --- 前提条件 ---
        wp_logout();
        set_theme_mod('integlight_cache_enable', true);

        // ダミーキャッシュを作成
        $ttkeys = ['home_content_home1', 'post_content_1', 'sidebar-1', 'main_menu'];
        foreach ($ttkeys as $ttkey) {
            set_transient('integlight_' . $ttkey, 'dummy_cache', 300);
            $this->assertNotFalse(get_transient('integlight_' . $ttkey), "キャッシュ $ttkey がセットされていることを確認");
        }













        // --- save_post をトリガー ---
        $post_id = $this->factory->post->create([
            'post_title'   => 'Trigger ClearAll Post',
            'post_content' => 'This post triggers clearAll.',
            'post_status'  => 'publish',
        ]);
        do_action('save_post', $post_id);

        global $wpdb;

        // --- 検証: 全キャッシュが削除されていること ---
        foreach ($ttkeys as $ttkey) {
            $option_name = '_transient_integlight_' . $ttkey;
            $exists = $wpdb->get_var(
                $wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", $option_name)
            );
            $this->assertNull($exists, "DBに $option_name が残っていないこと");
        }
    }

    public function test_clearAll_on_various_hooks()
    {
        global $wpdb;

        // --- 前提条件 ---
        wp_logout();
        set_theme_mod('integlight_cache_enable', true);

        // ダミーキャッシュキー
        $ttkeys = ['home_content_home1', 'post_content_1', 'sidebar-1', 'main_menu'];

        // フック一覧（upgrader_process_complete は除外済）
        $hooks = [
            'save_post'              => [$this->factory->post->create(['post_title' => 'Post', 'post_status' => 'publish'])],
            'edited_term'            => [1, 'category'],
            'activate_plugin'        => ['dummy-plugin/dummy.php'],
            'deactivate_plugin'      => ['dummy-plugin/dummy.php'],
            'customize_save_after'   => [],
            'wp_update_nav_menu'     => [1],
            'wp_delete_nav_menu'     => [1],
            'widget_update_callback' => [[]],
        ];

        foreach ($hooks as $hook => $args) {
            // --- キャッシュを再作成（DBに確実に書き込むため update_option を使う） ---
            foreach ($ttkeys as $ttkey) {
                // データ本体
                update_option('_transient_integlight_' . $ttkey, 'dummy_cache');
                // timeout 値（UNIXタイム）
                update_option('_transient_timeout_integlight_' . $ttkey, time() + 300);
            }

            // オブジェクトキャッシュをクリアして DB と同期させる
            wp_cache_flush();

            // --- before デバッグ: transient一覧を出力（DB直読み） ---
            $results = $wpdb->get_results("
            SELECT option_id, option_name, option_value
            FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_integlight_%'
               OR option_name LIKE '_transient_timeout_integlight_%'
        ");
            error_log("=== BEFORE hook {$hook} ===");
            foreach ($results as $row) {
                error_log("ID: {$row->option_id}, Name: {$row->option_name}, Value: {$row->option_value}");
            }
            error_log("keys:");
            error_log(print_r($ttkeys, true));
            error_log("=== BEFORE hook END ===");

            // --- フックを発火 ---
            do_action_ref_array($hook, $args);

            // --- after: オブジェクトキャッシュをフラッシュして DB を確認 ---
            wp_cache_flush();

            $results = $wpdb->get_results("
            SELECT option_id, option_name, option_value
            FROM {$wpdb->options}
            WHERE option_name LIKE '_transient_integlight_%'
               OR option_name LIKE '_transient_timeout_integlight_%'
        ");
            error_log("=== AFTER hook {$hook} ===");
            foreach ($results as $row) {
                error_log("ID: {$row->option_id}, Name: {$row->option_name}, Value: {$row->option_value}");
            }
            error_log("=== AFTER hook END ===");

            // --- 検証: SQLベースで transient が削除されていること ---
            foreach ($ttkeys as $ttkey) {
                $option_name = '_transient_integlight_' . $ttkey;
                $exists = $wpdb->get_var(
                    $wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", $option_name)
                );
                $this->assertNull($exists, "フック {$hook} で {$option_name} がDBから削除されていること");

                $timeout_name = '_transient_timeout_integlight_' . $ttkey;
                $exists = $wpdb->get_var(
                    $wpdb->prepare("SELECT option_id FROM {$wpdb->options} WHERE option_name = %s", $timeout_name)
                );
                $this->assertNull($exists, "フック {$hook} で {$timeout_name} がDBから削除されていること");
            }
        }
    }
}
