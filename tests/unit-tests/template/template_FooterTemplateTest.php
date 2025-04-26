<?php

/**
 * Class FooterTemplateTest
 *
 * Tests for the footer.php template file.
 */
class template_FooterTemplateTest extends WP_UnitTestCase
{
    /**
     * Store original theme mods.
     * @var array|false
     */
    private $original_theme_mods;

    /**
     * Set up before each test.
     */
    public function setUp(): void
    {
        parent::setUp();


        // Store original theme mods
        $this->original_theme_mods = get_theme_mods();

        // Reset theme mods for testing
        remove_theme_mods();

        // Clear caches
        wp_cache_delete('theme_mods_' . get_stylesheet(), 'theme_mods');
        wp_cache_flush();
    }

    /**
     * Tear down after each test.
     */
    public function tearDown(): void
    {
        // Restore original theme mods
        remove_theme_mods(); // Clear current mods first
        if (!empty($this->original_theme_mods) && is_array($this->original_theme_mods)) {
            foreach ($this->original_theme_mods as $key => $value) {
                set_theme_mod($key, $value);
            }
        }

        // Flush cache
        wp_cache_flush();

        parent::tearDown();
    }

    /**
     * Helper function to get footer output using require.
     */
    private function get_footer_output(): string
    {
        $footer_path = get_template_directory() . '/footer.php';
        if (!file_exists($footer_path)) {
            trigger_error('footer.php not found at ' . $footer_path, E_USER_WARNING);
            return '';
        }
        ob_start();
        // wp_footer アクションをトリガーする必要があるか検討
        // do_action('wp_footer'); // footer.php が wp_footer フックに依存している場合に必要かも
        require $footer_path; // Use require instead of get_footer()
        return ob_get_clean();
    }

    /**
     * @test
     * Test if basic footer elements are present.
     * @expectedDeprecated the_block_template_skip_link
     */
    public function test_basic_footer_elements_exist()
    {
        // --- Act ---
        add_filter('deprecated_function_trigger_error', '__return_false');

        $output = $this->get_footer_output();

        // --- Assert ---
        $this->assertStringContainsString('<footer id="colophon" class="site-footer ly_site_footer">', $output, 'Footer element #colophon not found or has wrong classes.');
        $this->assertStringContainsString('<div class="site-info">', $output, 'Div .site-info not found.');
        $this->assertStringContainsString('<a href="#" id="page-top">', $output, 'Page top link #page-top not found.');
        $this->assertStringContainsString('<i class="fa-solid fa-angle-up"></i>', $output, 'Page top icon not found.');
        $this->assertStringContainsString('</html>', $output, 'Closing </html> tag not found.'); // Ensure the template completes
    }

    /**
     * @test
     * Test if custom copyright text is displayed when set.
     * @expectedDeprecated the_block_template_skip_link

     */
    public function test_custom_copyright_text_is_displayed()
    {
        // --- Arrange ---
        $custom_copyright = '© 2024 My Awesome Site';
        set_theme_mod('integlight_footer_copy_right', $custom_copyright);

        // --- Act ---
        $output = $this->get_footer_output();

        // --- Assert ---
        $this->assertStringContainsString(esc_html($custom_copyright), $output, 'Custom copyright text not found.');
    }

    /**
     * @test
     * Test if copyright text area is empty when not set.
     * @expectedDeprecated the_block_template_skip_link
     */
    public function test_copyright_text_is_empty_when_not_set()
    {
        // --- Arrange ---
        // No copyright text set (already reset in setUp)

        // --- Act ---
        $output = $this->get_footer_output();

        // --- Assert ---
        // Check the specific area where copyright is output, expecting only whitespace/newline potentially
        preg_match('/<div class="site-info">(.*?)<br>/s', $output, $matches);
        $this->assertCount(2, $matches, 'Could not find the site-info div or the <br> tag after copyright.');
        $this->assertEmpty(trim($matches[1]), 'Copyright area should be empty or only whitespace when not set.');
    }

    /**
     * @test
     * Test if credits are displayed by default (or when set to true).
     * @expectedDeprecated the_block_template_skip_link

     */
    public function test_credits_are_displayed_by_default()
    {
        // --- Arrange ---
        // Set show_credit to true (or leave as default)
        set_theme_mod('integlight_footer_show_credit', true);

        // --- Act ---
        $output = $this->get_footer_output();

        // --- Assert ---
        $this->assertStringContainsString('https://wordpress.org/', $output, 'WordPress credit link not found.');
        $this->assertStringContainsString('Proudly powered by', $output, 'WordPress credit text not found.');
        $this->assertStringContainsString('https://color.toshidayurika.com/', $output, 'Theme author credit link not found.');
        $this->assertStringContainsString('Theme: Integlight by', $output, 'Theme credit text not found.');
    }

    /**
     * @test
     * Test if credits are hidden when set to false.
     * @expectedDeprecated the_block_template_skip_link

     */
    public function test_credits_are_hidden_when_set_to_false()
    {
        // --- Arrange ---
        // Set show_credit to false
        set_theme_mod('integlight_footer_show_credit', false);

        // --- Act ---
        $output = $this->get_footer_output();

        // --- Assert ---
        $this->assertStringNotContainsString('https://wordpress.org/', $output, 'WordPress credit link should not be present.');
        $this->assertStringNotContainsString('Proudly powered by', $output, 'WordPress credit text should not be present.');
        $this->assertStringNotContainsString('https://color.toshidayurika.com/', $output, 'Theme author credit link should not be present.');
        $this->assertStringNotContainsString('Theme: Integlight by', $output, 'Theme credit text should not be present.');
    }
}
