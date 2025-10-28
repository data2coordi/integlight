import { test } from "@playwright/test";
import {
  openCustomizer,
  saveCustomizer,
  setSiteType,
  selSliderEffect,
  ensureCustomizerRoot,
  openHeaderSetting,
} from "../utils/common";
import {
  Customizer_header,
  Customizer_siteType,
  Customizer_utils,
} from "../utils/commonClass";
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

    const cm_utils = new Customizer_utils(page);
    const cm_siteType = new Customizer_siteType(page);
    await cm_utils.openCustomizer();
    await cm_siteType.setSiteType(psiteType);
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
