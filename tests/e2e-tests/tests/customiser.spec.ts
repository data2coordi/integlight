import { test, expect } from '@playwright/test';

// 共通関数：ログイン処理
async function login(page, baseUrl: string) {
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

// 共通関数：カスタマイザーを開く
async function openCustomizer(page, baseUrl: string) {
    const customizerUrl = `${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`;
    await page.goto(customizerUrl, { waitUntil: 'networkidle' });
    await expect(page.locator('.wp-full-overlay-main')).toBeVisible({ timeout: 10000 });
}

// 共通関数：テーマを切り替え
async function activateTheme(page, baseUrl: string, themeSlug: string) {
    await page.goto(`${baseUrl}/wp-admin/themes.php`, { waitUntil: 'networkidle' });

    const themeSelector = `.theme[data-slug="${themeSlug}"]`;
    const activateButton = page.locator(`${themeSelector} .theme-actions .activate`);
    const isActive = await page.locator(`${themeSelector}.active`).count();

    if (isActive === 0) {
        await activateButton.click();
        await page.waitForSelector(`${themeSelector}.active`, { timeout: 5000 });
    }
}

// 4ケース共通のテスト群を関数化
function runCustomizerTests({
    testTitlePrefix,
    baseUrl,
    settingButtonName,
    inputLabel,
    inputCode,
    outputTarget, // 'head' or 'body'
}: {
    testTitlePrefix: string;
    baseUrl: string;
    settingButtonName: string;
    inputLabel: string;
    inputCode: string;
    outputTarget: 'head' | 'body';
}) {
    test.describe(`${testTitlePrefix} カスタマイザー設定`, () => {
        test('E2E-GA-01:トラッキングIDを入力し保存できる', async ({ page }) => {
            await login(page, baseUrl);
            await openCustomizer(page, baseUrl);

            await page.getByRole('button', { name: settingButtonName }).click();
            const input = page.getByLabel(inputLabel);

            await input.fill('');
            await input.fill(inputCode);

            const saveBtn = page.locator('#save');
            await saveBtn.click();
            await expect(saveBtn).toHaveAttribute('value', '公開済み');
            await expect(saveBtn).toBeDisabled();
        });

        test('E2E-GA-02:保存したトラッキングIDが次回表示時に復元される', async ({ page }) => {
            await login(page, baseUrl);
            await openCustomizer(page, baseUrl);

            await page.getByRole('button', { name: settingButtonName }).click();
            const input = page.getByLabel(inputLabel);

            await expect(input).toHaveValue(inputCode);
        });

        test('E2E-GA-03: トラッキングIDがフロントエンドに出力される', async ({ page }) => {
            await page.goto(baseUrl, { waitUntil: 'networkidle' });
            const targetContent = await page.locator(outputTarget).innerHTML();
            expect(targetContent).toContain(inputCode);
        });
        test('E2E-GA-04: Twenty TwentyでもGAがheadに出力される', async ({ page }) => {
            await login(page, baseUrl);
            await activateTheme(page, baseUrl, 'twentytwenty');

            await page.goto(baseUrl, { waitUntil: 'networkidle' });
            const headContent = await page.locator(outputTarget).innerHTML();

            expect(headContent).toContain(inputCode);
            await activateTheme(page, baseUrl, 'integlight');
        });

        // 5. Twenty Twentyで保存IDが復元される
        test('E2E-GA-05: Twenty Twentyでも保存したトラッキングIDが次回表示時に復元される', async ({ page }) => {
            await login(page, baseUrl);
            await activateTheme(page, baseUrl, 'twentytwenty');
            await openCustomizer(page, baseUrl);

            await page.getByRole('button', { name: settingButtonName }).click();
            const trackingIdInput = page.getByLabel(inputLabel);

            await expect(trackingIdInput).toHaveValue(inputCode);
            await activateTheme(page, baseUrl, 'integlight');
        });
    });
}

const baseUrl = 'http://wpdev.toshidayurika.com:7100';


runCustomizerTests({
    testTitlePrefix: 'Google Analytics',
    baseUrl,
    settingButtonName: 'Google Analytics 設定',
    inputLabel: 'Google Analytics トラッキングコード',
    inputCode: '<script>UA-12345678-1</script>',
    outputTarget: 'head',
});


runCustomizerTests({
    testTitlePrefix: 'Google Tag Manager（head）',
    baseUrl,
    settingButtonName: 'Google Tag Manager 設定',
    inputLabel: 'headタグに出力するコード',
    inputCode: '<script>GTM-head-12345678-1</script>',
    outputTarget: 'head',
});

runCustomizerTests({
    testTitlePrefix: 'Google Tag Manager（body）',
    baseUrl,
    settingButtonName: 'Google Tag Manager 設定',
    inputLabel: 'bodyタグ開始直後に出力するコード',
    inputCode: '<script>GTM-body-12345678-1</script>',
    outputTarget: 'body',
});

runCustomizerTests({
    testTitlePrefix: 'Googleアドセンス自動広告',
    baseUrl,
    settingButtonName: 'Googleアドセンス自動広告',
    inputLabel: 'アドセンス自動広告コード',
    inputCode: '<script>adSense-head-12345678-1</script>',
    outputTarget: 'head',
});


// 追加で必要なら、Twenty Twenty テーマでの確認テストも別途関数化可能です。
