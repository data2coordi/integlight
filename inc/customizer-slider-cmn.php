<?php



class Integlight_customizer_slider_settings
{

    private static $seffectName_fade = 'fade';
    private static $seffectName_slide = 'slide';
    private static $shomeType1Name = 'home1';
    private static $shomeType2Name = 'home2';
    private static $shomeType3Name = 'home3';
    private static $shomeType4Name = 'home4';


    public static function getEffectNameFade()
    {
        return self::$seffectName_fade;
    }

    public static function getEffectNameSlide()
    {
        return self::$seffectName_slide;
    }

    public static function getHomeType1Name()
    {
        return self::$shomeType1Name;
    }

    public static function getHomeType2Name()
    {
        return self::$shomeType2Name;
    }

    public static function getHomeType3Name()
    {
        return self::$shomeType3Name;
    }

    public static function getHomeType4Name()
    {
        return self::$shomeType4Name;
    }
}
//使用例
//echo Integlight_customizer_slider_settings::getEffectNameFade(); // fade
//echo Integlight_customizer_slider_settings::getHomeType4Name();  // home4
