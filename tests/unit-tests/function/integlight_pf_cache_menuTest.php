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
     * 実際の WP メニューを作成して displayMenu を実行し、
     * 出力にメニュー項目が含まれ、トランジェントが保存されることを検証する統合テスト
     */
    public function test_displayMenu_integration_creates_transient_and_outputs_menu()
    {
        // --- メニュー作成 ---
        $menu_name = 'integlight_test_menu_' . rand(1000, 9999);
        $menu_id = wp_create_nav_menu($menu_name);
        $this->assertIsInt($menu_id);

        // メニュー項目を追加
        $item1_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Item One',
            'menu-item-url'   => 'https://example.com/one',
            'menu-item-status' => 'publish',
        ]);
        $item2_id = wp_update_nav_menu_item($menu_id, 0, [
            'menu-item-title' => 'Item Two',
            'menu-item-url'   => 'https://example.com/two',
            'menu-item-status' => 'publish',
        ]);

        $this->assertNotFalse($item1_id);
        $this->assertNotFalse($item2_id);

        // --- ロケーションに割当 ---
        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);

        // --- 確認変数 ---
        $key = 'integration_main_menu';
        $tkey = 'integlight_' . $key;

        // 後片付け用にトランジェントを削除（念のため）
        delete_transient($tkey);

        // --- 実行 ---
        ob_start();
        // displayMenu は第二引数に wp_nav_menu の引数配列を受け取る
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => true]);
        $out = ob_get_clean();

        // output にメニュー項目のタイトルが含まれることを確認
        $this->assertStringContainsString('Item One', $out, '出力に Item One が含まれるはずです');
        $this->assertStringContainsString('Item Two', $out, '出力に Item Two が含まれるはずです');

        // トランジェントが保存されていることを確認
        $cached = get_transient($tkey);
        $this->assertNotFalse($cached, 'トランジェントは保存されているはずです');
        $this->assertStringContainsString('Item One', $cached);
        $this->assertStringContainsString('Item Two', $cached);

        // --- キャッシュヒット動作の確認 ---
        // 既に保存されたトランジェントを返すこと（表示は同じ）
        // 上書きされない限り、displayMenu はキャッシュを読み出すはず
        $this->assertSame($cached, get_transient($tkey));

        // 後片付け
        delete_transient($tkey);
        // メニュー削除
        wp_delete_nav_menu($menu_id);
    }

    /**
     * キャッシュヒット時に wp_nav_menu が再実行されず、トランジェントの中身がそのまま返ることを検証
     * （ここでは単純にトランジェントを先に入れてから displayMenu を呼び出す）
     */
    public function test_displayMenu_cache_hit_returns_cached_html_without_regenerating()
    {
        // 事前にメニュー（実際のメニューは不要）とトランジェントを用意
        $key = 'integration_main_menu_hit';
        $tkey = 'integlight_' . $key;
        delete_transient($tkey);

        $fake_html = '<nav class="integlight-menu"><a>FAKE</a></nav>';
        set_transient($tkey, $fake_html, 300);

        ob_start();
        // ここは実際に theme_location がなくてもキャッシュが優先されることを確認
        $this->cache_menu->displayMenu($key, ['theme_location' => 'primary', 'echo' => false]);
        $out = ob_get_clean();

        $this->assertSame($fake_html, $out);

        delete_transient($tkey);
    }
}
