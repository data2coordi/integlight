<?php

/**
 * Integlight functions and definitions
 * テーマ導入時の初期設定処理
 *
 * @package Integlight
 */


class Integlight_initSampleSetup
{
	public function __construct()
	{

		add_action('after_setup_theme', [$this, 'integlight_initSampleSetup']);
		//add_action('after_switch_theme', [$this, 'integlight_initSampleSetup']);
	}


	private function initSlider()
	{
		// カスタマイザーの設定名
		set_theme_mod('integlight_display_choice', 'slider');

		set_theme_mod('integlight_slider_effect', 'fade');
		set_theme_mod('integlight_slider_change_duration', 3);

		set_theme_mod('integlight_slider_text_1', 'Integlightであなたの経験・知識をデジタル資産に');
		set_theme_mod('integlight_slider_text_2', 'あなたが日々何気なく話していることや、仕事や趣味で得た知識・経験は、誰かにとって価値ある情報です。ブログに記録することで、それは時間とともに蓄積され、あなたの「デジタル資産」となります。発信を続けることで、多くの人に届く価値を生み出してみませんか？');
		set_theme_mod('integlight_slider_text_font', 'yu_gothic');
		set_theme_mod('integlight_slider_text_top', 100);
		set_theme_mod('integlight_slider_text_left', 200);

		$default_image_url = get_template_directory_uri() . '/img/sample_slider_pc_01.webp';
		set_theme_mod('integlight_slider_image_1', $default_image_url);

		$default_image_url = get_template_directory_uri() . '/img/sample_slider_pc_02.webp';
		set_theme_mod('integlight_slider_image_2', $default_image_url);
	}

	private function initLogo()
	{

		// すでにロゴが設定されている場合は何もしない
		if (get_theme_mod('custom_logo')) {
			return;
		}

		// サンプルのロゴ画像をテーマディレクトリ内に配置
		$logoFilename = '/samplelogo_white.png';
		$logo_path = get_template_directory() . '/img' . $logoFilename;

		// WordPressメディアライブラリに画像を登録
		$upload_dir = wp_upload_dir();

		if (!file_exists($upload_dir['path'] . $logoFilename)) {
			copy($logo_path, $upload_dir['path'] . $logoFilename);
		}


		$logo_url = $upload_dir['url'] . $logoFilename;
		// メディアライブラリに登録
		$attachment = array(
			'guid'           => $logo_url,
			'post_mime_type' => 'image/png',
			'post_title'     => 'Sample Logo TEST',
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id = wp_insert_attachment($attachment, $upload_dir['path'] . $logoFilename);

		// 画像のメタデータを生成
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $upload_dir['path'] . $logoFilename);
		wp_update_attachment_metadata($attach_id, $attach_data);

		// テーマカスタマイザーの `custom_logo` オプションを更新
		set_theme_mod('custom_logo', $attach_id);
	}

	public function integlight_initSampleSetup()
	{
		//var_dump('test0');
		if (get_option('integlight_initSetup_done')) {
			//var_dump('test1');
			return;
		}
		//var_dump('test2');
		$this->initLogo();
		$this->initSlider();

		//一度実行したらフラグをセット
		update_option('integlight_initSetup_done', true);
	}
}

new Integlight_initSampleSetup();
