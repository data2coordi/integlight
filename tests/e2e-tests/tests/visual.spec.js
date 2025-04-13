import { test, expect } from '@playwright/test';



test('ページのビジュアルリグレッションテストhome top', async ({ page }) => {

  //home top
  await page.goto('https://tech.toshidayurika.com/', { waitUntil: 'networkidle' });
  await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000, maxDiffPixelRatio: 0.15 });
  //await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000 });

});


test('ページのビジュアルリグレッションテストfront top', async ({ page }) => {
  //front top
  await page.goto('https://tech.toshidayurika.com/', { waitUntil: 'networkidle' });
  await expect(page).toHaveScreenshot({ fullPage: true });


});


test('ページのビジュアルリグレッションテストカテゴリ一覧', async ({ page }) => {

  //カテゴリ一覧
  await page.goto('https://color.toshidayurika.com/category/color/');
  await expect(page).toHaveScreenshot({ fullPage: true });

});


test('ページのビジュアルリグレッションテスト 固定ページ', async ({ page }) => {


  //固定ページ
  await page.goto('https://color.toshidayurika.com/profile_cto/');
  await expect(page).toHaveScreenshot({ fullPage: true });

});

test('ページのビジュアルリグレッションテスト ブログ', async ({ page }) => {


  //ブログ
  await page.goto('https://color.toshidayurika.com/howtouse/');
  await expect(page).toHaveScreenshot({ fullPage: true });




});

