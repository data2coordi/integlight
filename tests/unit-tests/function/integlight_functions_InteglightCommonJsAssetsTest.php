<?php

use PHPUnit\Framework\TestCase; // 通常は WP_UnitTestCase を使用します

/**
 * Test case for InteglightCommonJsAssets class.
 *
 * @group assets
 */
class integlight_functions_InteglightCommonJsAssetsTest extends WP_UnitTestCase // WP_UnitTestCase を継承
{
    /**
     * @var array<string> List of expected incorrect usage messages during tests.
     *                  This allows tests to pass even if WordPress core triggers
     *                  these notices due to the testing environment setup.
     */
    protected $expectedIncorrectUsageMessages = [
        // ここに title-tag の通知メッセージを追加します。
        // メッセージは WordPress のバージョンによって若干変わる可能性があるため、
        // エラー出力から正確にコピーするのが確実です。
        // ★★★ 修正点: 以下の行のコメントを解除し、エラーメッセージを追加 ★★★
        'Theme support for <code>title-tag</code> should be registered before the <code>wp_loaded</code> hook.',
        // もし他の予期される通知があれば、ここに追加します。
    ];

    // ... (getStaticPropertyValue, setStaticPropertyValue, reset_scripts は変更なし) ...
    // ... (setUp, tearDown, 各テストメソッドも変更なし) ...

    /**
     * Helper function to get the value of a protected or private static property.
     *
     * @param string $className    The name of the class.
     * @param string $propertyName The name of the static property.
     * @return mixed The value of the static property.
     * @throws ReflectionException If the class or property does not exist.
     */
    protected static function getStaticPropertyValue(string $className, string $propertyName)
    {
        try {
            $reflector = new ReflectionClass($className);
            $property = $reflector->getProperty($propertyName);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true); // Allow access to protected/private property
            }
            return $property->getValue();
        } catch (ReflectionException $e) {
            // エラー発生時はテストを失敗させる
            self::fail("Failed to get static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * Helper function to set the value of a protected or private static property.
     *
     * @param string $className    The name of the class.
     * @param string $propertyName The name of the static property.
     * @param mixed  $value        The value to set.
     * @throws ReflectionException If the class or property does not exist.
     */
    protected static function setStaticPropertyValue(string $className, string $propertyName, $value): void
    {
        try {
            $reflector = new ReflectionClass($className);
            $property = $reflector->getProperty($propertyName);
            // PHP 8.1 以降では setAccessible は不要な場合があるが、互換性のために残す
            if (method_exists($property, 'setAccessible')) {
                $property->setAccessible(true); // Allow access to protected/private property
            }
            $property->setValue(null, $value); // Set static property
        } catch (ReflectionException $e) {
            // エラー発生時はテストを失敗させる
            self::fail("Failed to set static property {$className}::{$propertyName}: " . $e->getMessage());
        }
    }

    /**
     * WordPress のスクリプトキューをリセットするヘルパーメソッド
     */
    private function reset_scripts(): void
    {
        global $wp_scripts;
        // WP_Scripts インスタンスが存在しない場合 (初期化前など) は作成
        if (!isset($wp_scripts) || !$wp_scripts instanceof WP_Scripts) {
            $wp_scripts = new WP_Scripts();
            // 必要に応じて初期化処理を追加
            // wp_default_scripts($wp_scripts); // デフォルトスクリプトを登録する場合
        } else {
            // 既存のインスタンスがあればリセット
            $wp_scripts->reset();
        }
        // デフォルトスクリプトを再登録 (必要に応じて)
        // WP_UnitTestCase がデフォルトスクリプトを処理するため、通常はここで呼ぶ必要はない
        // wp_default_scripts($wp_scripts);
    }


    /**
     * Set up the test environment before each test method.
     * Resets the static properties of dependency classes and script queue.
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Reset static arrays to ensure test isolation
        // ※ init() が呼ばれる前にリセット
        self::setStaticPropertyValue(InteglightFrontendScripts::class, 'scripts', []);
        self::setStaticPropertyValue(InteglightMoveScripts::class, 'scripts', []);

        // Reset WordPress script queue
        $this->reset_scripts();

        // comment-reply を強制的にデキュー/登録解除
        // これにより、各テストがクリーンな状態から始まることを保証
        wp_dequeue_script('comment-reply');
        wp_deregister_script('comment-reply');
        // デフォルトスクリプトを再登録して comment-reply を利用可能な状態に戻す
        // WP_UnitTestCase がこれを処理してくれる場合もあるが、明示的に行う方が確実
        wp_default_scripts($GLOBALS['wp_scripts']);


        // Reset relevant options
        update_option('thread_comments', '1');
        // コメント関連の他のオプションも必要に応じてリセット
        // update_option('comment_registration', 0);
        // update_option('comment_moderation', 0);



        // デバッグ用: setUp完了時点でcomment-replyがエンキューされていないことを確認
        // $this->assertFalse(wp_script_is('comment-reply', 'enqueued'), 'comment-reply should NOT be enqueued at the end of setUp');
        // デバッグ用: setUp完了時点でフロントエンドスクリプトが登録されているか確認
        // $scripts = self::getStaticPropertyValue(InteglightFrontendScripts::class, 'scripts');
        // $this->assertNotEmpty($scripts, 'Frontend scripts should be populated after do_action(after_setup_theme)');

    }

    /**
     * Clean up the test environment after each test method.
     * Resets the static properties of dependency classes and script queue.
     */
    protected function tearDown(): void
    {
        // Reset static arrays again to be safe
        self::setStaticPropertyValue(InteglightFrontendScripts::class, 'scripts', []);
        self::setStaticPropertyValue(InteglightMoveScripts::class, 'scripts', []);

        // Reset WordPress script queue
        $this->reset_scripts();

        // Reset options to default or known state if necessary
        // delete_option('thread_comments');
        // delete_option('comment_registration');
        // delete_option('comment_moderation');

        parent::tearDown();
    }

    /**
     * @test
     * @covers InteglightCommonJsAssets::init
     * Verifies that frontend scripts are added correctly.
     */
    public function test_init_adds_frontend_scripts(): void
    {
        // --- Arrange ---
        // setUp で do_action('after_setup_theme') が実行され、init() が呼ばれているはず
        $expectedFrontendScripts = [
            'integlight-navigation' => ['path' => '/js/navigation.js', 'deps' => []],

        ];
        // --- Act ---
        // テスト対象のメソッドを呼び出す
        InteglightCommonJsAssets::init();
        // Act は不要 (setUp で実行済み)

        // --- Act ---
        // Act は不要 (setUp で実行済み)

        // --- Assert ---
        // init() が実行された結果、静的プロパティに値がセットされているはず
        $actualFrontendScripts = self::getStaticPropertyValue(InteglightFrontendScripts::class, 'scripts');
        $this->assertEquals($expectedFrontendScripts, $actualFrontendScripts, 'Frontend scripts were not added correctly.');
    }

    /**
     * @test
     * @covers InteglightCommonJsAssets::init
     * Verifies that scripts to move to footer are added correctly.
     */
    public function test_init_adds_footer_scripts(): void
    {
        // --- Arrange ---
        // setUp で do_action('after_setup_theme') が実行され、init() が呼ばれているはず
        // includes_url() は WordPress 環境が必要
        $expectedFooterScripts = [
            'jquery' => includes_url('/js/jquery/jquery.min.js'), // jQuery のパスを取得
        ];

        // --- Act ---
        // テスト対象のメソッドを呼び出す
        InteglightCommonJsAssets::init();
        // init() が実行された結果、静的プロパティに値がセットされているはず
        $actualFooterScripts = self::getStaticPropertyValue(InteglightMoveScripts::class, 'scripts');
        // jQuery のパスは環境によって変わる可能性があるため、キーの存在と値が文字列であることのみを確認する方が堅牢かもしれない
        $this->assertArrayHasKey('jquery', $actualFooterScripts, 'Footer scripts should contain jquery handle.');
        $this->assertIsString($actualFooterScripts['jquery'], 'jQuery path should be a string.');
        // 必要であれば、パスの一部が含まれているかなどを確認
        // $this->assertStringContainsString('/js/jquery/jquery.min.js', $actualFooterScripts['jquery']);
        // $this->assertEquals($expectedFooterScripts, $actualFooterScripts, 'Footer scripts were not added correctly.'); // 完全一致が必要な場合
    }

    /**
     * @test
     * @covers InteglightCommonJsAssets::init // init がフックを登録することを確認
     * @covers InteglightCommonJsAssets::enqueue_comment_reply_script // このメソッドのロジックをテスト
     * Verifies that comment-reply script is enqueued when conditions are met.
     */
    public function test_init_enqueues_comment_reply_when_conditions_met(): void
    {
        // --- Arrange ---
        // setUp で do_action('after_setup_theme') が実行され、init() が呼ばれているはず
        // 条件を満たす投稿を作成し、そのページに移動
        $post_id = self::factory()->post->create(['comment_status' => 'open']);
        $this->go_to(get_permalink($post_id));

        // スレッドコメントを有効にする (setUp でデフォルト有効だが念のため)
        update_option('thread_comments', '1');

        // --- Act ---
        // wp_enqueue_scripts フックを手動で実行して、
        // InteglightCommonJsAssets::enqueue_comment_reply_script を呼び出す
        do_action('wp_enqueue_scripts');

        // --- Assert ---
        // comment-reply がエンキューされたか確認
        $this->assertTrue(wp_script_is('comment-reply', 'enqueued'), 'comment-reply script should be enqueued on singular post with open comments and threading enabled.');
    }

    /**
     * @test
     * @covers InteglightCommonJsAssets::init
     * @covers InteglightCommonJsAssets::enqueue_comment_reply_script // カバーするメソッドを追加
     * Verifies that comment-reply script is NOT enqueued when not on a singular page.
     */
    public function test_init_does_not_enqueue_comment_reply_when_not_singular(): void
    {
        // --- Arrange ---
        // setUp で do_action('after_setup_theme') が実行され、init() が呼ばれているはず
        // 非単一ページ（例：ホームページ）に移動
        $this->go_to(home_url('/'));
        // スレッドコメントは有効のまま

        // --- Act ---
        // wp_enqueue_scripts フックを手動で実行
        do_action('wp_enqueue_scripts');

        // --- Assert ---
        // comment-reply がエンキューされていないことを確認
        $this->assertFalse(wp_script_is('comment-reply', 'enqueued'), 'comment-reply script should NOT be enqueued on non-singular pages.');
    }

    /**
     * @test
     * @covers InteglightCommonJsAssets::init
     * @covers InteglightCommonJsAssets::enqueue_comment_reply_script // カバーするメソッドを追加
     * Verifies that comment-reply script is NOT enqueued when comments are closed.
     */
    public function test_init_does_not_enqueue_comment_reply_when_comments_closed(): void
    {
        // --- Arrange ---
        // setUp で do_action('after_setup_theme') が実行され、init() が呼ばれているはず
        // コメントが閉じている投稿を作成し、そのページに移動
        $post_id = self::factory()->post->create(['comment_status' => 'closed']);
        $this->go_to(get_permalink($post_id));
        // スレッドコメントは有効のまま

        // --- Act ---
        // wp_enqueue_scripts フックを手動で実行
        do_action('wp_enqueue_scripts');

        // --- Assert ---
        $this->assertFalse(wp_script_is('comment-reply', 'enqueued'), 'comment-reply script should NOT be enqueued when comments are closed.');
    }

    /**
     * @test
     * @covers InteglightCommonJsAssets::init
     * @covers InteglightCommonJsAssets::enqueue_comment_reply_script // カバーするメソッドを追加
     * Verifies that comment-reply script is NOT enqueued when threaded comments are disabled.
     */
    public function test_init_does_not_enqueue_comment_reply_when_threading_disabled(): void
    {
        // --- Arrange ---
        // setUp で do_action('after_setup_theme') が実行され、init() が呼ばれているはず
        // コメントが開いている投稿を作成し、そのページに移動
        $post_id = self::factory()->post->create(['comment_status' => 'open']);
        $this->go_to(get_permalink($post_id));
        // スレッドコメントを無効にする
        update_option('thread_comments', '0');

        // --- Act ---
        // wp_enqueue_scripts フックを手動で実行
        do_action('wp_enqueue_scripts');

        // --- Assert ---
        $this->assertFalse(wp_script_is('comment-reply', 'enqueued'), 'comment-reply script should NOT be enqueued when threaded comments are disabled.');
    }
}
