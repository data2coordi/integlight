<?php
class InteglightSliderSettings
{
    public $effectName_fade = 'fade';
    public $effectName_slide = 'slide';
    public $headerTypeName_slider = 'slider';
    public $headerTypeName_image = 'image';
    public $homeType1 = 'home1';
    public $homeType2 = 'home2';
    public $homeType3 = 'home3';
    public $homeType4 = 'home4';

    public static function init()
    {
        $GLOBALS['Integlight_slider_settings'] = new self();
    }
}

InteglightSliderSettings::init();
