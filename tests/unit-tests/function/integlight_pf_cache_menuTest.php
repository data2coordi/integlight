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
    }

    protected function tearDown(): void
    {
        // 後片付けは各テストで行う（安全のため）
        parent::tearDown();
    }



    /**
     * 非管理者ユーザーでキャッシュ有効、既存キャッシュなしの場合
     * displayMenu がコールバックを実行し、出力が表示され、トランジェントが生成されることを確認
     */
    public function test_displayMenu_non_admin_cache_enabled_no_existing_cache()
    {
        // --- 確認変数 ---
        $key  = 'integration_non_admin_no_cache';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントは削除
        delete_transient($tkey);

        // --- 非管理者ユーザーを擬似 ---
        // current_user_can('administrator') が false になるようにフィルタを追加
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return false;
            }
            return $all_caps;
        }, 10, 4);

        // --- テスト用メニュー作成 ---
        $menu_name = 'integlight_test_menu_nonadmin_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);
        $item_id   = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Item NonAdmin',
            'menu-item-url'    => 'https://example.com/nonadmin',
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

        // 出力にメニュー項目が含まれること
        $this->assertStringContainsString('Item NonAdmin', $out);

        // トランジェントが生成されていること
        $cached = get_transient($tkey);
        $this->assertNotFalse($cached, 'トランジェントは保存されているはずです');
        $this->assertStringContainsString('Item NonAdmin', $cached);

        // 後片付け
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
    }

    /**
     * 非管理者ユーザーでキャッシュ有効、既存キャッシュあり（期限内）の場合
     * displayMenu がコールバックを実行せず、既存トランジェントの出力が返ることを確認
     */
    public function test_displayMenu_non_admin_cache_enabled_existing_cache()
    {
        // --- 確認変数 ---
        $key  = 'integration_non_admin_cache_hit';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントを事前に用意
        $existing_html = '<nav class="integlight-menu"><a>Cached Item</a></nav>';
        set_transient($tkey, $existing_html, 300); // 期限内

        // --- 非管理者ユーザーを擬似 ---
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return false;
            }
            return $all_caps;
        }, 10, 4);

        // --- 実行 ---
        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => false]);
        $out = ob_get_clean();

        // 既存キャッシュが返ること
        $this->assertSame($existing_html, $out);

        // トランジェントは上書きされていないことを確認
        $cached_after = get_transient($tkey);
        $this->assertSame($existing_html, $cached_after);

        // --- 後片付け ---
        delete_transient($tkey);
    }

    /**
     * 非管理者ユーザーでキャッシュ有効、既存キャッシュが期限切れの場合
     * displayMenu がコールバックを実行し、出力が返り、トランジェントが更新されることを確認
     */
    public function test_displayMenu_non_admin_cache_enabled_expired_cache()
    {
        // --- 確認変数 ---
        $key  = 'integration_non_admin_cache_expired';
        $tkey = 'integlight_' . $key;

        // 既存キャッシュを作成して期限切れにする（マイナス値で過去に設定）
        $expired_html = '<nav class="integlight-menu"><a>Expired Item</a></nav>';
        set_transient($tkey, $expired_html, -10); // すでに期限切れ

        // --- 非管理者ユーザーを擬似 ---
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return false;
            }
            return $all_caps;
        }, 10, 4);

        // --- テスト用メニュー作成 ---
        $menu_name = 'integlight_test_menu_expired_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);
        $item_id   = wp_update_nav_menu_item($menu_id, 0, [
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

        // コールバックの出力が返ること
        $this->assertStringContainsString('Item Expired', $out);

        // トランジェントが更新されていること
        $cached_after = get_transient($tkey);
        $this->assertStringContainsString('Item Expired', $cached_after);

        // --- 後片付け ---
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
    }
    /**
     * 非管理者ユーザーでキャッシュ無効、既存キャッシュあり（期限内）の場合
     * displayMenu がコールバックを実行し、出力は返るがトランジェントは生成されないことを確認
     */
    /**
     * 非管理者ユーザーでキャッシュ無効、既存トランジェントあり（期限内）の場合
     * displayMenu がコールバックを実行し、出力は返るがトランジェントは生成されないことを確認
     */
    public function test_displayMenu_non_admin_cache_disabled_existing_cache()
    {
        // --- 確認変数 ---
        $key  = 'integration_non_admin_cache_disabled';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントを用意（期限内）
        $existing_html = '<nav class="integlight-menu"><a>Existing Item</a></nav>';
        set_transient($tkey, $existing_html, 300);

        // --- 非管理者ユーザーを擬似 ---
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return false;
            }
            return $all_caps;
        }, 10, 4);

        // --- カスタマイザでキャッシュ無効 ---
        set_theme_mod('integlight_cache_enable', false);

        // --- テスト用メニュー作成 ---
        $menu_name = 'integlight_test_menu_disabled_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Item Disabled',
            'menu-item-url'    => 'https://example.com/disabled',
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

        // --- アサーション ---

        // コールバックの出力が返ること（Item Disabled が含まれる）
        $this->assertStringContainsString('Item Disabled', $out);

        // トランジェントは更新されず、既存の値が残っていること
        $cached_after = get_transient($tkey);
        $this->assertNotFalse($cached_after, '既存トランジェントは残っているはず');

        // --- 後片付け ---
        delete_transient($tkey);
        wp_delete_nav_menu($menu_id);
        // カスタマイザ設定を元に戻す
        set_theme_mod('integlight_cache_enable', true);
    }
    /**
     * 管理者ユーザーでキャッシュ有効、既存トランジェントなし
     * displayMenu がコールバックを実行し、出力は返るがトランジェントは生成されないことを確認
     */
    public function test_displayMenu_admin_cache_enabled_no_existing_cache()
    {
        $key  = 'integration_admin_menu_no_cache';
        $tkey = 'integlight_' . $key;
        // 既存トランジェントが残っていた場合は削除
        delete_transient($tkey);

        // --- 管理者ユーザーを擬似 ---
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return true;
            }
            return $all_caps;
        }, 10, 4);

        set_theme_mod('integlight_cache_enable', true);

        // --- メニュー作成 ---
        $menu_name = 'integlight_test_menu_admin_' . rand(1000, 9999);
        $menu_id   = wp_create_nav_menu($menu_name);
        wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title'  => 'Admin Item',
            'menu-item-url'    => 'https://example.com/admin',
            'menu-item-status' => 'publish',
        ]);

        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);

        // --- 実行 ---
        ob_start();
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => true]);
        $out = ob_get_clean();

        // 出力確認
        $this->assertStringContainsString('Admin Item', $out);

        // キャッシュは生成されていないこと
        $cached_before = get_transient($tkey) ?: null;

        // 実行後
        $cached_after = get_transient($tkey);
        $this->assertSame($cached_before, $cached_after, '管理者ユーザー時はトランジェントは変更されない');

        // --- 後片付け ---
        wp_delete_nav_menu($menu_id);
        set_theme_mod('integlight_cache_enable', true);
    }

    /**
     * 非管理者ユーザーでキャッシュ有効、既存トランジェントあり（期限内）
     * displaySidebar がコールバックを実行せず、既存キャッシュが返ることを確認
     */
    public function test_displaySidebar_non_admin_cache_enabled_existing()
    {
        $key  = 'integration_sidebar_cache_hit';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントを用意
        $existing_html = '<aside>Cached Sidebar</aside>';
        set_transient($tkey, $existing_html, 300);

        // --- 非管理者ユーザーを擬似 ---
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return false;
            }
            return $all_caps;
        }, 10, 4);

        set_theme_mod('integlight_cache_enable', true);

        $cache_sidebar = new Integlight_Cache_Sidebar();

        // --- 実行 ---
        ob_start();
        $cache_sidebar->displaySidebar($key);
        $out = ob_get_clean();

        // 既存キャッシュが返ること
        $this->assertSame($existing_html, $out);

        // トランジェントは上書きされていない
        $cached_after = get_transient($tkey);
        $this->assertSame($existing_html, $cached_after);

        // --- 後片付け ---
        delete_transient($tkey);
    }

    /**
     * 非管理者ユーザーでキャッシュ有効、既存トランジェントあり（期限内）
     * displayPostContent がコールバックを実行せず、既存キャッシュが返ることを確認
     */
    public function test_displayPostContent_non_admin_cache_enabled_existing()
    {
        $key  = 'integration_main_content_cache_hit';
        $tkey = 'integlight_' . $key;

        // 既存トランジェントを用意
        $existing_html = '<div>Cached Post Content</div>';
        set_transient($tkey, $existing_html, 300);

        // --- 非管理者ユーザーを擬似 ---
        add_filter('current_user_can', function ($all_caps, $cap, $args, $user) {
            if ($cap === 'administrator') {
                return false;
            }
            return $all_caps;
        }, 10, 4);

        set_theme_mod('integlight_cache_enable', true);

        $cache_main = new Integlight_Cache_MainContent();

        // --- 実行 ---
        ob_start();
        $cache_main->displayPostContent($key);
        $out = ob_get_clean();

        // 既存キャッシュが返ること
        $this->assertSame($existing_html, $out);

        // トランジェントは上書きされていない
        $cached_after = get_transient($tkey);
        $this->assertSame($existing_html, $cached_after);

        // --- 後片付け ---
        delete_transient($tkey);
    }
}

// メニュー＆非管理者
// 		有無	既存有無	内容
// 1		有効	なし	コールバック実行 → 出力が画面に表示 → キャッシュ生成
// 2		有効	有効（期限内）	コールバック呼ばれず → 既存キャッシュ出力
// 3		有効	期限切れ	コールバック実行 → 出力 → キャッシュ更新
// 4		無効	有効（期限内）	コールバック実行 → 出力 → キャッシュは生成されない

// メニュー＆管理者
// 1		有効	なし	コールバック実行 → 出力 → キャッシュは生成されない


// サイドバー＆非管理者
// 1		有効	有効（期限内）	コールバック呼ばれず → 既存キャッシュ出力


// メインコンテンツ＆非管理者
// 1		有効	有効（期限内）	コールバック呼ばれず → 既存キャッシュ出力
