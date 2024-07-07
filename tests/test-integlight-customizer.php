<?php

use PHPUnit\Framework\TestCase;

class InteglightThemeCustomizeTest extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // テスト対象のクラスをインスタンス化
        $this->customize = new InteglightThemeCustomize();
    }

    public function test_customize_register()
    {
        global $wp_customize;

        // カスタマイズマネージャをモック
        $wp_customize = $this->getMockBuilder('WP_Customize_Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $wp_customize->expects($this->exactly(1))
            ->method('add_section')
            ->with($this->arrayHasKey('base_pattern_section'));

        $wp_customize->expects($this->exactly(1))
            ->method('add_setting')
            ->with('base_color_setting', $this->arrayHasKey('sanitize_callback'));

        $wp_customize->expects($this->exactly(1))
            ->method('add_control')
            ->with('base_color_setting', $this->arrayHasKey('choices'));

        // メソッド呼び出し
        $this->customize->customize_register($wp_customize);
    }

    public function test_sanitize_choices()
    {
        global $wp_customize;

        // カスタマイズマネージャをモック
        $wp_customize = $this->getMockBuilder('WP_Customize_Manager')
            ->disableOriginalConstructor()
            ->getMock();

        $control = $this->getMockBuilder('WP_Customize_Control')
            ->disableOriginalConstructor()
            ->getMock();

        $control->method('choices')
            ->willReturn([
                'pattern1' => 'None',
                'pattern2' => 'Blue',
                'pattern3' => 'Green',
                'pattern4' => 'Orange',
                'pattern5' => 'Red',
                'pattern6' => 'Pink',
            ]);

        $wp_customize->method('get_control')
            ->willReturn($control);

        $setting = $this->getMockBuilder('WP_Customize_Setting')
            ->disableOriginalConstructor()
            ->getMock();

        $setting->id = 'base_color_setting';
        $setting->default = 'pattern1';

        // 有効な選択肢をテスト
        $valid_choice = $this->customize->sanitize_choices('pattern2', $setting);
        $this->assertEquals('pattern2', $valid_choice);

        // 無効な選択肢をテスト
        $invalid_choice = $this->customize->sanitize_choices('invalid_pattern', $setting);
        $this->assertEquals('pattern1', $invalid_choice);
    }

    public function test_enqueue_custom_css()
    {
        // テーマモディファイアを設定
        set_theme_mod('base_color_setting', 'pattern2');

        // スタイルをエンキューする
        $this->customize->enqueue_custom_css();

        // グローバル変数を使用してスタイルのエンキュー状況を確認
        global $wp_styles;
        $this->assertArrayHasKey('custom-pattern', $wp_styles->registered);
        $this->assertEquals(
            get_template_directory_uri() . '/css/pattern2.css',
            $wp_styles->registered['custom-pattern']->src
        );
    }
}
