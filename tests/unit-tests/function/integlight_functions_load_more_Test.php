<?php

/**
 */
// WP_UnitTestCase の代わりに WP_Ajax_UnitTestCase を継承することで、AJAXテストのセットアップが簡略化されます。
class integlight_functions_load_more_Test extends WP_Ajax_UnitTestCase
{
    protected string $ajax_action_load_more = 'integlight_load_more_posts';
    protected int $admin_id;
    protected $post_ids = [];

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

    public function test_constructor_registers_template_redirect_hook(): void
    {
        $instance = new Integlight_Load_More();

        $hook_priority = has_action('template_redirect', [$instance, 'pre_enqueue_scripts']);

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add template_redirect hook for pre_enqueue_scripts.'
        );
        $this->assertEquals(
            10,
            $hook_priority,
            'template_redirect hook priority should be 10 by default.'
        );
    }

    public function test_pre_enqueue_scripts_registers_when_condition_met(): void
    {
        // モッククラスを作って静的呼び出しを記録
        InteglightFrontendScripts::$called_scripts = [];
        InteglightDeferJs::$called_scripts = [];

        // 条件を満たすようにモック化
        add_filter('pre_option_theme_mods_' . get_option('stylesheet'), function () {
            return ['integlight_hometype_setting' => 'home2'];
        });
        add_filter('home_url', '__return_true'); // is_home() を true にするため

        $instance = new Integlight_Load_More();
        $instance->pre_enqueue_scripts();

        $this->assertNotEmpty(
            InteglightFrontendScripts::$called_scripts,
            'pre_enqueue_scripts should call add_scripts() when condition met.'
        );
        $this->assertNotEmpty(
            InteglightDeferJs::$called_scripts,
            'pre_enqueue_scripts should call add_deferred_scripts() when condition met.'
        );
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
