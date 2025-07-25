import { defineConfig } from '@playwright/test';

export default defineConfig({
    use: {
        video: 'on',  // テスト開始から終了まで常に動画を録画する
        // video: 'retain-on-failure', // 失敗時のみ保存したい場合はこちら
    },
});