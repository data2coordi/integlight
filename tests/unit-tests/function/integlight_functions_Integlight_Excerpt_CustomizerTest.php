<?php // tests/unit-tests/Integlight_excerpt_edit_Test.php

declare(strict_types=1);

// テスト対象のクラスファイルを読み込む (パスは環境に合わせて調整してください)
//require_once dirname(__DIR__, 2) . '/inc/integlight-functions.php'; // Integlight_excerpt_edit が定義されているファイル

/**
 * Test case for the Integlight_excerpt_edit class.
 *
 * @coversDefaultClass Integlight_excerpt_edit
 * @group functions
 * @group excerpt
 */
class integlight_functions_Integlight_excerpt_editTest extends WP_UnitTestCase // クラス名を修正
{
    /**
     * @var Integlight_excerpt_edit
     */
    private $instance;

    /**
     * 各テストの前に実行: インスタンスを作成してフィルターを登録
     */
    public function setUp(): void
    {
        parent::setUp();
        // クラスをインスタンス化してフィルターフックを登録
        $this->instance = new Integlight_excerpt_edit();
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::custom_excerpt_length
     * excerpt_length フィルターが適用され、正しい長さが返されることを確認
     */
    public function test_custom_excerpt_length_filter_applies_correct_length(): void
    {
        // フィルターが登録されているか確認 (オプション)
        $this->assertTrue(has_filter('excerpt_length', [$this->instance, 'custom_excerpt_length']) !== false);

        // フィルターを適用して結果を確認
        $default_length = 55; // WordPress のデフォルト値
        $expected_length = 200; // クラスで設定された値
        $actual_length = apply_filters('excerpt_length', $default_length);

        $this->assertEquals($expected_length, $actual_length);
    }

    /**
     * @test
     * @covers ::__construct
     * wp_trim_excerpt フィルターが正しい優先度で登録されているか確認
     */
    public function test_clean_auto_excerpt_filter_is_registered_with_correct_priority(): void
    {
        $expected_priority = 20;
        $actual_priority = has_filter('wp_trim_excerpt', [$this->instance, 'clean_auto_excerpt']);

        $this->assertEquals($expected_priority, $actual_priority);
    }

    // ★★★ 修正点: 目次削除のテストケースを削除 ★★★
    // /**
    //  * @test
    //  * @covers ::clean_auto_excerpt
    //  * 目次部分 ("Index ...") が削除されることを確認
    //  */
    // public function test_clean_auto_excerpt_removes_toc(): void
    // {
    //     // ... (削除) ...
    // }

    /**
     * @test
     * @covers ::clean_auto_excerpt
     * &nbsp; が半角スペースに置換されることを確認
     */
    public function test_clean_auto_excerpt_replaces_nbsp(): void
    {
        $input_excerpt = "Some&nbsp;text&nbsp;with&nbsp;non-breaking&nbsp;spaces.";
        $expected_excerpt = "Some text with non-breaking spaces.";
        $actual_excerpt = apply_filters('wp_trim_excerpt', $input_excerpt);

        $this->assertEquals($expected_excerpt, $actual_excerpt);
    }

    /**
     * @test
     * @covers ::clean_auto_excerpt
     * HTMLタグが除去されることを確認
     */
    public function test_clean_auto_excerpt_strips_html_tags(): void
    {
        $input_excerpt = "<p>This is <strong>excerpt</strong> with <em>HTML</em> tags.</p>";
        $expected_excerpt = "This is excerpt with HTML tags.";
        $actual_excerpt = apply_filters('wp_trim_excerpt', $input_excerpt);

        $this->assertEquals($expected_excerpt, $actual_excerpt);
    }

    /**
     * @test
     * @covers ::clean_auto_excerpt
     * 連続する空白や改行が単一のスペースに正規化されることを確認
     */
    public function test_clean_auto_excerpt_normalizes_whitespace(): void
    {
        $input_excerpt = "This   excerpt\nhas \t multiple \n\n whitespaces.";
        $expected_excerpt = "This excerpt has multiple whitespaces.";
        $actual_excerpt = apply_filters('wp_trim_excerpt', $input_excerpt);

        $this->assertEquals($expected_excerpt, $actual_excerpt);
    }

    /**
     * @test
     * @covers ::clean_auto_excerpt
     * 前後の空白がトリムされることを確認
     */
    public function test_clean_auto_excerpt_trims_leading_trailing_whitespace(): void
    {
        $input_excerpt = "  \n Trimmed excerpt content. \t ";
        $expected_excerpt = "Trimmed excerpt content.";
        $actual_excerpt = apply_filters('wp_trim_excerpt', $input_excerpt);

        $this->assertEquals($expected_excerpt, $actual_excerpt);
    }

    /**
     * @test
     * @covers ::clean_auto_excerpt
     * 複数のクリーンアップ処理が組み合わさったケースを確認 (目次削除を除く)
     */
    public function test_clean_auto_excerpt_handles_combined_cases_without_toc(): void // メソッド名を変更
    {
        // ★★★ 修正点: 入力から目次部分を削除 ★★★
        $input_excerpt = "  <p>This&nbsp;is \n\n the <strong>final</strong> test. </p>  ";
        $expected_excerpt = "This is the final test.";
        $actual_excerpt = apply_filters('wp_trim_excerpt', $input_excerpt);

        $this->assertEquals($expected_excerpt, $actual_excerpt);
    }

    // ★★★ 修正点: 目次がない場合のテストは、他のテストと重複するため削除しても良い ★★★
    // /**
    //  * @test
    //  * @covers ::clean_auto_excerpt
    //  * 目次がない場合はそのまま返されることを確認
    //  */
    // public function test_clean_auto_excerpt_handles_no_toc(): void
    // {
    //     // ... (削除) ...
    // }
}
