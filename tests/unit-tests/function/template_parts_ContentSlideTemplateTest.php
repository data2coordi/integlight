<?php

/**
 * Class ContentSlideTemplateTest
 *
 * Tests for the template part template-parts/content-slide.php
 *
 * @package Integlight
 */

// 現在のクラス名を使用
class template_parts_ContentSlideTemplateTest extends WP_UnitTestCase
{

    // テスト用のアタッチメントIDを保持するプロパティ
    private static $image_id_1;
    private static $image_id_2;
    private static $image_id_3;
    private static $image_url_1; // 画像URLも保持
    private static $image_url_2; // 画像URLも保持

    // テスト用のスライダー設定オブジェクトを模倣
    private static $mock_slider_settings;

    /**
     * テストクラス全体のセットアップ (テスト実行前に1回だけ)
     * テスト用のアタッチメントを作成します。
     */
    public static function wpSetUpBeforeClass($factory)
    {
        // テスト用のダミー画像ファイルパス (テストファイルからの相対パス)
        $dummy_image_path = dirname(__FILE__, 2) . '/dummy-image.png';
        if (! file_exists($dummy_image_path)) {
            // ダミー画像がない場合は一時ファイルを作成
            $dummy_image_path = wp_tempnam('dummy-image.png');
            // 注意: wp_tempnam は空ファイルを作成します。画像処理が必要な場合は別途対応が必要です。
            //       テストが画像の内容に依存しない場合はこれでOKです。
        }

        // アタッチメントを作成
        self::$image_id_1 = $factory->attachment->create_upload_object($dummy_image_path, 0);
        self::$image_id_2 = $factory->attachment->create_upload_object($dummy_image_path, 0);
        self::$image_id_3 = $factory->attachment->create_upload_object($dummy_image_path, 0);

        // 画像URLを取得して保存 (テストでの比較用)
        // エラーチェックを追加
        if (self::$image_id_1 && !is_wp_error(self::$image_id_1)) {
            self::$image_url_1 = wp_get_attachment_url(self::$image_id_1);
        }
        if (self::$image_id_2 && !is_wp_error(self::$image_id_2)) {
            self::$image_url_2 = wp_get_attachment_url(self::$image_id_2);
        }


        // 一時ファイルとしてダミー画像を作成した場合、削除する
        if (strpos($dummy_image_path, get_temp_dir()) === 0 && file_exists($dummy_image_path)) {
            unlink($dummy_image_path);
        }

        // $Integlight_slider_settings グローバル変数を模倣
        self::$mock_slider_settings = new stdClass();
        // 重要: 'slider' の値が実際のテーマの $Integlight_slider_settings->headerTypeName_slider と一致しているか確認してください。
        self::$mock_slider_settings->headerTypeName_slider = 'slider';
    }

    /**
     * テストクラス全体のティアダウン (全テスト実行後に1回だけ)
     * 作成したアタッチメントを削除します。
     */
    public static function wpTearDownAfterClass()
    {
        // IDが有効な場合のみ削除
        if (self::$image_id_1) wp_delete_attachment(self::$image_id_1, true);
        if (self::$image_id_2) wp_delete_attachment(self::$image_id_2, true);
        if (self::$image_id_3) wp_delete_attachment(self::$image_id_3, true);
    }

    /**
     * 各テストメソッド実行前のセットアップ
     */
    public function set_up()
    {
        parent::set_up();
        // グローバル変数を設定
        global $Integlight_slider_settings;
        $Integlight_slider_settings = self::$mock_slider_settings;

        // 各テストの前にテーマ設定をクリア
        remove_theme_mod('integlight_display_choice');
        remove_theme_mod('integlight_slider_image_1');
        remove_theme_mod('integlight_slider_image_2');
        remove_theme_mod('integlight_slider_image_3');
        remove_theme_mod('integlight_slider_text_1');
        remove_theme_mod('integlight_slider_text_2');
        remove_theme_mod('integlight_slider_image_mobile_1');
        remove_theme_mod('integlight_slider_image_mobile_2');
        remove_theme_mod('integlight_slider_image_mobile_3');
    }

    /**
     * 各テストメソッド実行後のティアダウン
     */
    public function tear_down()
    {
        // グローバル変数をクリア
        unset($GLOBALS['Integlight_slider_settings']);
        parent::tear_down();
    }

    /**
     * ヘルパー関数: content-slide.php の出力を取得します。
     *
     * @return string キャプチャされたHTML出力。
     */
    private function get_slide_template_output(): string
    {
        ob_start();
        get_template_part('template-parts/content-slide');
        return ob_get_clean();
    }

    /**
     * @test
     * テーマ設定 'integlight_display_choice' がスライダー以外の場合、スライドの中身が出力されないことを確認。
     */
    public function test_slider_not_displayed_when_choice_is_not_slider()
    {
        // Arrange
        set_theme_mod('integlight_display_choice', 'not_slider');
        set_theme_mod('integlight_slider_image_1', self::$image_id_1);

        // Act
        $output = $this->get_slide_template_output();

        // Assert: スライドの中身（.slides や img）が含まれていないことを確認
        $this->assertStringNotContainsString('<div class="slides">', $output, "Slider content (.slides) should not be displayed when 'integlight_display_choice' is not 'slider'.");
        $this->assertStringNotContainsString('<img', $output, "Slider content (img) should not be displayed when 'integlight_display_choice' is not 'slider'.");
    }

    /**
     * @test
     * 表示設定がスライダーでも、画像が1つも設定されていない場合、スライドの中身が出力されないことを確認。
     */
    public function test_slider_not_displayed_when_no_images_set()
    {
        // Arrange
        set_theme_mod('integlight_display_choice', 'slider');

        // Act
        $output = $this->get_slide_template_output();

        // Assert: スライドの中身（.slides や img）が含まれていないことを確認
        $this->assertStringNotContainsString('<div class="slides">', $output, "Slider content (.slides) should not be displayed when no images are set.");
        $this->assertStringNotContainsString('<img', $output, "Slider content (img) should not be displayed when no images are set.");
    }

    /**
     * @test
     * 表示設定がスライダーで、PC用画像とテキストが設定されている場合、
     * スライダーHTML、画像、テキストが正しく出力されることを確認 (非モバイル時を想定)。
     */
    public function test_slider_displays_with_images_and_text_on_desktop()
    {
        // Arrange
        set_theme_mod('integlight_display_choice', 'slider');
        set_theme_mod('integlight_slider_image_1', self::$image_id_1);
        set_theme_mod('integlight_slider_image_2', self::$image_id_2);
        set_theme_mod('integlight_slider_text_1', "Main Text\nLine 2");
        set_theme_mod('integlight_slider_text_2', "Sub Text");

        // Act
        $output = $this->get_slide_template_output();

        // Assert:
        // 1. 主要なコンテナクラス
        $this->assertStringContainsString('<div class="slider">', $output);
        $this->assertStringContainsString('<div class="slides">', $output);
        $this->assertStringContainsString('<div class="text-overlay">', $output);

        // 2. 設定した画像 (image_1, image_2) が src 属性で出力されているか
        $this->assertStringContainsString(esc_url(self::$image_url_1), $output, "Slide 1 image URL not found.");
        $this->assertStringContainsString(esc_url(self::$image_url_2), $output, "Slide 2 image URL not found.");

        // 3. テキストオーバーレイの内容 (nl2br, wp_kses_post 適用後)
        $this->assertMatchesRegularExpression(
            '#<div class="text-overlay1">\s*<h1>Main Text<br />\s*Line 2</h1>\s*</div>#',
            $output,
            "Slider text 1 not found or incorrect."
        );
        // *** MODIFIED ASSERTION START ***
        // text-overlay2 も正規表現で検証
        $this->assertMatchesRegularExpression(
            '#<div class="text-overlay2">\s*<h2>Sub Text</h2>\s*</div>#', // 正規表現パターン
            $output, // 対象文字列
            "Slider text 2 not found or incorrect." // エラーメッセージ
        );
        // *** MODIFIED ASSERTION END ***
    }



    /**
     * @test
     * モバイル用画像が設定されている場合、PC表示ではPC用画像が使われることを確認
     */
    public function test_slider_uses_pc_images_on_desktop_even_if_mobile_set()
    {
        // Arrange
        set_theme_mod('integlight_display_choice', 'slider');
        set_theme_mod('integlight_slider_image_1', self::$image_id_1); // PC Image 1
        set_theme_mod('integlight_slider_image_mobile_1', self::$image_id_2); // Mobile Image 1 (違うID)

        // Act
        $output = $this->get_slide_template_output(); // wp_is_mobile() は false

        // Assert: PC用画像 (image_url_1) の URL が使われていることを確認
        $this->assertStringContainsString(esc_url(self::$image_url_1), $output, "PC Image 1 URL should be used on desktop.");
        // モバイル用画像 (image_url_2) の URL が使われていないことを確認
        if (self::$image_url_2) { // モバイル用URLが取得できている場合のみチェック
            $this->assertStringNotContainsString(esc_url(self::$image_url_2), $output, "Mobile Image 1 URL should not be used on desktop.");
        }
    }
}
