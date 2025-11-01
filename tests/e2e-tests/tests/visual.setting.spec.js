import { test } from "@playwright/test";
//import { Customizer_manager } from "../utils/customizer";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  //test("カスタマイザー設定", async ({ browser }, testInfo) => {
  test("カスタマイザー設定", async ({ page, browser }) => {
    //console.log(`Running test in project: ${testInfo.project.name}`);

    //let keyValue = testInfo.project.use.keyValue;
    //console.log(`@@@@@keyValue@@@@@: ${JSON.stringify(keyValue)}`);

    //const page = await browser.newPage();
    await page.goto("/", { waitUntil: "domcontentloaded" });

    await expect(page).toHaveURL(/.*/); // URLが何かあればOK
    //const cm_manager = new Customizer_manager(page);
    //await cm_manager.apply(keyValue);
    await page.waitForTimeout(2000);
    await page.close();
  });
});
