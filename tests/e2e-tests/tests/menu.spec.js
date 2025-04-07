import { test, expect } from '@playwright/test';


test.describe('メニューの動作テスト', () => {

  /*
  test.beforeEach(async ({ page }) => {
    // テストページを開く
    await page.goto('http://color.toshidayurika.com '); // 実際のURLに変更
  });
  */

  test('メニューの動作テスト2', async ({ page }) => {
    // 1. ページにアクセス
    await page.goto('http://color.toshidayurika.com '); // 実際のURLに変更
    console.log('Step 1: ページにアクセス');
    expect(page.url()).toBe('https://color.toshidayurika.com');  // URL確認

    // 2. メニューの「ブログ」をクリック（サブメニューを表示する位置にクリック）
    console.log('Step 2: メニュー「ブログ」をクリック');
    const blogMenuItem = await page.locator('#menu-item-827 > a');
    const linkWidth = await blogMenuItem.boundingBox();
    const clickPositionX = linkWidth.x + linkWidth.width - 40; // 右端の20pxにクリックする
    await page.mouse.click(clickPositionX, linkWidth.y + 10); // yは適切な垂直位置に設定
    console.log('ブログメニューをクリックした後');

    // 3. ページが遷移する場合、遷移完了を待つ
    console.log('Step 3: ページ遷移を待機');
    await page.waitForNavigation({ waitUntil: 'networkidle' });
    expect(page.url()).toBe('https://color.toshidayurika.com/concept/');  // 遷移後のURL確認
    console.log('ページ遷移後のURL確認完了');

    // 4. サブメニューが表示されるのを待機
    console.log('Step 4: サブメニューが表示されるのを待機');
    const subMenu = page.locator('.menu-item-has-children.active > .sub-menu');
    await expect(subMenu).toBeVisible({ timeout: 10000 });
    console.log('サブメニューが表示されることを確認');

    // 必要に応じて、サブメニューが見えることを確認するチェックを追加
    const subMenuVisibility = await subMenu.isVisible();
    console.log(`サブメニュー表示状態: ${subMenuVisibility ? '表示' : '非表示'}`);
    expect(subMenuVisibility).toBe(true);  // サブメニューが表示されていることを確認

    // ここでサブメニュー閉じるテストは除外
  });
  /*
    test('フォーカスアウトでサブメニューが閉じることを確認', async ({ page }) => {
      // メニューアイテムを選択
      const menuItem = await page.locator('.menu-item-has-children > a');
  
      // メニューアイテムをクリックしてサブメニューを開く
      await menuItem.click();
  
      const subMenu = page.locator('.menu-item-has-children.active > .sub-menu');
      await expect(subMenu).toBeVisible();
  
      // フォーカスを外してサブメニューが閉じることを確認
      await page.keyboard.press('Tab');
      await expect(subMenu).toBeHidden();
    });
  
    test('メニューアイテムをクリックした際に active クラスがトグルされることを確認', async ({ page }) => {
      // メニューアイテムを選択
      const menuItem = await page.locator('.menu-item-has-children > a');
  
      // 初期状態では active クラスがないことを確認
      const parentItem = menuItem.locator('..');
      await expect(parentItem).not.toHaveClass(/active/);
  
      // メニューアイテムをクリックして active クラスが追加されることを確認
      await menuItem.click();
      await expect(parentItem).toHaveClass(/active/);
  
      // 再度クリックして active クラスがトグルされることを確認
      await menuItem.click();
      await expect(parentItem).not.toHaveClass(/active/);
    });
  
    test('メニューアイテムをクリックしたとき、他の同階層メニューアイテムから active クラスが削除されることを確認', async ({ page }) => {
      // 最初のメニューアイテム
      const firstMenuItem = await page.locator('.menu-item-has-children:first-of-type > a');
      const firstParentItem = firstMenuItem.locator('..');
  
      // 2番目のメニューアイテム
      const secondMenuItem = await page.locator('.menu-item-has-children:nth-of-type(2) > a');
      const secondParentItem = secondMenuItem.locator('..');
  
      // 最初のアイテムをクリックして active クラスが追加されることを確認
      await firstMenuItem.click();
      await expect(firstParentItem).toHaveClass(/active/);
  
      // 2番目のアイテムをクリックして、最初のアイテムから active クラスが削除され、2番目に追加されることを確認
      await secondMenuItem.click();
      await expect(firstParentItem).not.toHaveClass(/active/);
      await expect(secondParentItem).toHaveClass(/active/);
    });
  
    test('サブメニューがクリックで開くことを確認', async ({ page }) => {
      // 親メニューアイテムをクリックしてサブメニューを開く
      const parentMenuItem = await page.locator('.menu-item-has-children > a');
      await parentMenuItem.click();
  
      // サブメニューが表示されることを確認
      const subMenu = page.locator('.sub-menu');
      await expect(subMenu).toBeVisible();
    });
  
    test('ハンバーガーメニューの表示/非表示の切り替えを確認', async ({ page }) => {
      // ハンバーガーメニューのチェックボックスをクリックしてメニューを開く
      const menuToggle = await page.locator('.menuToggle-checkbox');
      await menuToggle.check();
  
      // メニューオーバーレイが表示されることを確認
      const menuOverlay = page.locator('.menuToggle-containerForMenu');
      await expect(menuOverlay).toBeVisible();
  
      // メニューが非表示になることを確認
      await menuToggle.uncheck();
      await expect(menuOverlay).toBeHidden();
    });
    */
});

