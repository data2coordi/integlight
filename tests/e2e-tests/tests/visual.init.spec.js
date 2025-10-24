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
const baseUrl = "";

const pages = [
  { name: "home top", url: `${baseUrl}/` },
  // {
  //   name: "front top",
  //   url: `${baseUrl}/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/`,
  // },
  // { name: "カテゴリ一覧", url: `${baseUrl}/category/fire-blog/` },
  // { name: "固定ページ", url: `${baseUrl}/profile/` },
  // { name: "ブログ", url: `${baseUrl}/sidefire-7500man-life-cost/` },
  // { name: "プラグイン1", url: `${baseUrl}/ptest/` },
  // { name: "プラグイン2", url: `${baseUrl}/ptest2/` },
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
];

const siteTypes = ["エレガント", "ポップ"];

// ======= テスト展開 =======
// for (const siteType of siteTypes) {
//   test.describe(`${siteType}`, () => {
//     test.beforeAll(async ({ browser }) => {
//       const page = await browser.newPage();
//       await openCustomizer(page);
//       await setSiteType(page, siteType);
//       await ensureCustomizerRoot(page);
//       await selSliderEffect(page, "スライド", "60"); // スライダーエフェクトを「スライド」、変更時間間隔を3秒に設定
//       await saveCustomizer(page);

//       await page.close();
//     });

//     for (const device of devices) {
//       test.describe(`${device.name} : ${siteType}`, () => {
//         test.use(device.use);

//         for (const { name, url, options } of pages) {
//           test(`： ${name}`, async ({ page }) => {
//             await page.goto(url, { waitUntil: "networkidle" });

//             // 安全待機: ページ全体が見える状態を確認
//             //await page.locator('body').waitFor({ state: 'visible', timeout: 10000 });

//             // 任意で、LazyLoad画像の読み込みやフォント描画を待つ場合
//             //await page.waitForTimeout(500); // 0.5秒程度の余裕待機

//             const options = {
//               maxDiffPixelRatio: 0.03, // 人間の目でわからないレベル
//               threshold: 0.03,
//             };
//             await expect(page).toHaveScreenshot({
//               fullPage: true,
//               timeout: 100000,
//               ...options,
//             });
//           });
//         }
//       });
//     }
//   });
// }

//画像ヘッダーを選択
async function setHeaderImage(page, config) {
  await ensureCustomizerRoot(page);
  await openHeaderImage(page);

  await page.getByRole("button", { name: "画像を追加" }).nth(0).click();
  const mediaModal = page.locator(".attachments-browser");
  await mediaModal.waitFor({ state: "visible", timeout: 15000 });

  const searchInput = page.locator("#media-search-input");
  await searchInput.fill(config.imagePartialName);
  await searchInput.press("Enter");

  const targetImage = page
    .locator(`.attachments-browser img[src*="${config.imagePartialName}"]`)
    .first();
  await targetImage.waitFor({ state: "visible", timeout: 15000 });
  await targetImage.click({ force: true });

  await page.getByRole("button", { name: "選択して切り抜く" }).nth(0).click();
  await page.getByRole("button", { name: "画像切り抜き" }).nth(0).click();
}

async function settingWrapper(page, siteType) {
  console.log(`Setting site type to: ${siteType}`);
  await openCustomizer(page);
  await setSiteType(page, siteType);
  await ensureCustomizerRoot(page);
  await saveCustomizer(page);
}

for (const siteType of siteTypes) {
  test.describe(`画像ヘッダーテスト:${siteType}`, () => {
    test.beforeAll(async ({ browser }) => {
      const page = await browser.newPage();
      await settingWrapper(page, "ポップ");
      await page.close();
    });

    for (const device of devices) {
      test.describe(`${device.name} : ${siteType}`, () => {
        test.use(device.use);

        for (const { name, url, options } of pagesForImageHeader) {
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
        }
      });
    }
  });
}
