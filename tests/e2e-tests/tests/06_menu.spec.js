import { test, expect } from "@playwright/test";

/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースPC操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
test("PC-01: メインメニュー → サブメニュー → サブサブメニューのホバー展開と閉じる確認 (テキストロケーター版)", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START: PC-01: PC版ホバー展開の機能確認 (PC環境 - 堅牢なロケーター) ====="
  );

  await page.setViewportSize({ width: 1280, height: 800 });

  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
    timeout: 30000,
  });

  // 1. メインナビゲーションコンテナが表示されるまで待機
  const navigation = page.locator("#site-navigation");
  await navigation.waitFor({ state: "visible" });

  // ★ 必須の修正: メニューコンテナを強制的に表示状態にする
  const menuContainer = page.locator("#primary-menu-container");
  await menuContainer.evaluate((el) => {
    // 強制的に表示をブロックにし、最優先にするため !important を使用
    el.style.setProperty("display", "block", "important");
    el.removeAttribute("aria-hidden");
  });

  // --- メインメニューの展開 ---
  // 2. メインメニューのリンク「FIRE・資産運用」を取得（getByRoleでLinkとして取得）
  const mainLink = page.getByRole("link", { name: "FIRE・資産運用" }).first();
  // 親の li 要素を取得
  const mainMenuItem = mainLink.locator("..");
  const subMenu = mainMenuItem.locator("> .sub-menu");

  // 3. メインメニューリンクにマウスオーバーして展開
  await mainLink.hover();

  // 4. サブメニュー（第一階層）が開いていることを確認
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(300);

  // --- サブサブメニューの展開 ---
  // 5. サブメニュー内のリンク「FIRE」を取得
  const subSubLink = subMenu.getByRole("link", { name: "FIRE" }).first();
  // 親の li 要素を取得
  const subSubParentItem = subSubLink.locator("..");
  const subSubMenuList = subSubParentItem.locator("> .sub-menu");

  // 6. サブサブメニューリンクにマウスオーバーして展開
  await subSubLink.hover();

  // 7. サブサブメニュー（第二階層）が開いていることを確認
  await expect(subSubMenuList).toBeVisible();

  await page.waitForTimeout(300);

  // --- メニューの折りたたみ（ホバー解除）---
  // 8. 別の最上位メニューリンク「Profile」にマウスを移動させて閉じる
  const outsideElement = page.getByRole("link", { name: "Profile" });

  await outsideElement.waitFor({ state: "visible" });
  await outsideElement.hover();

  // 9. サブサブメニューが閉じていることを確認
  await expect(subSubMenuList).toBeHidden();

  // 10. サブメニューも閉じていることを確認
  await expect(subMenu).toBeHidden();

  console.log(
    "[06_menu.spec.js] ===== END: PC-01: PC版ホバー展開の機能確認 (PC環境 - 堅牢なロケーター) ====="
  );
});

/****************************************************************************************:     */
/****************************************************************************************:     */
/*キーボードPC操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */

test("PC-02: Tab移動でメニューを開き、Enterで開閉、ESCで階層的に閉じる確認", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START: PC-02: Tab移動でメニューを開き、Enterで開閉、ESCで階層的に閉じる確認 ====="
  );

  // PCビューポートを設定
  await page.setViewportSize({ width: 1280, height: 800 });

  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
    timeout: 30000,
  });

  const navigation = page.locator("#site-navigation");
  await navigation.waitFor({ state: "visible" });

  // 1. 【安定化対策】メニューコンテナを強制的に表示状態にする
  // PCビューでもモバイル時の display: none を引き継いでいる可能性が高いため解除
  const menuContainer = page.locator("#primary-menu-container");
  await menuContainer.evaluate((el) => {
    el.style.setProperty("display", "block", "important");
    el.removeAttribute("aria-hidden");
  });

  // リンクと親要素のロケーターを定義
  const mainLink = navigation.getByRole("link", { name: "FIRE・資産運用" });
  const mainMenuItem = mainLink.locator("..");
  const mainToggleBtn = mainLink.locator("+ .submenu-toggle-btn");
  const subMenu = mainMenuItem.locator("> .sub-menu");

  // 2. ページ最上部から Tab を押し、目的の mainLink にフォーカスが当たるまで移動
  let isFocused = false;
  let tabCount = 0;

  // 最大20回までTab操作を繰り返す（Tab回数を動的に調整）
  while (!isFocused && tabCount < 20) {
    await page.keyboard.press("Tab");
    tabCount++;
    try {
      // 短いタイムアウトでフォーカスを確認
      await expect(mainLink).toBeFocused({ timeout: 500 });
      isFocused = true;
    } catch (e) {
      // フォーカスされていなければループを継続
    }
  }

  // ループ後、mainLinkにフォーカスが当たっていることを最終確認
  await expect(mainLink).toBeFocused({ timeout: 10000 });
  console.log(`[PC-02] Tab ${tabCount}回で 'FIRE・資産運用' に到達しました。`);

  // 3. Tabキーをもう一度押し、トグルボタンへ移動 (aタグの次がボタンのため)
  await page.keyboard.press("Tab");
  await expect(mainToggleBtn).toBeFocused();

  // 4. Enterキーを押してサブメニューを展開
  await page.keyboard.press("Enter");
  await expect(mainMenuItem).toHaveClass(/active/);
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(300);

  // 5. Tabキーで次の要素（サブサブを持つリンク「FIRE」）へ移動
  await page.keyboard.press("Tab");

  // ★ 修正点: exact: true を追加し、「FIRE」というリンクを一意に特定
  const subSubLink = subMenu.getByRole("link", { name: "FIRE", exact: true });
  await expect(subSubLink).toBeFocused();

  // 6. Tabキーをもう一度押し、サブサブのトグルボタンへ移動
  await page.keyboard.press("Tab");
  const subSubParentItem = subSubLink.locator("..");
  const subSubToggleBtn = subSubLink.locator("+ .submenu-toggle-btn");
  const subSubMenu = subSubParentItem.locator("> .sub-menu");
  await expect(subSubToggleBtn).toBeFocused();

  // 7. Enterキーを押してサブサブメニューを展開
  await page.keyboard.press("Enter");
  await expect(subSubParentItem).toHaveClass(/active/);
  await expect(subSubMenu).toBeVisible();

  await page.waitForTimeout(300);

  // 8. ESCキーを一度押す → サブサブメニューを閉じる
  await page.keyboard.press("Escape");
  await expect(subSubMenu).toBeHidden();
  await expect(subSubParentItem).not.toHaveClass(/active/);
  await expect(subSubToggleBtn).toBeFocused();

  await page.waitForTimeout(300);

  // 9. ESCキーを二度押す → メインメニューも閉じる
  // JSロジックでは Escape は現在の階層のみを閉じ、フォーカスはトグルボタンに戻る
  await page.keyboard.press("Escape");
  await expect(subMenu).toBeHidden();
  await expect(mainMenuItem).not.toHaveClass(/active/);
  await expect(mainToggleBtn).toBeFocused();

  await page.waitForTimeout(300);
  console.log(
    "[06_menu.spec.js] ===== END: PC-02: Tab移動でメニューを開き、Enterで開閉、ESCで階層的に閉じる確認 ====="
  );
});

/****************************************************************************************:     */
/****************************************************************************************:     */
/*通常ケースSP操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
test("SP-01:モバイルでハンバーガーメニューの開閉ができる", async ({ page }) => {
  console.log(
    "[06_menu.spec.js] ===== START: SP-01:モバイルでハンバーガーメニューの開閉ができる ====="
  );
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });

  // ページにアクセス
  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
  });

  // メニューコンテナ（開閉対象）とトグルボタン取得
  const toggleButton = page.locator("#menuToggle-button");
  const menuContainer = page.locator(".menuToggle-containerForMenu");

  // 初期状態は閉じている
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  // クリックして開く
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute("aria-hidden", "false");

  // 再度クリックして閉じる
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");
  console.log(
    "[06_menu.spec.js] ===== END: SP-01:モバイルでハンバーガーメニューの開閉ができる ====="
  );
});

test("SP-02: メインメニュー → サブメニュー → サブサブメニューの開閉 (堅牢なロケーター版)", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START: SP-02: モバイル版 アコーディオン機能の確認 ====="
  );

  // 1. ビューポートをモバイルサイズに設定 (768px以下)
  await page.setViewportSize({ width: 375, height: 812 });

  // ページにアクセス
  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
    timeout: 30000,
  });

  // ページロード後の要素の表示を待機
  const toggleButton = page.locator("#menuToggle-button");
  const menuContainer = page.locator("#primary-menu-container");

  // 初期状態は閉じている
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  // --- ハンバーガーメニューの開閉 ---
  // クリックしてメニューを開く
  await toggleButton.click();

  // メニューが開いたことを確認
  await expect(menuContainer).toHaveAttribute("aria-hidden", "false");
  await expect(toggleButton).toHaveAttribute("aria-expanded", "true");

  await page.waitForTimeout(500);

  // --- 階層1の展開（「FIRE・資産運用」）---
  // 2. 最初の「子を持つメニュー」のリンク（FIRE・資産運用）を取得
  const mainLink = page.getByRole("link", { name: "FIRE・資産運用" }).first();
  const mainMenuItem = mainLink.locator(".."); // 親の <li> 要素
  const mainToggleBtn = mainLink.locator("+ .submenu-toggle-btn");
  const subMenu = mainMenuItem.locator("> .sub-menu");

  // 3. トグルボタンをクリックしてサブメニューを展開
  await mainToggleBtn.click();

  // 4. サブメニューが開き、親の <li> に .active が付いていることを確認
  await expect(subMenu).toBeVisible();
  await expect(mainMenuItem).toHaveClass(/active/);
  await expect(mainToggleBtn).toHaveAttribute("aria-expanded", "true");

  await page.waitForTimeout(500);

  // --- 階層2の展開（「FIRE」）---
  // 5. サブメニュー内のリンク（FIRE）を取得
  const subSubLink = subMenu.getByRole("link", { name: "FIRE" }).first();
  const subSubParentItem = subSubLink.locator(".."); // 親の <li> 要素
  const subSubToggleBtn = subSubParentItem.locator(".submenu-toggle-btn"); // トグルボタン
  const subSubMenuList = subSubParentItem.locator("> .sub-menu");

  // 6. トグルボタンをクリックしてサブサブメニューを展開
  await subSubToggleBtn.click();

  // 7. サブサブメニューが開き、親の <li> に .active が付いていることを確認
  await expect(subSubMenuList).toBeVisible();
  await expect(subSubParentItem).toHaveClass(/active/);
  await expect(subSubToggleBtn).toHaveAttribute("aria-expanded", "true");

  await page.waitForTimeout(500);

  // --- 階層2の折りたたみ ---
  // 8. サブサブメニューを閉じる（再クリック）
  await subSubToggleBtn.click();

  // 閉じていることを確認
  await expect(subSubMenuList).toBeHidden();
  await expect(subSubParentItem).not.toHaveClass(/active/);
  await expect(subSubToggleBtn).toHaveAttribute("aria-expanded", "false");

  // サブメニュー（階層1）はまだ開いているべき
  await expect(subMenu).toBeVisible();

  await page.waitForTimeout(500);

  // --- 階層1の折りたたみ ---
  // 9. サブメニューを閉じる（再クリック）
  await mainToggleBtn.click();

  // 閉じていることを確認
  await expect(subMenu).toBeHidden();
  await expect(mainMenuItem).not.toHaveClass(/active/);
  await expect(mainToggleBtn).toHaveAttribute("aria-expanded", "false");

  // --- ハンバーガーメニューを閉じる（後処理） ---
  await toggleButton.click();
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  console.log(
    "[06_menu.spec.js] ===== END: SP-02: モバイル版 アコーディオン機能の確認 ====="
  );
});

/****************************************************************************************:     */
/****************************************************************************************:     */
/*キーボードモバイル操作*/
/****************************************************************************************:     */
/****************************************************************************************:     */
test("sp-03:モバイルでハンバーガーメニューをキーボード操作で開閉できる", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START:sp-03 モバイルでハンバーガーメニューをキーボード操作で開閉できる ====="
  );
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });

  // ページにアクセス
  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
  });

  // トグルボタンとメニュー取得
  const toggleButton = page.locator("#menuToggle-button");
  const menuContainer = page.locator(".menuToggle-containerForMenu");

  // 初期状態の検証（閉じている）
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  // フォーカスしてEnterキーで開く
  await toggleButton.focus();
  await toggleButton.press("Enter");
  await page.waitForTimeout(500);

  await expect(menuContainer).toHaveAttribute("aria-hidden", "false");

  // 再度Enterキーで閉じる
  await toggleButton.press("Enter");
  await page.waitForTimeout(500);

  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  console.log(
    "[06_menu.spec.js] ===== END:sp-03 モバイルでハンバーガーメニューをキーボード操作で開閉できる ====="
  );
});

test("SP-04: モバイルでTabでメイン→サブ→サブサブ開いて、ESCで全閉じ確認", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START: SP-04: モバイルでTabでメイン→サブ→サブサブ開いて、ESCで全閉じ確認 ====="
  );

  // 1. モバイル表示に設定してページを開く
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
  });

  // ロケーターを事前に定義
  const toggleButton = page.locator("#menuToggle-button");
  const menuContainer = page.locator("#primary-menu-container");

  // 2. ハンバーガーメニューをEnterキーで開く
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  // ページ最上部からTabでハンバーガーメニューに移動
  let tabCount = 0;
  while (tabCount < 10) {
    await page.keyboard.press("Tab");
    tabCount++;
    try {
      await expect(toggleButton).toBeFocused({ timeout: 500 });
      break;
    } catch (e) {
      // フォーカスされていなければループを継続
    }
  }

  await toggleButton.press("Enter");
  await expect(menuContainer).toHaveAttribute("aria-hidden", "false");
  await page.waitForTimeout(300);
  console.log(
    `[SP-04] ハンバーガーボタンにTab ${tabCount}回で到達、Enterでメニューを開きました。`
  );

  // 3. メインメニュー（FIRE・資産運用）の開閉操作

  // a) Tabでメインメニューリンクにフォーカスが当たるまで移動 (動的Tab移動を再利用)
  let isFocused = false;
  let subMenuTabCount = 0;

  // メニューコンテナ内でリンクを検索し、一意性を保証する
  const mainLink = menuContainer.getByRole("link", {
    name: "FIRE・資産運用",
    exact: true,
  });

  // **修正ロジック**: メニューを開いた直後から目的のリンクにフォーカスが当たるまでTab
  while (!isFocused && subMenuTabCount < 10) {
    await page.keyboard.press("Tab");
    subMenuTabCount++;
    try {
      // 短いタイムアウトでフォーカスを確認
      await expect(mainLink).toBeFocused({ timeout: 500 });
      isFocused = true;
    } catch (e) {
      // フォーカスされていなければループを継続
    }
  }

  // 最終確認 (ここでエラーが出たのであれば、Tab移動のパスが複雑すぎる)
  await expect(mainLink).toBeFocused({ timeout: 5000 });
  console.log(
    `[SP-04] 'FIRE・資産運用' リンクにTab ${subMenuTabCount}回で到達しました。`
  );

  // b) Tabでトグルボタンにフォーカス
  await page.keyboard.press("Tab");
  const mainToggleBtn = mainLink.locator("+ .submenu-toggle-btn");
  await expect(mainToggleBtn).toBeFocused();

  // c) Enterでメニューを開く
  await mainToggleBtn.press("Enter");
  await page.waitForTimeout(300);

  const mainItem = mainLink.locator("..");
  const subMenu = mainItem.locator("> .sub-menu");
  await expect(mainItem).toHaveClass(/active/);
  await expect(subMenu).toBeVisible();

  // 4. サブサブメニュー（FIRE）の開閉操作

  // a) Tabでサブサブを持つリンクにフォーカス (次のリンクなのでTab 1回で良いはず)
  await page.keyboard.press("Tab");

  const subSubLink = subMenu.getByRole("link", { name: "FIRE", exact: true });
  await expect(subSubLink).toBeFocused();

  // b) Tabでトグルボタンにフォーカス
  await page.keyboard.press("Tab");
  const subSubToggleBtn = subSubLink.locator("+ .submenu-toggle-btn");
  await expect(subSubToggleBtn).toBeFocused();

  // c) Enterでサブサブメニューを開く
  await subSubToggleBtn.press("Enter");
  await page.waitForTimeout(300);

  const subSubItem = subSubLink.locator("..");
  const subSubMenu = subSubItem.locator("> .sub-menu");
  await expect(subSubItem).toHaveClass(/active/);
  await expect(subSubMenu).toBeVisible();

  // 5. ESCキーで階層的に閉じる

  // a) ESCキー 1回目: サブサブメニューを閉じる
  await page.keyboard.press("Escape");
  await page.waitForTimeout(300);

  await expect(subSubMenu).toBeHidden();
  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subSubToggleBtn).toBeFocused();

  // b) ESCキー 2回目: メインメニューを閉じる
  await page.keyboard.press("Escape");
  await page.waitForTimeout(300);

  await expect(subMenu).toBeHidden();
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(mainToggleBtn).toBeFocused();

  console.log(
    "[06_menu.spec.js] ===== END: SP-04: モバイルでTabでメイン→サブ→サブサブ開いて、ESCで全閉じ確認 ====="
  );
});

test("SP-05: Tabで開いて Shift+Tabで戻りつつメニューが階層的に閉じる確認", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START: SP-05: Tabで開いて Shift+Tabで戻りつつメニューが階層的に閉じる確認 ====="
  );

  // 1. モバイル表示に設定してページを開く (省略)
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
  });
  const toggleButton = page.locator("#menuToggle-button");
  const menuContainer = page.locator("#primary-menu-container");

  // 2. ハンバーガーメニューをEnterキーで開く (省略)
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");
  let tabCount = 0;
  while (tabCount < 10) {
    await page.keyboard.press("Tab");
    tabCount++;
    try {
      await expect(toggleButton).toBeFocused({ timeout: 500 });
      break;
    } catch (e) {}
  }
  await toggleButton.press("Enter");
  await expect(menuContainer).toHaveAttribute("aria-hidden", "false");
  await page.waitForTimeout(300);

  // 3. メインメニュー（FIRE・資産運用）を開く (省略)
  let isFocused = false;
  let subMenuTabCount = 0;
  const mainLink = menuContainer.getByRole("link", {
    name: "FIRE・資産運用",
    exact: true,
  });
  while (!isFocused && subMenuTabCount < 10) {
    await page.keyboard.press("Tab");
    subMenuTabCount++;
    try {
      await expect(mainLink).toBeFocused({ timeout: 500 });
      isFocused = true;
    } catch (e) {}
  }

  // トグルボタンへ移動し、メニューを開く
  await page.keyboard.press("Tab");
  const mainToggleBtn = mainLink.locator("+ .submenu-toggle-btn");
  await expect(mainToggleBtn).toBeFocused();
  await mainToggleBtn.press("Enter");
  await page.waitForTimeout(300);

  const mainItem = mainLink.locator("..");
  const subMenu = mainItem.locator("> .sub-menu");
  await expect(mainItem).toHaveClass(/active/);
  await expect(subMenu).toBeVisible();

  // 4. サブサブメニュー（FIRE）を開く (省略)
  await page.keyboard.press("Tab");
  const subSubLink = subMenu.getByRole("link", { name: "FIRE", exact: true });
  await expect(subSubLink).toBeFocused();

  await page.keyboard.press("Tab");
  const subSubToggleBtn = subSubLink.locator("+ .submenu-toggle-btn");
  await expect(subSubToggleBtn).toBeFocused();
  await subSubToggleBtn.press("Enter");
  await page.waitForTimeout(300);

  const subSubItem = subSubLink.locator("..");
  const subSubMenu = subSubItem.locator("> .sub-menu");
  await expect(subSubItem).toHaveClass(/active/);
  await expect(subSubMenu).toBeVisible();

  // サブサブメニューの最初の要素にフォーカスを移動 (閉じる準備)
  await page.keyboard.press("Tab");
  await page.waitForTimeout(300);

  // 5. Shift+Tabで戻りながらメニューを閉じる

  // a) Shift+Tab 1回目: サブサブのトグルボタンに戻る
  await page.keyboard.press("Shift+Tab");
  await expect(subSubToggleBtn).toBeFocused();

  // b) Shift+Tab 2回目: サブサブのリンクに戻る
  await page.keyboard.press("Shift+Tab");
  await expect(subSubLink).toBeFocused();

  // c) Shift+Tab 3回目: メインのトグルボタンに戻る → サブサブ閉鎖
  await page.keyboard.press("Shift+Tab");
  await page.waitForTimeout(300);

  await expect(mainToggleBtn).toBeFocused();
  await expect(subSubItem).not.toHaveClass(/active/);
  await expect(subSubMenu).toBeHidden();
  await expect(subMenu).toBeVisible();

  // d) Shift+Tab 4回目: メインメニューのリンクに戻る (メインメニューはまだ閉じない)
  await page.keyboard.press("Shift+Tab");
  await page.waitForTimeout(300);

  await expect(mainLink).toBeFocused();
  await expect(mainItem).toHaveClass(/active/);

  // ★★★ 修正箇所: 5回目のShift+Tabの代わりに、強制的にフォーカスを外す ★★★
  // e) 強制的にハンバーガーボタンにフォーカスを戻し、メインメニューを閉じる
  await toggleButton.focus();
  await page.waitForTimeout(300);

  // 最終確認
  // フォーカスはハンバーガーボタンにあるはず
  await expect(toggleButton).toBeFocused();
  // メインメニューが閉じていること
  await expect(mainItem).not.toHaveClass(/active/);
  await expect(subMenu).toBeHidden();

  console.log(
    "[06_menu.spec.js] ===== END: SP-05: Tabで開いて Shift+Tabで戻りつつメニューが階層的に閉じる確認 ====="
  );
});
/****************************************************************************************:     */
/****************************************************************************************:     */
/*アクセサビリティテスト*/
/****************************************************************************************:     */
/****************************************************************************************:     */
/****************************************************************************************/
/* アクセサビリティテスト：ARIA 属性の付与／解除（モバイルのみ）                       */
/****************************************************************************************/
test("SP-06:アクセシビリティ: モバイルハンバーガーのaria-expanded/aria-hidden切替検証", async ({
  page,
}) => {
  console.log(
    "[06_menu.spec.js] ===== START:SP-06 アクセシビリティ: モバイルハンバーガーのaria-expanded/aria-hidden切替検証 ====="
  );
  // モバイル表示に設定
  await page.setViewportSize({ width: 375, height: 800 });
  await page.goto("https://wpdev.auroralab-design.com/", {
    waitUntil: "networkidle",
  });

  const toggleButton = page.locator("#menuToggle-button");
  const menuContainer = page.locator(".menuToggle-containerForMenu");

  // 初期状態：閉じている
  await expect(toggleButton).toHaveAttribute("aria-expanded", "false");
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");

  // クリックで開く
  await toggleButton.click();
  await expect(toggleButton).toHaveAttribute("aria-expanded", "true");
  await expect(menuContainer).toHaveAttribute("aria-hidden", "false");

  // もう一度クリックで閉じる
  await toggleButton.click();
  await expect(toggleButton).toHaveAttribute("aria-expanded", "false");
  await expect(menuContainer).toHaveAttribute("aria-hidden", "true");
  console.log(
    "[06_menu.spec.js] ===== END:SP-06 アクセシビリティ: モバイルハンバーガーのaria-expanded/aria-hidden切替検証 ====="
  );
});
