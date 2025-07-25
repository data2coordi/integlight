import { test, expect } from '@playwright/test';



test('ページのビジュアルリグレッションテストhome top', async ({ page }) => {

  //home top
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });
  await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000, maxDiffPixelRatio: 0.15 });
  //await expect(page).toHaveScreenshot({ fullPage: true, timeout: 100000 });

});


test('ページのビジュアルリグレッションテストfront top', async ({ page }) => {
  //front top
  await page.goto('http://wpdev.toshidayurika.com:7100/fire%e3%81%a7%e8%87%aa%e7%94%b1%e3%81%a8%e6%88%90%e9%95%b7%e3%82%92%e6%8e%b4%e3%82%80%ef%bc%81/', { waitUntil: 'networkidle' });
  await expect(page).toHaveScreenshot({ fullPage: true });


});


test('ページのビジュアルリグレッションテストカテゴリ一覧', async ({ page }) => {

  //カテゴリ一覧
  await page.goto('http://wpdev.toshidayurika.com:7100/category/fire-blog/http://wpdev.toshidayurika.com:7100/category/fire-blog/');
  await expect(page).toHaveScreenshot({ fullPage: true });

});


test('ページのビジュアルリグレッションテスト 固定ページ', async ({ page }) => {


  //固定ページ
  await page.goto('http://wpdev.toshidayurika.com:7100/profile/');
  await expect(page).toHaveScreenshot({ fullPage: true });

});

test('ページのビジュアルリグレッションテスト ブログ', async ({ page }) => {


  //ブログ
  await page.goto('http://wpdev.toshidayurika.com:7100/sidefire-7500man-life-cost/');
  await expect(page).toHaveScreenshot({ fullPage: true });




});

test('ページのビジュアルリグレッションテスト プラグイン１', async ({ page }) => {


  await page.goto('http://wpdev.toshidayurika.com:7100/ptest/');
  await expect(page).toHaveScreenshot({ fullPage: true });




});
test('ページのビジュアルリグレッションテスト プラグイン2', async ({ page }) => {


  await page.goto('http://wpdev.toshidayurika.com:7100/ptest2/');
  await expect(page).toHaveScreenshot({ fullPage: true });




});