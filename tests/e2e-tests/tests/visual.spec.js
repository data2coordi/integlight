import { test } from "@playwright/test";
import {
  openCustomizer,
  saveCustomizer,
  setSiteType,
  selSliderEffect,
  ensureCustomizerRoot,
} from "../utils/common";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test.beforeAll(async ({ browser }, testInfo) => {
    const { siteType } = testInfo.project.use; // ✅ ここで取得
    const page = await browser.newPage();
    await openCustomizer(page);
    await setSiteType(page, siteType);
    await ensureCustomizerRoot(page);
    await selSliderEffect(page, "スライド", "60"); // スライダーエフェクトを「スライド」、変更時間間隔を3秒に設定
    await saveCustomizer(page);

    await page.close();
  });
});
