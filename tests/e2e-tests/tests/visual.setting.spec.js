import { test } from "@playwright/test";
import { Customizer_manager } from "../utils/customizer";
// ======= 共通関数 =======

// ======= テスト展開 =======
test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ page, browser }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`);

    // await page.goto("/", {
    //   waitUntil: "networkidle",
    // });

    let keyValue = testInfo.project.use.keyValue;
    console.log(`@@@@@keyValue@@@@@: ${JSON.stringify(keyValue)}`);

    const cm_manager = new Customizer_manager(page);
    await cm_manager.apply(keyValue);

    await page.close();
  });
});
