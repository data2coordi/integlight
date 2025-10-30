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

import { Customizer_manager } from "../utils/customizer";

// テスト設定を統合し、階層的な構造にするa
const TEST_SCENARIOS = {
  ヘッダーなし: {
    siteType: "エレガント",
    headerType: "なし",
    selector:
      "body > .site > a:first-child + header:nth-child(2) + main:nth-child(3)",
  },
  スライダー: {
    siteType: "エレガント",
    headerType: "スライダー",
    selector: "body > .site > .slider",
  },
  ヘッダー画像: {
    siteType: "エレガント",
    headerType: "静止画像",
    selector: "body > .site > .header-image",
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

  const keyValue = {
    siteType: config.siteType,
    sliderType: {},
  };
  const cm_manager = new Customizer_manager(page);
  await cm_manager.apply(keyValue);
}

// データ駆動型テストの実行
for (const [headerGroup, config] of Object.entries(TEST_SCENARIOS)) {
  test.describe(headerGroup, () => {
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
      console.log(
        `[04_header.spec.ts] ===== START: ${headerGroup} - ヘッダーの設定の妥当性を確認 =====`
      );
      await runCustomizerFlow(page, config);
      await verifyHeaderContents(page, config.selector);
    });
  });
}
