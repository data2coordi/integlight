import { test, expect } from '@playwright/test';

const pages = [
  { name: 'home top', url: 'https://wpdev.toshidayurika.com/', options: { maxDiffPixelRatio: 0.15 } },
  { name: 'front top', url: 'https://wpdev.toshidayurika.com/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/' },
  { name: 'カテゴリ一覧', url: 'https://wpdev.toshidayurika.com/category/fire-blog/' },
  { name: '固定ページ', url: 'https://wpdev.toshidayurika.com/profile/' },
  { name: 'ブログ', url: 'https://wpdev.toshidayurika.com/sidefire-7500man-life-cost/' },
  { name: 'プラグイン1', url: 'https://wpdev.toshidayurika.com/ptest/' },
  { name: 'プラグイン2', url: 'https://wpdev.toshidayurika.com/ptest2/' }
];

// デバイス設定
const devices = [
  {
    name: 'PC',
    use: {
      viewport: { width: 1920, height: 1080 },
      userAgent:
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137 Safari/537.36'
    }
  },
  {
    name: 'Mobile',
    use: {
      viewport: { width: 375, height: 800 },
      userAgent:
        'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
      extraHTTPHeaders: {
        'sec-ch-ua-mobile': '?1'
      }
    }
  }
];

for (const device of devices) {
  test.describe(`${device.name} ビジュアルリグレッション`, () => {
    test.use(device.use);

    for (const { name, url, options } of pages) {
      test(`${device.name} - ${name}`, async ({ page }) => {
        await page.goto(url, { waitUntil: 'networkidle' });
        await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000, ...options });
      });
    }
  });
}
