import { test, expect } from '@playwright/test';

test('ページのビジュアルリグレッションテスト', async ({ page }) => {
  await page.goto('https://toshidayurika.com:7100/');
  await expect(page).toHaveScreenshot({ fullPage: true });

  //home top
  //  await page.goto('https://tech.toshidayurika.com/');
  //  await expect(page).toHaveScreenshot({ fullPage: true });

  //front top
  //await page.goto('https://color.toshidayurika.com/');
  //await expect(page).toHaveScreenshot({ fullPage: true });

  /*
  //カテゴリ一覧
  await page.goto('https://color.toshidayurika.com/category/color/');
  await expect(page).toHaveScreenshot({ fullPage: true });


  //固定ページ
  await page.goto('https://color.toshidayurika.com/profile_cto/');
  await expect(page).toHaveScreenshot({ fullPage: true });

  //ブログ
  await page.goto('https://color.toshidayurika.com/2023/07/04/homemadedrape/');
  await expect(page).toHaveScreenshot({ fullPage: true });
*/

  console.log('テスト完了');
});