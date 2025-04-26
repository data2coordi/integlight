<?php

/**
 * Class ArchiveTemplateTest
 *
 * Tests for the archive.php template file.
 */
class template_ArchiveTemplateTest extends WP_UnitTestCase
{
    // ... (setUp, tearDown, properties は変更なし) ...

    // --- 削除: get_template_output_for_url ヘルパー関数 ---
    // private function get_template_output_for_url(string $url): string { ... }

    /**
     * @test
     * Test category archive page with posts.
     */
    public function test_category_archive_with_posts()
    {
        // --- Arrange ---
        $category = $this->factory->category->create_and_get(['name' => 'Test Category']);
        $this->factory->post->create_many(3, ['post_category' => [$category->term_id]]);

        // Activate sidebar for testing its presence
        update_option('sidebars_widgets', ['sidebar-1' => ['text-1'], 'wp_inactive_widgets' => []]);
        set_theme_mod('integlight_sidebar1_position', 'right');

        // --- Simulate Query ---
        global $wp_query, $wp_the_query;
        $query_args = [
            'cat'            => $category->term_id,
            'posts_per_page' => 2, // From setUp
            'paged'          => 1,
        ];
        $wp_query = new WP_Query($query_args);
        $wp_the_query = $wp_query; // Set the global query object

        // --- Act: Require the template directly ---
        ob_start();
        // archive.php が内部で get_header/get_footer を呼ぶことを想定
        require get_theme_file_path('archive.php');
        $output = ob_get_clean();

        // --- Assert ---
        $this->assertNotEmpty($output, 'Output should not be empty.');
        $this->assertStringContainsString('<main id="primary" class="site-main', $output, 'Main content area #primary not found.');
        $this->assertStringContainsString('<header class="page-header">', $output, 'Page header not found.');
        // --- 修正: the_archive_title の出力に合わせて span タグを含める ---
        $this->assertStringContainsString('<h1 class="page-title">Category: <span>Test Category</span></h1>', $output, 'Correct archive title not found.');
        $this->assertStringContainsString('class="bl_card_container"', $output, 'Post card container (from content-arc.php) not found.');
        $this->assertEquals(2, substr_count($output, 'class="bl_card_container"'), 'Expected 2 posts per page.');
        // --- 修正: サイドバーとフッターは archive.php 内で require されるため、存在を確認 ---
        $this->assertStringContainsString('id="secondary"', $output, 'Sidebar #secondary not found.'); // get_sidebar() が呼ばれる
        $this->assertStringContainsString('<footer id="colophon"', $output, 'Footer #colophon not found.'); // get_footer() が呼ばれる

        // --- Cleanup ---
        wp_reset_query(); // クエリをリセット
    }

    /**
     * @test
     * Test category archive page with pagination.
     */
    public function test_category_archive_with_pagination()
    {
        // --- Arrange ---
        $category = $this->factory->category->create_and_get(['name' => 'Paginated Category']);
        $this->factory->post->create_many(5, ['post_category' => [$category->term_id]]); // 5 posts, posts_per_page=2 -> 3 pages

        // --- Simulate Query ---
        global $wp_query, $wp_the_query;
        $query_args = [
            'cat'            => $category->term_id,
            'posts_per_page' => 2, // From setUp
            'paged'          => 1, // Test on page 1
        ];
        $wp_query = new WP_Query($query_args);
        $wp_the_query = $wp_query;

        // --- Act: Require the template directly ---
        ob_start();
        require get_theme_file_path('archive.php');
        $output = ob_get_clean();

        // --- Assert ---
        $this->assertNotEmpty($output, 'Output should not be empty.');
        // --- 修正: the_archive_title の出力に合わせて span タグを含める ---
        $this->assertStringContainsString('<h1 class="page-title">Category: <span>Paginated Category</span></h1>', $output, 'Correct archive title not found.');
        $this->assertStringContainsString('class="page-numbers"', $output, 'Pagination links (.page-numbers) not found.');
        $this->assertStringContainsString('class="next page-numbers"', $output, 'Next page link not found.');
        $this->assertStringContainsString('>2<', $output, 'Link to page 2 not found.');
        $this->assertStringContainsString('>3<', $output, 'Link to page 3 not found.');

        // --- Cleanup ---
        wp_reset_query();
    }

    /**
     * @test
     * Test empty category archive page loads content-none.
     */
    public function test_empty_category_archive_loads_content_none()
    {
        // --- Arrange ---
        $category = $this->factory->category->create_and_get(['name' => 'Empty Category']);
        // No posts created

        // --- Simulate Query ---
        global $wp_query, $wp_the_query;
        $query_args = [
            'cat'            => $category->term_id,
            'posts_per_page' => 2, // From setUp
            'paged'          => 1,
        ];
        $wp_query = new WP_Query($query_args);
        $wp_the_query = $wp_query;

        // --- Act: Require the template directly ---
        ob_start();
        require get_theme_file_path('archive.php');
        $output = ob_get_clean();

        // --- Assert ---
        $this->assertNotEmpty($output, 'Output should not be empty.');

        // --- 修正: アーカイブタイトル (例: "Category: Empty Category") が含まれていないことを確認 ---
        $this->assertStringNotContainsString('Category: Empty Category</h1>', $output, 'Archive title should NOT be present when no posts are found.');

        // --- 修正: content-none.php が出力するヘッダーとタイトルが存在することを確認 ---
        $this->assertStringContainsString('<header class="page-header">', $output, 'Page header from content-none.php should be present.');
        $this->assertStringContainsString('<h1 class="page-title">Nothing Found</h1>', $output, 'Title from content-none.php not found.');
        $this->assertStringContainsString('It seems we can&rsquo;t find what you&rsquo;re looking for.', $output, 'Content from content-none.php not found.');
        $this->assertStringNotContainsString('class="bl_card_container"', $output, 'Post card container should not be present.');

        // --- Cleanup ---
        wp_reset_query();
    }
}
