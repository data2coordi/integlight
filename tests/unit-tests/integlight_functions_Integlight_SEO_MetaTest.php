<?php

/**
 * Tests for the Integlight_SEO_Meta class.
 *
 * @package Integlight
 * @group seo-meta
 */

// Load the class file if it's not already loaded (adjust path as needed)
// require_once dirname( __FILE__ ) . '/../inc/integlight-functions.php'; // Assuming tests are in a 'tests' directory

class integlight_functions_Integlight_SEO_MetaTest extends WP_UnitTestCase
{

    /**
     * Instance of the class under test.
     * @var Integlight_SEO_Meta
     */
    private $instance;

    /**
     * Post ID for testing.
     * @var int
     */
    private $post_id;

    /**
     * Page ID for testing.
     * @var int
     */
    private $page_id;

    /**
     * Set up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->instance = new Integlight_SEO_Meta();

        // Create a test post and page
        $this->post_id = self::factory()->post->create(['post_type' => 'post']);
        $this->page_id = self::factory()->post->create(['post_type' => 'page']);

        // Set a user with editing capabilities
        $user_id = self::factory()->user->create(['role' => 'editor']);
        wp_set_current_user($user_id);
    }

    /**
     * Tear down the test environment.
     */
    public function tearDown(): void
    {
        wp_delete_post($this->post_id, true);
        wp_delete_post($this->page_id, true);
        wp_set_current_user(0); // Reset user
        parent::tearDown();
    }

    /**
     * Test if hooks are added correctly.
     * @covers Integlight_SEO_Meta::__construct
     */
    public function test_hooks_are_added()
    {
        $this->assertGreaterThan(0, has_action('add_meta_boxes', [$this->instance, 'add_meta_box']));
        $this->assertGreaterThan(0, has_action('save_post', [$this->instance, 'save_meta_data']));
        $this->assertGreaterThan(0, has_filter('document_title_parts', [$this->instance, 'filter_document_title']));
        $this->assertGreaterThan(0, has_action('wp_head', [$this->instance, 'output_meta_description']));
    }

    /**
     * Test the add_meta_box method.
     * We can't directly test add_meta_box function calls easily without mocking,
     * but we can ensure the hook callback exists.
     * @covers Integlight_SEO_Meta::add_meta_box
     */
    public function test_add_meta_box_callback_exists()
    {
        $this->assertTrue(method_exists($this->instance, 'add_meta_box'));
        // Further testing could involve checking if the meta box appears in the admin UI,
        // which is more suitable for integration or end-to-end testing.
    }

    /**
     * Test the display_meta_box_content method output.
     * @covers Integlight_SEO_Meta::display_meta_box_content
     */
    public function test_display_meta_box_content()
    {
        $post = get_post($this->post_id);
        $meta_key_title = '_custom_meta_title';
        $meta_key_description = '_custom_meta_description';
        $nonce_action = 'seo_meta_box_nonce_action';
        $nonce_name = 'seo_meta_box_nonce';

        // Test with no existing meta data
        ob_start();
        $this->instance->display_meta_box_content($post);
        $output_empty = ob_get_clean();

        $this->assertStringContainsString('name="' . $nonce_name . '"', $output_empty, 'Nonce field HTML input should be present.');

        $this->assertStringContainsString('name="' . $meta_key_title . '"', $output_empty);
        $this->assertStringContainsString('id="custom_meta_title"', $output_empty);
        $this->assertStringContainsString('value=""', $output_empty, 'Title value should be empty initially.');
        $this->assertStringContainsString('name="' . $meta_key_description . '"', $output_empty);
        $this->assertStringContainsString('id="custom_meta_description"', $output_empty);
        $this->assertStringContainsString('</textarea>', $output_empty);
        $this->assertStringNotContainsString('Existing Title</textarea>', $output_empty, 'Description should be empty initially.'); // Check it's empty

        // Test with existing meta data
        $existing_title = 'Existing Title';
        $existing_desc = 'Existing Description';
        update_post_meta($this->post_id, $meta_key_title, $existing_title);
        update_post_meta($this->post_id, $meta_key_description, $existing_desc);

        ob_start();
        $this->instance->display_meta_box_content($post);
        $output_filled = ob_get_clean();

        $this->assertStringContainsString('value="' . esc_attr($existing_title) . '"', $output_filled, 'Existing title should be displayed.');
        $this->assertStringContainsString('>' . esc_textarea($existing_desc) . '</textarea>', $output_filled, 'Existing description should be displayed.');
    }

    /**
     * Test the save_meta_data method.
     * @covers Integlight_SEO_Meta::save_meta_data
     */
    public function test_save_meta_data()
    {
        $meta_key_title = '_custom_meta_title';
        $meta_key_description = '_custom_meta_description';
        $nonce_action = 'seo_meta_box_nonce_action';
        $nonce_name = 'seo_meta_box_nonce';

        // 1. Test saving valid data for a post
        $_POST[$nonce_name] = wp_create_nonce($nonce_action);
        $_POST['post_type'] = 'post'; // Simulate post type context
        $_POST[$meta_key_title] = 'Test Title <script>alert("xss");</script>';
        $_POST[$meta_key_description] = "Test Description\n with line breaks <script>alert('xss');</script>";

        $this->instance->save_meta_data($this->post_id);

        $this->assertEquals('Test Title', get_post_meta($this->post_id, $meta_key_title, true), 'Sanitized title should be saved.');
        $this->assertEquals("Test Description\n with line breaks", get_post_meta($this->post_id, $meta_key_description, true), 'Sanitized description should be saved.');

        // Clean up $_POST
        unset($_POST[$nonce_name], $_POST['post_type'], $_POST[$meta_key_title], $_POST[$meta_key_description]);

        // 2. Test saving valid data for a page
        $_POST[$nonce_name] = wp_create_nonce($nonce_action);
        $_POST['post_type'] = 'page'; // Simulate page type context
        $_POST[$meta_key_title] = 'Page Title';
        $_POST[$meta_key_description] = 'Page Description';

        $this->instance->save_meta_data($this->page_id);

        $this->assertEquals('Page Title', get_post_meta($this->page_id, $meta_key_title, true));
        $this->assertEquals('Page Description', get_post_meta($this->page_id, $meta_key_description, true));

        // Clean up $_POST
        unset($_POST[$nonce_name], $_POST['post_type'], $_POST[$meta_key_title], $_POST[$meta_key_description]);

        // 3. Test saving empty data (should update meta to empty string)
        update_post_meta($this->post_id, $meta_key_title, 'Should Be Cleared');
        update_post_meta($this->post_id, $meta_key_description, 'Should Be Cleared Too');

        $_POST[$nonce_name] = wp_create_nonce($nonce_action);
        $_POST['post_type'] = 'post';
        $_POST[$meta_key_title] = '';
        $_POST[$meta_key_description] = '';

        $this->instance->save_meta_data($this->post_id);

        $this->assertEquals('', get_post_meta($this->post_id, $meta_key_title, true), 'Empty title should clear existing meta.');
        $this->assertEquals('', get_post_meta($this->post_id, $meta_key_description, true), 'Empty description should clear existing meta.');

        // Clean up $_POST
        unset($_POST[$nonce_name], $_POST['post_type'], $_POST[$meta_key_title], $_POST[$meta_key_description]);

        // 4. Test without nonce (should not save)
        delete_post_meta($this->post_id, $meta_key_title);
        $_POST['post_type'] = 'post';
        $_POST[$meta_key_title] = 'No Nonce Title';
        $this->instance->save_meta_data($this->post_id);
        $this->assertEmpty(get_post_meta($this->post_id, $meta_key_title, true), 'Data should not be saved without nonce.');
        unset($_POST['post_type'], $_POST[$meta_key_title]);


        // 5. Test with invalid nonce (should not save)
        $_POST[$nonce_name] = 'invalid-nonce';
        $_POST['post_type'] = 'post';
        $_POST[$meta_key_title] = 'Invalid Nonce Title';
        $this->instance->save_meta_data($this->post_id);
        $this->assertEmpty(get_post_meta($this->post_id, $meta_key_title, true), 'Data should not be saved with invalid nonce.');
        unset($_POST[$nonce_name], $_POST['post_type'], $_POST[$meta_key_title]);

        // 6. Test insufficient permissions (should not save)
        wp_set_current_user(self::factory()->user->create(['role' => 'subscriber'])); // User without edit caps
        $_POST[$nonce_name] = wp_create_nonce($nonce_action);
        $_POST['post_type'] = 'post';
        $_POST[$meta_key_title] = 'No Permission Title';
        $this->instance->save_meta_data($this->post_id);
        $this->assertEmpty(get_post_meta($this->post_id, $meta_key_title, true), 'Data should not be saved without permissions.');

        unset($_POST[$nonce_name], $_POST['post_type'], $_POST[$meta_key_title]);
        // Restore editor user
        $editor_user = get_user_by('email', 'editor@example.org'); // Use standard WP function
        if ($editor_user) {
            wp_set_current_user($editor_user->ID);
        }
        // Note: Testing DOING_AUTOSAVE requires more complex setup or mocking.
    }

    /**
     * Test the filter_document_title method.
     * @covers Integlight_SEO_Meta::filter_document_title
     */
    public function test_filter_document_title()
    {
        $meta_key_title = '_custom_meta_title';
        $original_title_parts = [
            'title' => 'Original Post Title',
            'page' => '',
            'tagline' => 'Site Tagline',
            'site' => 'Test Site',
        ];

        // 1. Test on a singular post page with custom title
        $custom_title = 'My Custom SEO Title';
        update_post_meta($this->post_id, $meta_key_title, $custom_title);
        $this->go_to(get_permalink($this->post_id)); // Set context to the post

        $filtered_parts = $this->instance->filter_document_title($original_title_parts);
        $this->assertEquals($custom_title, $filtered_parts['title'], 'Custom title should override original title on singular view.');
        // Check other parts remain (unless intentionally removed in the filter)
        $this->assertEquals($original_title_parts['site'], $filtered_parts['site']);

        // 2. Test on a singular post page without custom title
        delete_post_meta($this->post_id, $meta_key_title);
        $this->go_to(get_permalink($this->post_id)); // Reset context

        $filtered_parts_no_custom = $this->instance->filter_document_title($original_title_parts);
        $this->assertEquals($original_title_parts['title'], $filtered_parts_no_custom['title'], 'Original title should remain if no custom title is set.');
        $this->assertEquals($original_title_parts, $filtered_parts_no_custom, 'Title parts should be unchanged without custom title.');

        // 3. Test on a non-singular page (e.g., archive)
        $this->go_to(get_post_type_archive_link('post')); // Set context to archive
        update_post_meta($this->post_id, $meta_key_title, $custom_title); // Add meta back just in case

        $filtered_parts_archive = $this->instance->filter_document_title($original_title_parts);
        $this->assertEquals($original_title_parts, $filtered_parts_archive, 'Title parts should be unchanged on non-singular views.');
    }

    /**
     * Test the output_meta_description method.
     * @covers Integlight_SEO_Meta::output_meta_description
     */
    public function test_output_meta_description()
    {
        $meta_key_description = '_custom_meta_description';

        // 1. Test on singular post with custom description
        $custom_desc = 'My Custom SEO Description';
        update_post_meta($this->post_id, $meta_key_description, $custom_desc);
        $this->go_to(get_permalink($this->post_id));

        ob_start();
        $this->instance->output_meta_description();
        $output_custom = ob_get_clean();
        $expected_tag_custom = '<meta name="description" content="' . esc_attr($custom_desc) . '">' . "\n";
        $this->assertEquals($expected_tag_custom, $output_custom, 'Custom meta description tag should be output.');

        // 2. Test on singular post with excerpt but no custom description
        delete_post_meta($this->post_id, $meta_key_description);
        $excerpt = 'This is the post excerpt.';
        wp_update_post(['ID' => $this->post_id, 'post_excerpt' => $excerpt]);
        $this->go_to(get_permalink($this->post_id)); // Reset context

        ob_start();
        $this->instance->output_meta_description();
        $output_excerpt = ob_get_clean();
        $expected_tag_excerpt = '<meta name="description" content="' . esc_attr($excerpt) . '">' . "\n";
        $this->assertEquals($expected_tag_excerpt, $output_excerpt, 'Excerpt should be used for meta description if custom is empty.');

        // 3. Test on singular post with neither custom description nor excerpt
        wp_update_post(['ID' => $this->post_id, 'post_excerpt' => '']); // Remove excerpt
        $this->go_to(get_permalink($this->post_id)); // Reset context

        ob_start();
        $this->instance->output_meta_description();
        $output_none = ob_get_clean();
        $this->assertEmpty($output_none, 'No meta description tag should be output if neither custom nor excerpt exists.');

        // 4. Test on a non-singular page (e.g., archive)
        $this->go_to(get_post_type_archive_link('post')); // Set context to archive
        update_post_meta($this->post_id, $meta_key_description, $custom_desc); // Add meta back

        ob_start();
        $this->instance->output_meta_description();
        $output_archive = ob_get_clean();
        $this->assertEmpty($output_archive, 'No meta description tag should be output on non-singular views.');
    }
}
