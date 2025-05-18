<?php

/**
 * Class template_404TemplateTest
 *
 * Tests for the 404.php template file.
 */
class template_404TemplateTest extends WP_UnitTestCase
{
    /**
     * Store original theme mods.
     * @var array|false
     */
    private $original_theme_mods;

    /**
     * Store original widget settings.
     * @var array|false
     */
    private $original_sidebars_widgets;

    /**
     * Set up before each test.
     */
    /**
     * Set up before each test.
     */
    public function set_up()
    {
        parent::set_up();


        // --- 追加: 404.php で使用されるコアウィジェットを明示的に登録 ---
        require_once ABSPATH . WPINC . '/widgets/class-wp-widget-recent-posts.php';
        register_widget('WP_Widget_Recent_Posts');
        require_once ABSPATH . WPINC . '/widgets/class-wp-widget-archives.php';
        register_widget('WP_Widget_Archives');
        require_once ABSPATH . WPINC . '/widgets/class-wp-widget-tag-cloud.php';
        register_widget('WP_Widget_Tag_Cloud');
        // --- 追加ここまで ---


    }


    /**
     * Tear down after each test.
     */
    public function tear_down()
    {


        parent::tear_down();
    }

    /**
     * Helper function to get the template output for a 404 request by requiring 404.php.
     */
    private function get_404_template_output(): string
    {

        // 404.php のパスを取得
        $template_404_path = get_theme_file_path('404.php');
        if (!file_exists($template_404_path)) {
            trigger_error('404.php not found at ' . $template_404_path, E_USER_WARNING);
            return '';
        }

        // 出力バッファリングして 404.php を読み込む
        ob_start();
        require $template_404_path;
        return ob_get_clean();
        // --- 修正ここまで ---
    }


    /**
     * @test
     * Test 404 page loads correctly and contains expected elements.
     */
    public function test_404_page_loads_correctly()
    {

        // Activate sidebar for testing its presence

        // --- Act ---
        $output = $this->get_404_template_output();

        // --- Assert ---
        $this->assertNotEmpty($output, 'Output should not be empty for 404 page.');

        // Check if WordPress query is indeed 404 (redundant check, but good practice)
        //global $wp_query;
        //$this->assertTrue($wp_query->is_404(), 'Global query object should be 404.');

        // Basic structure
        $this->assertStringContainsString('<main id="primary" class="site-main', $output, 'Main content area #primary not found.');
        $this->assertStringContainsString('<section class="error-404 not-found">', $output, 'Section .error-404 not found.');

        // 404 Title and Content
        $this->assertStringContainsString('<h1 class="page-title">Oops! That page can&rsquo;t be found.</h1>', $output, 'Correct 404 title not found.'); // Adjust expected title if needed
        $this->assertStringContainsString('It looks like nothing was found at this location.', $output, 'Expected 404 content text not found.'); // Adjust expected text

        // Search Form (assuming get_search_form() is called in 404.php)
        $this->assertStringContainsString('<form role="search"', $output, 'Search form not found.');
        // $this->assertStringContainsString('id="searchform"', $output, 'Search form ID not found.'); // Check for specific ID if your theme uses it

        // Sidebar (assuming 404.php calls get_sidebar())
        // $this->assertStringContainsString('id="secondary"', $output, 'Sidebar #secondary not found.');

        // Footer (assuming 404.php calls get_footer())
        $this->assertStringContainsString('<footer id="colophon"', $output, 'Footer #colophon not found.');
    }
}
