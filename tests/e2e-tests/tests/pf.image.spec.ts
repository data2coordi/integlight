import { test, expect } from '@playwright/test';

// 共通設定
const BASE_URL = 'https://wpdev.toshidayurika.com';

// テスト用設定一覧
const TEST_CONFIGS = {
    spHome1: {
        viewport: { width: 375, height: 800 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        siteType: 'エレガント',
        headerType: 'スライダー',
        headCt: 1,
        bodyCt: 0,
    },
    pcHome1: {
        siteType: 'エレガント',
        headerType: 'スライダー',
        headCt: 1,
        bodyCt: 0,
    },
    spHome2: {
        viewport: { width: 375, height: 800 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        siteType: 'ポップ',
        headerType: 'スライダー',
        headCt: 1,
        bodyCt: 1,
    },
    pcHome2: {
        siteType: 'ポップ',
        headerType: 'スライダー',
        headCt: 3,
        bodyCt: 2,
    },
};

const TEST_CONFIGS_NO_HEAD = {
    spHome1: {
        viewport: { width: 375, height: 800 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        siteType: 'エレガント',
        headerType: 'なし',
        bodyCt: 1,
    },
    pcHome1: {
        siteType: 'エレガント',
        headerType: 'なし',
        bodyCt: 3,
    },
    spHome2: {
        viewport: { width: 375, height: 800 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        siteType: 'ポップ',
        headerType: 'なし',
        bodyCt: 2,
    },
    pcHome2: {
        siteType: 'ポップ',
        headerType: 'なし',
        bodyCt: 4,
    },
};


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

async function openHeaderSetting(page, setting) {
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダーまたは画像を選択' }).click();
    const effectSelect = page.getByRole('combobox', { name: 'スライダーまたは画像を表示' });
    await effectSelect.selectOption({ label: setting });

}




async function saveCustomizer(page) {
    const saveBtn = page.locator('#save');

    if (!(await saveBtn.isEnabled())) {
        return;

    }
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();

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
    }

}



async function verifyImageAttributes(page, baseUrl, selector, priorityCount = 1) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });

    const images = page.locator(selector);
    const count = await images.count();

    //console.log('@@@@@@@@start@@@@@@@@@');
    for (let i = 0; i < count; i++) {
        const img = images.nth(i);
        const src = await img.getAttribute('src') || '(no src)';
        const fetchpriority = await img.getAttribute('fetchpriority');
        const loading = await img.getAttribute('loading');

        //console.log(`[${i + 1}枚目:${src}] ct:${priorityCount} fetchpriority="${fetchpriority}" loading="${loading}`);

        if (i < priorityCount) {
            // 優先読み込み対象
            if (fetchpriority !== 'high') {
                throw new Error(
                    `[${i + 1}枚目:${src}] fetchpriority expected="high" actual="${fetchpriority}"`
                );
            }
            if (loading === 'lazy') {
                throw new Error(
                    `[${i + 1}枚目:${src}] loading should NOT be "lazy", actual="${loading}"`
                );
            }
        } else {
            // 遅延読み込み対象
            if (loading !== 'lazy') {
                throw new Error(
                    `[${i + 1}枚目:${src}] loading expected="lazy" actual="${loading}"`
                );
            }
            if (fetchpriority === 'high') {
                throw new Error(
                    `[${i + 1}枚目:${src}] fetchpriority should NOT be "high", actual="${fetchpriority}"`
                );
            }
        }
    }
}






// 共通テストフロー
async function runCustomizerFlow(page, config) {
    await test.step('1. 管理画面にログイン', () => login(page, BASE_URL));
    await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
    await test.step('3. ヘッダー有無を設定', () => openHeaderSetting(page, config.headerType));
    await test.step('4. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));
    await test.step('5.カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
    await test.step('6.ホームタイプの変更', async () => {
        await setSiteType(page, config.siteType);
    });
    await test.step('8. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));
}

//テスト本体

test.describe('ヘッダースライダー無し', () => {

    test.describe('home1', () => {

        let page: Page;

        test.beforeAll(async ({ browser }) => {
            const context = await browser.newContext();
            page = await context.newPage();
            const config = TEST_CONFIGS_NO_HEAD.spHome1;
            await runCustomizerFlow(page, config);
        });

        test.afterAll(async () => {
            await page.close();
        });

        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS_NO_HEAD.spHome1.viewport,
                userAgent: TEST_CONFIGS_NO_HEAD.spHome1.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });


            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS_NO_HEAD.spHome1.bodyCt));
            });
        });

        test.describe('PC環境', () => {
            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS_NO_HEAD.pcHome1.bodyCt));

            });
        });
    });


    test.describe('home2', () => {

        let page: Page;

        test.beforeAll(async ({ browser }) => {
            const context = await browser.newContext();
            page = await context.newPage();
            const config = TEST_CONFIGS_NO_HEAD.spHome2;
            await runCustomizerFlow(page, config);
        });

        test.afterAll(async () => {
            await page.close();
        });

        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS_NO_HEAD.spHome1.viewport,
                userAgent: TEST_CONFIGS_NO_HEAD.spHome1.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });


            test('画像ロード属性を確認', async ({ page }) => {

                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS_NO_HEAD.spHome2.bodyCt));
            });
        });

        test.describe('PC環境', () => {
            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS_NO_HEAD.pcHome2.bodyCt));

            });
        });
    });

});

test.describe('ヘッダースライダー有り', () => {

    test.describe('home1', () => {

        let page: Page;

        test.beforeAll(async ({ browser }) => {
            const context = await browser.newContext();
            page = await context.newPage();
            const config = TEST_CONFIGS.spHome1;
            await runCustomizerFlow(page, config);
        });

        test.afterAll(async () => {
            await page.close();
        });

        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS.spHome1.viewport,
                userAgent: TEST_CONFIGS.spHome1.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });


            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでヘッダー画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.slider img', 1));
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS.spHome1.bodyCt));
            });
        });

        test.describe('PC環境', () => {
            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでヘッダー画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.slider img', 1));
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS.pcHome1.bodyCt));

            });
        });
    });


    test.describe('home2', () => {

        let page: Page;

        test.beforeAll(async ({ browser }) => {
            const context = await browser.newContext();
            page = await context.newPage();
            const config = TEST_CONFIGS.spHome2;
            await runCustomizerFlow(page, config);
        });

        test.afterAll(async () => {
            await page.close();
        });

        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS.spHome1.viewport,
                userAgent: TEST_CONFIGS.spHome1.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });


            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでヘッダー画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.slider img', TEST_CONFIGS.spHome2.headCt));
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS.spHome2.bodyCt));
            });
        });

        test.describe('PC環境', () => {
            test('画像ロード属性を確認', async ({ page }) => {
                await test.step('ホームページでヘッダー画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.slider img', TEST_CONFIGS.pcHome2.headCt));
                await test.step('ホームページでボディ画像の属性チェック', () =>
                    verifyImageAttributes(page, BASE_URL, '.post-grid .grid-item .post-thumbnail img', TEST_CONFIGS.pcHome2.bodyCt));

            });
        });
    });

});

