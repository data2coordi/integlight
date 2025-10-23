<?php

/**
 */
// WP_UnitTestCase の代わりに WP_Ajax_UnitTestCase を継承することで、AJAXテストのセットアップが簡略化されます。
class integlight_functions_load_more_Test extends WP_Ajax_UnitTestCase
{
    protected string $ajax_action_load_more = 'integlight_load_more_posts';
    protected int $admin_id;
    protected $post_ids = [];

    private $instance;
    protected int $cat_id;
    protected array $post_ids_cat_test = [];
    protected string $ajax_action_load_more_cat = 'integlight_load_more_category_posts';

    public function setUp(): void
    {
        // WP_Ajax_UnitTestCase の setUp を呼び出します。
        parent::setUp();

        //$this->admin_id = $this->factory->user->create(['role' => 'administrator']);
        //wp_set_current_user($this->admin_id);

    }

    public function tearDown(): void
    {
        // WP_Ajax_UnitTestCase が後処理を行うため、手動でのリセットはほぼ不要です。
        parent::tearDown();
    }


    /**
     * Reflection を使用して静的プロパティをリセットするヘルパーメソッド
     */
    private function reset_static_property(string $className, string $propertyName, $defaultValue = []): void
    {
        try {
            // クラスが存在するか確認
            if (!class_exists($className)) {
                $this->markTestSkipped("Dependency class {$className} not found.");
                return;
            }
            $reflection = new ReflectionProperty($className, $propertyName);
            if (method_exists($reflection, 'setAccessible')) {
                $reflection->setAccessible(true);
            }
            $reflection->setValue(null, $defaultValue);
        } catch (ReflectionException $e) {
            $this->fail("Failed to reset static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * Reflection を使用して静的プロパティの値を取得するヘルパーメソッド
     */
    private function get_static_property_value(string $className, string $propertyName)
    {
        try {
            // クラスが存在するか確認
            if (!class_exists($className)) {
                $this->markTestSkipped("Dependency class {$className} not found.");
                return null;
            }
            $reflectionClass = new ReflectionClass($className);
            $property = $reflectionClass->getProperty($propertyName);
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true);
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            $this->fail("Failed to get static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }




    private function pre_post()
    {

        $this->post_ids = $this->factory->post->create_many(3, [
            'post_status'  => 'publish',
            'post_title'   => 'Test post',
            'post_content' => 'dummy',
        ]);
    }
    public function test_ajax_load_more_posts_success()
    {



        $this->pre_post();
        // POST パラメータをセット
        $_POST['nonce']  = wp_create_nonce('integlight_load_more_nonce');
        $_POST['page']   = 1;
        $_POST['action'] = $this->ajax_action_load_more;

        // AJAX 実行
        try {
            $this->_handleAjax($this->ajax_action_load_more);
        } catch (WPAjaxDieContinueException $e) {
            // 正常終了の例外は無視
        }

        // レスポンス取得
        $response = $this->_last_response;
        $this->assertNotEmpty($response, 'レスポンスが空です');

        // JSON デコード
        $json = json_decode($response, true);
        $this->assertIsArray($json, 'JSON デコード失敗');

        // success チェック
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success'], 'success が true ではありません');

        // HTML 内に投稿タイトルが含まれるか確認
        $this->assertStringContainsString('Test post', $json['data'], '投稿タイトルが含まれていません');
    }
    public function test_ajax_load_more_posts_no_posts()
    {
        $this->pre_post();


        // POST パラメータをセット（ページ数を大きくして存在しない投稿を取得）
        $_POST['nonce']  = wp_create_nonce('integlight_load_more_nonce');
        $_POST['page']   = 999; // 存在しないページ
        $_POST['action'] = $this->ajax_action_load_more;

        // AJAX 実行
        try {
            $this->_handleAjax($this->ajax_action_load_more);
        } catch (WPAjaxDieContinueException $e) {
            // 正常終了の例外は無視
        }

        // レスポンス取得
        $response = $this->_last_response;
        $this->assertNotEmpty($response, 'レスポンスが空です');

        // JSON デコード
        $json = json_decode($response, true);
        $this->assertIsArray($json, 'JSON デコード失敗');

        // success が false であることを確認
        $this->assertArrayHasKey('success', $json);
        $this->assertFalse($json['success'], 'success が false ではありません');

        // data が 'no_more_posts' であることを確認
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('no_more_posts', $json['data'], "data が 'no_more_posts' ではありません");
    }

    private function pre_cat()
    {
        // 投稿用カテゴリを作成
        $this->cat_id = $this->factory->term->create([
            'taxonomy' => 'category',
            'name'     => 'Test Category'
        ]);

        // カテゴリ付き投稿を作成
        $this->post_ids_cat_test = $this->factory->post->create_many(2, [
            'post_status' => 'publish',
            'post_title'  => 'Category post',
            'post_content' => 'dummy content',
            'post_category' => [$this->cat_id],
        ]);
    }
    public function test_ajax_load_more_category_posts_success()
    {
        $this->pre_cat();


        // POST パラメータをセット
        $_POST['nonce']  = wp_create_nonce('integlight_load_more_nonce');
        $_POST['page']   = 1;
        $_POST['cat']    = $this->cat_id;
        $_POST['action'] = $this->ajax_action_load_more_cat;

        // AJAX 実行
        try {
            $this->_handleAjax($this->ajax_action_load_more_cat);
        } catch (WPAjaxDieContinueException $e) {
            // 正常終了の例外は無視
        }

        // レスポンス取得
        $response = $this->_last_response;
        $this->assertNotEmpty($response, 'レスポンスが空です');

        // JSON デコード
        $json = json_decode($response, true);
        $this->assertIsArray($json, 'JSON デコード失敗');

        // success チェック
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success'], 'success が true ではありません');

        // HTML 内に投稿タイトルが含まれるか確認
        $this->assertStringContainsString('Category post', $json['data'], '投稿タイトルが含まれていません');
    }
    public function test_ajax_load_more_category_posts_no_posts()
    {


        $this->pre_cat();



        // POST パラメータをセット（ページ数を大きくして存在しない投稿を取得）
        $_POST['nonce']  = wp_create_nonce('integlight_load_more_nonce');
        $_POST['page']   = 999; // 存在しないページ
        $_POST['cat']    = $this->cat_id;
        $_POST['action'] = $this->ajax_action_load_more_cat;

        // AJAX 実行
        try {
            $this->_handleAjax($this->ajax_action_load_more_cat);
        } catch (WPAjaxDieContinueException $e) {
            // 正常終了の例外は無視
        }

        // レスポンス取得
        $response = $this->_last_response;
        $this->assertNotEmpty($response, 'レスポンスが空です');

        // JSON デコード
        $json = json_decode($response, true);
        $this->assertIsArray($json, 'JSON デコード失敗');

        // success が false であることを確認
        $this->assertArrayHasKey('success', $json);
        $this->assertFalse($json['success'], 'success が false ではありません');

        // data が 'no_more_posts' であることを確認
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('no_more_posts', $json['data'], "data が 'no_more_posts' ではありません");
    }

    public function test_ajax_load_more_category_posts_invalid_category()
    {

        $this->pre_cat();

        // 不正なカテゴリIDを指定
        $_POST['nonce']  = wp_create_nonce('integlight_load_more_nonce');
        $_POST['page']   = 1;
        $_POST['cat']    = 0; // 無効
        $_POST['action'] = $this->ajax_action_load_more_cat;

        // AJAX 実行
        try {
            $this->_handleAjax($this->ajax_action_load_more_cat);
        } catch (WPAjaxDieContinueException $e) {
            // 正常終了の例外は無視
        }

        // レスポンス取得
        $response = $this->_last_response;
        $this->assertNotEmpty($response, 'レスポンスが空です');

        // JSON デコード
        $json = json_decode($response, true);
        $this->assertIsArray($json, 'JSON デコード失敗');

        // success が false であることを確認
        $this->assertArrayHasKey('success', $json);
        $this->assertFalse($json['success'], 'success が false ではありません');

        // data が 'invalid_category' であることを確認
        $this->assertArrayHasKey('data', $json);
        $this->assertEquals('invalid_category', $json['data'], "data が 'invalid_category' ではありません");
    }

    public function test_ajax_load_more_posts_invalid_nonce_try_catch()
    {
        $this->pre_post(); // 必要な投稿セットアップがあれば

        $_POST['nonce']  = 'invalid_nonce';
        $_POST['page']   = 1;
        $_POST['action'] = $this->ajax_action_load_more;

        try {
            $this->_handleAjax($this->ajax_action_load_more);
        } catch (WPAjaxDieStopException $e) {
            // 無効 nonce による wp_die('-1') はここで捕まえる
            $caught = true;
        }

        $this->assertTrue(isset($caught) && $caught, '無効 nonce の場合、WPAjaxDieStopException が発生するはずです');


        // 無効 nonce の場合、_last_response は空になる
        $response = $this->_last_response;
        $this->assertEmpty($response, '無効 nonce の場合、レスポンスは空であるはずです');
    }
    public function test_ajax_load_more_category_posts_invalid_nonce_try_catch()
    {
        $this->pre_cat(); // カテゴリ投稿セットアップがあれば

        $_POST['nonce']  = 'invalid_nonce';
        $_POST['page']   = 1;
        $_POST['cat']    = $this->cat_id;
        $_POST['action'] = $this->ajax_action_load_more_cat;

        $caught = false;
        try {
            $this->_handleAjax($this->ajax_action_load_more_cat);
        } catch (WPAjaxDieStopException $e) {
            // 無効 nonce による wp_die('-1') を捕捉
            $caught = true;
        }

        $this->assertTrue($caught, '無効 nonce の場合、WPAjaxDieStopException が発生するはずです');

        // レスポンスは空であることも確認可能
        $response = $this->_last_response;
        $this->assertEmpty($response, '無効 nonce の場合、レスポンスは空であるはずです');
    }




    /**
     * @test
     * @covers ::__construct
     * コンストラクタがフックを正しく登録するかテスト（スクリプト系 + Ajax系）
     */
    public function constructor_should_add_all_hooks(): void
    {
        // setUpで $this->instance が Integlight_Load_More のインスタンスとして作成されている前提
        $this->instance = new Integlight_loadMore();


        // --- スクリプト関連フック ---
        $hook_priority_pre = has_action('template_redirect', [$this->instance, 'pre_enqueue_scripts']);
        $this->assertNotFalse($hook_priority_pre, 'Constructor should add template_redirect hook for pre_enqueue_scripts.');
        $this->assertEquals(10, $hook_priority_pre, 'template_redirect hook priority should be 10 for pre_enqueue_scripts.');

        $hook_priority_enqueue = has_action('wp_enqueue_scripts', [$this->instance, 'enqueue_scripts']);
        $this->assertNotFalse($hook_priority_enqueue, 'Constructor should add wp_enqueue_scripts hook for enqueue_scripts.');
        $this->assertEquals(10, $hook_priority_enqueue, 'wp_enqueue_scripts hook priority should be 10 for enqueue_scripts.');

        // --- Ajax関連フック ---
        $hook_ajax_posts = has_action('wp_ajax_integlight_load_more_posts', [$this->instance, 'ajax_load_more_posts']);
        $this->assertNotFalse($hook_ajax_posts, 'Constructor should add wp_ajax_integlight_load_more_posts hook.');
        $this->assertEquals(10, $hook_ajax_posts, 'wp_ajax_integlight_load_more_posts hook priority should be 10.');

        $hook_ajax_posts_nopriv = has_action('wp_ajax_nopriv_integlight_load_more_posts', [$this->instance, 'ajax_load_more_posts']);
        $this->assertNotFalse($hook_ajax_posts_nopriv, 'Constructor should add wp_ajax_nopriv_integlight_load_more_posts hook.');
        $this->assertEquals(10, $hook_ajax_posts_nopriv, 'wp_ajax_nopriv_integlight_load_more_posts hook priority should be 10.');

        $hook_ajax_cat = has_action('wp_ajax_integlight_load_more_category_posts', [$this->instance, 'ajax_load_more_category_posts']);
        $this->assertNotFalse($hook_ajax_cat, 'Constructor should add wp_ajax_integlight_load_more_category_posts hook.');
        $this->assertEquals(10, $hook_ajax_cat, 'wp_ajax_integlight_load_more_category_posts hook priority should be 10.');

        $hook_ajax_cat_nopriv = has_action('wp_ajax_nopriv_integlight_load_more_category_posts', [$this->instance, 'ajax_load_more_category_posts']);
        $this->assertNotFalse($hook_ajax_cat_nopriv, 'Constructor should add wp_ajax_nopriv_integlight_load_more_category_posts hook.');
        $this->assertEquals(10, $hook_ajax_cat_nopriv, 'wp_ajax_nopriv_integlight_load_more_category_posts hook priority should be 10.');
    }



    /**
     * @test
     * @covers ::pre_enqueue_scripts
     * home1 または非ホームページではスクリプトが登録されないことをテスト
     */
    public function pre_enqueue_scripts_should_not_add_scripts_when_condition_not_met(): void
    {
        // Arrange: 条件を満たさないテーマ設定にする
        $this->instance = new Integlight_loadMore();


        // Act
        //$this->reset_static_property_value(InteglightFrontendScripts::class, 'scripts')
        $this->instance->pre_enqueue_scripts();

        // Assert: InteglightFrontendScripts に追加されていない
        $frontend_scripts = $this->get_static_property_value(InteglightFrontendScripts::class, 'scripts');
        $this->assertArrayNotHasKey('integlight-loadmore', $frontend_scripts, 'FrontendScripts should not contain "integlight-loadmore".');

        // Assert: InteglightDeferJs にも追加されていない
        $deferred_scripts = $this->get_static_property_value(InteglightDeferJs::class, 'deferred_scripts');
        $this->assertNotContains('integlight-loadmore', $deferred_scripts, 'Deferred scripts should not contain "integlight-loadmore".');
    }
    /**
     * @test
     * @covers ::pre_enqueue_scripts
     * home2 設定時にスクリプトが登録され、遅延スクリプトにも追加されることをテスト
     */
    public function pre_enqueue_scripts_should_add_scripts_and_defer(): void
    {
        // Arrange: 条件を満たすテーマ設定にする
        set_theme_mod('integlight_hometype_setting', 'home2');

        global $wp_query;
        $wp_query = new WP_Query();
        $wp_query->is_home = true;
        $wp_query->is_main_query = true;


        $this->instance = new Integlight_loadMore();

        // Act: メソッドを直接呼び出し
        $this->instance->pre_enqueue_scripts();

        // Assert: InteglightFrontendScripts にスクリプトが追加されたか
        $frontend_scripts = $this->get_static_property_value(InteglightFrontendScripts::class, 'scripts');
        $this->assertArrayHasKey('integlight-loadmore', $frontend_scripts, 'FrontendScripts should have "integlight-loadmore" key.');
        $this->assertEquals(
            '/js/build/loadmore.js',
            $frontend_scripts['integlight-loadmore']['path'],
            'FrontendScripts path should be correct.'
        );

        // Assert: InteglightDeferJs に遅延スクリプトが追加されたか
        $deferred_scripts = $this->get_static_property_value(InteglightDeferJs::class, 'deferred_scripts');
        $this->assertContains(
            'integlight-loadmore',
            $deferred_scripts,
            'Script "integlight-loadmore" should be added for deferring.'
        );
    }

    public function test_enqueue_scripts_localizes_loadmore_script(): void
    {
        // Arrange: $wp_query をホームページとして設定
        global $wp_query;
        $wp_query = new WP_Query();
        $wp_query->is_home = true;
        $wp_query->is_main_query = true;

        // Arrange: テーマ設定 home2
        set_theme_mod('integlight_hometype_setting', 'home2');

        $this->instance = new Integlight_loadMore();

        wp_register_script(
            'integlight-loadmore',
            '/js/build/loadmore.js',
            ['jquery'],
            '1.0',
            true
        );
        // Act: enqueue_scripts() を直接呼ぶ
        $this->instance->enqueue_scripts();

        // Assert: wp_localize_script の結果を確認
        global $wp_scripts;

        $localized = $wp_scripts->get_data('integlight-loadmore', 'data');
        $this->assertNotEmpty($localized, 'wp_localize_script のデータが空です');

        // JSON 部分だけ抽出
        preg_match('/var integlightLoadMore = (.*?);$/m', $localized, $matches);
        $this->assertNotEmpty($matches, 'integlightLoadMore JS オブジェクトが見つかりません');

        $data = json_decode($matches[1], true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('loadMoreText', $data);
        $this->assertArrayHasKey('loadingText', $data);
        $this->assertArrayHasKey('ajax_url', $data);
        $this->assertArrayHasKey('nonce', $data);

        $this->assertEquals(__('もっと見る', 'integlight'), $data['loadMoreText']);
        $this->assertEquals(__('読み込み中...', 'integlight'), $data['loadingText']);
        $this->assertStringContainsString('admin-ajax.php', $data['ajax_url']);
    }
}


/*

テストケース	本体ポイント	具体的確認
Ajax 投稿成功	ajax_load_more_posts()	投稿 HTML が success: true で返る
Ajax 投稿なし	ajax_load_more_posts()	success: false + 'no_more_posts'
Ajax カテゴリ成功	ajax_load_more_category_posts()	投稿 HTML + success: true
Ajax カテゴリなし	ajax_load_more_category_posts()	'no_more_posts'
Ajax 不正カテゴリ	ajax_load_more_category_posts()	'invalid_category'
Ajax 無効 nonce	両方	WPDieException 発生

スクリプト登録	pre_enqueue_scripts()	条件で登録・非登録を確認
ローカライズ	enqueue_scripts()	integlightLoadMore オブジェクトの値を確認
*/
