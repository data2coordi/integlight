import { test, expect, type Page } from "@playwright/test";
import {
  timeStart,
  logStepTime,
  openCustomizer,
  openHeaderSetting,
  selSliderEffect,
  saveCustomizer,
  setSiteType,
  ensureCustomizerRoot,
} from "../utils/common";

// テスト設定を統合し、階層的な構造にするa
const TEST_SCENARIOS = {
  ヘッダーなし: {
    pcHome1: {
      siteType: "エレガント",
      headerType: "なし",
      selector: "main#primary > section.latest-posts",
    },
  },
  スライダー: {
    pcHome1: {
      siteType: "エレガント",
      headerType: "スライダー",
      selector: "body > .slider",
    },
  },
  ヘッダー画像: {
    pcHome1: {
      siteType: "エレガント",
      headerType: "静止画像",
      selector: "body > .site > .topImage",
    },
  },
};

//////////////////////////////外出し候補end

async function verifyHeaderContents(page: Page, selector: string) {
  await page.goto("/", { waitUntil: "networkidle" });

  const latestPosts = page.locator(selector);
  await expect(latestPosts).toHaveCount(1);
}

// 共通テストフロー
async function runCustomizerFlow(page: Page, config: any) {
  // ログイン処理はauth.setup.tsで完了済み

  //timeStart('openCustomizer_1');
  await test.step("2. カスタマイザー画面を開く", () => openCustomizer(page));
  //logStepTime('openCustomizer_1');

  //timeStart('openHeaderSetting');
  await test.step("3. ヘッダー有無を設定", () =>
    openHeaderSetting(page, config.headerType));
  //logStepTime('openHeaderSetting');

  //timeStart('CustomizerRoot_1');
  await ensureCustomizerRoot(page);
  //logStepTime('CustomizerRoot_1');

  if (config.headerType === "スライダー") {
    //timeStart('sliderSettings');
    await test.step("8. スライダー設定", async () => {
      await selSliderEffect(page);
    });
    //logStepTime('sliderSettings');

    //timeStart('CustomizerRoot_2');
    await ensureCustomizerRoot(page);
    //logStepTime('CustomizerRoot_2');
  }

  //timeStart('setSiteType');
  await test.step("6. ホームタイプの変更", () =>
    setSiteType(page, config.siteType));
  //logStepTime('setSiteType');

  //timeStart('saveCustomizer');
  await test.step("7. 変更を保存", () => saveCustomizer(page));
  //logStepTime('saveCustomizer');
}

// データ駆動型テストの実行
for (const [headerGroup, scenarios] of Object.entries(TEST_SCENARIOS)) {
  test.describe(headerGroup, () => {
    for (const [testCaseName, config] of Object.entries(scenarios)) {
      let page: Page;
      let context;

      test.beforeAll(async ({ browser }) => {
        context = await browser.newContext();
        page = await context.newPage();
      });

      test.afterAll(async () => {
        await page.close();
        await context.close();
      });

      test("ヘッダーの設定の妥当性を確認", async () => {
        await runCustomizerFlow(page, config);
        await verifyHeaderContents(page, config.selector);
      });
    }
  });
}
