<?php








class integlight_customizer_slider_outerAssets
{

	private $pInteglight_slider_settings;


	public function __construct($slider_settings)
	{
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('wp_enqueue_scripts', array($this, 'provideTOjs'));

		$styles = [
			'integlight-slide' => ['path' => '/css/build/integlight-slide-style.css', 'deps' => ['integlight-style-plus']],
		];
		InteglightFrontendStyles::add_styles($styles);

		$scripts = [
			'integlight_slider-script' =>  ['path' => '/js/build/slider.js'],
			//'integlight_slider-script' =>  ['path' => '/js/build/slider.js', 'deps' => ['jquery']],
		];
		InteglightFrontendScripts::add_scripts($scripts);


		// 遅延対象のスクリプトを登録
		$deferredScripts = [
			'integlight_slider-script',
		];
		InteglightDeferJs::add_deferred_scripts($deferredScripts);
		/* レンダリングブロック、layout計算増加の防止のためのチューニング e*/
	}

	public function provideTOjs()
	{

		// カスタマイザーの設定値をJavaScriptに渡す
		wp_localize_script('integlight_slider-script', 'integlight_sliderSettings', array(
			'changeDuration' => get_theme_mod('integlight_slider_change_duration', 3),
			'effect' => get_theme_mod('integlight_slider_effect', $this->pInteglight_slider_settings->effectName_fade),
			'homeType' => get_theme_mod('integlight_hometype_setting', $this->pInteglight_slider_settings->homeType1Name),
			'fadeName' => $this->pInteglight_slider_settings->effectName_fade,
			'slideName' => $this->pInteglight_slider_settings->effectName_slide,
			'home1Name' => $this->pInteglight_slider_settings->homeType1Name,
			'home2Name' => $this->pInteglight_slider_settings->homeType2Name,
			'home3Name' => $this->pInteglight_slider_settings->homeType3Name,
			'home4Name' => $this->pInteglight_slider_settings->homeType4Name,
		));
	}
}







/*カスタマイザーで設定したスライダー機能をフロントでオープンしたときにロード*/
add_action('wp', function () {
	if (is_front_page()) {
		if (InteglightHeaderSettings::getSlider() === get_theme_mod('integlight_display_choice', 'none')) {
			if (
				get_theme_mod('integlight_slider_image_mobile_1') ||
				get_theme_mod('integlight_slider_image_mobile_2') ||
				get_theme_mod('integlight_slider_image_mobile_3') ||
				get_theme_mod('integlight_slider_image_1') ||
				get_theme_mod('integlight_slider_image_2') ||
				get_theme_mod('integlight_slider_image_3')
			) {
				// いずれかがセットされているときの処理
				global $Integlight_slider_settings;
				new integlight_customizer_slider_outerAssets($Integlight_slider_settings);
			}
		}
	}
});
