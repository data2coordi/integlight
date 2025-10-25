import { test, expect } from "@playwright/test";
import {
  openCustomizer,
  saveCustomizer,
  setSiteType,
  selSliderEffect,
  ensureCustomizerRoot,
  openHeaderSetting,
} from "../utils/common";
// ======= 共通関数 =======

// ======= テスト対象ページ設定 =======
// playwright.config.js の baseURL を基準とした相対パスを定義します。
const pages = [
  { name: "home top", url: `/` },
  {
    name: "front top",
    url: `/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/`,
  },
  { name: "カテゴリ一覧", url: `/category/fire-blog/` },
  { name: "固定ページ", url: `/profile/` },
  { name: "ブログ", url: `/sidefire-7500man-life-cost/` },
  { name: "プラグイン1", url: `/ptest/` },
  { name: "プラグイン2", url: `/ptest2/` },
];

const pagesForImageHeader = [{ name: "home top for ImageHeader", url: `/` }];

const devices = [
  {
    name: "PC",
    use: {
      viewport: { width: 1920, height: 1080 },
      userAgent:
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137 Safari/537.36",
    },
  },
  {
    name: "Mobile",
    use: {
      viewport: { width: 375, height: 800 },
      userAgent:
        "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1",
      extraHTTPHeaders: { "sec-ch-ua-mobile": "?1" },
    },
  },
];

const siteTypes = ["エレガント", "ポップ"];

// ======= テストケース定義 =======
// この配列にテストの組み合わせを追加することで、保守性を高く保ちます。
// 各オブジェクトは「ヘッダーの種類」「対象ページ」「事前設定処理」を定義します。
const testCases = [
  {
    name: "スライダーヘッダー",
    pages: pages,
    beforeAll: async (page, siteType) => {
      await setSiteType(page, siteType);
      await ensureCustomizerRoot(page);
      await selSliderEffect(page, "スライド", "60");
    },
  },
  {
    name: "画像ヘッダー",
    pages: pagesForImageHeader,
    beforeAll: async (page, siteType) => {
      await setSiteType(page, siteType);
      await ensureCustomizerRoot(page);
      await openHeaderSetting(page, "静止画像");
    },
  },
];

// ======= テスト展開 =======
for (const testCase of testCases) {
  for (const siteType of siteTypes) {
    // 例: 「スライダーヘッダーテスト: エレガント」というテストスイートを作成
    test.describe(`${testCase.name}テスト: ${siteType}`, () => {
      // ★★★ 保守性のための重要ポイント(1) ★★★
      // WordPressカスタマイザーは状態を持つため、設定が競合しないよう
      // `siteType` と `testCase` の組み合わせごとにテストを「直列実行」します。
      test.describe.configure({ mode: "serial" });

      // このスイート（例: エレガントのスライダー）内の全テストの前に一度だけ実行されます。
      // これにより、コストの高い設定変更処理をテストごとに行う必要がなくなります。
      test.beforeAll(async ({ browser }) => {
        const page = await browser.newPage();
        await openCustomizer(page);
        // 各テストケース固有のセットアップを実行
        await testCase.beforeAll(page, siteType);
        await saveCustomizer(page);
        await page.close();
      });

      // ★★★ 保守性のための重要ポイント(2) ★★★
      // 上記の `beforeAll` が完了した後、この中のテスト(PC/Mobile)は
      // 互いに影響しないため、「並列実行」させてテスト時間を短縮します。
      // Playwrightが自動的に空いているワーカーに割り当ててくれます。
      for (const device of devices) {
        test.describe(`${device.name}`, () => {
          test.use(device.use);

          // ページごとにテストを生成
          for (const { name, url } of testCase.pages) {
            test(`： ${name}`, async ({ page }) => {
              await page.goto(url, { waitUntil: "networkidle" });

              const options = {
                maxDiffPixelRatio: 0.03,
                threshold: 0.03,
              };
              await expect(page).toHaveScreenshot({
                fullPage: true,
                timeout: 100000,
                ...options,
              });
            });
          }
        });
      }
    });
  }
}
