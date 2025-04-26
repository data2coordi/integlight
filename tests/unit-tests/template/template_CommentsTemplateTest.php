<?php

/**
 * Test for the comments template
 *
 * @package Integlight
 */

class template_CommentsTemplateTest extends WP_UnitTestCase
{

    public function setUp(): void
    {
        parent::setUp();

        // テスト用にテーマを切り替え（コメントテンプレートを確実に読み込む）
        switch_theme('integlight');

        // ファイル非推奨警告を抑制
        add_filter('deprecated_file_trigger_error', '__return_false');
    }

    /**
     * テンプレートを直接読み込んで出力を取得するヘルパー
     *
     * @param int   $post_id     対象投稿ID
     * @param array $comments    コメント配列（get_comments() を期待）
     * @return string
     */
    protected function get_comments_template_output($post_id, $comments = [])
    {
        // グローバルに設定
        global $post, $wp_query;
        $post = get_post($post_id);
        setup_postdata($post);

        $wp_query->comments       = $comments;
        $wp_query->comment_count  = count($comments);

        ob_start();
        include get_theme_file_path('comments.php');
        wp_reset_postdata();
        return ob_get_clean();
    }

    public function test_comments_area_with_comments()
    {
        $post_id = $this->factory->post->create();
        // コメントを作成して配列取得
        $this->factory->comment->create_many(2, ['comment_post_ID' => $post_id]);
        $comments = get_comments(['post_id' => $post_id]);

        $this->go_to(get_permalink($post_id));
        $output = $this->get_comments_template_output($post_id, $comments);

        // コンテナ
        $this->assertStringContainsString('class="comments-area"', $output);
        // タイトルに “thoughts on” が含まれている
        $this->assertMatchesRegularExpression('/\d+\s+thoughts on/', $output);
        // コメントリスト
        $this->assertStringContainsString('<ol class="comment-list">', $output);
        // フォーム
        $this->assertStringContainsString('class="comment-form"', $output);
    }

    public function test_comments_area_without_comments()
    {
        $post_id = $this->factory->post->create();
        $comments = []; // 空

        $this->go_to(get_permalink($post_id));
        $output = $this->get_comments_template_output($post_id, $comments);

        // タイトル／リストは出ない
        $this->assertStringNotContainsString('comments-title', $output);
        $this->assertStringNotContainsString('<ol class="comment-list">', $output);
        // フォームだけ出る
        $this->assertStringContainsString('class="comment-form"', $output);
    }

    public function test_comments_closed_message()
    {
        $post_id = $this->factory->post->create();
        $this->factory->comment->create(['comment_post_ID' => $post_id]);
        // コメント取得
        $comments = get_comments(['post_id' => $post_id]);
        // コメントを閉じる
        wp_update_post([
            'ID'             => $post_id,
            'comment_status' => 'closed',
        ]);

        $this->go_to(get_permalink($post_id));
        $output = $this->get_comments_template_output($post_id, $comments);

        // リストは出る
        $this->assertStringContainsString('<ol class="comment-list">', $output);
        // クローズメッセージ
        $this->assertStringContainsString('Comments are closed.', $output);
    }

    public function test_comments_password_protected()
    {
        $post_id = $this->factory->post->create(['post_password' => 'secret']);

        $this->go_to(get_permalink($post_id));
        // パスワード保護時は何も出力しない
        $output = $this->get_comments_template_output($post_id, []);
        $this->assertEmpty(trim($output));
    }
}
