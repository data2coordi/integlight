<?php

/**
 * @package Integlight
 * @group cache
 * @group integration
 */

class integlight_pf_cache_menuTest extends WP_UnitTestCase
{
    /** @var Integlight_Cache_Menu */
    protected $cache_menu;

    protected function setUp(): void
    {
        parent::setUp();

        // テスト対象クラスが読み込まれていることを前提
        $this->cache_menu = new Integlight_Cache_Menu();

        // テスト用に 'primary' ロケーションを登録しておく（テーマに依存しないように）
        register_nav_menu('primary', 'Primary Menu');

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
     * 非ログイン（ゲスト）でキャッシュ有効、既存キャッシュなし
     */
    public function test_displayMenu_guest_cache_enabled_no_existing_cache()
    {
        // ゲスト状態を保証
        wp_logout();

        $key  = 'integration_guest_no_cache';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントは削除
        delete_transient($tkey);

        // --- テスト用メニュー作成 ---
        $menu_name = 'integlight_test_menu_guest_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);

        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Guest Item',
            'menu-item-url'    => 'https://example.com/guest',
            'menu-item-status' => 'publish',
        ]);

        // ロケーションに割当
        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);

        // --- 実行 ---
        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => true]);
        $out = ob_get_clean();

        // 出力にメニュー項目が含まれること（タイトルまたはURLで確認）
        $this->assertStringContainsString('Guest Item', $out);
        $this->assertStringContainsString('https://example.com/guest', $out);

        // トランジェントが生成されていること
        $cached = get_transient($tkey);
        $this->assertNotFalse($cached, 'トランジェントは保存されているはずです');
        $this->assertStringContainsString('Guest Item', $cached);

        // 後片付け
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
    }

    /**
     * 非ログインでキャッシュ有効、既存キャッシュあり（期限内）
     * 既存トランジェントが返ること
     */
    public function test_displayMenu_guest_cache_enabled_existing_cache()
    {
        wp_logout();

        $key  = 'integration_guest_cache_hit';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントを事前に用意
        $existing_html = '<nav class="integlight-menu"><a>Cached Item</a></nav>';
        set_transient($tkey, $existing_html, 300); // 期限内

        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => false]);
        $out = ob_get_clean();

        // 既存キャッシュが返ること
        $this->assertSame($existing_html, $out);

        // トランジェントは上書きされていないことを確認
        $cached_after = get_transient($tkey);
        $this->assertSame($existing_html, $cached_after);

        // 後片付け
        delete_transient($tkey);
    }

    /**
     * 非ログインでキャッシュ有効、既存キャッシュが期限切れの場合
     * コールバックが実行され、トランジェントが更新されること
     */
    public function test_displayMenu_guest_cache_enabled_expired_cache()
    {
        wp_logout();

        $key  = 'integration_guest_cache_expired';
        $tkey = 'integlight_' . $key;

        // 既存キャッシュを用意して短時間で期限切れにする（確実に期限切れさせるため TTL=1 + sleep）
        $expired_html = '<nav class="integlight-menu"><a>Expired Item</a></nav>';
        set_transient($tkey, $expired_html, 1); // 失効
        // 確実に期限切れさせる
        delete_transient($tkey); // <-- 意図的に削除して「期限切れ」の状態を再現


        // --- テスト用メニュー作成 ---
        $menu_name = 'integlight_test_menu_expired_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);

        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Item Expired',
            'menu-item-url'    => 'https://example.com/expired',
            'menu-item-status' => 'publish',
        ]);

        // ロケーションに割当
        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);

        // --- 実行 ---
        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => true]);
        $out = ob_get_clean();

        // 新しいメニューの出力が返ること。
        $this->assertStringContainsString('Item Expired', $out);


        // トランジェントが更新されていること（更新後のキャッシュに "Expired" または URL が含まれることを確認）
        $cached_after = get_transient($tkey);
        $this->assertNotFalse($cached_after);
        $this->assertStringContainsString('Item Expired', $cached_after);

        // 後片付け
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
    }

    /**
     * 非ログインでカスタマイザがキャッシュ無効、既存トランジェントあり（期限内）の場合
     * コールバックは実行されるが、既存トランジェントは残る（上書きされない）
     */
    public function test_displayMenu_guest_cache_disabled_existing_cache()
    {
        wp_logout();

        $key  = 'integration_guest_cache_disabled';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントを用意（期限内）
        $existing_html = '<nav class="integlight-menu"><a>Existing Item</a></nav>';
        set_transient($tkey, $existing_html, 300);

        // カスタマイザでキャッシュ無効にする
        set_theme_mod('integlight_cache_enable', false);

        // テスト用メニュー
        $menu_name = 'integlight_test_menu_disabled_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Item Disabled',
            'menu-item-url'    => 'https://example.com/disabled',
            'menu-item-status' => 'publish',
        ]);
        set_theme_mod('nav_menu_locations', ['primary' => $menu_id]);

        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => true]);
        $out = ob_get_clean();

        // コールバックが実行され出力は返る
        $this->assertStringContainsString('Item Disabled', $out);

        // トランジェントは既存のまま（更新されていない）
        $cached_after = get_transient($tkey);
        $this->assertNotFalse($cached_after, '既存トランジェントは残っているはず');

        // 後片付け
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
        // カスタマイザ設定を戻す
        set_theme_mod('integlight_cache_enable', true);
    }

    /**
     * ログインユーザー（任意のユーザー）でキャッシュ有効、既存トランジェントなし
     * ログインユーザーではトランジェントは生成されないことを確認
     */
    public function test_displayMenu_logged_in_cache_enabled_no_existing_cache()
    {
        // ダミーユーザーを作成してログインさせる（subscriber で十分）
        $user_id = $this->factory->user->create(['role' => 'subscriber']);
        wp_set_current_user($user_id);

        $key  = 'integration_logged_in_no_cache';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントが残っていた場合は削除
        delete_transient($tkey);

        set_theme_mod('integlight_cache_enable', true);

        // --- メニュー作成 ---
        $menu_name = 'integlight_test_menu_logged_in_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Item LoggedIn',
            'menu-item-url'    => 'https://example.com/loggedin',
            'menu-item-status' => 'publish',
        ]);
        set_theme_mod('nav_menu_locations', ['primary' => $menu_id]);

        // --- 実行 ---
        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => true]);
        $out = ob_get_clean();

        // 出力は返る
        $this->assertStringContainsString('Item LoggedIn', $out);
        $this->assertStringContainsString('https://example.com/loggedin', $out);

        // トランジェントは生成されない（ログインユーザーではキャッシュ無効）
        $this->assertFalse(get_transient($tkey), 'ログインユーザーではトランジェントが生成されないはずです');

        // 後片付け
        wp_delete_nav_menu($menu_id);
        wp_logout();
    }

    /**
     * 非ログインでサイドバー既存キャッシュが返ること
     */
    public function test_displaySidebar_guest_cache_enabled_existing()
    {
        wp_logout();

        $key  = 'integration_sidebar_cache_hit';
        $tkey = 'integlight_' . $key;

        $existing_html = '<aside>Cached Sidebar</aside>';
        set_transient($tkey, $existing_html, 300);

        $cache_sidebar = new Integlight_Cache_Sidebar();

        ob_start();
        $cache_sidebar->displaySidebar($key);
        $out = ob_get_clean();

        $this->assertSame($existing_html, $out);
        $this->assertSame($existing_html, get_transient($tkey));

        delete_transient($tkey);
    }

    /**
     * 非ログインでメインコンテンツ既存キャッシュが返ること
     */
    public function test_displayPostContent_guest_cache_enabled_existing()
    {
        wp_logout();

        $key  = 'integration_main_content_cache_hit';
        $tkey = 'integlight_' . $key;

        $existing_html = '<div>Cached Post Content</div>';
        set_transient($tkey, $existing_html, 300);

        $cache_main = new Integlight_Cache_MainContent();

        ob_start();
        $cache_main->displayPostContent($key);
        $out = ob_get_clean();

        $this->assertSame($existing_html, $out);
        $this->assertSame($existing_html, get_transient($tkey));

        delete_transient($tkey);
    }


    /**
     * 非ログインで TemplatePart の既存キャッシュが返ること
     */
    public function test_displayTemplatePart_guest_cache_enabled_existing()
    {
        wp_logout();

        $key  = 'integration_templatepart_cache_hit';
        $tkey = 'integlight_' . $key;

        $existing_html = '<div class="template-part">Cached TemplatePart</div>';
        set_transient($tkey, $existing_html, 300);

        $cache_template = new Integlight_Cache_TemplatePart();

        // slug と name は実際のファイルが無くてもキャッシュが優先されれば問題ない
        ob_start();
        $cache_template->displayTemplatePart($key, 'dummy-slug', 'dummy-name');
        $out = ob_get_clean();

        $this->assertSame($existing_html, $out);
        $this->assertSame($existing_html, get_transient($tkey));

        delete_transient($tkey);
    }


    /**
     * header.php を実際にロードしてメニューキャッシュを検証する統合テスト
     * @group integration
     * @group header-full-load
     */
    public function test_header_menu_full_integration()
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
        get_header();
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
    public function test_header_menu_full_integration_cache_hit()
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
        get_header();
        $output_html = ob_get_clean();

        // --- 【検証】外部から見える結果のみをアサート ---

        // 1. 出力されたHTMLが、事前に保存したダミーのキャッシュと完全に一致することを確認
        $this->assertSame($dummy_cached_html, $output_html, '出力がキャッシュの内容と一致しません。');

        // 2. キャッシュが上書きされていないことを確認
        $cached_after = get_transient($tkey);
        $this->assertSame($dummy_cached_html, $cached_after, 'キャッシュが上書きされました。');

        // --- 【後片付け】 ---
        // テストで使用したリソースを削除
        delete_transient($tkey);
    }
}
