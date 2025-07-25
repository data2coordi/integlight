import { test, expect } from '@playwright/test';

test('PC-01: メインメニュー → サブメニュー → サブサブメニューの開閉', async ({ page }) => {
  await page.goto('http://wpdev.toshidayurika.com:7100/', { waitUntil: 'networkidle' });

  // メインメニューの親メニュー項目（子メニューがあるもの）を取得
  const mainMenu = page.locator('.main-navigation .menu-item-has-children').first();

  // メインメニューのリンク右端をクリック（例：横幅200pxの右端付近）
  const box = await mainMenu.locator('> a').boundingBox();
  if (!box) throw new Error('メインメニューリンクのboundingBoxが取得できません');
  await mainMenu.locator('> a').click({ position: { x: box.width - 10, y: box.height / 2 } });

  // 直下のサブメニューのみ取得して表示確認
  const subMenu = mainMenu.locator('> .sub-menu');
  await expect(subMenu).toBeVisible();

  // サブメニュー内でさらに子メニューを持つ最初の項目を取得
  const subSubTrigger = subMenu.locator('.menu-item-has-children').first();

  // そのリンクの右端をクリック（子メニュー開閉のトリガー想定）
  const subSubLink = subSubTrigger.locator('> a');
  const subSubBox = await subSubLink.boundingBox();
  if (!subSubBox) throw new Error('サブサブメニューリンクのboundingBoxが取得できません');
  await subSubLink.click({ position: { x: subSubBox.width - 10, y: subSubBox.height / 2 } });

  // サブサブメニューの直下の表示を確認
  const subSubMenu = subSubTrigger.locator('> .sub-menu');
  await expect(subSubMenu).toBeVisible();
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
