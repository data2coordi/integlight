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
  { name: "ブログ", url: `${baseUrl}/sidefire-7500man-life-cost/` },
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
test.describe("ビジュアルテスト", () => {
  test.beforeAll(async ({ browser }, testInfo) => {
    const { siteType } = testInfo.project.use; // ✅ ここで取得
    const page = await browser.newPage();
    await openCustomizer(page);
    await setSiteType(page, siteType);
    await ensureCustomizerRoot(page);
    await selSliderEffect(page, "スライド", "60"); // スライダーエフェクトを「スライド」、変更時間間隔を3秒に設定
    await saveCustomizer(page);

    await page.close();
  });

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
