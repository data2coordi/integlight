// tests/visual.setting.spec.js

import { test, expect } from "@playwright/test";
// Customizer_manager は一旦削除せず残しておきますが、認証なしでは機能しません
// import { Customizer_manager } from "../utils/customizer";

// 🚨 Playwright Test Runnerは、Configファイルから認証情報（storageState）を自動でロードします。
//    テストコードから認証ファイルパスの定義を削除します。
// const authFile = "playwright/.auth/user.json";

test.describe("動画デバッグ用テスト", () => {
  // pageフィクスチャを使用することで、Configファイルの設定（baseURL, video, storageState）が適用されます。
  test("標準のページ遷移と待機", async ({ page, browser }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);

    // 1. Configファイルから設定されたbaseURLへ移動
    //    Configファイルにvideo設定があるため、ここで自動的に録画が開始されているはず
    const page = await browser.newPage();
    await page.goto("/", { waitUntil: "domcontentloaded" });

    // 2. 録画時間を稼ぐための待機と簡単なアサーション（デバッグ用）
    await expect(page).toHaveURL(/.*/); // URLが何かあればOK

    // 3. 動画ファイルパスをログに出力（自動保存の確認用）
    const video = page.video();
    if (video) {
      console.log(
        `[DEBUG] Video is active. Path (if saved): ${await video
          .path()
          .catch((e) => "Path unavailable")}`
      );
    } else {
      console.log(`[DEBUG] Video object is NOT available via page.video().`);
    }

    // 🚨 Configファイル設定に基づき、テスト終了時にPlaywrightが自動で動画を保存するはずです。

    // ページやコンテキストの手動 close は不要（フィクスチャが自動で処理）
    // await page.close();
    // await context.close();
  });
});
