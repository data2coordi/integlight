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
        actionTimeout: 3_000,
        // 動画録画設定
        video: 'on',
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

        // 2. 本テストを実行するプロジェクト
        {
            name: 'main',
            testDir: './tests', // テストファイルのディレクトリを指定
            dependencies: ['setup'], // setupプロジェクトの完了を待機
            use: {
                ...devices['Desktop Chrome'], // デスクトップChromeを使用
                // 保存した認証状態をロード
                storageState: authFile,
                // テストごとの動画録画設定を上書きすることも可能
            },
        },
    ],
});