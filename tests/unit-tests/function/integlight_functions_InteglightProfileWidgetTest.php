<?php

/**
 * Tests for the Integlight_Profile_Widget class.
 *
 * @package Integlight
 * @group profile-widget
 */

class integlight_functions_InteglightProfileWidgetTest extends WP_UnitTestCase
{
    /**
     * テスト用ユーザーID
     * @var int
     */
    private $user_id;

    /**
     * URLが空のユーザーID
     * @var int
     */
    private $user_with_empty_url;

    /**
     * display_nameにHTMLタグが含まれるユーザーID
     * @var int
     */
    private $user_with_html_in_name;

    /**
     * ウィジェットインスタンス
     * @var Integlight_Profile_Widget
     */
    private $widget;

    /**
     * Set up the test environment.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user_id = self::factory()->user->create([
            'role'         => 'author',
            'display_name' => 'テスト太郎',
            'user_url'     => 'https://example.com',
            'description'  => "自己紹介１行目\n自己紹介２行目",
        ]);

        $this->user_with_empty_url = self::factory()->user->create([
            'role'         => 'author',
            'display_name' => 'No URL User',
            'user_url'     => '',
            'description'  => '説明文',
        ]);

        $this->user_with_html_in_name = self::factory()->user->create([
            'role'         => 'author',
            'display_name' => '<b>悪意ある名前</b>',
            'user_url'     => 'https://example.org',
            'description'  => '説明文',
        ]);

        $this->widget = new Integlight_Profile_Widget();
    }

    /**
     * Tear down the test environment.
     */
    public function tearDown(): void
    {
        wp_delete_user($this->user_id);
        wp_delete_user($this->user_with_empty_url);
        wp_delete_user($this->user_with_html_in_name);
        parent::tearDown();
    }

    /**
     * Test that widget output includes display_name, description and URL
     * when a valid user_id is provided.
     * 
     * @covers Integlight_Profile_Widget::widget
     * BB-2 有効なユーザーIDを指定 実在ユーザーのID display_name・description・URL・アバターが含まれたHTMLが出力される
     * BB-3 description に改行が含まれる 実在ユーザー（説明あり） <br>に変換されて表示される
     */
    public function test_widget_outputs_valid_profile_info()
    {
        $args = [
            'before_widget' => '<div class="widget">',
            'after_widget'  => '</div>',
        ];
        $instance = [
            'user_id' => $this->user_id,
        ];

        ob_start();
        $this->widget->widget($args, $instance);
        $output = ob_get_clean();

        $this->assertStringContainsString('<div class="integlight-author-profile-widget">', $output);
        $this->assertStringContainsString('テスト太郎', $output);
        $this->assertStringContainsString('https://example.com', $output);
        $this->assertStringContainsString('自己紹介１行目', $output);
        $this->assertStringContainsString('<br />', $output);
        $this->assertStringContainsString('<img', $output);
        $this->assertMatchesRegularExpression('/<img[^>]+src=["\'].*["\']/', $output);
    }

    /*
     * BB-1 ユーザーIDが未指定 ''または0 ウィジェットは出力されない
     */
    public function test_widget_outputs_nothing_when_user_id_is_empty()
    {
        $args = [
            'before_widget' => '<div class="widget">',
            'after_widget'  => '</div>',
        ];
        $instance = [
            // user_id を設定しない（未指定）
        ];

        ob_start();
        $this->widget->widget($args, $instance);
        $output = ob_get_clean();

        // HTML出力が空であることを確認
        $this->assertSame('', trim($output));
    }

    /**
     * BB-4 URLが空のユーザーでリンクのhrefが空かどうか検証
     * BB-5 display_nameにHTMLタグが含まれる場合、タグが除去されて表示されるか検証
     *
     * @covers Integlight_Profile_Widget::widget
     */
    public function test_widget_with_empty_url_and_html_in_display_name()
    {
        $args = [
            'before_widget' => '<div class="widget">',
            'after_widget'  => '</div>',
        ];

        // BB-4 URLが空のユーザー
        $instance_empty_url = ['user_id' => $this->user_with_empty_url];
        ob_start();
        $this->widget->widget($args, $instance_empty_url);
        $output_empty_url = ob_get_clean();

        $this->assertStringContainsString('<a href=""', $output_empty_url);
        $this->assertStringContainsString('No URL User', $output_empty_url);

        // BB-5 display_nameにHTMLタグが含まれるユーザー
        $instance_html_name = ['user_id' => $this->user_with_html_in_name];
        ob_start();
        $this->widget->widget($args, $instance_html_name);
        $output_html_name = ob_get_clean();

        $this->assertStringNotContainsString('<b>', $output_html_name);
        $this->assertStringNotContainsString('</b>', $output_html_name);
        $this->assertStringContainsString('悪意ある名前', $output_html_name);
    }

    /**
     * BB-6 ユーザーが複数人 例：3人のユーザーが存在 全ユーザーの display_name が選択肢に表示される
     *
     * @covers Integlight_Profile_Widget::form
     */
    public function test_form_shows_user_in_options()
    {
        $instance = ['user_id' => $this->user_id];

        ob_start();
        $this->widget->form($instance);
        $output = ob_get_clean();

        $this->assertStringContainsString('<select', $output);
        $this->assertStringContainsString('テスト太郎', $output);

        // 空欄の選択肢があること
        $this->assertStringContainsString('-- Please select a user --', $output);

        // 選択済みオプションがあること
        $this->assertMatchesRegularExpression("/selected=['\"]selected['\"]/", $output);
    }

    /**
     * @test
     * BB-7 設定済みユーザーがセレクト済み：ユーザーIDが設定されていると、その選択肢だけが selected 状態になる
     */
    public function test_form_selected_option_reflects_saved_user_id()
    {
        // テスト用ユーザー作成
        $selected_user = self::factory()->user->create([
            'display_name' => 'セレクト対象ユーザー',
        ]);

        // 別の選択肢となるユーザーも用意
        $other_user = self::factory()->user->create([
            'display_name' => '他のユーザー',
        ]);

        $instance = ['user_id' => $selected_user]; // 選択済みIDを設定

        ob_start();
        $this->widget->form($instance);
        $output = ob_get_clean();

        // 選択済みユーザーだけが selected
        $this->assertMatchesRegularExpression(
            '/<option value="' . $selected_user . '" selected=[\'"]selected[\'"]>.*?<\/option>/',
            $output,
            '設定されたユーザーIDが selected 状態で表示されるべき'
        );

        // 他のユーザーは selected でないことも確認しておくと堅実
        $this->assertDoesNotMatchRegularExpression(
            '/<option value="' . $other_user . '" selected=[\'"]selected[\'"]>/',
            $output,
            '他のユーザーが誤って selected 状態になっていないこと'
        );
    }
    /**
     * @test
     * BB-8〜BB-10 ユーザーIDの保存処理：正常値、空、無効値の入力パターンを確認
     */
    public function test_update_user_id_variations()
    {
        // BB-8: 正常なユーザーID（数字の文字列）
        $result = $this->widget->update(['user_id' => '3'], []);
        $this->assertSame(3, $result['user_id'], '数字文字列 "3" は整数 3 にキャストされて保存される');

        // BB-9: 空文字
        $result = $this->widget->update(['user_id' => ''], []);
        $this->assertSame(0, $result['user_id'], '空文字は 0 にキャストされて保存される');

        // BB-10: 無効な文字列（数字以外）
        $result = $this->widget->update(['user_id' => 'abc'], []);
        $this->assertSame(0, $result['user_id'], '無効な文字列 "abc" は 0 にキャストされて保存される');
    }
}


/*
【1】ウィジェット出力 (widget())
番号 テスト内容 入力（user_id） 期待される出力
BB-1 ユーザーIDが未指定 ''または0 ウィジェットは出力されない
BB-2 有効なユーザーIDを指定 実在ユーザーのID display_name・description・URL・アバターが含まれたHTMLが出力される
BB-3 description に改行が含まれる 実在ユーザー（説明あり） <br>に変換されて表示される
BB-4 ユーザーURLが空 実在ユーザー（user_url未設定） 名前リンクがhref=""になるか表示されない（仕様どおり）
BB-5 display_name にHTMLタグを含む <b>悪意ある名前</b> HTMLが除去されて表示される（XSS対策）

【2】設定フォーム表示 (form())
番号 テスト内容 状態 期待される出力
BB-6 ユーザーが複数人 例：3人のユーザーが存在 全ユーザーの display_name が選択肢に表示される
BB-7 設定済みユーザーがセレクト済み ユーザーIDが設定されている 対応する選択肢が selected 状態になる

【3】設定保存処理 (update())
番号 テスト内容 入力 保存される値（出力）
BB-8 正常なユーザーIDを送信 3 3 が保存される
BB-9 空の値を送信 '' 0 が保存される
BB-10 数字でない値を送信 'abc' 0 にキャストされて保存される
*/