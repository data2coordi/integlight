// playwright.config.init.js

import { defineConfig, devices } from "@playwright/test";

export default defineConfig({
  // グローバルタイムアウト
  timeout: 60_000,

  // ⭐ デバッグ対象のプロジェクトを直接定義
  projects: [
    {
      name: `setting_init_elegant_slider`, // ターゲットプロジェクト名
      testDir: "./tests",
      testMatch: /visual\.setting\.spec\.js/,

      // 動画出力のための設定を強制
      use: {
        baseURL: "https://t2.auroralab-design.com",
        ...devices["Desktop Chrome"],
        video: {
          mode: "on",
          retainOnFailure: true, // 成功・失敗に関わらず動画を残す
        },
      },
    },
  ],
});
