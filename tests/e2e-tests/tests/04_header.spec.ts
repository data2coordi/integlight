import { test, expect, type Page } from "@playwright/test";

import { Customizer_manager } from "../utils/customizer";

// テスト設定を統合し、階層的な構造にするa
const TEST_SCENARIOS = {
  ヘッダーなし: {
    siteType: "エレガント",
    headerType: "なし",
    selector:
      "body > .site > div.ly_site_content > main.site-main article.post-card:first-child",
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
  let keyValue = {
    siteType: config.siteType,
    headerType: config.headerType,
  };

  if (config.headerType === "スライダー") {
    keyValue = {
      ...keyValue,
      sliderType: {}, //警告が出るが使える。
    };
  }
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
