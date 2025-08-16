import { test, expect } from '@playwright/test';

// 共通設定
const BASE_URL = 'https://wpdev.toshidayurika.com';

// テスト用設定一覧
const TEST_CONFIGS = {
    spHome1: {
        viewport: { width: 375, height: 800 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
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
        siteType: 'エレガント',
    },
    pcHome1: {
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
        siteType: 'エレガント',
    },
    spHome2: {
        viewport: { width: 375, height: 800 },
        userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
        imagePartialName: 'Firefly-260521.webp',
        siteType: 'ポップ',
    },
    pcHome2: {
        imagePartialName: 'Firefly-51159-1.webp',
        siteType: 'ポップ',
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

async function openSliderSetting(page) {
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダー設定' }).click();
}

async function setSliderEffectAndInterval(page, effectLabel, interval) {
    const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
    await effectSelect.selectOption({ label: effectLabel });
    await expect(effectSelect).toBeVisible();
    const intervalInput = page.getByLabel('変更時間間隔（秒）');
    await intervalInput.fill('999999');
    await intervalInput.fill(interval);
}

async function setSliderImage(page, imagePartialName, image_delBtnNo, image_selBtnNo) {
    await page.getByRole('button', { name: '削除' }).nth(image_delBtnNo).click();
    await page.getByRole('button', { name: '画像を選択' }).nth(image_selBtnNo).click();
    const mediaModal = page.locator('.attachments-browser');
    await mediaModal.waitFor({ state: 'visible', timeout: 10000 });
    const targetImage = page.locator(`img[src*="${imagePartialName}"]`).first();
    await expect(targetImage).toBeVisible({ timeout: 15000 });
    await targetImage.scrollIntoViewIfNeeded();
    await targetImage.click({ force: true });
    await page.locator('.media-button-select').click();
    await page.locator('.media-modal').waitFor({ state: 'hidden', timeout: 10000 });
    const selectedSrc = await targetImage.getAttribute('src');
    expect(selectedSrc).toContain(imagePartialName);
}

async function setSliderText(page, mainText, subText) {
    await page.getByLabel('スライダーテキスト（メイン）').nth(0).fill(mainText);
    await page.getByLabel('スライダーテキスト（サブ）').nth(0).fill(subText);
    await expect(page.getByLabel('スライダーテキスト（メイン）').nth(0)).toHaveValue(mainText);
    await expect(page.getByLabel('スライダーテキスト（サブ）').nth(0)).toHaveValue(subText);
}

async function setTextPosition(page, top, left, text_positionLavel_top, text_positionLavel_left) {


    await page.getByLabel(text_positionLavel_top).fill(top);
    await page.getByLabel(text_positionLavel_left).fill(left);
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


async function verifySliderOnSlide(page, baseUrl, imagePartialName, expectedCount = 2) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    await expect(page.locator('.slider.slide-effect')).toBeVisible();

    // 画像が1秒で切り替わる
    const getTranslateX = async () =>
        await page.locator('.slider.slide-effect .slides').evaluate(
            el => getComputedStyle(el).transform
        );

    const firstTransform = await getTranslateX();

    await expect.poll(async () => {
        const currentTransform = await getTranslateX();
        // transform が変わっていればスライドが動いたと判断
        return currentTransform !== firstTransform;
    }, {
        timeout: 5000,
        message: 'スライドが移動しませんでした',
    }).toBe(true);

    //クローンした画像と合わせて２つ存在
    await expect(
        page.locator(`.slider.slide-effect .slide img[src*="${imagePartialName}"]`)
    ).toHaveCount(expectedCount);

}

async function verifySliderOnHome2FadeSp(page, baseUrl, imagePartialName) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    await expect(page.locator('.slider.fade-effect')).toBeVisible();

    // 画像が1秒で切り替わる
    const getActiveImageSrc = async () =>
        await page.locator('.slider.fade-effect .slide.active img').getAttribute('src');
    const firstSrc = await getActiveImageSrc();
    await expect
        .poll(async () => {
            const currentSrc = await getActiveImageSrc();
            return currentSrc !== firstSrc;
        }, {
            timeout: 3000,
            message: 'スライド画像が切り替わりませんでした',
        })
        .toBe(true);

    await expect(
        page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
    ).toHaveCount(1);

}

async function verifySliderOnFront(page, baseUrl, imagePartialName, mainText, subText, top, left) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    await expect(page.locator('.slider.fade-effect')).toBeVisible();

    // 画像が1秒で切り替わる
    const getActiveImageSrc = async () =>
        await page.locator('.slider.fade-effect .slide.active img').getAttribute('src');
    const firstSrc = await getActiveImageSrc();
    await expect
        .poll(async () => {
            const currentSrc = await getActiveImageSrc();
            return currentSrc !== firstSrc;
        }, {
            timeout: 3000,
            message: 'スライド画像が切り替わりませんでした',
        })
        .toBe(true);

    await expect(
        page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
    ).toHaveCount(1);

    // テキストと位置確認
    const mainTextLocator = page.locator('.slider .text-overlay h1');
    const subTextLocator = page.locator('.slider .text-overlay h2');
    await expect(mainTextLocator).toHaveText(mainText);
    await expect(subTextLocator).toHaveText(subText);

    const overlay = page.locator('.slider .text-overlay');
    const position = await overlay.evaluate((el) => {
        const style = window.getComputedStyle(el);
        return {
            top: style.top,
            left: style.left,
        };
    });
    expect(position.top).toBe(`${top}px`);
    expect(position.left).toBe(`${left}px`);
}


async function verifySliderOnHome2Fade(page, baseUrl, imagePartialName) {
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    await expect(page.locator('.slider.fade-effect')).toBeVisible();

    await expect(
        page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
    ).toHaveCount(1);

    // 画像が1秒で切り替わる
    const getActiveImageSrc = async () =>
        await page.locator('.slider.fade-effect .slide-center img').getAttribute('src');

    const firstSrc = await getActiveImageSrc();
    await expect
        .poll(async () => {
            const currentSrc = await getActiveImageSrc();
            return currentSrc !== firstSrc;
        }, {
            timeout: 3000,
            message: 'スライド画像が切り替わりませんでした',
        })
        .toBe(true);

    const secondSrc = await getActiveImageSrc();
    await expect
        .poll(async () => {
            const currentSrc = await getActiveImageSrc();
            return currentSrc !== secondSrc;
        }, {
            timeout: 3000,
            message: 'スライド画像が切り替わりませんでした',
        })
        .toBe(true);

    const thirdSrc = await getActiveImageSrc();

    expect(firstSrc).not.toBe(secondSrc);
    expect(secondSrc).not.toBe(thirdSrc);
    expect(thirdSrc).not.toBe(firstSrc);

    expect(firstSrc.includes(imagePartialName)).toBe(true);

}



// 共通テストフロー
async function runCustomizerFlow(page, config) {
    await test.step('1. 管理画面にログイン', () => login(page, BASE_URL));
    await test.step('2. カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
    await test.step('3. スライダー設定を開く', () => openSliderSetting(page));
    await test.step('4. スライダーのエフェクトと変更間隔を設定', () =>
        setSliderEffectAndInterval(page, config.effectLabel, config.interval));
    await test.step('5.1 スライダー画像を設定', () =>
        setSliderImage(page, config.imagePartialName, config.image_delBtnNo, config.image_selBtnNo));
    await test.step('5.2 スライダーテキストを入力', () =>
        setSliderText(page, config.mainText, config.subText));
    await test.step('5.3 テキストの表示位置を設定', () =>
        setTextPosition(page, config.textPositionTop, config.textPositionLeft, config.text_positionLavel_top, config.text_positionLavel_left));
    await test.step('5.4. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));
    await test.step('6.カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
    await test.step('7.ホームタイプの変更', async () => {
        await setSiteType(page, config.siteType);
    });
    await test.step('8. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));
}

//テスト本体
test.describe('フェード', () => {

    test.describe('home1', () => {

        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS.spHome1.viewport,
                userAgent: TEST_CONFIGS.spHome1.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });

            test('カスタマイザーで画像、テキストを選択...', async ({ page }) => {
                const config = TEST_CONFIGS.spHome1;
                await runCustomizerFlow(page, config);
                await test.step('フロントページで表示確認', () =>
                    verifySliderOnFront(page, BASE_URL, config.imagePartialName, config.mainText, config.subText, config.textPositionTop, config.textPositionLeft));
            });
        });

        test.describe('PC環境', () => {
            test('カスタマイザーで画像、テキストを選択...', async ({ page }) => {
                const config = TEST_CONFIGS.pcHome1;
                await runCustomizerFlow(page, config);
                await test.step('フロントページで表示確認', () =>
                    verifySliderOnFront(page, BASE_URL, config.imagePartialName, config.mainText, config.subText, config.textPositionTop, config.textPositionLeft));
            });
        });
    });


    test.describe('home2', () => {
        test.beforeAll(async ({ browser }) => {

            const context = await browser.newContext();
            const page = await context.newPage();

            await test.step('1. 管理画面にログイン', () => login(page, BASE_URL));
            await test.step('2.1.カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
            await test.step('2.2. スライダー設定を開く', () => openSliderSetting(page));
            await test.step('2.3. スライダーのエフェクトと変更間隔を設定', () =>
                setSliderEffectAndInterval(page, 'フェード', '1'));
            await test.step('2.4. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));

            await test.step('3.1. カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
            await test.step('3.2 ホームタイプの変更', async () => {
                await setSiteType(page, 'ポップ');
            });
            await test.step('3.3. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));

            await page.close();
            await context.close();
        });


        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS.spHome2.viewport,
                userAgent: TEST_CONFIGS.spHome2.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });

            test('フェード画像切り替え確認', async ({ page }) => {
                const config = TEST_CONFIGS.spHome2;
                await test.step('トップページで表示確認', () =>
                    verifySliderOnHome2FadeSp(page, BASE_URL, config.imagePartialName));
            });
        });

        test.describe('PC環境', () => {


            test('フェード画像切り替え確認', async ({ page }) => {
                const config = TEST_CONFIGS.pcHome2;
                await test.step('トップページで表示確認', async () => {
                    await verifySliderOnHome2Fade(page, BASE_URL, config.imagePartialName);
                });
            });
        });
    });
});

test.describe('スライド', () => {

    test.describe('home1', () => {
        test.beforeAll(async ({ browser }) => {

            const context = await browser.newContext();
            const page = await context.newPage();

            await test.step('1. 管理画面にログイン', () => login(page, BASE_URL));
            await test.step('2.1.カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
            await test.step('2.2. スライダー設定を開く', () => openSliderSetting(page));
            await test.step('2.3. スライダーのエフェクトと変更間隔を設定', () =>
                setSliderEffectAndInterval(page, 'スライド', '1'));
            await test.step('2.4. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));
            await test.step('3.1. カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
            await test.step('3.2ホームタイプの変更', async () => {
                await setSiteType(page, 'エレガント');
            });
            await test.step('4. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));

            await page.close();
            await context.close();
        });


        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS.spHome2.viewport,
                userAgent: TEST_CONFIGS.spHome2.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });

            test('スライド画像切り替え確認', async ({ page }) => {
                const config = TEST_CONFIGS.spHome1;
                await test.step('トップページで表示確認', () =>
                    verifySliderOnSlide(page, BASE_URL, config.imagePartialName));
            });
        });

        test.describe('PC環境', () => {


            test('スライド画像切り替え確認', async ({ page }) => {
                const config = TEST_CONFIGS.pcHome1;
                await test.step('トップページで表示確認', async () => {
                    await verifySliderOnSlide(page, BASE_URL, config.imagePartialName);
                });
            });
        });

    });


    test.describe('home2', () => {
        test.beforeAll(async ({ browser }) => {

            const context = await browser.newContext();
            const page = await context.newPage();

            await test.step('1. 管理画面にログイン', () => login(page, BASE_URL));
            await test.step('2.1.カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
            await test.step('2.2. スライダー設定を開く', () => openSliderSetting(page));
            await test.step('2.3. スライダーのエフェクトと変更間隔を設定', () =>
                setSliderEffectAndInterval(page, 'スライド', '1'));
            await test.step('2.4. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));
            await test.step('3.1. カスタマイザー画面を開く', () => openCustomizer(page, BASE_URL));
            await test.step('3.2ホームタイプの変更', async () => {
                await setSiteType(page, 'ポップ');
            });
            await test.step('3.3. 公開ボタンをクリックして変更を保存', () => saveCustomizer(page));

            await page.close();
            await context.close();
        });


        test.describe('SP環境', () => {
            test.use({
                viewport: TEST_CONFIGS.spHome2.viewport,
                userAgent: TEST_CONFIGS.spHome2.userAgent,
                extraHTTPHeaders: { 'sec-ch-ua-mobile': '?1' },
            });

            test('スライド画像切り替え確認', async ({ page }) => {
                const config = TEST_CONFIGS.spHome2;
                await test.step('トップページで表示確認', () =>
                    verifySliderOnSlide(page, BASE_URL, config.imagePartialName));
            });
        });

        test.describe('PC環境', () => {


            test('未完了スライド画像切り替え確認', async ({ page }) => {
                const config = TEST_CONFIGS.pcHome2;
                await test.step('トップページで表示確認', async () => {
                    await verifySliderOnSlide(page, BASE_URL, config.imagePartialName, 3);
                });
            });
        });

    });
});