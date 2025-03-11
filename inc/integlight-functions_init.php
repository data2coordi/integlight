<?php

/**
 * Integlight functions and definitions
 * テーマ導入時の初期設定処理
 *
 * @package Integlight
 */

//ロゴの設定
class integlight_initSampleSetup_logo
{
	public function __construct()
	{

		add_action('after_setup_theme', [$this, 'integlight_initSampleSetup_logo']);
	}

	public function integlight_initSampleSetup_logo()
	{

		if (get_option('integlight_logo_setup_done')) {
			return;
		}

		// すでにロゴが設定されている場合は何もしない
		if (get_theme_mod('custom_logo')) {
			return;
		}

		// サンプルのロゴ画像をテーマディレクトリ内に配置
		$logo_path = get_template_directory() . '/img/samplelogo_white.png';

		// WordPressメディアライブラリに画像を登録
		$upload_dir = wp_upload_dir();

		if (!file_exists($upload_dir['path'] . '/samplelogo_white.png')) {
			copy($logo_path, $upload_dir['path'] . '/samplelogo_white.png');
		}


		$logo_url = $upload_dir['url'] . '/samplelogo_white.png';
		// メディアライブラリに登録
		$attachment = array(
			'guid'           => $logo_url,
			'post_mime_type' => 'image/png',
			'post_title'     => 'Sample Logo TEST',
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id = wp_insert_attachment($attachment, $upload_dir['path'] . '/samplelogo_white.png');

		// 画像のメタデータを生成
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attach_data = wp_generate_attachment_metadata($attach_id, $upload_dir['path'] . '/samplelogo_white.png');
		wp_update_attachment_metadata($attach_id, $attach_data);

		// テーマカスタマイザーの `custom_logo` オプションを更新
		set_theme_mod('custom_logo', $attach_id);

		//一度実行したらフラグをセット
		update_option('integlight_logo_setup_done', true);
	}
}

new integlight_initSampleSetup_logo();
