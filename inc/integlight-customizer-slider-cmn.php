<?php
class InteglightSliderSettings
{
    public $effectName_fade = 'fade';
    public $effectName_slide = 'slide';
    public $homeType1Name = 'home1';
    public $homeType2Name = 'home2';
    public $homeType3Name = 'home3';
    public $homeType4Name = 'home4';

    public static function init()
    {
        $GLOBALS['Integlight_slider_settings'] = new self();
    }
}

InteglightSliderSettings::init();
