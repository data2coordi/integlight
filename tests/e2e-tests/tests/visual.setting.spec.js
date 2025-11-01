import { test, expect } from "@playwright/test";
import { Customizer_manager } from "../utils/customizer";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ browser }, testInfo) => {
    // 1. Playwrightの設定を無視し、コンテキスト作成時に動画設定を強制的に適用
    const context = await browser.newContext({
      storageState: authFile, // 認証情報を手動で再指定 (必須)
      video: {
        // 動画設定を直接適用 (必須)
        mode: "on",
        retainOnFailure: true,
      },
      // ... (他のuse:設定があればここに記述)
    });

    const page = await context.newPage();

    // 2. 動画が起動されているかチェックし、起動させる（前回提案のロジック）
    const video = page.video();
    // ... 動画初期化と操作のロジック ...

    const cm_manager = new Customizer_manager(page);
    await cm_manager.apply(keyValue);

    // 3. 動画の手動保存（必須）
    if (video) {
      // ... 手動保存ロジック ...
      await video.saveAs(
        `test-results/${testInfo.project.name}/${testInfo.title}_manual.webm`
      );
    }

    await page.close();
    await context.close(); // コンテキストを閉じることで、動画の書き込みをトリガー
  });
});
