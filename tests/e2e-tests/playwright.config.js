import { defineConfig, devices } from '@playwright/test';

// 環境によってURLや認証ファイルパスが変わるため、定数として定義
const BASE_URL = 'https://wpdev.toshidayurika.com';
const authFile = 'playwright/.auth/user.json';

export default defineConfig({
    // 各テストのデフォルトタイムアウト（ms）
    timeout: 60_000,

    // プロジェクト間で共有される設定
    use: {
        // click や fill など1アクションのタイムアウト
        actionTimeout: 10_000,
        // 動画録画設定
        video: 'off',
        // ブラウザのベースURL
        baseURL: BASE_URL,
    },

    // 複数のテストプロジェクトを定義
    projects: [
        // 1. 認証処理を実行するプロジェクト
        {
            name: 'setup',
            testMatch: 'auth.setup.ts',
        },

        // 2. 【新規】認証が不要なテスト用のプロジェクト
        {
            name: 'unauthenticated',
            testMatch: [/menu\.spec\.js/, /pf\.image\.post\.spec\.ts/], // 認証不要なテストファイルを指定
            use: {
                ...devices['Desktop Chrome'],
                // storageState を使わないので、ログイン状態にはならない
            },
        },

        // 3. 【変更】認証が必要な本テストを実行するプロジェクト
        {
            name: 'main',
            testDir: './tests', // テストファイルのディレクトリを指定
            testIgnore: [/auth\.setup\.ts/, /menu\.spec\.js/, /pf\.image\.post\.spec\.ts/, /visual\.spec\.js/], // setupと認証不要テストを除外
            dependencies: ['setup'], // setupプロジェクトの完了を待機
            use: {
                ...devices['Desktop Chrome'], // デスクトップChromeを使用
                // 保存した認証状態をロード
                storageState: authFile,
            },
        },
        {
            name: 'visual',
            testDir: './tests', // テストファイルのディレクトリを指定
            testMatch: [/visual\.spec\.js/],
            dependencies: ['setup'], // setupプロジェクトの完了を待機
            use: {
                ...devices['Desktop Chrome'], // デスクトップChromeを使用
                // 保存した認証状態をロード
                storageState: authFile,
            },
        },
    ],
});