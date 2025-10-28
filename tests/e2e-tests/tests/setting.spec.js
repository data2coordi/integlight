import { test } from "@playwright/test";
import {
  openCustomizer,
  saveCustomizer,
  setSiteType,
  selSliderEffect,
  ensureCustomizerRoot,
  openHeaderSetting,
} from "../utils/common";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ browser }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);
    const { testid, siteType, headerType } = testInfo.project.use; // ✅ ここで取得
    console.log(
      `Test ID: ${testid}, Site Type: ${siteType}, Header Type: ${headerType}`
    );
    const page = await browser.newPage();
    await openCustomizer(page);
    await setSiteType(page, siteType);
    if (headerType === "スライダー") {
      await ensureCustomizerRoot(page);
      await selSliderEffect(page, "スライド", "60"); // スライダーエフェクトを「スライド」、変更時間間隔を3秒に設定
    }
    await ensureCustomizerRoot(page);
    await openHeaderSetting(page, headerType);
    await saveCustomizer(page);

    await page.close();
  });
});
