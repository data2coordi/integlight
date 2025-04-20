<?php // tests/unit-tests/integlight_customizer_sliderTest.php

declare(strict_types=1);

// テスト対象クラスと依存クラスを読み込む (オートロードされていない場合)

/**
 * integlight_customizer_slider クラスのユニットテスト
 *
 * @covers integlight_customizer_slider
 * @group customizer
 */
class integlight_customizer_sliderTest extends WP_UnitTestCase
{
	/**
	 * 各テストの後にグローバル変数をクリーンアップします。
	 */
	public function tearDown(): void
	{
		// テスト中に設定されたグローバル変数を削除
		unset($GLOBALS['Integlight_slider_settings']);

		// 注意: 依存クラスによって追加されたアクションフックも必要に応じて削除する必要がある場合がありますが、
		// このテストクラスの範囲を超える可能性があります。
		// 例: remove_action(...)

		parent::tearDown();
	}

	/**
	 * @test
	 * コンストラクタがグローバル設定変数を正しく初期化することをテストします。
	 */
	/**
	 * @test
	 * コンストラクタがグローバル設定変数を正しく初期化することをテストします。
	 */
	public function constructor_should_initialize_global_settings(): void
	{
		// クラスをインスタンス化（コンストラクタが実行される）
		new integlight_customizer_slider();

		// グローバル変数が存在し、オブジェクトであることを確認
		$this->assertArrayHasKey('Integlight_slider_settings', $GLOBALS, 'Global variable $Integlight_slider_settings should exist.');
		$this->assertIsObject($GLOBALS['Integlight_slider_settings'], 'Global variable $Integlight_slider_settings should be an object.');

		// グローバルオブジェクトのプロパティが期待通りか確認
		$settings = $GLOBALS['Integlight_slider_settings'];

		// assertObjectHasAttribute を assertObjectHasProperty に変更
		$this->assertObjectHasProperty('effectName_fade', $settings);
		$this->assertSame('fade', $settings->effectName_fade, 'Property effectName_fade should be "fade".');

		// assertObjectHasAttribute を assertObjectHasProperty に変更
		$this->assertObjectHasProperty('effectName_slide', $settings);
		$this->assertSame('slide', $settings->effectName_slide, 'Property effectName_slide should be "slide".');

		// assertObjectHasAttribute を assertObjectHasProperty に変更
		$this->assertObjectHasProperty('headerTypeName_slider', $settings);
		$this->assertSame('slider', $settings->headerTypeName_slider, 'Property headerTypeName_slider should be "slider".');

		// assertObjectHasAttribute を assertObjectHasProperty に変更
		$this->assertObjectHasProperty('headerTypeName_image', $settings);
		$this->assertSame('image', $settings->headerTypeName_image, 'Property headerTypeName_image should be "image".');
	}
	/**
	 * @test
	 * コンストラクタが依存クラスをインスタンス化し、
	 * それによって関連するアクションフックが登録されることを（間接的に）テストします。
	 * これは統合テストに近い側面を持ちます。
	 */
	public function constructor_should_trigger_dependency_hook_registration(): void
	{
		// クラスをインスタンス化
		new integlight_customizer_slider();

		// 依存クラスが追加するであろう主要なアクションフックが存在するかどうかを確認
		// 注意: どのクラスがどのフックを追加するかを把握している必要があります。
		//       また、コールバックが特定のオブジェクトメソッドであることまでは、
		//       インスタンスを取得しない限り通常は確認しません。

		// integlight_customizer_slider_creSection などが追加するフック
		$this->assertTrue(has_action('customize_register') > 0, 'customize_register action should be hooked.');

		// integlight_customizer_slider_applyHeaderTextStyle が追加するフック
		$this->assertTrue(has_action('wp_head') > 0, 'wp_head action should be hooked.');

		// integlight_customizer_slider_outerAssets などが追加するフック
		$this->assertTrue(has_action('wp_enqueue_scripts') > 0, 'wp_enqueue_scripts action should be hooked.');
	}
}
