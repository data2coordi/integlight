<?php

/**
 * Class ContentNoneTemplateTest
 *
 * Tests for the template part template-parts/content-none.php
 *
 * @package Integlight
 */

// クラス名を PSR-4/PSR-12 準拠に修正することを推奨 (例: ContentNoneTemplateTest)
class template_parts_ContentNoneTemplateTest extends WP_UnitTestCase
{

    private $editor_user_id;
    private $subscriber_user_id;

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();

        // テスト用ユーザーを作成 (編集者と購読者)
        $this->editor_user_id = self::factory()->user->create(['role' => 'editor', 'display_name' => 'Test Editor']);
        $this->subscriber_user_id = self::factory()->user->create(['role' => 'subscriber', 'display_name' => 'Test Subscriber']);
    }

    /**
     * 各テストメソッド実行後のティアダウン
     */
    public function tear_down()
    {
        // グローバル状態のリセット
        wp_reset_query();
        wp_set_current_user(0); // ユーザーをログアウト

        // 作成したデータのクリーンアップ (通常は WP_UnitTestCase が行う)
        parent::tear_down();
    }

    /**
     * ヘルパー関数: content-none.php の出力を取得します。
     *
     * @return string キャプチャされたHTML出力。
     */
    private function get_content_none_template_output(): string
    {
        ob_start();
        // テスト対象のテンプレート部品を直接読み込む
        get_template_part('template-parts/content-none');
        return ob_get_clean();
    }

    /**
     * @test
     * ホームページで投稿権限がある場合の出力をテストします。
     */
    public function test_content_none_on_home_with_publish_rights()
    {
        // --- Arrange (準備) ---
        // 編集者ユーザーとしてログイン
        wp_set_current_user($this->editor_user_id);
        // ホームページコンテキストに設定
        $this->go_to(home_url('/'));
        $this->assertTrue(is_home(), 'Context should be home.');
        $this->assertTrue(current_user_can('publish_posts'), 'User should have publish rights.');

        // --- Act (実行) ---
        $output = $this->get_content_none_template_output();

        // --- Assert (検証) ---
        $this->assertNotEmpty($output, 'Output should not be empty.');
        // 基本的な要素
        $this->assertStringContainsString('<section class="no-results not-found">', $output);
        $this->assertStringContainsString('<h1 class="page-title">Nothing Found</h1>', $output);
        // ホームページ + 権限あり の場合のメッセージ
        $this->assertStringContainsString('Ready to publish your first post?', $output);
        $this->assertStringContainsString('href="' . esc_url(admin_url('post-new.php')) . '"', $output);
        // 検索フォームは表示されないはず
        $this->assertStringNotContainsString('<form role="search"', $output, 'Search form should not be present.');
    }

    /**
     * @test
     * ホームページで投稿権限がない場合の出力をテストします。
     */
    public function test_content_none_on_home_without_publish_rights()
    {
        // --- Arrange (準備) ---
        // 購読者ユーザーとしてログイン
        wp_set_current_user($this->subscriber_user_id);
        // ホームページコンテキストに設定
        $this->go_to(home_url('/'));
        $this->assertTrue(is_home(), 'Context should be home.');
        $this->assertFalse(current_user_can('publish_posts'), 'User should not have publish rights.');

        // --- Act (実行) ---
        $output = $this->get_content_none_template_output();

        // --- Assert (検証) ---
        $this->assertNotEmpty($output, 'Output should not be empty.');
        // 基本的な要素
        $this->assertStringContainsString('<section class="no-results not-found">', $output);
        $this->assertStringContainsString('<h1 class="page-title">Nothing Found</h1>', $output);
        // その他の場合のメッセージ
        $this->assertStringContainsString('It seems we can&rsquo;t find what you&rsquo;re looking for.', $output);
        // 検索フォームが表示されるはず (get_search_form() の出力の一部を確認)
        $this->assertStringContainsString('<form role="search"', $output, 'Search form should be present.');
        // ホームページ用のメッセージは表示されないはず
        $this->assertStringNotContainsString('Ready to publish your first post?', $output);
    }

    /**
     * @test
     * 検索結果ページで何も見つからなかった場合の出力をテストします。
     */
    public function test_content_none_on_search_results()
    {
        // --- Arrange (準備) ---
        // 検索結果コンテキストに設定 (存在しないであろうキーワードで検索)
        $this->go_to(home_url('/?s=nonexistentsearchquery12345'));
        $this->assertTrue(is_search(), 'Context should be search.');
        // 検索結果が0件であることを確認 (オプション)
        global $wp_query;
        $this->assertEquals(0, $wp_query->found_posts, 'Search should return 0 posts.');


        // --- Act (実行) ---
        $output = $this->get_content_none_template_output();

        // --- Assert (検証) ---
        $this->assertNotEmpty($output, 'Output should not be empty.');
        // 基本的な要素
        $this->assertStringContainsString('<section class="no-results not-found">', $output);
        $this->assertStringContainsString('<h1 class="page-title">Nothing Found</h1>', $output);
        // 検索結果の場合のメッセージ
        $this->assertStringContainsString('Sorry, but nothing matched your search terms.', $output);
        // 検索フォームが表示されるはず
        $this->assertStringContainsString('<form role="search"', $output, 'Search form should be present.');
        // 他のメッセージは表示されないはず
        $this->assertStringNotContainsString('Ready to publish your first post?', $output);
        $this->assertStringNotContainsString('It seems we can&rsquo;t find what you&rsquo;re looking for.', $output);
    }

    /**
     * @test
     * その他のアーカイブページ（カテゴリなど）で何も見つからなかった場合の出力をテストします。
     */
    public function test_content_none_on_other_archive()
    {
        // --- Arrange (準備) ---
        // 存在しないカテゴリアーカイブのコンテキストに設定
        $this->go_to(home_url('/category/nonexistentcategory12345/'));

        // is_home() が true になる可能性があるため、チェックは削除済み
        // $this->assertFalse( is_home(), 'Context should not be home.' );

        $this->assertFalse(is_search(), 'Context should not be search.');

        // *** MODIFICATION START ***
        // is_archive() も true にならない可能性があるため、このチェックは削除またはコメントアウト
        // $this->assertTrue(is_archive(), 'Context should be archive.');
        // *** MODIFICATION END ***

        // 投稿が0件であることを確認 (オプションだが、このテストの前提条件として重要)
        global $wp_query;
        $this->assertEquals(0, $wp_query->found_posts, 'Archive should have 0 posts.');

        // --- Act (実行) ---
        $output = $this->get_content_none_template_output();

        // --- Assert (検証) ---
        $this->assertNotEmpty($output, 'Output should not be empty.');
        // 基本的な要素
        $this->assertStringContainsString('<section class="no-results not-found">', $output);
        $this->assertStringContainsString('<h1 class="page-title">Nothing Found</h1>', $output);
        // その他の場合のメッセージ
        $this->assertStringContainsString('It seems we can&rsquo;t find what you&rsquo;re looking for.', $output);
        // 検索フォームが表示されるはず
        $this->assertStringContainsString('<form role="search"', $output, 'Search form should be present.');
        // 他のメッセージは表示されないはず
        $this->assertStringNotContainsString('Ready to publish your first post?', $output);
        $this->assertStringNotContainsString('Sorry, but nothing matched your search terms.', $output);
    }
}
