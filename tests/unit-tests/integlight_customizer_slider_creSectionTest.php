<?php

declare(strict_types=1);

// Test target class file
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider-creSection.php'; // Ensure this is loaded if not autoloaded

// Need WP_Customize_Manager for instantiation, ensure it's loaded
// Note: The check in setUp already handles this.

/**
 * integlight_customizer_slider_creSection class unit tests
 *
 * @group customizer
 */
class integlight_customizer_slider_creSectionTest extends WP_UnitTestCase
{
    /**
     * Test target class instance
     * @var integlight_customizer_slider_creSection
     */
    private $instance;

    /**
     * Global WP_Customize_Manager instance for testing
     * @var WP_Customize_Manager
     */
    private $wp_customize;

    /**
     * Set up method runs before each test.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Ensure WP_Customize_Manager class is loaded
        if (!class_exists('WP_Customize_Manager')) {
            if (defined('ABSPATH') && defined('WPINC')) {
                $customize_manager_path = ABSPATH . WPINC . '/class-wp-customize-manager.php';
                if (file_exists($customize_manager_path)) {
                    require_once $customize_manager_path;
                } else {
                    $this->fail('WP_Customize_Manager class file not found at expected path: ' . $customize_manager_path);
                }
            } else {
                $this->fail('WordPress core constants (ABSPATH, WPINC) are not defined. WP test environment might not be loaded.');
            }
        }
        if (!class_exists('WP_Customize_Manager')) {
            $this->fail('Failed to load WP_Customize_Manager class.');
        }

        // Instantiate the class under test BEFORE creating WP_Customize_Manager
        // because its constructor adds the action hook we might trigger later.
        $this->instance = new integlight_customizer_slider_creSection();

        // Create a real WP_Customize_Manager instance for testing
        // We need to ensure WordPress core functions used by the manager are available.
        // WP_UnitTestCase should handle most of this.
        // We might need to set up the global $wp_customize variable if tests depend on it,
        // but often passing the instance directly is cleaner.
        global $wp_customize; // Make the global available if needed elsewhere
        $this->wp_customize = new WP_Customize_Manager();
        $wp_customize = $this->wp_customize; // Assign to global if necessary for compatibility

    }

    /**
     * Tear down method runs after each test.
     */
    public function tearDown(): void
    {
        remove_theme_mod('integlight_display_choice');
        // Remove the action hook added by the constructor
        remove_action('customize_register', [$this->instance, 'creSection']);

        // Clean up the global customize manager instance if set
        unset($GLOBALS['wp_customize']);
        unset($this->wp_customize); // Clean up the property

        parent::tearDown();
    }

    /**
     * 定数のゲッターメソッドが正しい値を返すかテストします。
     */
    public function testConstants(): void
    {
        $this->assertSame('slider_panel', $this->instance->getSliderPanelId());
        $this->assertSame('slider_section', $this->instance->getSliderSectionId());
        $this->assertSame('sliderOrImage_section', $this->instance->getSliderOrImageSectionId());
    }

    /**
     * creSection() メソッドが WP_Customize_Manager を使用して
     * パネルとセクションを正しく登録するかテストします。
     * (モックではなく実際のインスタンスを使用)
     */
    public function testCreSectionRegistersPanelAndSections(): void
    {
        // $this->wp_customize は setUp で初期化済みの実際のインスタンス

        // Execute the method under test, passing the real customize manager
        $this->instance->creSection($this->wp_customize);

        // --- Assertions ---
        // Panel の検証
        $panel = $this->wp_customize->get_panel(integlight_customizer_slider_creSection::SLIDER_PANEL_ID);
        $this->assertInstanceOf(WP_Customize_Panel::class, $panel, 'Slider panel should be registered.');
        $this->assertEquals(__('Top Header Setting', 'integlight'), $panel->title, 'Panel title should match.');
        $this->assertEquals(29, $panel->priority, 'Panel priority should match.');
        $this->assertEquals(__('Please select whether to display a slider or an image in the top header. The settings button for the selected option will be displayed.', 'integlight'), $panel->description, 'Panel description should match.');


        // Section 1 (sliderOrImage_section) の検証
        $section1 = $this->wp_customize->get_section(integlight_customizer_slider_creSection::SLIDER_OR_IMAGE_SECTION_ID);
        $this->assertInstanceOf(WP_Customize_Section::class, $section1, 'Slider or Image section should be registered.');
        $this->assertEquals(__('Select - Slider or Image', 'integlight'), $section1->title, 'Section 1 title should match.');
        $this->assertEquals(29, $section1->priority, 'Section 1 priority should match.');
        $this->assertEquals(integlight_customizer_slider_creSection::SLIDER_PANEL_ID, $section1->panel, 'Section 1 should belong to the correct panel.');


        // Section 2 (slider_section) の検証
        $section2 = $this->wp_customize->get_section(integlight_customizer_slider_creSection::SLIDER_SECTION_ID);
        $this->assertInstanceOf(WP_Customize_Section::class, $section2, 'Slider section should be registered.');
        $this->assertEquals(__('Slider Settings', 'integlight'), $section2->title, 'Section 2 title should match.');
        $this->assertEquals(29, $section2->priority, 'Section 2 priority should match.');
        $this->assertEquals(integlight_customizer_slider_creSection::SLIDER_PANEL_ID, $section2->panel, 'Section 2 should belong to the correct panel.');
        $this->assertTrue(is_callable($section2->active_callback), 'Section 2 should have a callable active_callback.');

        // active_callback の動作もここでテストできる
        // (testActiveCallbackLogicWithWpUnitTestCase と重複するが、統合テストとして有効)
        set_theme_mod('integlight_display_choice', 'slider');
        $this->assertTrue(call_user_func($section2->active_callback), 'Active callback should return true when choice is slider.');
        set_theme_mod('integlight_display_choice', 'image');
        $this->assertFalse(call_user_func($section2->active_callback), 'Active callback should return false when choice is image.');
        remove_theme_mod('integlight_display_choice'); // デフォルト値('slider')をテスト
        $this->assertTrue(call_user_func($section2->active_callback), 'Active callback should return true when choice is default.');
    }

    /**
     * slider_section の active_callback のロジックをテストします。
     * WP_UnitTestCase を利用して get_theme_mod の動作をシミュレートします。
     * (このテストは testCreSectionRegistersPanelAndSections 内でも検証されるが、単体テストとして残す)
     */
    public function testActiveCallbackLogicWithWpUnitTestCase(): void
    {
        // creSection 内で定義されている active_callback のロジックを再現
        $callback_logic = function () {
            return get_theme_mod('integlight_display_choice', 'slider') === 'slider';
        };

        // ケース1: theme_mod が 'slider' に設定されている場合
        set_theme_mod('integlight_display_choice', 'slider');
        $this->assertTrue(call_user_func($callback_logic), "Callback should return true when theme_mod is 'slider'");

        // ケース2: theme_mod が 'image' に設定されている場合
        set_theme_mod('integlight_display_choice', 'image');
        $this->assertFalse(call_user_func($callback_logic), "Callback should return false when theme_mod is 'image'");

        // ケース3: theme_mod が未設定の場合 (デフォルト値 'slider' が使われる)
        remove_theme_mod('integlight_display_choice');
        $this->assertTrue(call_user_func($callback_logic), "Callback should return true when theme_mod is not set (defaults to 'slider')");
    }

    /**
     * コンストラクタが customize_register アクションフックを正しく登録するかテスト
     *
     * @depends testConstants
     */
    public function testConstructorAddsActionHook(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority = has_action('customize_register', [$this->instance, 'creSection']);
        $this->assertNotFalse(
            $hook_priority,
            'The creSection method should be hooked to customize_register.'
        );
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }
}
