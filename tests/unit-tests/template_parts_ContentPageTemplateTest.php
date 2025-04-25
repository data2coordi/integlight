<?php

/**
 * Class ContentPageTemplateTest
 *
 * Tests for the template part template-parts/content-page.php
 *
 * @package Integlight
 */

class template_parts_ContentPageTemplateTest extends WP_UnitTestCase
{

    private $page_id;
    private $user_id;

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();

        // テスト用ユーザーを作成し、現在のユーザーとして設定
        $this->user_id = self::factory()->user->create(['role' => 'editor', 'display_name' => 'Test Editor']);
        wp_set_current_user($this->user_id); // 編集リンクのテストに必要

        // テスト用固定ページを作成
        $this->page_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Test Page Title',
            'post_content' => 'This is the test page content.',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);
    }

    /**
     * 各テストメソッド実行後のティアダウン
     */
    public function tear_down()
    {
        // グローバル状態のリセット
        wp_reset_postdata();
        wp_reset_query();
        unset($GLOBALS['post']);
        wp_set_current_user(0); // ユーザーをログアウト

        // 作成したデータのクリーンアップ (通常は WP_UnitTestCase が行う)
        parent::tear_down();
    }

    /**
     * ヘルパー関数: content-page.php の出力を取得します。
     *
     * @param int $page_id テンプレート部品を表示する固定ページのID。
     * @return string キャプチャされたHTML出力。
     */
    private function get_content_page_template_output(int $page_id): string
    {
        global $post;
        $post_obj = get_post($page_id);
        if (!$post_obj) {
            return '';
        }
        $post = $post_obj;
        setup_postdata($post);

        ob_start();
        // テスト対象のテンプレート部品を直接読み込む
        get_template_part('template-parts/content-page');
        $output = ob_get_clean();

        wp_reset_postdata();
        return $output;
    }

    /**
     * @test
     * 個別固定ページ表示時の content-page.php の基本的な出力をテストします。
     */
    public function test_content_page_output_on_single_view()
    {
        // --- Arrange (準備) ---
        // WordPress のクエリコンテキストを個別固定ページに設定
        $this->go_to(get_permalink($this->page_id));
        // コンテキストが正しく設定されたか確認
        $this->assertTrue(is_singular(), 'Context should be singular.');
        $this->assertTrue(is_page(), 'Context should be page.');

        // --- Act (実行) ---
        // ヘルパー関数を使ってテンプレート部品の出力を取得
        $output = $this->get_content_page_template_output($this->page_id);
        // 出力が空でないことを確認
        $this->assertNotEmpty($output, 'Template part output should not be empty.');

        // --- Assert (検証) ---
        // 1. 主要な article タグが存在し、基本的なクラスが含まれるか
        $this->assertStringContainsString('<article id="post-' . $this->page_id . '"', $output);
        //   クラスの順序に依存しないように正規表現でチェック
        $this->assertMatchesRegularExpression('/class="[^"]*\bpage\b/', $output, 'Should have "page" class.');
        $this->assertMatchesRegularExpression('/class="[^"]*\btype-page\b/', $output, 'Should have "type-page" class.');
        $this->assertMatchesRegularExpression('/class="[^"]*\bstatus-publish\b/', $output, 'Should have "status-publish" class.');

        // 2. ヘッダーとタイトル (H1) が存在するか
        $this->assertStringContainsString('<header class="entry-header">', $output);
        $this->assertStringContainsString('<h1 class="entry-title">Test Page Title</h1>', $output);

        // 3. 投稿メタ情報 (.entry-meta) が *存在しない* ことを確認
        $this->assertStringNotContainsString('<div class="entry-meta">', $output, 'Entry meta should not be present on pages.');

        // 4. コンテンツラッパーとコンテンツ本体が存在するか
        $this->assertStringContainsString('<div class="entry-content">', $output);
        $this->assertStringContainsString('This is the test page content.', $output);

        // 5. フッターが存在し、編集リンクが含まれるか
        $this->assertStringContainsString('<footer class="entry-footer">', $output);
        $this->assertStringContainsString('Edit <span class="screen-reader-text">Test Page Title</span>', $output, 'Edit link should be present.');

        // 6. カテゴリーやタグ情報が *存在しない* ことを確認
        $this->assertStringNotContainsString('Posted in', $output, 'Category info should not be present.');
        $this->assertStringNotContainsString('Tagged', $output, 'Tag info should not be present.');
    }
}
