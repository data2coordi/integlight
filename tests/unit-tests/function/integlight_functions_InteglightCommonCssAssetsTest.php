<?php

use PHPUnit\Framework\TestCase; // または WP_UnitTestCase を使用

// bootstrap.php で読み込まれるため、以下の require_once は不要になります
// require_once dirname(__FILE__, 3) . '/inc/integlight-functions-outerAssets.php';
// require_once dirname(__FILE__, 3) . '/inc/integlight-functions.php';

/**
 * Test case for InteglightCommonCssAssets class.
 */
class integlight_functions_InteglightCommonCssAssetsTest extends TestCase // または extends WP_UnitTestCase
{
    /**
     * Helper function to get the value of a protected or private static property.
     *
     * @param string $className    The name of the class.
     * @param string $propertyName The name of the static property.
     * @return mixed The value of the static property.
     * @throws ReflectionException If the class or property does not exist.
     */
    protected static function getStaticPropertyValue(string $className, string $propertyName)
    {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true); // Allow access to protected/private property
        return $property->getValue();
    }

    /**
     * Helper function to set the value of a protected or private static property.
     *
     * @param string $className    The name of the class.
     * @param string $propertyName The name of the static property.
     * @param mixed  $value        The value to set.
     * @throws ReflectionException If the class or property does not exist.
     */
    protected static function setStaticPropertyValue(string $className, string $propertyName, $value): void
    {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true); // Allow access to protected/private property
        $property->setValue(null, $value); // Set static property
    }

    /**
     * Set up the test environment before each test method.
     * Resets the static properties of dependency classes.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Reset static arrays to ensure test isolation
        // bootstrap.php で InteglightCommonCssAssets::init() が実行されている可能性があるため、
        // ここでリセットすることが重要になります。
        self::setStaticPropertyValue(InteglightFrontendStyles::class, 'styles', []);
        self::setStaticPropertyValue(InteglightEditorStyles::class, 'styles', []);
        self::setStaticPropertyValue(InteglightDeferCss::class, 'deferred_styles', []);
    }

    /**
     * Clean up the test environment after each test method.
     * Resets the static properties of dependency classes.
     */
    protected function tearDown(): void
    {
        // Reset static arrays again to be safe
        self::setStaticPropertyValue(InteglightFrontendStyles::class, 'styles', []);
        self::setStaticPropertyValue(InteglightEditorStyles::class, 'styles', []);
        self::setStaticPropertyValue(InteglightDeferCss::class, 'deferred_styles', []);
        parent::tearDown();
    }


    /**
     * Test the init() method.
     *
     * Verifies that the correct styles are added to the frontend, editor,
     * and deferred style lists.
     *
     * @covers InteglightCommonCssAssets::init
     */
    public function test_init_adds_correct_styles_and_deferred_handles(): void
    {
        // --- Arrange ---
        // bootstrap.php で init() が既に呼ばれている可能性を考慮し、
        // setUp() でリセットされた状態からテストを開始します。

        $expectedFrontendStyles = [
            'integlight-base-style-plus' => '/css/base-style.css',
            'integlight-style-plus' => '/css/integlight-style.css',
            'integlight-sp-style' => '/css/integlight-sp-style.css', // Included in frontend
            'integlight-layout' => '/css/layout.css',
            'integlight-integlight-menu' => '/css/integlight-menu.css',
            'integlight-post' => '/css/post.css',
            'integlight-page' => '/css/page.css',
            'integlight-front' => '/css/front.css',
            'integlight-home' => '/css/home.css',
            'integlight-module' => '/css/module.css',
            'integlight-helper' => '/css/helper.css',
        ];

        $expectedEditorStyles = $expectedFrontendStyles;
        unset($expectedEditorStyles['integlight-sp-style']); // Excluded from editor

        $expectedDeferredStyles = [
            'integlight-sp-style',
            'integlight-block-module'
        ];
        // Sort for consistent comparison as order might not be guaranteed internally
        sort($expectedDeferredStyles);

        // --- Act ---
        // init() は bootstrap.php で実行されているか、
        // あるいはテスト対象として明示的に呼び出す必要があるかもしれません。
        // ここでは、init() がテスト対象のメソッドであるため、明示的に呼び出します。
        // もし bootstrap で既に呼ばれていて、その結果をテストしたい場合は、
        // この呼び出しは不要で、setUp() でのリセットも不要になる場合があります。
        // (ただし、テストの独立性を保つためには setUp/tearDown でのリセットが推奨されます)
        InteglightCommonCssAssets::init();

        // --- Assert ---
        $actualFrontendStyles = self::getStaticPropertyValue(InteglightFrontendStyles::class, 'styles');
        $actualEditorStyles = self::getStaticPropertyValue(InteglightEditorStyles::class, 'styles');
        $actualDeferredStyles = self::getStaticPropertyValue(InteglightDeferCss::class, 'deferred_styles');
        sort($actualDeferredStyles); // Sort for comparison

        $this->assertEquals($expectedFrontendStyles, $actualFrontendStyles, 'Frontend styles were not added correctly.');
        $this->assertEquals($expectedEditorStyles, $actualEditorStyles, 'Editor styles were not added correctly (or exclusion failed).');
        $this->assertEquals($expectedDeferredStyles, $actualDeferredStyles, 'Deferred style handles were not added correctly.');
    }
}
