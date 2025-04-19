<?php

//use PHPUnit\Framework\TestCase;

class CustomizerSliderTest extends WP_UnitTestCase
{

	public function test_slider_settings_globals_are_set_correctly()
	{
		new integlight_customizer_slider();

		$this->assertSame('fade', $GLOBALS['Integlight_slider_settings']->effectName_fade);

		$this->assertSame('slide', $GLOBALS['Integlight_slider_settings']->effectName_slide);

		$this->assertSame('slider', $GLOBALS['Integlight_slider_settings']->headerTypeName_slider);

		$this->assertSame('image', $GLOBALS['Integlight_slider_settings']->headerTypeName_image);
	}
}
