import { test, expect } from '@playwright/test';


/****************************************************************************************:     */
/****************************************************************************************:     */
/*slider通常ケース*/
/****************************************************************************************:     */
/****************************************************************************************:     */
/*

test('E2E-01: カスタマイザーでフェイド方式を選択し、トップページでスライダーが表示されることを確認', async ({ page }) => {
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


  // 3. スライダー設定を開く
  await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
  await page.getByRole('button', { name: 'スライダー設定' }).click();


  // 4. スライダー方式「エフェクト: スライド」を選択
  const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
  await effectSelect.selectOption({ label: 'フェード' });
  await expect(effectSelect).toBeVisible();
  await page.waitForTimeout(500);


  const intervalInput = page.getByLabel('変更時間間隔（秒）');
  await intervalInput.fill('999999');
  await page.waitForTimeout(500);
  await intervalInput.fill('1');


  // 5. 「公開」ボタンをクリック（日本語環境に対応）
  await page.locator('#save').click();
  await page.waitForTimeout(500);

  // 7. フロント画面に遷移してスライダーが表示されることを確認
  await page.goto(baseUrl, { waitUntil: 'networkidle' });
  await expect(page.locator('.slider.fade-effect')).toBeVisible();

  // ８. 画像が１秒で変わることを確認
  // 表示中のスライド画像のsrcを取得（activeクラスがあるスライド内のimg）
  const getActiveImageSrc = async () => {
    const activeSlide = page.locator('.slider.fade-effect .slide.active img');
    return activeSlide.getAttribute('src');
  };

  const firstSrc = await getActiveImageSrc();
  expect(firstSrc).not.toBeNull();

  // 1秒待つ（画像切り替え待機）
  await page.waitForTimeout(1100);

  const secondSrc = await getActiveImageSrc();
  expect(secondSrc).not.toBeNull();

  // 画像が切り替わっていることを確認
  expect(secondSrc).not.toBe(firstSrc);


});

test('E2E-01: カスタマイザーでスライド方式を選択し、トップページでスライダーが表示されることを確認', async ({ page }) => {
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


  // 3. スライダー設定を開く
  await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
  await page.getByRole('button', { name: 'スライダー設定' }).click();


  // 4. スライダー方式「エフェクト: スライド」を選択
  const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
  await effectSelect.selectOption({ label: 'スライド' });
  await expect(effectSelect).toBeVisible();
  await page.waitForTimeout(500);


  const intervalInput = page.getByLabel('変更時間間隔（秒）');
  await intervalInput.fill('999999');
  await page.waitForTimeout(500);
  await intervalInput.fill('1');


  // 5. 「公開」ボタンをクリック（日本語環境に対応）
  await page.locator('#save').click();
  await page.waitForTimeout(500);

  // 7. フロント画面に遷移してスライダーが表示されることを確認
  await page.goto(baseUrl, { waitUntil: 'networkidle' });
  await expect(page.locator('.slider.slide-effect')).toBeVisible();

  // ８. 画像が１秒で変わることを確認

  const slides = page.locator('.slider .slides');

  // 初期のtransform値を取得
  const initialTransform = await slides.evaluate(el => getComputedStyle(el).transform);

  // 1.2秒待機（余裕を持たせる）
  await page.waitForTimeout(1200);

  // 変化後のtransform値を取得
  const nextTransform = await slides.evaluate(el => getComputedStyle(el).transform);

  expect(initialTransform).not.toBe(nextTransform);


});

*/

test('E2E-03: カスタマイザーで画像、テキストを選択し、トップページでスライダーが表示されることを確認', async ({ page }) => {
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


  // 3. スライダー設定を開く
  await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
  await page.getByRole('button', { name: 'スライダー設定' }).click();


  // 4. スライダー方式「エフェクト: スライド」を選択
  const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
  await effectSelect.selectOption({ label: 'フェード' });
  await expect(effectSelect).toBeVisible();
  await page.waitForTimeout(500);


  const intervalInput = page.getByLabel('変更時間間隔（秒）');
  await intervalInput.fill('999999');
  await page.waitForTimeout(500);
  await intervalInput.fill('1');



  // 最初の画像をクリックして選択
  await mediaModal.locator('.attachments .attachment').first().click();
  await mediaModal.getByRole('button', { name: '画像を選択' }).click();
  await page.waitForTimeout(1000);

  // ↓↓↓ 画像2枚目を設定（必要なら）↓↓↓
  const secondImageBtn = page.getByRole('button', { name: '画像を選択', exact: true }).nth(1);
  await secondImageBtn.click();
  await expect(mediaModal).toBeVisible();

  // 2枚目の画像をクリック（firstと違うものを選ぶとより確実）
  await mediaModal.locator('.attachments .attachment').nth(1).click();
  await mediaModal.getByRole('button', { name: '画像を選択' }).click();
  await page.waitForTimeout(1000);


  // 5. 「公開」ボタンをクリック（日本語環境に対応）
  await page.locator('#save').click();
  await page.waitForTimeout(500);

  // 7. フロント画面に遷移してスライダーが表示されることを確認
  await page.goto(baseUrl, { waitUntil: 'networkidle' });
  await expect(page.locator('.slider.fade-effect')).toBeVisible();

  // ８. 画像が１秒で変わることを確認
  // 表示中のスライド画像のsrcを取得（activeクラスがあるスライド内のimg）
  const getActiveImageSrc = async () => {
    const activeSlide = page.locator('.slider.fade-effect .slide.active img');
    return activeSlide.getAttribute('src');
  };

  const firstSrc = await getActiveImageSrc();
  expect(firstSrc).not.toBeNull();

  // 1秒待つ（画像切り替え待機）
  await page.waitForTimeout(1100);

  const secondSrc = await getActiveImageSrc();
  expect(secondSrc).not.toBeNull();

  // 画像が切り替わっていることを確認
  expect(secondSrc).not.toBe(firstSrc);


});



/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースSP操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
