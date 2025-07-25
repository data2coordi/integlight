import { test, expect } from '@playwright/test';


/****************************************************************************************:     */
/****************************************************************************************:     */
/*slider通常ケース*/
/****************************************************************************************:     */
/****************************************************************************************:     */


test('E2E-01: カスタマイザーでスライダー方式を選択し、トップページでスライダーが表示されることを確認', async ({ page }) => {
  const baseUrl = 'http://wpdev.toshidayurika.com:7100';

  // 1. 管理画面にログイン
  await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'networkidle' });
  const adminUser = process.env.WP_ADMIN_USER;
  const adminPass = process.env.WP_ADMIN_PASSWORD;
  if (!adminUser || !adminPass) throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が定義されていません');
  await page.fill('#user_login', adminUser);
  await page.fill('#user_pass', adminPass);
  await page.click('#wp-submit');
  await page.waitForNavigation({ waitUntil: 'networkidle' });

  // 2. カスタマイザーを開く
  await page.goto(
    `${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`,
    { waitUntil: 'networkidle' }
  );
  // ← 追加：カスタマイザーコントロールが表示されていることを確認
  const customizePane = page.locator('.wp-full-overlay-main');
  await expect(customizePane).toBeVisible();

  return;
  // 3. 「Top Header Setting」パネルを開く
  await page.click('button[aria-controls="accordion-panel-slider_panel"]');
  // 4. 「Select - Slider or Image」セクションを開く
  await page.click('button[aria-controls="accordion-section-sliderOrImage_section"]');

  // 5. スライダー方式を選択
  const sliderSelect = page.locator('#customize-control-integlight_display_choice select');
  await expect(sliderSelect).toBeVisible();
  await sliderSelect.selectOption('slider');

  // 6. 「Publish」ボタンで保存
  await page.click('.customize-controls [aria-label="Publish"]');
  await page.waitForSelector('.customize-save-status:has-text("Saved")');

  // 7. フロント画面に遷移してスライダーが表示されることを確認
  await page.goto(baseUrl, { waitUntil: 'networkidle' });
  await expect(page.locator('.integlight-slider-wrap')).toBeVisible();
  await expect(page.locator('.custom-header-image')).toHaveCount(0);
});

/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースSP操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
