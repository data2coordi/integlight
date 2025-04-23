<?php // tests/unit-tests/integlight_functions_Integlight_PostHelperTest_WPUnit_Simple.php

declare(strict_types=1);

/**
 * Test case for Integlight_PostHelper::get_post_navigation using WP_UnitTestCase.
 * Focuses on the public method without mocks, keeping setup relatively simple.
 *
 * @coversDefaultClass Integlight_PostHelper
 * @covers ::get_post_navigation
 * @group functions
 * @group posts
 */
class integlight_functions_Integlight_PostHelperTest extends WP_UnitTestCase
{
    protected static $prev_post_id;
    protected static $current_post_id;
    protected static $next_post_id;
    protected static $prev_thumb_id;
    // next_post にはサムネイルを設定しない（本文画像テスト用）
    protected static $current_content_img_url = 'http://example.com/current-content.png'; // 本文画像のURL (テスト用)
    protected static $dummy_image_path;

    /**
     * テストクラス全体のセットアップ (投稿やアタッチメントを作成)
     */
    public static function wpSetUpBeforeClass($factory): void
    {
        // --- テスト用画像ファイルの準備 ---
        // プロジェクト内のテスト用画像へのパス (例: tests/assets/dummy-image.png)
        // このパスは実際の環境に合わせて調整してください。
        self::$dummy_image_path = dirname(__FILE__, 2) . '/unit-tests/dummy-image.png';

        // 画像ファイルが存在しない場合は、一時ファイルを作成 (内容は空でも可)
        if (!file_exists(self::$dummy_image_path)) {
            self::$dummy_image_path = wp_tempnam('dummy-image.png');
            if (!self::$dummy_image_path) {
                self::fail('Failed to create temporary dummy image file.');
            }
        }

        // --- 投稿を作成 (日付をずらして順序を確定) ---
        self::$prev_post_id = $factory->post->create([
            'post_title' => 'Previous Post Title',
            'post_date' => '2023-01-01 10:00:00',
            'post_status' => 'publish',
        ]);
        self::$current_post_id = $factory->post->create([
            'post_title' => 'Current Post Title Which Is Very Long Indeed',
            'post_date' => '2023-01-01 11:00:00',
            'post_status' => 'publish',
            // この投稿自体にはサムネイルも本文画像も設定しない (ナビゲーションのテストには不要)
        ]);
        self::$next_post_id = $factory->post->create([
            'post_title' => 'Next Post Title',
            'post_date' => '2023-01-01 12:00:00',
            'post_status' => 'publish',
            // next_post にはサムネイルを設定せず、本文画像を使うテスト用
            'post_content' => '<p>Content with <img src="' . self::$current_content_img_url . '"> image.</p>',
        ]);




        // --- アタッチメントを作成し、サムネイルとして設定 (prev_post のみ) ---
        self::$prev_thumb_id = $factory->attachment->create_upload_object(self::$dummy_image_path, self::$prev_post_id);
        if (is_wp_error(self::$prev_thumb_id)) {
            self::fail('Failed to create attachment for prev post: ' . self::$prev_thumb_id->get_error_message());
        }
        set_post_thumbnail(self::$prev_post_id, self::$prev_thumb_id);
    }

    /**
     * テストクラス全体のティアダウン (作成したデータを削除)
     */
    public static function wpTearDownAfterClass(): void
    {
        wp_delete_post(self::$prev_post_id, true);
        wp_delete_post(self::$current_post_id, true);
        wp_delete_post(self::$next_post_id, true);

        if (!is_wp_error(self::$prev_thumb_id)) {
            wp_delete_attachment(self::$prev_thumb_id, true);
        }

        // 一時ファイルとしてダミー画像を作成した場合、削除する
        if (strpos(self::$dummy_image_path, get_temp_dir()) === 0 && file_exists(self::$dummy_image_path)) {
            unlink(self::$dummy_image_path);
        }
    }

    /**
     * @test
     * 前後の投稿が存在する場合に、正しいナビゲーションHTMLが出力されることをテストします。
     * prev: サムネイルあり / next: 本文画像あり
     */
    public function get_post_navigation_should_output_correctly_with_prev_and_next(): void
    {
        // --- Arrange ---
        $this->go_to(get_permalink(self::$current_post_id)); // 現在の投稿ページに移動

        $prev_post_url = get_permalink(self::$prev_post_id);
        $next_post_url = get_permalink(self::$next_post_id);
        $prev_image_url = get_the_post_thumbnail_url(self::$prev_post_id, 'full');
        // next_post はサムネイルがないので、本文画像が使われるはず (self::$current_content_img_url)

        $this->assertNotEmpty($prev_image_url, 'Previous post thumbnail URL should exist.');

        // --- Act ---
        ob_start();
        Integlight_PostHelper::get_post_navigation();
        $output = ob_get_clean();

        // --- Assert ---
        $this->assertStringContainsString('<nav class="post-navigation"', $output);
        // 前の投稿部分 (サムネイル)
        $this->assertStringContainsString('<div class="nav-previous"', $output);
        $this->assertStringContainsString('style="background-image: url(\'' . esc_url($prev_image_url) . '\');"', $output);
        $this->assertStringContainsString('<a href="' . esc_url($prev_post_url) . '">', $output);
        $this->assertStringContainsString(esc_html('Previous Post ...'), $output); // 切り詰め
        // 次の投稿部分 (本文画像)
        $this->assertStringContainsString('<div class="nav-next"', $output);
        $this->assertStringContainsString('style="background-image: url(\'' . esc_url(self::$current_content_img_url) . '\');"', $output);
        $this->assertStringContainsString('<a href="' . esc_url($next_post_url) . '">', $output);
        $this->assertStringContainsString(esc_html('Next Post Titl...'), $output); // 切り詰め
        $this->assertStringContainsString('</nav>', $output);
    }

    /**
     * @test
     * 最初の投稿の場合 (前の投稿がない場合) の出力をテストします。
     * next: 本文画像あり
     */
    public function get_post_navigation_should_output_only_next_when_no_prev(): void
    {
        // --- Arrange ---
        $this->go_to(get_permalink(self::$prev_post_id)); // 最初の投稿ページに移動
        $next_post_url = get_permalink(self::$current_post_id); // 次の投稿は current_post_id

        // --- Act ---
        ob_start();
        Integlight_PostHelper::get_post_navigation();
        $output = ob_get_clean();

        // --- Assert ---
        $this->assertStringContainsString('<nav class="post-navigation"', $output);
        // 前の投稿部分がないこと
        $this->assertStringNotContainsString('<div class="nav-previous"', $output);
        // 次の投稿部分 (current_post_id が次になる)
        $this->assertStringContainsString('<div class="nav-next"', $output);
        // current_post_id には画像がないので、背景画像URLは空になるはず
        $this->assertStringContainsString('style="background-image: url(\'\');"', $output);
        $this->assertStringContainsString('<a href="' . esc_url($next_post_url) . '">', $output);
        $this->assertStringContainsString(esc_html('Current Post T...'), $output); // 切り詰め
        $this->assertStringContainsString('</nav>', $output);
    }

    /**
     * @test
     * 最後の投稿の場合 (次の投稿がない場合) の出力をテストします。
     * prev: サムネイルなし (current_post_id には画像がないため)
     */
    public function get_post_navigation_should_output_only_prev_when_no_next(): void
    {
        // --- Arrange ---
        $this->go_to(get_permalink(self::$next_post_id)); // 最後の投稿ページに移動
        $prev_post_url = get_permalink(self::$current_post_id); // 前の投稿は current_post_id

        // --- Act ---
        ob_start();
        Integlight_PostHelper::get_post_navigation();
        $output = ob_get_clean();

        // --- Assert ---
        $this->assertStringContainsString('<nav class="post-navigation"', $output);
        // 前の投稿部分 (current_post_id が前になる)
        $this->assertStringContainsString('<div class="nav-previous"', $output);
        // current_post_id には画像がないので、背景画像URLは空になるはず
        $this->assertStringContainsString('style="background-image: url(\'\');"', $output);
        $this->assertStringContainsString('<a href="' . esc_url($prev_post_url) . '">', $output);
        $this->assertStringContainsString(esc_html('Current Post T...'), $output); // 切り詰め
        // 次の投稿部分がないこと
        $this->assertStringNotContainsString('<div class="nav-next"', $output);
        $this->assertStringContainsString('</nav>', $output);
    }

    /**
     * @test
     * 前後の投稿がどちらも存在しない場合に、何も出力しないことをテストします。
     */
    /**
     * @test
     * 前後の投稿がどちらも存在しない場合に、何も出力しないことをテストします。
     */
    public function get_post_navigation_should_output_nothing_when_no_prev_and_no_next(): void
    {
        // --- Arrange ---
        // wpSetUpBeforeClass で作成された投稿を一時的に非公開にする
        $original_statuses = [];
        $posts_to_hide = [self::$prev_post_id, self::$current_post_id, self::$next_post_id];
        foreach ($posts_to_hide as $post_id) {
            // 投稿が存在するか確認してからステータスを変更
            $post = get_post($post_id);
            if ($post) {
                $original_statuses[$post_id] = $post->post_status;
                wp_update_post(['ID' => $post_id, 'post_status' => 'draft']);
            }
        }

        // 1つしか投稿がない状態を作る
        $single_post_id = self::factory()->post->create(['post_title' => 'Only One Post', 'post_status' => 'publish']);
        $this->go_to(get_permalink($single_post_id));

        // --- Act ---
        ob_start();
        Integlight_PostHelper::get_post_navigation();
        $output = ob_get_clean();

        // --- Assert ---
        // 失敗時のメッセージを追加して、何が出力されたか分かりやすくする
        $this->assertEmpty($output, "Output should be empty when there are no previous or next posts. Actual output: " . $output);

        // --- Cleanup ---
        // 作成した単一投稿を削除
        wp_delete_post($single_post_id, true);
        // 非公開にした投稿を元のステータスに戻す
        foreach ($original_statuses as $post_id => $status) {
            wp_update_post(['ID' => $post_id, 'post_status' => $status]);
        }
    }
}
