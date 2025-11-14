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
    console.log("@@@@@admin適用 s@@@@@");
    const adminData = testData.admin || {};
    if (adminData.needs == true) {
      console.log("@@@@@admin対象 @@@@@");
      const ad_easySetup = new admin_easySetup(page);
      await ad_easySetup.apply(adminData);
    }
    console.log("@@@@@admin適用 e@@@@@");

    //customizer適用
    console.log("@@@@@custmizer適用 s@@@@@");
    const customizerData = testData.customizer || {};
    const cm_manager = new Customizer_manager(page);
    await cm_manager.apply(customizerData);
    console.log("@@@@@custmizer適用 e@@@@@");

    await page.close();
  });
});
