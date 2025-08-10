<?php





//フロントエンドでの表示制御用
function integlight_display_headerContents()
{
	global $Integlight_slider_settings;

	$choice = get_theme_mod('integlight_display_choice', 'none');


	switch ($choice) {
		case $Integlight_slider_settings->headerTypeName_slider:
			// 値1と一致する場合の処理
			get_template_part('template-parts/content', 'slide');

			break;

		case $Integlight_slider_settings->headerTypeName_image:
			if (get_header_image()) {
				echo '<img src="' . esc_url(get_header_image()) . '" class="topImage" ' .  ' alt="' . esc_attr(get_bloginfo('name')) . '">';
			}
			break;

		default:
			// どのケースにも一致しない場合の処理
	}
}



class integlight_customizer_slider_outerAssets
{

	private $pInteglight_slider_settings;


	public function __construct($slider_settings)
	{
		$this->pInteglight_slider_settings = $slider_settings;
		add_action('wp_enqueue_scripts', array($this, 'provideTOjs'));

		$styles = [
			'integlight-slide' => '/css/build/integlight-slide-style.css',
		];
		InteglightFrontendStyles::add_styles($styles);

		$scripts = [
			'integlight_slider-script' =>  ['path' => '/js/build/slider.js', 'deps' => ['jquery']],
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
			'displayChoice' => get_theme_mod('integlight_display_choice'),
			'changeDuration' => get_theme_mod('integlight_slider_change_duration', 3),
			'effect' => get_theme_mod('integlight_slider_effect', $this->pInteglight_slider_settings->effectName_fade),
			'homeType' => get_theme_mod('integlight_hometype_setting', $this->pInteglight_slider_settings->homeType1),
			'fade' => $this->pInteglight_slider_settings->effectName_fade,
			'slide' => $this->pInteglight_slider_settings->effectName_slide,
			'home1' => $this->pInteglight_slider_settings->homeType1,
			'home2' => $this->pInteglight_slider_settings->homeType2,
			'home3' => $this->pInteglight_slider_settings->homeType3,
			'home4' => $this->pInteglight_slider_settings->homeType4,

			'headerTypeNameSlider' => $this->pInteglight_slider_settings->headerTypeName_slider
		));
	}
}







/*カスタマイザーで設定したスライダー機能をフロントでオープンしたときにロード*/
add_action('template_redirect', function () {
	if (is_home() || is_front_page()) {
		global $Integlight_slider_settings;
		if ($Integlight_slider_settings->headerTypeName_slider === get_theme_mod('integlight_display_choice', 'none')) {
			new integlight_customizer_slider_outerAssets($Integlight_slider_settings);
		}
	}
});
