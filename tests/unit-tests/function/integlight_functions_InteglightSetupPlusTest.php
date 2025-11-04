<?php // tests/unit-tests/InteglightSetupPlusTest.php

/**
 * Test case for the integlight_setup_plus function.
 *
 * @group functions
 */
// クラス名を PSR-4/PSR-12 準拠の InteglightSetupPlusTest に変更することを推奨
class integlight_functions_InteglightSetupPlusTest extends WP_UnitTestCase
{
    /**
     * Ensure theme functions are loaded before tests run.
     * Note: This might indicate an issue with the bootstrap process if required.
     */
    public static function setUpBeforeClass(): void
    {
        // Adjust the path if your functions.php is elsewhere
        // ブートストラップが正しく設定されていれば不要な場合が多い
        require_once get_stylesheet_directory() . '/functions.php';
        parent::setUpBeforeClass();
    }




    /**
     * Test if integlight_setup_plus adds the expected theme supports.
     *
     * @covers ::integlight_setup_plus
     */
    public function test_integlight_setup_plus_adds_theme_supports(): void
    {
        // --- Arrange ---
        // WP_UnitTestCase setup should have run 'after_setup_theme' by now.

        // --- Act ---
        // No explicit action needed here.

        // --- Assert ---
        // デバッグコードは削除
        // Check if each theme support was added
        $this->assertTrue(
            current_theme_supports('wp-block-styles'),
            'Theme support for "wp-block-styles" should be added.'
        );
        $this->assertTrue(
            current_theme_supports('responsive-embeds'),
            'Theme support for "responsive-embeds" should be added.'
        );
        $this->assertTrue(
            current_theme_supports('align-wide'),
            'Theme support for "align-wide" should be added.'
        );
    }


    /**
     * Clean up after tests if necessary.
     */
    public function tearDown(): void
    {
        // WP_UnitTestCase がテスト間の分離を処理するため、
        // 通常はこれらの remove_theme_support は不要。
        // テスト失敗の原因となっていたためコメントアウト。
        // remove_theme_support('wp-block-styles');
        // remove_theme_support('responsive-embeds'); // コメントアウトまたは削除
        // remove_theme_support('align-wide');      // コメントアウトまたは削除

        parent::tearDown();
    }
}
