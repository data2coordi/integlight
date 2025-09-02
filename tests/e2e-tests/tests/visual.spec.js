import { test, expect } from '@playwright/test';
import {
  openCustomizer,
  saveCustomizer,
  setSiteType,
  selSliderEffect,
  ensureCustomizerRoot,
} from '../utils/common';
// ======= 共通関数 =======






// ======= 設定 =======
const baseUrl = 'https://wpdev.toshidayurika.com';

const pages = [
  { name: 'カテゴリ一覧', url: `${baseUrl}/category/fire-blog/` }
];

const devices = [
  {
    name: 'PC',
    use: {
      viewport: { width: 1920, height: 1080 },
      userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137 Safari/537.36'
    }
  },
];

const siteTypes = ['エレガント', 'ポップ'];

// ======= テスト展開 =======
for (const siteType of siteTypes) {
  test.describe(`${siteType}`, () => {
    test.beforeAll(async ({ browser }) => {
      const page = await browser.newPage();
      await openCustomizer(page);
      await setSiteType(page, siteType);
      await ensureCustomizerRoot(page);
      await selSliderEffect(page, 'スライド', '60'); // スライダーエフェクトを「スライド」、変更時間間隔を3秒に設定
      await saveCustomizer(page);

      await page.close();

    });

    for (const device of devices) {

      test.describe(`${device.name} : ${siteType}`, () => {
        test.use(device.use);


        for (const { name, url, options } of pages) {
          test(`： ${name}`, async ({ page }) => {
            await page.goto(url, { waitUntil: 'networkidle' });

            // 安全待機: ページ全体が見える状態を確認
            //await page.locator('body').waitFor({ state: 'visible', timeout: 10000 });

            // 任意で、LazyLoad画像の読み込みやフォント描画を待つ場合
            //await page.waitForTimeout(500); // 0.5秒程度の余裕待機

            const options = {
              maxDiffPixelRatio: 0.50, // 人間の目でわからないレベル
              threshold: 0.50
            };
            //await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000, ...options });
          });
          //break;

        }
      });
      //break;
    }

  });
  //break;
}
