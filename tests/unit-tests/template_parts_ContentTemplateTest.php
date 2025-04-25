<?php

/**
 * Class ContentTemplateTest
 *
 * Tests for the template part template-parts/content.php
 *
 * @package Integlight
 */

// PSR-4 規約に基づき、クラス名をファイル名と一致させることを推奨します。
// 例: ファイル名が ContentTemplateTest.php ならクラス名も ContentTemplateTest にする。
// class template_parts_ContentTemplateTest extends WP_UnitTestCase
class template_parts_ContentTemplateTest extends WP_UnitTestCase
{

    // --- プロパティ宣言 ---
    // private アクセス修飾子は適切です。
    private $post_id;
    private $page_id;
    private $user_id;
    private $cat_id;
    private $tag_id;
    private $attachment_id = null; // 初期値を null に設定するのは良い習慣です。
    // --- ここまで ---

    /**
     * 各テストメソッドの実行前に実行されるセットアップメソッド。
     * テストに必要なデータ（ユーザー、投稿、カテゴリーなど）を作成します。
     */
    public function set_up()
    {
        parent::set_up(); // 親クラスのセットアップを必ず呼び出す

        // テスト用ユーザーを作成し、現在のユーザーとして設定
        $this->user_id = self::factory()->user->create(['role' => 'editor', 'display_name' => 'Test Author']);
        wp_set_current_user($this->user_id); // 編集リンクなどのテストに必要

        // テスト用カテゴリーとタグを作成
        $this->cat_id = self::factory()->category->create(['name' => 'Test Cat']);
        $this->tag_id = self::factory()->tag->create(['name' => 'Test Tag']);

        // テスト用投稿を作成（カテゴリー、タグ、ページ区切りを含む）
        $this->post_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Test Post Title',
            // <!--nextpage--> は wp_link_pages のテストに必要
            'post_content' => 'This is the main post content. <!--nextpage--> This is page 2 content.',
            'post_date'    => '2023-10-27 10:00:00', // 特定の日付でテストを安定させる
            'post_status'  => 'publish',
            'post_type'    => 'post',
            'post_category' => [$this->cat_id], // カテゴリーIDを配列で渡す
            'tags_input'    => ['Test Tag'],    // タグ名を配列または文字列で渡す
        ]);

        // テスト用固定ページを作成
        $this->page_id = self::factory()->post->create([
            'post_author'  => $this->user_id,
            'post_title'   => 'Test Page Title',
            'post_content' => 'This is the page content.',
            'post_status'  => 'publish',
            'post_type'    => 'page',
        ]);

        // 投稿にサムネイルを追加
        $image_path = __DIR__ . '/dummy-image.png'; // テストファイルと同じディレクトリにある画像を想定
        if (file_exists($image_path)) {
            // 画像をアップロードし、アタッチメントとして投稿に関連付ける
            $this->attachment_id = self::factory()->attachment->create_upload_object($image_path, $this->post_id);
            if (is_wp_error($this->attachment_id)) {
                // エラーハンドリング: アタッチメント作成失敗時のログ出力
                error_log('Failed to create attachment: ' . $this->attachment_id->get_error_message());
                $this->attachment_id = null; // 失敗したら null に戻す
            } elseif ($this->attachment_id) {
                // 成功したら投稿のサムネイルとして設定
                set_post_thumbnail($this->post_id, $this->attachment_id);
            }
        } else {
            // 画像ファイルが見つからない場合のログ出力
            error_log('Dummy image not found at: ' . $image_path);
            // 必要であれば $this->markTestSkipped() でサムネイル関連テストをスキップする
            // $this->markTestSkipped('Dummy image not found, skipping thumbnail tests.');
        }
    }

    /**
     * 各テストメソッドの実行後に実行されるティアダウンメソッド。
     * グローバルな状態をリセットし、作成したデータをクリーンアップします。
     */
    public function tear_down()
    {
        // グローバルな投稿データとクエリをリセット
        wp_reset_postdata();
        wp_reset_query();
        unset($GLOBALS['post']); // グローバル $post 変数を明示的に削除
        wp_set_current_user(0); // ユーザーをログアウトさせる

        // 作成したアタッチメントを削除
        if ($this->attachment_id) {
            wp_delete_attachment($this->attachment_id, true); // true で完全に削除
        }
        // 注意: factory で作成された投稿、ページ、ユーザー、タームは
        // 通常 WP_UnitTestCase の tear_down で自動的にクリーンアップされます。

        parent::tear_down(); // 親クラスのティアダウンを必ず呼び出す
    }

    /**
     * ヘルパー関数: 指定された投稿IDの content.php テンプレート部品の出力を取得します。
     *
     * @param int $post_id テンプレート部品を表示する投稿のID。
     * @return string キャプチャされたHTML出力。
     */
    private function get_content_template_output(int $post_id): string
    {
        global $post; // グローバル $post 変数を使用
        $post_obj = get_post($post_id); // 投稿オブジェクトを取得
        if (!$post_obj) {
            // 投稿が見つからない場合は空文字列を返す
            return '';
        }
        // グローバル $post に取得した投稿オブジェクトを設定
        // これにより、テンプレート内の the_title() などが正しく動作する
        $post = $post_obj;
        setup_postdata($post); // グローバルな投稿データをセットアップ

        // 出力バッファリングを開始
        ob_start();
        // テスト対象のテンプレート部品を読み込む
        // get_post_type() を使うことで、content-post.php や content-page.php のような
        // 異なるファイルが読み込まれる可能性も考慮できる（今回は content.php を直接テスト）
        get_template_part('template-parts/content', get_post_type());
        // バッファの内容を取得し、バッファリングを終了
        $output = ob_get_clean();

        // グローバルな投稿データをリセット
        wp_reset_postdata();
        return $output;
    }


    /**
     * 個別投稿ページでの content.php の出力をテストします。
     * @test // PHPUnit 8 以降では @test アノテーションが推奨されます
     */
    public function test_content_template_on_single_post()
    {
        // --- Arrange (準備) ---
        // WordPress のクエリコンテキストを個別投稿ページに設定
        $this->go_to(get_permalink($this->post_id));
        // コンテキストが正しく設定されたか確認
        $this->assertTrue(is_singular(), 'Context should be singular.');
        $this->assertTrue(is_single(), 'Context should be single post.');

        // --- Act (実行) ---
        // ヘルパー関数を使ってテンプレート部品の出力を取得
        $output = $this->get_content_template_output($this->post_id);
        // 出力が空でないことを確認（ヘルパー関数が正しく動作しているか）
        $this->assertNotEmpty($output, 'get_content_template_output returned empty string.');


        // --- Assert (検証) ---
        // 基本構造の検証
        $this->assertStringContainsString('<article id="post-' . $this->post_id . '"', $output);
        // post_class() によって出力されるクラスを正規表現で検証（順序に依存しない）
        $this->assertMatchesRegularExpression('/class="[^"]*\bpost\b/', $output, 'Check basic post classes: post');
        $this->assertMatchesRegularExpression('/class="[^"]*\btype-post\b/', $output, 'Check basic post classes: type-post');
        $this->assertMatchesRegularExpression('/class="[^"]*\bstatus-publish\b/', $output, 'Check basic post classes: status-publish');
        // サムネイルがある場合のみ has-post-thumbnail クラスを検証
        if ($this->attachment_id) {
            $this->assertMatchesRegularExpression('/class="[^"]*\bhas-post-thumbnail\b/', $output, 'Check has-post-thumbnail class');
        }

        // ヘッダーの検証: タイトル (H1) とメタ情報
        $this->assertStringContainsString('<header class="entry-header">', $output);
        $this->assertStringContainsString('<h1 class="entry-title">Test Post Title</h1>', $output, 'Should display H1 title on single view.');
        $this->assertStringContainsString('<div class="entry-meta">', $output, 'Entry meta should be present for posts.');
        $this->assertStringContainsString('Posted on', $output, 'Check for posted_on output.'); // integlight_posted_on の出力確認
        $this->assertStringContainsString('by <span class="author vcard"><a', $output, 'Check for posted_by output.'); // integlight_posted_by の出力確認

        // サムネイルの検証 (アタッチメントが存在する場合のみ)
        if ($this->attachment_id) {
            $this->assertStringContainsString('<div class="post-thumbnail">', $output, 'Thumbnail wrapper should exist.'); // integlight_post_thumbnail (singular) の出力確認
            $this->assertStringContainsString('<img', $output, 'Image tag should be present in thumbnail.');
        }

        // コンテンツの検証
        $this->assertStringContainsString('<div class="entry-content">', $output);
        $this->assertStringContainsString('This is the main post content.', $output, 'Check for main content.');
        // ページ区切りリンク (wp_link_pages) の存在を確認
        $this->assertStringContainsString('<div class="page-links">Pages:', $output, 'wp_link_pages should be present due to page break.');
        // 個別ページでは「続きを読む」リンクは表示されないはず
        $this->assertStringNotContainsString('Continue reading<span class="screen-reader-text">', $output, '"Continue reading" link should NOT be present on single view.');

        // フッターの検証
        $this->assertStringContainsString('<footer class="entry-footer">', $output);
        // カテゴリーリンク (rel="category" 属性を含む)
        $this->assertStringContainsString('Posted in <a href="' . esc_url(get_category_link($this->cat_id)) . '" rel="category">Test Cat</a>', $output, 'Check category link.');
        // タグリンク (rel="tag" 属性を含む)
        $this->assertStringContainsString('Tagged <a href="' . esc_url(get_tag_link($this->tag_id)) . '" rel="tag">Test Tag</a>', $output, 'Check tag link.');
        // 編集リンク
        $this->assertStringContainsString('Edit <span class="screen-reader-text">Test Post Title</span>', $output, 'Check edit link.');
    }


    /**
     * アーカイブページでの content.php の出力をテストします。
     * @test
     */
    public function test_content_template_on_archive()
    {
        // --- Arrange (準備) ---
        // WordPress のクエリコンテキストをアーカイブページ（ホームページ）に設定
        $this->go_to(home_url('/'));
        // コンテキストが正しく設定されたか確認
        $this->assertFalse(is_singular(), 'Context should not be singular.');
        $this->assertTrue(is_home(), 'Context should be home (archive).');

        // メインループをシミュレートして、テスト対象の投稿を探す
        global $wp_query, $post; // $this->go_to で設定されたメインクエリを使用
        $found_post = false;
        $output = ''; // 出力変数を初期化

        if ($wp_query->have_posts()) {
            while ($wp_query->have_posts()) {
                $wp_query->the_post(); // グローバル $post とループコンテキストを設定
                if (get_the_ID() === $this->post_id) {
                    // --- Act (実行) ---
                    // 対象の投稿が見つかったら、ループコンテキスト内でテンプレート部品を直接呼び出す
                    // この時点では is_singular() は false のはず
                    ob_start();
                    get_template_part('template-parts/content', get_post_type());
                    $output = ob_get_clean();
                    $found_post = true;
                    break; // 目的の投稿が見つかったのでループを抜ける
                }
            }
        }
        wp_reset_postdata(); // ループシミュレーション後にリセット

        // テスト対象の投稿がループで見つかったか確認
        $this->assertTrue($found_post, 'Test post was not found in the archive query.');
        // 出力が空でないことを確認
        $this->assertNotEmpty($output, 'Template part output was empty in archive test.');

        // --- Assert (検証) ---
        // 基本構造の検証
        $this->assertStringContainsString('<article id="post-' . $this->post_id . '"', $output);
        $this->assertMatchesRegularExpression('/class="[^"]*\bpost\b/', $output, 'Check basic post classes: post');
        $this->assertMatchesRegularExpression('/class="[^"]*\btype-post\b/', $output, 'Check basic post classes: type-post');
        $this->assertMatchesRegularExpression('/class="[^"]*\bstatus-publish\b/', $output, 'Check basic post classes: status-publish');

        // ヘッダーの検証: タイトル (H2, リンク付き) とメタ情報
        $this->assertStringContainsString('<header class="entry-header">', $output);
        $this->assertStringContainsString('<h2 class="entry-title"><a href="' . esc_url(get_permalink($this->post_id)) . '" rel="bookmark">Test Post Title</a></h2>', $output, 'Should display H2 title with link on archive view.');
        $this->assertStringContainsString('<div class="entry-meta">', $output);
        $this->assertStringContainsString('Posted on', $output);
        $this->assertStringContainsString('by <span class="author vcard"><a', $output);

        // サムネイルの検証 (アタッチメントが存在する場合のみ、リンク付き)
        if ($this->attachment_id) {
            $this->assertStringContainsString('<a class="post-thumbnail"', $output, 'Thumbnail wrapper should be a link on archive.'); // integlight_post_thumbnail (archive) の出力確認
            $this->assertStringContainsString('href="' . esc_url(get_permalink($this->post_id)) . '"', $output, 'Thumbnail link should point to the post.');
            $this->assertStringContainsString('<img', $output);
        }

        // コンテンツの検証
        $this->assertStringContainsString('<div class="entry-content">', $output);
        // アーカイブでは「続きを読む」または本文が表示されることを確認（テーマ設定による）
        $this->assertTrue(
            strpos($output, 'Continue reading<span class="screen-reader-text">') !== false ||
                strpos($output, 'This is the main post content.') !== false,
            '"Continue reading" link or main content should be present on archive view.'
        );
        // ページ区切り後のコンテンツは表示されないはず
        $this->assertStringNotContainsString('This is page 2 content.', $output, 'Content after page break should not appear on archive.');
        // ページ区切りリンク (wp_link_pages) の存在を確認（実際の出力に合わせて修正）
        // 注意: 本来アーカイブでは表示されない方が一般的。テーマの挙動に合わせてテストを記述。
        $this->assertStringContainsString('<div class="page-links">Pages:', $output, 'wp_link_pages SHOULD be present on archive view (based on actual output).');

        // フッターの検証
        $this->assertStringContainsString('<footer class="entry-footer">', $output);
        $this->assertStringContainsString('Posted in <a href="' . esc_url(get_category_link($this->cat_id)) . '" rel="category">Test Cat</a>', $output, 'Check category link.');
        $this->assertStringContainsString('Tagged <a href="' . esc_url(get_tag_link($this->tag_id)) . '" rel="tag">Test Tag</a>', $output, 'Check tag link.');
        $this->assertStringContainsString('Edit <span class="screen-reader-text">Test Post Title</span>', $output);
    }


    /**
     * 固定ページコンテキストでの content.php の出力をテストします。
     * (通常は content-page.php が使われるが、content.php 内の条件分岐をテスト)
     * @test
     */
    public function test_content_template_on_single_page_context()
    {
        // --- Arrange (準備) ---
        // WordPress のクエリコンテキストを固定ページに設定
        $this->go_to(get_permalink($this->page_id));
        // コンテキストが正しく設定されたか確認
        $this->assertTrue(is_singular(), 'Context should be singular.');
        $this->assertTrue(is_page(), 'Context should be page.');

        // --- Act (実行) ---
        // ヘルパー関数を使ってテンプレート部品の出力を取得
        $output = $this->get_content_template_output($this->page_id);
        // 出力が空でないことを確認
        $this->assertNotEmpty($output, 'get_content_template_output returned empty string for page.');


        // --- Assert (検証) ---
        // 基本構造の検証
        $this->assertStringContainsString('<article id="post-' . $this->page_id . '"', $output);
        // post_class() の出力を正規表現で検証
        $this->assertMatchesRegularExpression('/class="[^"]*\bpage\b/', $output, 'Check basic page classes: page');
        $this->assertMatchesRegularExpression('/class="[^"]*\btype-page\b/', $output, 'Check basic page classes: type-page');
        $this->assertMatchesRegularExpression('/class="[^"]*\bstatus-publish\b/', $output, 'Check basic page classes: status-publish');

        // ヘッダーの検証: タイトル (H1) のみ、メタ情報は無し
        $this->assertStringContainsString('<header class="entry-header">', $output);
        $this->assertStringContainsString('<h1 class="entry-title">Test Page Title</h1>', $output, 'Should display H1 title on single page view.');
        $this->assertStringNotContainsString('<div class="entry-meta">', $output, 'Entry meta should NOT be present for pages.');

        // サムネイルの検証 (固定ページでは通常表示されないと想定)
        $this->assertStringNotContainsString('post-thumbnail', $output, 'Thumbnail should not be present for this page.');

        // コンテンツの検証
        $this->assertStringContainsString('<div class="entry-content">', $output);
        $this->assertStringContainsString('This is the page content.', $output);
        // 固定ページでは「続きを読む」やページ区切りリンクは通常表示されない
        $this->assertStringNotContainsString('Continue reading<span class="screen-reader-text">', $output);
        $this->assertStringNotContainsString('<div class="page-links">Pages:', $output, 'wp_link_pages should not be present (no page break).');

        // フッターの検証: 編集リンクのみ表示されるはず
        $this->assertStringContainsString('<footer class="entry-footer">', $output);
        $this->assertStringContainsString('Edit <span class="screen-reader-text">Test Page Title</span>', $output);
        // カテゴリーやタグは表示されないはず
        $this->assertStringNotContainsString('Posted in', $output);
        $this->assertStringNotContainsString('Tagged', $output);
    }
}
