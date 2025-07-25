import { test, expect } from '@playwright/test';

test('PC-01: メインメニュー → サブメニュー → サブサブメニューの開閉', async ({ page }) => {
  // 1. ページを開く
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });

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

  // 4. サブメニュー内の「子を持つメニュー」(サブサブ開閉トリガー)を取得しクリック
  const subSubItem = subMenu.locator('li.menu-item-has-children').first();
  const subSubLink = subSubItem.locator('> a');
  const subSubBox = await subSubLink.boundingBox();
  if (!subSubBox) throw new Error('サブサブメニューリンクのboundingBoxが取得できません');
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });

  // 5. サブサブメニューが開き、クリックした <li> に .active が付いていることを確認
  const subSubMenu = subSubItem.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();
  await expect(subSubItem).toHaveClass(/active/);
});



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
