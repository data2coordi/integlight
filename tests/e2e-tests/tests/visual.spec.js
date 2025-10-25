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

// ======= 設定 =======
const baseUrl = "https://wpdev.auroralab-design.com";

const pages = [
  { name: "home top", url: `${baseUrl}/` },
  {
    name: "front top",
    url: `${baseUrl}/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/`,
  },
  { name: "カテゴリ一覧", url: `${baseUrl}/category/fire-blog/` },
  { name: "固定ページ", url: `${baseUrl}/profile/` },
  { name: "ブログ", url: `${baseUrl}/sidefire-7500man-life-cost/` },
  { name: "プラグイン1", url: `${baseUrl}/ptest/` },
  { name: "プラグイン2", url: `${baseUrl}/ptest2/` },
];

const pagesForImageHeader = [
  { name: "home top for ImageHeader", url: `${baseUrl}/` },
];

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
    test.describe(`${testCase.name}テスト: ${siteType}`, () => {
      // siteTypeとtestCaseの組み合わせごとに直列実行
      test.describe.configure({ mode: "serial" });

      // 各テストケースの前に一度だけ実行
      test.beforeAll(async ({ browser }) => {
        const page = await browser.newPage();
        await openCustomizer(page);
        // 各テストケース固有のセットアップを実行
        await testCase.beforeAll(page, siteType);
        await saveCustomizer(page);
        await page.close();
      });

      // デバイスごとに並列実行
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
