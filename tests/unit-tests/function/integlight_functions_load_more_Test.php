<?php

/**
 * @group ajax
 * @group integlight
 */
// WP_UnitTestCase の代わりに WP_Ajax_UnitTestCase を継承することで、AJAXテストのセットアップが簡略化されます。
class integlight_functions_load_more_Test extends WP_Ajax_UnitTestCase
{
    protected string $ajax_action_load_more = 'integlight_load_more_posts';
    protected int $admin_id;
    protected $post_ids = [];

    public function setUp(): void
    {
        // WP_Ajax_UnitTestCase の setUp を呼び出します。
        parent::setUp();

        $this->admin_id = $this->factory->user->create(['role' => 'administrator']);
        wp_set_current_user($this->admin_id);

        $this->post_ids = $this->factory->post->create_many(3, [
            'post_status'  => 'publish',
            'post_title'   => 'Test post',
            'post_content' => 'dummy',
        ]);
    }

    public function tearDown(): void
    {
        // WP_Ajax_UnitTestCase が後処理を行うため、手動でのリセットはほぼ不要です。
        parent::tearDown();
    }

    public function test_ajax_load_more_posts_endpoint_exists_and_returns_success2()
    {
        // 推奨
        $_POST['nonce'] = wp_create_nonce('integlight_load_more_nonce');
        $_POST['page']  = 1;
        $_POST['action'] = $this->ajax_action_load_more;
        try {
            // _handleAjax() は内部で do_action, 出力バッファリング、例外処理を行います。
            $this->_handleAjax($this->ajax_action_load_more);
        } catch (WPAjaxDieContinueException $e) {
            // WPAjaxDieContinueException は正常な終了を示すので、ここでは何もしません。
            // これ以外の例外はテスト失敗として扱われます。

        }


        // レスポンスは $this->_last_response に格納されます。
        $response = $this->_last_response;
        $this->assertNotEmpty($response);

        $json = json_decode($response, true);
        $this->assertIsArray($json);
        $this->assertArrayHasKey('success', $json);
        $this->assertTrue($json['success']);
        $this->assertStringContainsString('Test post', $json['data']);
    }
}

    // Integlight_Load_More のブラックボックステストケース一覧
    // No	テスト項目	入力・操作内容	期待される出力・結果	備考
    // 1	Ajaxエンドポイント存在確認	wp_ajax_integlight_load_more_posts にPOSTアクセス	HTTP 200（JSON）で成功レスポンスを返す	エンドポイントが正しく機能しているか
    // 2	Ajaxエンドポイント存在確認（非ログイン）	wp_ajax_nopriv_integlight_load_more_posts にPOSTアクセス	HTTP 200（JSON）で成功レスポンスを返す	未ログインユーザーからのアクセス確認
    // 3	Ajax投稿読み込み成功（一般投稿）	正しい page と有効なnonce付きPOSTリクエスト	success: true かつHTML本文（投稿リスト）を含むJSONを返す	投稿が存在する場合
    // 4	Ajax投稿読み込み終了（投稿なし）	ページ数が存在しないページ（例: 大きいpage）を指定	success: false と no_more_posts エラーコードのJSONを返す	追加投稿なしのケース
    // 5	Ajax投稿読み込み失敗（nonce不正）	不正なnonceを送信	WPのnonceエラーメッセージまたは401系のエラー	セキュリティチェックの有効性
    // 6	Ajaxカテゴリ投稿成功	cat IDと page、有効nonce付きPOSTリクエスト	success: true かつ該当カテゴリの投稿HTMLリストのJSONを返す	カテゴリ絞り込みが正常に機能
    // 7	Ajaxカテゴリ投稿失敗（カテゴリID不正）	catに0や無効値を指定	success: false と invalid_category エラーコードのJSONを返す	カテゴリIDの必須チェック
    // 8	Ajaxカテゴリ投稿終了（投稿なし）	存在しないページまたはカテゴリ指定	success: false と no_more_posts エラーコードのJSONを返す	投稿がないカテゴリの場合
    // 9	条件付きスクリプト登録確認	is_home()かつテーマ設定が home2の時にアクセス	integlight-loadmoreスクリプトが登録されている	条件に合致する時だけスクリプトが読み込まれるか
    // 10	条件付きスクリプト非登録確認	is_home()がfalseまたはテーマ設定が home1の時	スクリプトが登録されていない	不要なスクリプトの読み込み防止
    // 11	ローカライズスクリプト内容検証	integlight-loadmore に正しく ajax_url、nonce、loadMoreTextが渡る	ローカライズオブジェクトに期待値が含まれている	JSとの連携検証