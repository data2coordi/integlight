import { test } from "@playwright/test";
import { Customizer_manager } from "../utils/customizer";
import { admin_easySetup } from "../utils/admin";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ page }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);

    const testData = testInfo.project.use.testData;
    console.log(`@@@@@testData@@@@@: ${JSON.stringify(testData)}`);

    //admin適用
    const adminData = testData.admin || {};
    const ad_easySetup = new admin_easySetup(page);
    await ad_easySetup.apply(adminData);

    //customizer適用
    const customizerData = testData.customizer || {};
    const cm_manager = new Customizer_manager(page);
    await cm_manager.apply(customizerData);

    await page.close();
  });
});
