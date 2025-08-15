import { test, expect } from '@playwright/test';



// 共通関数
async function login(page, baseUrl) {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'domcontentloaded' });
    const adminUser = process.env.WP_ADMIN_USER;
    const adminPass = process.env.WP_ADMIN_PASSWORD;
    if (!adminUser || !adminPass)
        throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が未定義');
    await page.fill('#user_login', adminUser);
    await page.fill('#user_pass', adminPass);
    await page.click('#wp-submit');
    await page.waitForNavigation({ waitUntil: 'domcontentloaded' });
    await expect(page.locator('#wpadminbar')).toBeVisible(); // ログイン後の管理バーを確認
}

async function openCustomizer(page, baseUrl) {
    await page.goto(`${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`, {
        waitUntil: 'domcontentloaded',
    });
    await expect(page.locator('.wp-full-overlay-main')).toBeVisible();
}
async function setSiteType(page, siteType = 'エレガント') {
    await page.getByRole('button', { name: 'サイトタイプ設定' }).click();


    // エレガントのチェックボックスをクリック
    // labelのテキストで取得する場合
    // 渡された siteType のチェックボックスを取得
    const checkbox = page.getByLabel(siteType);

    // すでにチェックされていれば何もしない
    if (!(await checkbox.isChecked())) {
        await checkbox.check(); // チェックされていなければチェック
        await saveCustomizer(page); // 変更を保存
    }

}


async function saveCustomizer(page) {
    const saveBtn = page.locator('#save');
    await expect(saveBtn).toBeVisible();
    if (await saveBtn.isEnabled()) {
        await saveBtn.click();
        await expect(saveBtn).toHaveAttribute('value', '公開済み');
        await expect(saveBtn).toBeDisabled();
    } else {
        // すでに公開済みの場合は何もしない
        await expect(saveBtn).toHaveAttribute('value', '公開済み');
        await expect(saveBtn).toBeDisabled();
    }
}


// 共通化関数（home1ページナビゲーション）
async function verifyPageNavigation(
    page: any,
    baseUrl: string,
    gridSelector: string,
    pagenaviSelector: string,
    ExpectedCount: number,
    expectedTitle: string,
    expectedTitle2: string,
    buttonIndex = 0
) {

    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

    let grid = page.locator(gridSelector);
    await expect(grid).toBeVisible();
    let count = await grid.locator('.grid-item').count();
    await expect(count).toBe(ExpectedCount);
    let firstTitle = await grid.locator('.grid-item').nth(buttonIndex).locator('h2').innerText();
    expect(firstTitle.trim()).toContain(expectedTitle);

    const secondPageBtn = page.locator(pagenaviSelector).nth(1);
    await secondPageBtn.click();
    await expect(grid).toBeVisible();
    count = await grid.locator('.grid-item').count();
    await expect(count).toBe(ExpectedCount);
    firstTitle = await grid.locator('.grid-item').nth(buttonIndex).locator('h2').innerText();
    expect(firstTitle.trim()).toContain(expectedTitle2);

}

// 共通化関数（home1カテゴリナビゲーション）
async function verifyCategoryNavi(
    page: any,
    baseUrl: string,
    gridSelector: string,
    pagenaviSelector: string,
    ExpectedCount: number,
    expectedTitle: string,
    expectedTitle2: string,
    buttonIndex = 0
) {

    await page.goto(baseUrl, { waitUntil: 'domcontentloaded' });

    let grid = page.locator(gridSelector);
    await expect(grid).toBeVisible();
    let count = await grid.locator('.category-item').count();
    await expect(count).toBe(ExpectedCount);
    let firstTitle = await grid
        .locator('.category-item')
        .nth(0)
        .locator('.category-link')
        .innerText();
    expect(firstTitle.trim()).toContain(expectedTitle);

    /* カテゴリーを押下して遷移したページが正しいことを確認する*/
    await grid.locator('.category-item .category-link').first().click();
    // ページの読み込み完了を待つ
    await page.waitForLoadState('domcontentloaded');

    // h1テキストを取得
    const heading = await page.locator('h1').innerText();
    expect(heading.trim()).toContain(expectedTitle2);


    console.log(heading);


}









// 共通化関数（home2もっと見るボタン）
async function verifyLoadMoreGeneric(
    page: any,
    baseUrl: string,
    gridSelector: string,
    loadMoreSelector: string,
    initialCount: number,
    firstClickExpectedCount: number,
    secondClickExpectedCount: number,
    expectedTitle: string,
    gridIndex = 0,
    buttonIndex = 0
) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });

    const grid = page.locator(gridSelector).nth(gridIndex);
    const loadMoreBtn = page.locator(loadMoreSelector).nth(buttonIndex);

    const firstCount = await grid.locator('.grid-item').count();
    await expect(firstCount).toBe(initialCount);

    await loadMoreBtn.click();
    await expect(grid.locator('.grid-item')).toHaveCount(firstClickExpectedCount);

    const firstNewTitle = await grid.locator('.grid-item').nth(firstCount).locator('h2').innerText();
    expect(firstNewTitle.trim()).toContain(expectedTitle);

    await loadMoreBtn.click();
    await expect(grid.locator('.grid-item')).toHaveCount(secondClickExpectedCount);
}


test.describe('e2e-home1-PC:', () => {

    // テスト本体
    test('ナビゲーションで次のページに遷移できること', async ({ page }) => {

        const CONFIG = {
            baseUrl: 'https://wpdev.toshidayurika.com',

        };

        const {
            baseUrl,
        } = CONFIG;

        await test.step('1. 管理画面にログイン', () => login(page, baseUrl));
        await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, baseUrl));
        await test.step('3. ホームタイプ設定を開く', () => setSiteType(page, 'エレガント'));

        // 既存の呼び出し部分はこう書き換え可能
        await test.step('4. 新着情報の確認', () =>
            verifyPageNavigation(page, baseUrl, '.post-grid', '.page-numbers', 10, 'TEST1', 'サイドFIRE｜【体験談】夫婦でサイドFIRE'));


    });

    // テスト本体
    test('カテゴリーナビゲーションでカテゴリーページに遷移できること', async ({ page }) => {

        const CONFIG = {
            baseUrl: 'https://wpdev.toshidayurika.com',

        };

        const {
            baseUrl,
        } = CONFIG;

        await test.step('1. 管理画面にログイン', () => login(page, baseUrl));
        await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, baseUrl));
        await test.step('3. ホームタイプ設定を開く', () => setSiteType(page, 'エレガント'));

        // 既存の呼び出し部分はこう書き換え可能
        await test.step('4. カテゴリーナビゲーションの確認', () =>
            verifyCategoryNavi(page, baseUrl, '.category-list', '.page-numbers', 3, 'FIRE・資産運用', 'カテゴリー: FIRE・資産運用'));


    });

});



test.describe('e2e-home2-PC:', () => {

    // テスト本体
    test('もっとみるボタンでカードを取得できること', async ({ page }) => {

        const CONFIG = {
            baseUrl: 'https://wpdev.toshidayurika.com',

        };

        const {
            baseUrl,
        } = CONFIG;

        await test.step('1. 管理画面にログイン', () => login(page, baseUrl));
        await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, baseUrl));
        await test.step('3. ホームタイプ設定を開く', () => setSiteType(page, 'ポップ'));
        await test.step('4. 公開ボタンをクリックして変更を保存', () =>
            saveCustomizer(page));

        // 既存の呼び出し部分はこう書き換え可能
        await test.step('5. 新着情報の確認', () =>
            verifyLoadMoreGeneric(page, baseUrl, '#latest-posts-grid', '#load-more', 4, 8, 12, 'サイドFIRE｜【体験談】筆者が資産7500万・'));

        await test.step('6. カテゴリ情報の確認', () =>
            verifyLoadMoreGeneric(page, baseUrl, '.category-posts', '.load-more-cat', 2, 4, 6, 'プラグインテスト', 0, 0));

        await test.step('7. カテゴリ情報の3つ目の確認', () =>
            verifyLoadMoreGeneric(page, baseUrl, '.category-posts', '.load-more-cat', 2, 4, 6, '節約｜【夫婦実録】月35万円', 2, 2));

    });
});
