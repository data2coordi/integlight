import { test, expect } from '@playwright/test';


/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースPC操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */


test('PC-01: メインメニュー → サブメニュー → サブサブメニューの開閉と閉じる確認', async ({ page }) => {
  // 1. ページを開く
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });

  // 2. 最初のメインメニューを取得してクリック
  const mainMenuItem = page.locator('.main-navigation .menu-item-has-children').first();
  const mainLink = mainMenuItem.locator('> a');
  const mainBox = await mainLink.boundingBox();
  if (!mainBox) throw new Error('メインメニューリンクのboundingBoxが取得できません');
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });

  // 3. サブメニューが開いていることを確認
  const subMenu = mainMenuItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();
  await expect(mainMenuItem).toHaveClass(/active/);

  await page.waitForTimeout(500);

  // 4. サブメニュー内のサブサブ開閉トリガーをクリック
  const subSubParentItem = subMenu.locator('li.menu-item-has-children').first();
  const subSubLink = subSubParentItem.locator('> a');
  const subSubBox = await subSubLink.boundingBox();
  if (!subSubBox) throw new Error('サブサブメニューリンクのboundingBoxが取得できません');
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });

  // 5. サブサブメニューが開いていることを確認
  const subSubMenuList = subSubParentItem.locator('> .sub-menu');
  await expect(subSubMenuList).toBeVisible();
  await expect(subSubParentItem).toHaveClass(/active/);

  await page.waitForTimeout(500);


  // 6. サブサブメニューを閉じる（再クリック）
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });
  await expect(subSubMenuList).toBeHidden();
  await expect(subSubParentItem).not.toHaveClass(/active/);

  // サブメニューはまだ開いているべき
  await expect(subMenu).toBeVisible();

  // 7. サブメニューを閉じる（再クリック）
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });
  await expect(subMenu).toBeHidden();
  await expect(mainMenuItem).not.toHaveClass(/active/);


});



/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースSP操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */

test('モバイルでハンバーガーメニューの開閉ができる', async ({ page }) => {
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });

  // ページにアクセス
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });

  // メニューコンテナ（開閉対象）とトグルボタン取得
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');

  // 初期状態は閉じている
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

  // クリックして開く
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');

  // 再度クリックして閉じる
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');
});




test('SP-01: メインメニュー → サブメニュー → サブサブメニューの開閉', async ({ page }) => {

  // 1. ビューポートをモバイルサイズに設定し、ページを開く
  await page.setViewportSize({ width: 375, height: 812 }); // iPhone X相当



  // ページにアクセス
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });

  // メニューコンテナ（開閉対象）とトグルボタン取得
  const toggleButton = page.locator('#menuToggle-button');
  const menuContainer = page.locator('.menuToggle-containerForMenu');

  // 初期状態は閉じている
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'true');

  // クリックして開く
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute('aria-hidden', 'false');







  // 2. 最初の「子を持つメニュー」(メインメニュー)を取得しクリック
  const mainMenuItem = page.locator('.main-navigation .menu-item-has-children').first();
  const mainLink = mainMenuItem.locator('> a');
  const mainBox = await mainLink.boundingBox();
  if (!mainBox) throw new Error('メインメニューリンクのboundingBoxが取得できません');
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });

  // 3. サブメニューが開き、親の <li> に .active が付いていることを確認
  const subMenu = mainMenuItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();
  await expect(mainMenuItem).toHaveClass(/active/);

  await page.waitForTimeout(1000);

  // 4. サブメニュー内の「子を持つメニュー」(サブサブ開閉トリガー)を取得しクリック
  const subSubParentItem = subMenu.locator('li.menu-item-has-children').first();
  const subSubLink = subSubParentItem.locator('> a');
  const subSubBox = await subSubLink.boundingBox();
  if (!subSubBox) throw new Error('サブサブメニューリンクのboundingBoxが取得できません');
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });

  // 5. サブサブメニューが開き、クリックした <li> に .active が付いていることを確認
  const subSubMenuList = subSubParentItem.locator('> .sub-menu');
  await expect(subSubMenuList).toBeVisible();
  await expect(subSubParentItem).toHaveClass(/active/);

  await page.waitForTimeout(500);

  // 6. サブサブメニューを閉じる（再クリック）
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });
  await expect(subSubMenuList).toBeHidden();
  await expect(subSubParentItem).not.toHaveClass(/active/);

  // サブメニューはまだ開いているべき
  await expect(subMenu).toBeVisible();

  // 7. サブメニューを閉じる（再クリック）
  await mainLink.click({ position: { x: mainBox.width - 10, y: mainBox.height / 2 } });
  await expect(subMenu).toBeHidden();
  await expect(mainMenuItem).not.toHaveClass(/active/);


});




/****************************************************************************************:     */
/****************************************************************************************:     */
/*キーボードPC操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */



test('PC-02: Tabでメイン→サブ→サブサブ開いて、ESCで全閉じ確認', async ({ page }) => {
  // 1. ページを開く
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });

  // 2. メインメニューをTabでフォーカス→開く
  const mainLink = page.locator('.main-navigation .menu-item-has-children > a').first();
  await mainLink.focus();
  await mainLink.press('Tab');
  const mainItem = mainLink.locator('..');
  await expect(mainItem).toHaveClass(/active/);


  // 3. サブメニューが可視化されていることを確認
  const subMenu = mainItem.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // 4. サブサブを持つリンクをTabでフォーカス→開く
  const subSubLink = subMenu.locator('li.menu-item-has-children > a').first();
  await subSubLink.focus();
  await subSubLink.press('Tab');
  const subSubItem = subSubLink.locator('..');
  await expect(subSubItem).toHaveClass(/active/);

  // 5. サブサブメニューが可視化されていることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(500);


  // 6. ESCキーを一度押して全階層を閉じる
  await page.keyboard.press('Escape');

  // 7. どの階層にも .active がなく、サブ・サブサブともに隠れていること
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();
  await expect(subSubMenu).toBeHidden();

  await page.waitForTimeout(500);


});
