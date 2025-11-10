import { test } from "@playwright/test";
import { Customizer_manager } from "../utils/customizer";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ page }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);

    const testData = testInfo.project.use.testData;
    console.log(`@@@@@testData@@@@@: ${JSON.stringify(testData)}`);

    //const page = await browser.newPage();
    const cm_manager = new Customizer_manager(page);
    const customizerData = testData.customizer || {};
    await cm_manager.apply(customizerData);

    await page.close();
  });
});
