import { test, expect } from '@playwright/test';

import {

    openCustomizer

} from '../utils/common';



// 共通関数：テーマを切り替え
async function activateTheme(page, themeSlug: string) {
    await page.goto(`/wp-admin/themes.php`, { waitUntil: 'networkidle' });

    const themeSelector = `.theme[data-slug="${themeSlug}"]`;
    const activateButton = page.locator(`${themeSelector} .theme-actions .activate`);
    const isActive = await page.locator(`${themeSelector}.active`).count();

    if (isActive === 0) {
        await activateButton.click();
        await page.waitForSelector(`${themeSelector}.active`, { timeout: 5000 });
    }
}

/**
 * 画面上にコード内容を可視表示する（動画出力向け）
 * @param page PlaywrightのPageオブジェクト
 * @param code 表示したいコード文字列
 */
export async function showCodeOverlay(page: Page, code: string) {
    await page.evaluate((code) => {
        const el = document.createElement('pre');
        el.textContent = code;
        el.style.position = 'fixed';
        el.style.top = '0';
        el.style.left = '0';
        el.style.padding = '10px';
        el.style.background = 'white';
        el.style.color = 'black';
        el.style.fontSize = '14px';
        el.style.zIndex = '99999';
        el.id = 'visible-script-code';
        document.body.appendChild(el);


    }, code);
    await page.waitForTimeout(3000);
}

// 4ケース共通のテスト群を関数化
function runCustomizerTests({
    testTitlePrefix,
    settingButtonName,
    inputLabel,
    inputCode,
    outputTarget, // 'head' or 'body'
}: {
    testTitlePrefix: string;
    settingButtonName: string;
    inputLabel: string;
    inputCode: string;
    outputTarget: 'head' | 'body';
}) {
    test.describe(`${testTitlePrefix} カスタマイザー設定`, () => {
        test('E2E-01:トラッキングIDを入力し保存できる', async ({ page }) => {
            await openCustomizer(page);

            await page.getByRole('button', { name: settingButtonName }).click();
            const input = page.getByLabel(inputLabel);

            await input.fill('');
            await input.fill(inputCode);

            const saveBtn = page.locator('#save');
            await expect(saveBtn).toBeEnabled();

            await saveBtn.click();
            await expect(saveBtn).toHaveAttribute('value', '公開済み');
            await expect(saveBtn).toBeDisabled();
        });

        test('E2E-02:保存したトラッキングIDが次回表示時に復元される', async ({ page }) => {
            await openCustomizer(page);

            await page.getByRole('button', { name: settingButtonName }).click();
            const input = page.getByLabel(inputLabel);

            await expect(input).toHaveValue(inputCode);
        });

        test('E2E-03: トラッキングIDがフロントエンドに出力される', async ({ page }) => {
            await page.goto('/', { waitUntil: 'networkidle' });
            const targetContent = await page.locator(outputTarget).innerHTML();
            expect(targetContent).toContain(inputCode);

            await showCodeOverlay(page, inputCode);

        });
        test('E2E-04: Twenty Twentyでも出力される', async ({ page }) => {
            await activateTheme(page, 'twentytwenty');

            await page.goto('/', { waitUntil: 'networkidle' });
            const headContent = await page.locator(outputTarget).innerHTML();

            expect(headContent).toContain(inputCode);
            await activateTheme(page, 'integlight');
            await showCodeOverlay(page, inputCode);

        });

        // 5. Twenty Twentyで保存IDが復元される
        test('E2E-05: Twenty Twentyでも保存したトラッキングIDが次回表示時に復元される', async ({ page }) => {
            await activateTheme(page, 'twentytwenty');
            await openCustomizer(page);

            await page.getByRole('button', { name: settingButtonName }).click();
            const trackingIdInput = page.getByLabel(inputLabel);

            await expect(trackingIdInput).toHaveValue(inputCode);
            await activateTheme(page, 'integlight');
            await showCodeOverlay(page, inputCode);


        });
    });
}



runCustomizerTests({
    testTitlePrefix: 'GA',
    settingButtonName: 'Google Analytics 設定',
    inputLabel: 'Google Analytics トラッキングコード',
    inputCode: '<script>//UA-12345678-1</script>',
    outputTarget: 'head',
});


runCustomizerTests({
    testTitlePrefix: 'GTM-head',
    settingButtonName: 'Google Tag Manager 設定',
    inputLabel: 'headタグに出力するコード',
    inputCode: '<script>//GTM-head-12345678-1</script>',
    outputTarget: 'head',
});

runCustomizerTests({
    testTitlePrefix: 'GTM-body',
    settingButtonName: 'Google Tag Manager 設定',
    inputLabel: 'bodyタグ開始直後に出力するコード',
    inputCode: '<script>//GTM-body-12345678-1</script>',
    outputTarget: 'body',
});

runCustomizerTests({
    testTitlePrefix: 'adSense',
    settingButtonName: 'Googleアドセンス自動広告',
    inputLabel: 'アドセンス自動広告コード',
    inputCode: '<script>//adSense-head-12345678-1</script>',
    outputTarget: 'head',
});


// 追加で必要なら、Twenty Twenty テーマでの確認テストも別途関数化可能です。
