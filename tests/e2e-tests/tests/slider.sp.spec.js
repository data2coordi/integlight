import { test, expect } from '@playwright/test';

test.use({
    viewport: { width: 375, height: 800 },
    userAgent:
        'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
});

const CONFIG = {
    baseUrl: 'http://wpdev.toshidayurika.com:7100',
    effectLabel: 'フェード',
    interval: '1',
    imagePartialName: 'Firefly-260521',
    mainText: 'テストタイトルsp',
    subText: 'これはPlaywrightテストによって入力された説明文です。sp',
    textPositionTop: '10',
    textPositionLeft: '15',
};

test('E2E-05(モバイル): カスタマイザーで画像、テキストを選択...', async ({ page }) => {
    const {
        baseUrl,
        effectLabel,
        interval,
        imagePartialName,
        mainText,
        subText,
        textPositionTop,
        textPositionLeft,
    } = CONFIG;

    await test.step('1. 管理画面にログイン', async () => {
        await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'networkidle' });
        const adminUser = process.env.WP_ADMIN_USER;
        const adminPass = process.env.WP_ADMIN_PASSWORD;
        if (!adminUser || !adminPass)
            throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が未定義');
        await page.fill('#user_login', adminUser);
        await page.fill('#user_pass', adminPass);
        await page.click('#wp-submit');
        await page.waitForNavigation({ waitUntil: 'networkidle' });
    });

    await test.step('2. カスタマイザー画面を開く', async () => {
        await page.goto(`${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`, {
            waitUntil: 'networkidle',
        });
        await expect(page.locator('.wp-full-overlay-main')).toBeVisible();
    });

    await test.step('3. スライダー設定を開く', async () => {
        await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
        await page.getByRole('button', { name: 'スライダー設定' }).click();
    });

    await test.step('4. スライダーのエフェクトと変更間隔を設定', async () => {
        const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
        await effectSelect.selectOption({ label: effectLabel });
        await expect(effectSelect).toBeVisible();
        const intervalInput = page.getByLabel('変更時間間隔（秒）');
        await intervalInput.fill('999999');
        await intervalInput.fill(interval);
    });

    await test.step('5.1 スライダー画像を設定', async () => {
        await page.getByRole('button', { name: '削除' }).nth(3).click();
        await page.getByRole('button', { name: '画像を選択' }).nth(0).click();

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
    });

    await test.step('5.2 スライダーテキストを入力', async () => {
        await page.getByLabel('スライダーテキスト（メイン）').nth(0).fill(mainText);
        await page.getByLabel('スライダーテキスト（サブ）').nth(0).fill(subText);
        await expect(page.getByLabel('スライダーテキスト（メイン）').nth(0)).toHaveValue(mainText);
        await expect(page.getByLabel('スライダーテキスト（サブ）').nth(0)).toHaveValue(subText);
    });

    await test.step('5.3 テキストの表示位置を設定', async () => {
        await page.getByLabel('スライダーテキスト位置（モバイル、上）').fill(textPositionTop);
        await page.getByLabel('スライダーテキスト位置（モバイル、左）').fill(textPositionLeft);
    });

    await test.step('6. 公開ボタンをクリックして変更を保存', async () => {
        const saveBtn = page.locator('#save');

        await saveBtn.click();

        // ボタンのvalueが「公開済み」になるまで待つ
        await expect(saveBtn).toHaveAttribute('value', '公開済み');

        // ボタンがdisabled状態になることも確認
        await expect(saveBtn).toBeDisabled();
    });

    await test.step('7. フロントページを開きスライダー表示を確認', async () => {
        await page.goto(baseUrl, { waitUntil: 'networkidle' });
        await expect(page.locator('.slider.fade-effect')).toBeVisible();
    });

    await test.step('8.1 スライド画像が1秒で切り替わることを確認', async () => {
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
    });

    await test.step('8.2 設定した画像がスライダーに存在することを確認', async () => {
        await expect(
            page.locator(`.slider.fade-effect .slide img[src*="${imagePartialName}"]`)
        ).toHaveCount(1);
    });

    await test.step('9. スライダーテキストとその表示位置を確認', async () => {
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
        expect(position.top).toBe(`${textPositionTop}px`);
        expect(position.left).toBe(`${textPositionLeft}px`);
    });
});
