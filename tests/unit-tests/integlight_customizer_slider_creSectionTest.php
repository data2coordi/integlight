<?php

class integlight_customizer_slider_creSectionTest extends  WP_UnitTestCase
{
    public function testConstants()
    {
        $instance = new integlight_customizer_slider_creSection();

        $this->assertSame('slider_panel', $instance->getSliderPanelId());
        $this->assertSame('slider_section', $instance->getSliderSectionId());
        $this->assertSame('sliderOrImage_section', $instance->getSliderOrImageSectionId());
    }

    public function testCustomizeRegisterHook()
    {
        // add_action が正しく呼ばれているかは WordPress の環境が必要ですが、
        // 通常は do_action('customize_register') で creSection が呼ばれることで間接的に確認します。
        // ここでは簡易的に add_action のテストを飛ばし、creSection の挙動に注目します。
        $instance = new integlight_customizer_slider_creSection();

        $mock_customize = $this->createMock(WP_Customize_Manager::class);

        // add_panel の引数を検証
        $mock_customize->expects($this->once())
            ->method('add_panel')
            ->with(
                $this->equalTo('slider_panel'),
                $this->callback(function ($args) {
                    return $args['title'] === 'Top Header Setting' &&
                        isset($args['description']) &&
                        $args['priority'] === 29;
                })
            );

        // add_section は2回呼ばれるので、呼び出し回数を指定
        $mock_customize->expects($this->exactly(2))
            ->method('add_section');

        $instance->creSection($mock_customize);
    }
}
