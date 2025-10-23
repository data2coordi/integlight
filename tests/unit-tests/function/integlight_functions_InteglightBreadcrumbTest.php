<?php // tests/unit-tests/Integlight_breadCrumbTest.php

declare(strict_types=1);

/**
 * Test case for the Integlight_breadCrumb class.
 *
 * @coversDefaultClass Integlight_breadCrumb
 * @group functions
 * @group breadcrumb
 */
class integlight_functions_Integlight_breadCrumbTest extends WP_UnitTestCase
{
    /**
     * Test target class instance.
     * @var Integlight_breadCrumb|null
     */
    private $instance = null;

    /**
     * Set up the test environment before each test.
     */
    public function setUp(): void
    {
        parent::setUp();
        // Instantiate the class (this also adds the hooks)
        $this->instance = new Integlight_breadCrumb();
    }

    /**
     * Clean up the test environment after each test.
     */
    public function tearDown(): void
    {
        // Remove hooks added by the constructor
        remove_action('after_header', [$this->instance, 'add_breadcrumb'], 10);
        unset($this->instance);
        parent::tearDown();
    }

    /**
     * @test
     * @covers ::__construct
     * Test if constructor adds the necessary hook.
     */
    public function constructor_should_add_after_header_hook(): void
    {
        $this->assertGreaterThan(
            0,
            has_action('after_header', [$this->instance, 'add_breadcrumb']),
            'add_breadcrumb should be hooked to after_header.'
        );
        $this->assertEquals(
            10,
            has_action('after_header', [$this->instance, 'add_breadcrumb']),
            'add_breadcrumb hook priority should be 10.'
        );
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * Test if breadcrumb is NOT displayed on the front page.
     */
    public function add_breadcrumb_should_output_nothing_on_front_page(): void
    {
        // Arrange
        $this->go_to(home_url('/')); // Go to the front page

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertEmpty($output, 'Breadcrumb should not be displayed on the front page.');
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * @covers ::get_single_breadcrumb
     * Test breadcrumb output on a single post page without categories.
     */
    public function add_breadcrumb_should_output_correctly_on_single_post_no_category(): void
    {
        // Arrange
        $post_id = self::factory()->post->create(['post_title' => 'Single Post Title']);
        $this->go_to(get_permalink($post_id));

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/"  . '">HOME</a>', $output);
        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);
        $this->assertStringContainsString('<li>Single Post Title</li>', $output); // Only post title
        $this->assertStringContainsString('</ul>', $output);
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * @covers ::get_single_breadcrumb
     * Test breadcrumb output on a single post page with a category.
     */
    public function add_breadcrumb_should_output_correctly_on_single_post_with_category(): void
    {
        // Arrange
        $cat_id = self::factory()->category->create(['name' => 'Test Category']);
        $post_id = self::factory()->post->create([
            'post_title' => 'Post In Category',
            'post_category' => [$cat_id],
        ]);
        $this->go_to(get_permalink($post_id));
        $cat_link = get_category_link($cat_id);

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/" . '">HOME</a>', $output);
        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);

        // Category link should appear
        $this->assertStringContainsString('<li><a href="' . esc_url($cat_link) . '">Test Category</a><span class="icon-', $output);
        $this->assertStringContainsString('<li>Post In Category</li>', $output); // Post title at the end
        $this->assertStringContainsString('</ul>', $output);
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * @covers ::get_category_tag_breadcrumb
     * Test breadcrumb output on a category archive page with a parent category.
     */
    public function add_breadcrumb_should_output_correctly_on_category_archive_with_parent(): void
    {
        // Arrange
        $parent_cat_id = self::factory()->category->create(['name' => 'Parent Category']);
        $child_cat_id = self::factory()->category->create([
            'name' => 'Child Category',
            'parent' => $parent_cat_id,
        ]);
        $this->go_to(get_category_link($child_cat_id));
        $parent_cat_link = get_category_link($parent_cat_id);

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/" . '">HOME</a>', $output);
        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);

        // Parent category link should appear
        $this->assertStringContainsString('<li><a href="' . esc_url($parent_cat_link) . '">Parent Category</a></li><span class="icon-', $output);
        $this->assertStringContainsString('<li>Child Category</li>', $output); // Current category at the end
        $this->assertStringContainsString('</ul>', $output);
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * @covers ::get_category_tag_breadcrumb
     * Test breadcrumb output on a tag archive page.
     */
    public function add_breadcrumb_should_output_correctly_on_tag_archive(): void
    {
        // Arrange
        $tag_id = self::factory()->tag->create(['name' => 'Test Tag']);
        $this->go_to(get_tag_link($tag_id));

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/" . '">HOME</a>', $output);
        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);

        $this->assertStringContainsString('<li>Test Tag</li>', $output); // Tag name at the end
        $this->assertStringContainsString('</ul>', $output);
        $this->assertStringNotContainsString('<a href=', substr($output, strpos($output, 'HOME</a>') + strlen('HOME</a>'))); // No other links after HOME
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * Test breadcrumb output on a static page.
     */
    public function add_breadcrumb_should_output_correctly_on_page(): void
    {
        // Arrange
        $page_id = self::factory()->post->create(['post_type' => 'page', 'post_title' => 'Static Page Title']);
        $this->go_to(get_permalink($page_id));

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/" . '">HOME</a>', $output);
        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);
        $this->assertStringContainsString('<li>Static Page Title</li>', $output); // Page title at the end
        $this->assertStringContainsString('</ul>', $output);
        $this->assertStringNotContainsString('<a href=', substr($output, strpos($output, 'HOME</a>') + strlen('HOME</a>'))); // No other links after HOME
    }

    /**
     * @test
     * @covers ::generate_breadcrumb // Target the generation logic directly
     * @covers ::helper_addUl
     * Test breadcrumb output on a 404 page.
     */
    public function add_breadcrumb_should_output_correctly_on_404(): void
    {
        global $wp_query;

        // Arrange
        // Ensure $wp_query exists and set flags directly, without go_to()
        if (!isset($wp_query)) {
            $wp_query = new WP_Query(); // Ensure it exists if not already set
        }
        $wp_query->init(); // Reset flags to a known state
        $wp_query->is_404 = true;
        $wp_query->is_front_page = false;
        $wp_query->is_home = false;
        $wp_query->is_singular = false;
        $wp_query->is_archive = false;
        // Add other relevant flags if needed, ensuring they are false

        // Act: Call generate_breadcrumb directly using reflection
        $output = '';
        try {
            // Use reflection to access the private method
            $reflectionMethod = new ReflectionMethod(Integlight_breadCrumb::class, 'generate_breadcrumb');
            $reflectionMethod->setAccessible(true); // Make the private method accessible
            $output = $reflectionMethod->invoke($this->instance); // Invoke the method

        } catch (ReflectionException $e) {
            $this->fail('Failed to call generate_breadcrumb via reflection: ' . $e->getMessage());
        }

        // Assert
        $this->assertNotEmpty($output, 'Output from generate_breadcrumb should not be empty.');
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/"  . '">HOME</a>', $output);

        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);
        // Check the specific 404 part generated by generate_breadcrumb
        $this->assertStringContainsString('<li>Page not found</li>', $output); // Line 266 (approx)
        $this->assertStringContainsString('</ul>', $output);
    }

    /**
     * @test
     * @covers ::add_breadcrumb
     * @covers ::generate_breadcrumb
     * @covers ::helper_addUl
     * Test breadcrumb output on a generic archive page (e.g., date).
     */
    public function add_breadcrumb_should_output_correctly_on_generic_archive(): void
    {
        // Arrange
        $cat_id = self::factory()->category->create(['name' => 'Archive Test Category']);
        $this->go_to(get_category_link($cat_id)); // Go to category archive

        // Act
        ob_start();
        $this->instance->add_breadcrumb();
        $output = ob_get_clean();

        // Assert
        $this->assertStringContainsString('<ul class="create_bread">', $output);
        $this->assertStringContainsString('<a href="' . home_url() . "/" . '">HOME</a>', $output);
        $this->assertStringContainsString('<span class="icon-arrow"></span>', $output);
        // single_term_title() for date archives usually returns the date string
        $this->assertStringContainsString('<li>Archive Test Category</li>', $output); // Archive title

        $this->assertStringContainsString('</ul>', $output);
        $this->assertStringNotContainsString('<a href=', substr($output, strpos($output, 'HOME</a>') + strlen('HOME</a>'))); // No other links after HOME
    }
}
