import { test, expect } from "@playwright/test";
// ======= 共通関数 =======

// ======= 設定 =======
const baseUrl = "";

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

// ======= テスト展開 =======
test.describe.parallel("ビジュアルテスト", () => {
  const { pages } = test.info().project.use;
  // --- デバッグ出力 ---
  console.log("✅ Loaded pages from config:");
  console.table(pages.map((p, i) => ({ No: i + 1, name: p.name, url: p.url })));

  for (const device of devices) {
    test.describe(`${device.name}`, () => {
      test.use(device.use);

      for (const { name, url, options } of pages) {
        test(`： ${name}`, async ({ page }) => {
          await page.goto(url, { waitUntil: "networkidle" });

          const options = {
            maxDiffPixelRatio: 0.03, // 人間の目でわからないレベル
            threshold: 0.03,
          };
          await expect(page).toHaveScreenshot({
            fullPage: true,
            timeout: 100000,
            ...options,
          });
        });
        //break;
      }
    });
    //break;
  }
});
//break;
