import { test, expect, type Page } from '@playwright/test';

// 共通設定
const BASE_URL = 'https://wpdev.toshidayurika.com';

// テスト設定を統合し、階層的な構造にする
const TEST_SCENARIOS = {
    'ヘッダーなし': {
        spHome1: {
            viewport: { width: 375, height: 800 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
            siteType: 'エレガント',
            headerType: 'なし',
            headCt: 0,
            bodyCt: 1,
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
        pcHome1: {
            siteType: 'エレガント',
            headerType: 'なし',
            headCt: 0,
            bodyCt: 3,
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
        spHome2: {
            viewport: { width: 375, height: 800 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
            siteType: 'ポップ',
            headerType: 'なし',
            headCt: 0,
            bodyCt: 2,
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
        pcHome2: {
            siteType: 'ポップ',
            headerType: 'なし',
            headCt: 0,
            bodyCt: 4,
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
    },
    'ヘッダーあり': {
        spHome1: {
            viewport: { width: 375, height: 800 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
            siteType: 'エレガント',
            headerType: 'スライダー',
            headCt: 1,
            bodyCt: 0, // 優先読み込みすべきボディ画像がないことを意味する
            headSelector: '.slider img',
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
        pcHome1: {
            siteType: 'エレガント',
            headerType: 'スライダー',
            headCt: 1,
            bodyCt: 0, // 優先読み込みすべきボディ画像がないことを意味する
            headSelector: '.slider img',
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
        spHome2: {
            viewport: { width: 375, height: 800 },
            userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
            siteType: 'ポップ',
            headerType: 'スライダー',
            headCt: 1,
            bodyCt: 1,
            headSelector: '.slider img',
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
        pcHome2: {
            siteType: 'ポップ',
            headerType: 'スライダー',
            headCt: 3,
            bodyCt: 2,
            headSelector: '.slider img',
            bodySelector: '.post-grid .grid-item .post-thumbnail img',
        },
    },
};

// 共通関数
async function login(page: Page, baseUrl: string) {
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'networkidle' });
    const adminUser = process.env.WP_ADMIN_USER;
    const adminPass = process.env.WP_ADMIN_PASSWORD;
    if (!adminUser || !adminPass) {
        throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が未定義');
    }
    await page.fill('#user_login', adminUser);
    await page.fill('#user_pass', adminPass);
    await page.click('#wp-submit');
    await page.waitForNavigation({ waitUntil: 'networkidle' });
}

async function openCustomizer(page: Page, baseUrl: string) {
    await page.goto(`${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`, {
        waitUntil: 'networkidle',
    });
    await expect(page.locator('.wp-full-overlay-main')).toBeVisible();
}

async function openHeaderSetting(page: Page, setting: string) {
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダーまたは画像を選択' }).click();
    const effectSelect = page.getByRole('combobox', { name: 'スライダーまたは画像を表示' });
    await effectSelect.selectOption({ label: setting });
}

async function selSliderFad(page: Page) {
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダー設定' }).click();
    const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
    await effectSelect.selectOption({ label: 'フェード' });
}

async function saveCustomizer(page: Page) {
    const saveBtn = page.locator('#save');
    if (!(await saveBtn.isEnabled())) {
        return;
    }
    await saveBtn.click();
    await expect(saveBtn).toHaveAttribute('value', '公開済み');
    await expect(saveBtn).toBeDisabled();
}

async function setSiteType(page: Page, siteType: string) {
    await page.getByRole('button', { name: 'サイトタイプ設定' }).click();
    const checkbox = page.getByLabel(siteType);
    if (!(await checkbox.isChecked())) {
        await checkbox.check();
    }
}

async function verifyImageAttributes(page: Page, baseUrl: string, selector: string, priorityCount = 0) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    const images = page.locator(selector);
    const count = await images.count();

    console.log(`画像の総数: ${count}`);

    for (let i = 0; i < count; i++) {
        const img = images.nth(i);
        const src = await img.getAttribute('src') || '(no src)';
        const fetchpriority = await img.getAttribute('fetchpriority');
        const loading = await img.getAttribute('loading');

        console.log(`[${i + 1}枚目:${src}] ct:${priorityCount} fetchpriority="${fetchpriority}" loading="${loading}`);

        if (i < priorityCount) {
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
async function runCustomizerFlow(page: Page, config: any) {
    await test.step('1. 管理画面にログイン', () => login(page, BASE_URL));

    await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
    await test.step('3. ヘッダー有無を設定', () => openHeaderSetting(page, config.headerType));
    await test.step('4. 変更を保存', () => saveCustomizer(page));

    await test.step('5. カスタマイザー画面を再度開く', () => openCustomizer(page, BASE_URL));
    await test.step('6. ホームタイプの変更', () => setSiteType(page, config.siteType));
    await test.step('7. 変更を保存', () => saveCustomizer(page));

    if (config.headerType === 'スライダー') {
        await test.step('8. スライダー設定', async () => {
            await openCustomizer(page, BASE_URL);
            await selSliderFad(page);
        });
        await test.step('9. 変更を保存', () => saveCustomizer(page));
    }
}

// データ駆動型テストの実行
for (const [headerGroup, scenarios] of Object.entries(TEST_SCENARIOS)) {
    test.describe(headerGroup, () => {
        for (const [testCaseName, config] of Object.entries(scenarios)) {
            const isSP = testCaseName.startsWith('sp');
            const deviceDesc = isSP ? 'SP環境' : 'PC環境';

            test.describe(`${testCaseName} (${deviceDesc})`, () => {
                let page: Page;

                test.beforeAll(async ({ browser }) => {
                    const context = await browser.newContext();
                    page = await context.newPage();
                    await runCustomizerFlow(page, config);
                });

                test.afterAll(async () => {
                    await page.close();
                });

                if (isSP) {
                    test.use({
                        viewport: (config as any).viewport,
                        userAgent: (config as any).userAgent,
                        extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
                    });
                }

                test('画像ロード属性を確認', async () => {
                    if (config.headCt > 0 && config.headSelector) {
                        console.log(`@@@@@@@@@@ヘッダー画像のチェック: ${config.headSelector}`);
                        await test.step('ヘッダー画像の属性チェック', () =>
                            verifyImageAttributes(page, BASE_URL, config.headSelector, config.headCt));
                    }
                    // bodySelectorが存在する場合、bodyCtの値に関わらずチェックを実行
                    if (config.bodySelector) {
                        console.log(`@@@@@@@@@@body画像のチェック: ${config.bodySelector}`);
                        await test.step('ボディ画像の属性チェック', () =>
                            verifyImageAttributes(page, BASE_URL, config.bodySelector, config.bodyCt));
                    }
                });
            });
        }
    });
}