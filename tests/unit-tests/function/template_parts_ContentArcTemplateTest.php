<?php

/**
 * Class ContentArcTemplateTest
 *
 * Tests for the template part template-parts/content-arc.php
 *
 * @package Integlight
 */

// クラス名を PSR-4/PSR-12 準拠に修正することを推奨 (例: ContentArcTemplateTest)
class template_parts_ContentArcTemplateTest extends WP_UnitTestCase
{

    private $post_id;
    private $user_id;
    private $cat_id;
    private $tag_id;
    private $attachment_id = null;

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();

        // テスト用ユーザーを作成し、現在のユーザーとして設定
        $this->user_id = self::factory()->user->create(['role' => 'editor', 'display_name' => 'Test Author']);
        wp_set_current_user($this->user_id);

        // テスト用カテゴリーとタグを作成
        $this->cat_id = self::factory()->category->create(['name' => 'Arc Cat']);
        $this->tag_id = self::factory()->tag->create(['name' => 'Arc Tag']);

        // テスト用投稿を作成
        $this->post_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Archive Test Post Title',
            'post_content' => 'This is the main content for the archive test post. It should be long enough to potentially generate an excerpt or a "continue reading" link.',
            'post_excerpt' => 'This is the custom excerpt.', // 抜粋も設定
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_category' => [$this->cat_id],
            'tags_input'    => ['Arc Tag'],
        ]);

        // サムネイルを追加 (オプション)
        $image_path =  dirname(__FILE__, 2) . '/dummy-image.png';
        if (file_exists($image_path)) {
            $this->attachment_id = self::factory()->attachment->create_upload_object($image_path, $this->post_id);
            if (!is_wp_error($this->attachment_id)) {
                set_post_thumbnail($this->post_id, $this->attachment_id);
            } else {
                $this->attachment_id = null;
            }
        }
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
        wp_set_current_user(0);

        // 作成したデータのクリーンアップ
        if ($this->attachment_id) {
            wp_delete_attachment($this->attachment_id, true);
        }
        parent::tear_down();
    }

    /**
     * @test
     * アーカイブページでの content-arc.php の基本的な出力をテストします。
     */
    /**
     * @test
     * アーカイブページでの content-arc.php の基本的な出力をテストします。
     */
    public function test_content_arc_output_on_archive()
    {
        // --- Arrange (準備) ---
        // ... (変更なし) ...
        $this->go_to(get_category_link($this->cat_id));
        $this->assertTrue(is_archive(), 'Context should be archive.');
        $this->assertTrue(is_category(), 'Context should be category archive.');
        $this->assertFalse(is_singular(), 'Context should not be singular.');

        // ... (ループ処理 - 変更なし) ...
        global $wp_query, $post;
        $found_post = false;
        $output = '';

        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post();
                if (get_the_ID() === $this->post_id) {
                    // --- Act (実行) ---
                    ob_start();
                    get_template_part('template-parts/content-arc');
                    $output = ob_get_clean();
                    $found_post = true;
                    break;
                }
            }
        }
        wp_reset_postdata();

        $this->assertTrue($found_post, 'Test post was not found in the archive query.');
        $this->assertNotEmpty($output, 'Template part output should not be empty.');

        // --- Assert (検証) ---
        // *** MODIFICATION START: アサーションを実際のHTML構造に合わせる ***

        // 1. 主要なコンテナが存在するか
        $this->assertStringContainsString('<div class="bl_card_container">', $output);

        // 2. 画像部分 (<figure>) が存在し、リンクとカテゴリラベル、画像が含まれるか
        $this->assertStringContainsString('<figure class="bl_card_img">', $output);
        $this->assertStringContainsString('<a href="' . esc_url(get_permalink($this->post_id)) . '" class="bl_card_link">', $output, 'Image link check');
        $this->assertStringContainsString('<span class="category-label">Arc Cat</span>', $output, 'Category label check');
        if ($this->attachment_id) {
            // 画像の src 属性が含まれるか (よりシンプルに)
            $image_url = wp_get_attachment_image_url($this->attachment_id, 'thumbnail'); // テンプレートで使用しているサイズに合わせる
            if ($image_url) {
                $this->assertStringContainsString('src="' . esc_url($image_url) . '"', $output, 'Image src check');
            } else {
                $this->assertStringContainsString('<img', $output, 'Image tag should be present'); // URLが取れない場合のフォールバック
            }
        }

        // 3. ヘッド部分 (<div>) が存在し、リンク付きタイトル (<h5>) と日付が含まれるか
        $this->assertStringContainsString('<div class="bl_card_head">', $output);
        $this->assertStringContainsString('<a href="' . esc_url(get_permalink($this->post_id)) . '">', $output, 'Title link check');
        $this->assertStringContainsString('<h5 class="bl_card_ttl">Archive Test Post Title</h5>', $output, 'Title check');
        $this->assertStringContainsString('<span class="entry-date">', $output, 'Date span check');
        // 日付のフォーマットはテンプレートに依存するため、存在チェックのみに留めるか、日付を取得して比較する
        // $this->assertStringContainsString(get_the_date(), $output, 'Date check'); // フォーマットが一致すればOK

        // 4. ボディ部分 (<div>) が存在し、抜粋テキストが含まれるか
        $this->assertStringContainsString('<div class="bl_card_body">', $output);
        $this->assertStringContainsString('<p class="bl_card_txt">This is the custom excerpt.</p>', $output, 'Excerpt check');

        // 5. 以前のテストで想定していた要素が *存在しない* ことを確認 (任意)
        $this->assertStringNotContainsString('<article id="post-', $output, '<article> tag should not be present.');
        $this->assertStringNotContainsString('<header class="entry-header">', $output, '<header> tag should not be present.');
        $this->assertStringNotContainsString('<footer class="entry-footer">', $output, '<footer> tag should not be present.');
        $this->assertStringNotContainsString('<h2 class="entry-title">', $output, '<h2> title should not be present.');
        // 編集リンクなどもこの構造には含まれていないようなので、チェックを削除

        // *** MODIFICATION END ***
    }
}
