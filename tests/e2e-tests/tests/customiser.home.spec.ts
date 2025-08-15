import { test, expect } from '@playwright/test';



// 共通関数
async function login(page, baseUrl) {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'networkidle' });
    const adminUser = process.env.WP_ADMIN_USER;
    const adminPass = process.env.WP_ADMIN_PASSWORD;
    if (!adminUser || !adminPass)
        throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が未定義');
    await page.fill('#user_login', adminUser);
    await page.fill('#user_pass', adminPass);
    await page.click('#wp-submit');
    await page.waitForNavigation({ waitUntil: 'networkidle' });
}

async function openCustomizer(page, baseUrl) {
    await page.goto(`${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`, {
        waitUntil: 'networkidle',
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
        saveCustomizer(page); // 変更を保存
    }

}


async function saveCustomizer(page) {
    const saveBtn = page.locator('#save');
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
}

// 共通化関数（もっと見るボタン）
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

// 共通化関数（ページナビゲーション）
async function verifyPageNavigation(
    page: any,
    baseUrl: string,
    gridSelector: string,
    loadMoreSelector: string,
    initialCount: number,
    firstClickExpectedCount: number,
    secondClickExpectedCount: number,
    expectedTitle: string,
    buttonIndex = 0
) {
    console.log('@@@@@@@@@@@@@@@@@@@@@1');
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    console.log('@@@@@@@@@@@@@@@@@@@@@2');
    const grid = page.locator(gridSelector)
    //const loadMoreBtn = page.locator(loadMoreSelector).nth(buttonIndex);
    console.log('@@@@@@@@@@@@@@@@@@@@@3');

    const firstCount = await grid.locator('.grid-item').count();
    console.log('Initial count:', firstCount);
    await expect(firstCount).toBe(initialCount);

    /*
    await loadMoreBtn.click();
    await expect(grid.locator('.grid-item')).toHaveCount(firstClickExpectedCount);

    const firstNewTitle = await grid.locator('.grid-item').nth(firstCount).locator('h2').innerText();
    expect(firstNewTitle.trim()).toContain(expectedTitle);

    await loadMoreBtn.click();
    await expect(grid.locator('.grid-item')).toHaveCount(secondClickExpectedCount);
    */
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
        await test.step('5. 新着情報の確認', () =>
            verifyPageNavigation(page, baseUrl, '.posts-grid', '#load-more', 10, 10, 10, 'TEST1'));


    });
});


// test.describe('e2e-home2-PC:', () => {

//     // テスト本体
//     test('もっとみるボタンでカードを取得できること', async ({ page }) => {

//         const CONFIG = {
//             baseUrl: 'https://wpdev.toshidayurika.com',

//         };

//         const {
//             baseUrl,
//         } = CONFIG;

//         await test.step('1. 管理画面にログイン', () => login(page, baseUrl));
//         await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, baseUrl));
//         await test.step('3. ホームタイプ設定を開く', () => setSiteType(page,'ポップ'));
//         await test.step('4. 公開ボタンをクリックして変更を保存', () =>
//             saveCustomizer(page));

//         // 既存の呼び出し部分はこう書き換え可能
//         await test.step('5. 新着情報の確認', () =>
//             verifyLoadMoreGeneric(page, baseUrl, '#latest-posts-grid', '#load-more', 4, 8, 12, 'サイドFIRE｜【体験談】筆者が資産7500万・'));

//         await test.step('6. カテゴリ情報の確認', () =>
//             verifyLoadMoreGeneric(page, baseUrl, '.category-posts', '.load-more-cat', 2, 4, 6, 'プラグインテスト', 0, 0));

//         await test.step('7. カテゴリ情報の3つ目の確認', () =>
//             verifyLoadMoreGeneric(page, baseUrl, '.category-posts', '.load-more-cat', 2, 4, 6, '節約｜【夫婦実録】月35万円', 2, 2));

//     });
// });
