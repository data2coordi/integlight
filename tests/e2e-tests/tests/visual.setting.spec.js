import { test, devices } from "@playwright/test";
import { Customizer_manager } from "../utils/customizer";
const authFile = "playwright/.auth/user.json";

test.describe("ビジュアルテスト", () => {
  test("カスタマイザー設定", async ({ browser }, testInfo) => {
    console.log(`Running test in project: ${testInfo.project.name}`); // ⭐ 修正点: keyValueをここで定義する

    let keyValue = testInfo.project.use.keyValue;
    console.log(`@@@@@keyValue@@@@@: ${JSON.stringify(keyValue)}`);

    // ... Playwrightの設定を無視し、コンテキスト作成時に動画設定を強制的に適用
    const context = await browser.newContext({
      video: {
        mode: "on",
        retainOnFailure: true,
      },
    });

    const page = await context.newPage();

    // ... 動画強制初期化ロジック (省略)
    const video = page.video();

    const cm_manager = new Customizer_manager(page); // ⭐ 修正点: keyValueは既にこのスコープで定義されているため、OK
    await cm_manager.apply(keyValue);

    await page.waitForTimeout(2000);

    // ... 動画の手動保存ロジック ...
    if (video) {
      // ...
      await video.saveAs(
        `test-results/${testInfo.project.name}/${testInfo.title}_manual.webm`
      );
    }

    await page.close();
    await context.close();
  });
});
