<?php // tests/unit-tests/integlight_customizer_headerImage_updSectionTest.php

declare(strict_types=1);

// 依存クラスとテスト対象クラスを読み込む (オートロードされていない場合)
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-slider-creSection.php';
// require_once dirname(__DIR__, 2) . '/inc/integlight-customizer-headerImage-updSection.php';

/**
 * integlight_customizer_headerImage_updSection クラスのユニットテスト
 *
 * @coversDefaultClass integlight_customizer_headerImage_updSection
 * @group customizer
 * @group slider
 */
class integlight_customizer_headerImage_updSectionTest extends WP_UnitTestCase
{
    /**
     * WP_Customize_Manager のインスタンス
     * @var WP_Customize_Manager|null
     */
    private $wp_customize = null;

    /**
     * integlight_customizer_slider_creSection のインスタンス (依存関係)
     * @var integlight_customizer_slider_creSection|null
     */
    private $slider_section_helper = null;

    /**
     * テスト対象クラスのインスタンス
     * @var integlight_customizer_headerImage_updSection|null
     */
    private $instance = null;

    /**
     * 各テストの前に実行されるセットアップメソッド
     */
    public function setUp(): void
    {
        parent::setUp();

        // WP_Customize_Manager クラスを確実に読み込む
        if (!class_exists('WP_Customize_Manager')) {
            if (!defined('ABSPATH') || !defined('WPINC')) {
                $this->fail('WordPress core constants (ABSPATH, WPINC) are not defined.');
            }
            $customize_manager_path = ABSPATH . WPINC . '/class-wp-customize-manager.php';
            if (!file_exists($customize_manager_path)) {
                $this->fail('WP_Customize_Manager class file not found.');
            }
            require_once $customize_manager_path;
        }
        // WP_Customize_Manager の実際のインスタンスを作成
        $this->wp_customize = new WP_Customize_Manager();

        // 依存クラスのインスタンスを作成
        $this->slider_section_helper = new integlight_customizer_slider_creSection();

        // テスト対象クラスのインスタンスを作成
        $this->instance = new integlight_customizer_headerImage_updSection(
            $this->slider_section_helper
        );

        // テスト用の 'header_image' セクションを事前に追加しておく
        // (実際のWordPress環境ではコアによって追加される)
        $this->wp_customize->add_section('header_image', [
            'title' => 'Original Header Image Title',
            'priority' => 20, // 元の優先度 (テストで変更されることを確認するため)
            'panel' => '',    // 元のパネル (テストで変更されることを確認するため)
        ]);
    }

    /**
     * 各テストの後に実行されるティアダウンメソッド
     */
    public function tearDown(): void
    {
        // コンストラクタで追加されたアクションフックを削除
        remove_action('customize_register', [$this->instance, 'integlight_customizer_headerImage_updSection']);
        // テスト中に設定したテーマ設定を削除
        remove_theme_mod('integlight_display_choice');
        // プロパティをクリア
        unset($this->wp_customize);
        unset($this->slider_section_helper);
        unset($this->instance);

        parent::tearDown();
    }

    /**
     * @test
     * @covers ::__construct
     * コンストラクタが customize_register アクションフックを正しく登録するかテスト
     */
    public function constructor_should_add_customize_register_action(): void
    {
        // setUp でインスタンスが作成され、コンストラクタが実行されている前提
        $hook_priority = has_action('customize_register', [$this->instance, 'integlight_customizer_headerImage_updSection']);

        $this->assertNotFalse(
            $hook_priority,
            'Constructor should add the integlight_customizer_headerImage_updSection method to the customize_register action.'
        );

        // デフォルトの優先度は 10
        $this->assertEquals(10, $hook_priority, 'The hook priority should be the default (10).');
    }

    /**
     * @test
     * @covers ::integlight_customizer_headerImage_updSection
     * header_image セクションが存在し、表示設定が 'image' の場合にセクションが正しく更新されるかテスト
     */
    public function update_section_should_modify_properties_when_display_is_image(): void
    {
        // Arrange: テーマ設定を 'image' に設定
        set_theme_mod('integlight_display_choice', 'image');

        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->integlight_customizer_headerImage_updSection($this->wp_customize);

        // Assert: セクションのプロパティが更新されていることを確認
        $section = $this->wp_customize->get_section('header_image');
        $this->assertInstanceOf(WP_Customize_Section::class, $section, 'header_image section should exist.');

        // $section->title は元のコードでコメントアウトされているため、変更されないことを確認
        $this->assertEquals('Original Header Image Title', $section->title, 'Section title should remain unchanged.');
        $this->assertEquals(30, $section->priority, 'Section priority should be updated to 30.');
        // パネルIDが依存ヘルパーから取得したIDと一致するか確認
        $this->assertEquals($this->slider_section_helper->getSliderPanelId(), $section->panel, 'Section panel should be updated to the slider panel ID.');
        $this->assertTrue(is_callable($section->active_callback), 'Section active_callback should be callable.');

        // active_callback の結果を確認
        $this->assertTrue(call_user_func($section->active_callback), 'active_callback should return true when theme_mod is "image".');
    }

    /**
     * @test
     * @covers ::integlight_customizer_headerImage_updSection
     * header_image セクションが存在し、表示設定が 'slider' (またはデフォルト) の場合に active_callback が false を返すかテスト
     */
    public function update_section_active_callback_should_return_false_when_display_is_slider(): void
    {
        // Arrange: テーマ設定を 'slider' に設定
        set_theme_mod('integlight_display_choice', 'slider');

        // Act: フックされたメソッドを手動で呼び出し
        $this->instance->integlight_customizer_headerImage_updSection($this->wp_customize);

        // Assert: セクションを取得し、active_callback の結果を確認
        $section = $this->wp_customize->get_section('header_image');
        $this->assertInstanceOf(WP_Customize_Section::class, $section, 'header_image section should exist.');
        $this->assertTrue(is_callable($section->active_callback), 'Section active_callback should be callable.');
        $this->assertFalse(call_user_func($section->active_callback), 'active_callback should return false when theme_mod is "slider".');

        // Arrange: テーマ設定を削除 (デフォルト値 'slider' が使われるはず)
        remove_theme_mod('integlight_display_choice');

        // Act: 再度メソッドを呼び出し
        $this->instance->integlight_customizer_headerImage_updSection($this->wp_customize);

        // Assert: active_callback の結果を確認 (デフォルト値の場合)
        $section_default = $this->wp_customize->get_section('header_image');
        $this->assertTrue(is_callable($section_default->active_callback), 'Section active_callback should be callable (default case).');
        // 注意: get_theme_mod のデフォルト値が 'slider' であることを前提としています。
        // もしデフォルト値が異なる、または未定義の場合、このアサーションは失敗する可能性があります。
        // integlight_customizer_HeaderTypeSelecterTest でデフォルト値が 'slider' であることを確認済み。
        $this->assertFalse(call_user_func($section_default->active_callback), 'active_callback should return false when theme_mod uses default "slider".');
    }

    /**
     * @test
     * @covers ::integlight_customizer_headerImage_updSection
     * header_image セクションが存在しない場合にエラーが発生しないことをテスト
     */
    public function update_section_should_not_error_if_section_does_not_exist(): void
    {
        // Arrange: テスト用に 'header_image' セクションを削除
        $this->wp_customize->remove_section('header_image');
        set_theme_mod('integlight_display_choice', 'image'); // 念のため設定

        // Act & Assert: メソッドを実行してもエラーが発生しないことを確認
        // PHPUnit は通常、致命的なエラー以外はキャッチしないため、
        // ここで明示的な try-catch は不要。実行が完了すればOK。
        // エラーが発生すればテストランナーが報告する。
        $this->instance->integlight_customizer_headerImage_updSection($this->wp_customize);

        // 念のため、セクションが依然として存在しないことを確認
        $section = $this->wp_customize->get_section('header_image');
        $this->assertNull($section, 'header_image section should remain null after execution.');
    }
}
