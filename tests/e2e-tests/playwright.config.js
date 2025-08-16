import { defineConfig } from '@playwright/test';

export default defineConfig({
    timeout: 120_000, // 各テストのデフォルトタイムアウト（ms）
    use: {
        video: 'on',  // テスト開始から終了まで常に動画を録画する
        actionTimeout: 3_000, // click や fill など1アクションのタイムアウト
        // video: 'retain-on-failure', // 失敗時のみ保存したい場合はこちら
    },
});