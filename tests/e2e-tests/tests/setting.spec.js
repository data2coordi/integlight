import { test } from "@playwright/test";
import {
  Customizer_header,
  Customizer_siteType,
  Customizer_utils,
  Customizer_slider,
  Customizer_manager,
} from "../utils/customizer";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ browser }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);
    const { testid, siteType, headerType } = testInfo.project.use; // ✅ ここで取得
    console.log(
      `Test ID: ${testid}, Site Type: ${siteType}, Header Type: ${headerType}`
    );
    const config = testInfo.project.use; // ✅ ここで取得
    console.log(`config:${config}`);

    const page = await browser.newPage();
    // const cm_utils = new Customizer_utils(page);
    // const cm_siteType = new Customizer_siteType(page);
    // const cm_header = new Customizer_header(page);
    // const cm_slider = new Customizer_slider(page);
    const cm_manager = new Customizer_manager(page);

    // await cm_utils.openCustomizer();
    // await cm_siteType.setSiteType(siteType);
    // if (headerType === "スライダー") {
    //   await cm_slider.selSliderEffect("スライド", "60"); // スライダーエフェクトを「スライド」、変更時間間隔を3秒に設定
    // }

    await cm_manager.apply(config);
    //await cm_utils.saveCustomizer();

    await page.close();
  });
});
