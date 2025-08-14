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

async function setSiteType(page) {
    await page.getByRole('button', { name: 'サイトタイプ設定' }).click();
    // エレガントのチェックボックスをクリック
    // labelのテキストで取得する場合
    const popCheckbox = page.getByLabel('ポップ');
    const isChecked = await popCheckbox.isChecked();

    if (!isChecked) {
        await popCheckbox.check(); // チェックされていなければチェック
    } else {
        const elegantCheckbox = page.getByLabel('エレガント');
        await elegantCheckbox.check(); // チェックされていなければチェック
        await popCheckbox.check(); // チェックされていなければチェック
    }
}


async function saveCustomizer(page) {
    const saveBtn = page.locator('#save');
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
}



async function verifyLoadMore(page: any, baseUrl: string) {
    // ページに移動
    await page.goto(baseUrl, { waitUntil: 'networkidle' });

    const grid = page.locator('#latest-posts-grid');
    const loadMoreBtn = page.locator('#load-more');

    // クリック前のカード数
    const firstCount = await grid.locator('.grid-item').count();
    await expect(firstCount).toBe(4);


    // 1つ目の「もっと見る」をクリック

    await loadMoreBtn.click();
    let expectedCount = 8;
    await expect(grid.locator('.grid-item')).toHaveCount(expectedCount);




    // DOM更新待機
    //await page.waitForTimeout(3000);

    // 追加分の1件目タイトル取得
    const nextTopIndex = firstCount;
    const firstNewTitle = await grid
        .locator('.grid-item')
        .nth(nextTopIndex)
        .locator('h2')
        .innerText();

    const firstNewTitleTrimmed = firstNewTitle.trim();

    // 期待タイトル
    const expectedTitle = 'サイドFIRE｜【体験談】筆者が資産7500万・';
    expect(firstNewTitleTrimmed).toContain(expectedTitle);

    // 1つ目の「もっと見る」をクリック
    await loadMoreBtn.click();
    expectedCount = 12;
    await expect(grid.locator('.grid-item')).toHaveCount(expectedCount);

}


async function verifyLoadMoreCat(page: any, baseUrl: string) {
    // ページに移動
    await page.goto(baseUrl, { waitUntil: 'networkidle' });

    const grid = page.locator('.category-posts').first();
    const loadMoreBtn = page.locator('.load-more-cat').first();

    // クリック前のカード数
    const firstCount = await grid.locator('.grid-item').count();
    await expect(firstCount).toBe(2);


    // 1つ目の「もっと見る」をクリック

    await loadMoreBtn.click();
    let expectedCount = 4;
    await expect(grid.locator('.grid-item')).toHaveCount(expectedCount);




    // DOM更新待機
    //await page.waitForTimeout(3000);

    // 追加分の1件目タイトル取得
    const nextTopIndex = firstCount;
    const firstNewTitle = await grid
        .locator('.grid-item')
        .nth(nextTopIndex)
        .locator('h2')
        .innerText();

    const firstNewTitleTrimmed = firstNewTitle.trim();

    // 期待タイトル
    const expectedTitle = 'プラグインテスト';
    expect(firstNewTitleTrimmed).toContain(expectedTitle);

    // 1つ目の「もっと見る」をクリック
    await loadMoreBtn.click();
    expectedCount = 6;
    await expect(grid.locator('.grid-item')).toHaveCount(expectedCount);

}

async function verifyLoadMoreCat3(page: any, baseUrl: string) {
    // ページに移動
    await page.goto(baseUrl, { waitUntil: 'networkidle' });

    const grid = page.locator('.category-posts').nth(2);
    const loadMoreBtn = page.locator('.load-more-cat').nth(2);

    // クリック前のカード数
    const firstCount = await grid.locator('.grid-item').count();
    await expect(firstCount).toBe(2);


    // 1つ目の「もっと見る」をクリック

    await loadMoreBtn.click();
    let expectedCount = 4;
    await expect(grid.locator('.grid-item')).toHaveCount(expectedCount);




    // DOM更新待機
    //await page.waitForTimeout(3000);

    // 追加分の1件目タイトル取得
    const nextTopIndex = firstCount;
    const firstNewTitle = await grid
        .locator('.grid-item')
        .nth(nextTopIndex)
        .locator('h2')
        .innerText();

    const firstNewTitleTrimmed = firstNewTitle.trim();

    // 期待タイトル
    const expectedTitle = '節約｜【夫婦実録】月35万円';
    expect(firstNewTitleTrimmed).toContain(expectedTitle);

    // 1つ目の「もっと見る」をクリック
    await loadMoreBtn.click();
    expectedCount = 6;
    await expect(grid.locator('.grid-item')).toHaveCount(expectedCount);

}


/*
test.describe('モバイル環境', () => {
    // 共通設定
    test.use({
        viewport: { width: 375, height: 800 },
        userAgent:
            'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        extraHTTPHeaders: {
            'sec-ch-ua-mobile': '?1',
        },
    });
    // テスト本体
    test('E2E-slide-sp: カスタマイザーで画像、テキストを選択...', async ({ page }) => {

        const CONFIG = {
            baseUrl: 'https://wpdev.toshidayurika.com',
            effectLabel: 'フェード',
            interval: '1',
            imagePartialName: 'Firefly-260521',
            mainText: 'テストタイトルsp',
            subText: 'これはPlaywrightテストによって入力された説明文です。sp',
            textPositionTop: '10',
            textPositionLeft: '15',
            image_delBtnNo: 3,
            image_selBtnNo: 0,
            text_positionLavel_top: 'スライダーテキスト位置（モバイル、上）（px）',
            text_positionLavel_left: 'スライダーテキスト位置（モバイル、左）（px）',
        };

        const {
            baseUrl,
            effectLabel,
            interval,
            imagePartialName,
            mainText,
            subText,
            textPositionTop,
            textPositionLeft,
            image_delBtnNo,
            image_selBtnNo,
            text_positionLavel_top,
            text_positionLavel_left,
        } = CONFIG;

        await test.step('1. 管理画面にログイン', () => login(page, baseUrl));
        await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, baseUrl));
        await test.step('3. スライダー設定を開く', () => openSliderSetting(page));
        await test.step('4. スライダーのエフェクトと変更間隔を設定', () =>
            setSliderEffectAndInterval(page, effectLabel, interval));
        await test.step('5.1 スライダー画像を設定', () =>
            setSliderImage(page, imagePartialName, image_delBtnNo, image_selBtnNo));
        await test.step('5.2 スライダーテキストを入力', () =>
            setSliderText(page, mainText, subText));
        await test.step('5.3 テキストの表示位置を設定', () =>
            setTextPosition(page, textPositionTop, textPositionLeft, text_positionLavel_top, text_positionLavel_left));
        await test.step('6. 公開ボタンをクリックして変更を保存', () =>
            saveCustomizer(page));
        await test.step('7〜9. フロントページで表示確認', () =>
            verifySliderOnFront(page, baseUrl, imagePartialName, mainText, subText, textPositionTop, textPositionLeft));
    });
});
*/

test.describe('PC環境', () => {

    // テスト本体
    test('E2E-slide-PC: カスタマイザーで画像、テキストを選択...', async ({ page }) => {

        const CONFIG = {
            baseUrl: 'https://wpdev.toshidayurika.com',
            effectLabel: 'フェード',
            interval: '1',
            imagePartialName: 'Firefly-203280',
            mainText: 'テストタイトルpc',
            subText: 'これはPlaywrightテストによって入力された説明文です。pc',
            textPositionTop: '100',
            textPositionLeft: '150',
            image_delBtnNo: 0,
            image_selBtnNo: 0,
            text_positionLavel_top: 'スライダーテキスト位置（上）（px）',
            text_positionLavel_left: 'スライダーテキスト位置（左）（px）',
        };

        const {
            baseUrl,
            effectLabel,
            interval,
            imagePartialName,
            mainText,
            subText,
            textPositionTop,
            textPositionLeft,
            image_delBtnNo,
            image_selBtnNo,
            text_positionLavel_top,
            text_positionLavel_left,
        } = CONFIG;

        await test.step('1. 管理画面にログイン', () => login(page, baseUrl));
        await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, baseUrl));
        await test.step('3. ホームタイプ設定を開く', () => setSiteType(page));
        await test.step('4. 公開ボタンをクリックして変更を保存', () =>
            saveCustomizer(page));

        await test.step('5. 新着情報の確認', () =>
            verifyLoadMore(page, baseUrl));
        await test.step('6. カテゴリ情報の確認', () =>
            verifyLoadMoreCat(page, baseUrl));
        await test.step('7. カテゴリ情報の3つ目の確認', () =>
            verifyLoadMoreCat3(page, baseUrl));

    });
});
