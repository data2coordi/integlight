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



  // 5. 画像設定 - 1枚目の画像設定開始

  // 画像変更ボタンをクリック（1枚目）
  await page.getByRole('button', { name: '画像を変更' }).nth(0).click();

  // メディアライブラリのモーダルが表示されるのを待つ
  await page.waitForSelector('.media-frame');
  await page.waitForSelector('.attachment');

  // 画像が表示されるエリアをスクロール（遅延読み込み対応）
  const mediaModal = page.locator('.attachments-browser');
  await mediaModal.evaluate(el => {
    el.scrollTop = el.scrollHeight;
  });
  await page.waitForTimeout(1500);


  const imageFileNamePartial = 'Firefly-203280';
  await page.waitForSelector(`img[src*="${imageFileNamePartial}"]`, {
    timeout: 10000,
    state: 'visible',
  });

  // 特定の画像を取得
  const targetImage = page.locator(`img[src*="${imageFileNamePartial}"]`);
  await expect(targetImage).toHaveCount(1);

  // 確実に見えるようにする
  await targetImage.scrollIntoViewIfNeeded();
  await page.locator('img[src*="Firefly-203280"]').click({ force: true });

  await page.locator('.media-button-select').click();

















  // 6. 「公開」ボタンをクリック（日本語環境に対応）
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
