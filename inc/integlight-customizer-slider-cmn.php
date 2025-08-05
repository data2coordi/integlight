<?php
class InteglightSliderSettings
{
    public $effectName_fade = 'fade';
    public $effectName_slide = 'slide';
    public $headerTypeName_slider = 'slider';
    public $headerTypeName_image = 'image';

    public static function init()
    {
        $GLOBALS['Integlight_slider_settings'] = new self();
    }
}

InteglightSliderSettings::init();
