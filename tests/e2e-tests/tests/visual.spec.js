import { test, expect } from '@playwright/test';

// ======= 共通関数 =======
async function login(page, baseUrl) {
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
  const adminUser = process.env.WP_ADMIN_USER;
  const adminPass = process.env.WP_ADMIN_PASSWORD;
  if (!adminUser || !adminPass)
    throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が未定義');
  await page.fill('#user_login', adminUser);
  await page.fill('#user_pass', adminPass);
  await page.click('#wp-submit');
  await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
  await expect(page.locator('#wpadminbar')).toBeVisible();
}

async function openCustomizer(page, baseUrl) {
  await page.goto(`${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`, {
    waitUntil: 'domcontentloaded',
  });
  await expect(page.locator('.wp-full-overlay-main')).toBeVisible();
}

async function setSiteType(page, siteType = 'エレガント') {
  await page.getByRole('button', { name: 'サイトタイプ設定' }).click();
  const checkbox = page.getByLabel(siteType);
  if (!(await checkbox.isChecked())) {
    await checkbox.check();
    await saveCustomizer(page);
  }
}

async function saveCustomizer(page) {
  const saveBtn = page.locator('#save');
  await expect(saveBtn).toBeVisible();
  if (await saveBtn.isEnabled()) {
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
  } else {
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
  }
}

// ======= 設定 =======
const baseUrl = 'https://wpdev.toshidayurika.com';

const pages = [
  { name: 'home top', url: `${baseUrl}/` },
  { name: 'front top', url: `${baseUrl}/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/` },
  { name: 'カテゴリ一覧', url: `${baseUrl}/category/fire-blog/` },
  { name: '固定ページ', url: `${baseUrl}/profile/` },
  { name: 'ブログ', url: `${baseUrl}/sidefire-7500man-life-cost/` },
  { name: 'プラグイン1', url: `${baseUrl}/ptest/` },
  { name: 'プラグイン2', url: `${baseUrl}/ptest2/` }
];

const devices = [
  {
    name: 'PC',
    use: {
      viewport: { width: 1920, height: 1080 },
      userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137 Safari/537.36'
    }
  },
  {
    name: 'Mobile',
    use: {
      viewport: { width: 375, height: 800 },
      userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
      extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' }
    }
  }
];

const siteTypes = ['エレガント', 'ポップ'];

// ======= テスト展開 =======
for (const device of devices) {
  for (const siteType of siteTypes) {
    test.describe(`${device.name} - サイトタイプ: ${siteType}`, () => {
      test.use(device.use);

      test.beforeAll(async ({ browser }) => {
        const page = await browser.newPage();
        await login(page, baseUrl);
        await openCustomizer(page, baseUrl);
        await setSiteType(page, siteType);
        await page.close();
      });

      for (const { name, url, options } of pages) {
        test(`${device.name} - ${siteType} - ${name}`, async ({ page }) => {
          await page.goto(url, { waitUntil: 'networkidle' });

          // 安全待機: ページ全体が見える状態を確認
          await page.locator('body').waitFor({ state: 'visible', timeout: 10000 });

          // 任意で、LazyLoad画像の読み込みやフォント描画を待つ場合
          await page.waitForTimeout(500); // 0.5秒程度の余裕待機

          const options = {
            maxDiffPixelRatio: 0.02, // 人間の目でわからないレベル
            threshold: 0.01
          };
          await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000, ...options });
        });
      }
    });
  }
}
