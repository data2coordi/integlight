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
test.describe('Google Analytics カスタマイザー設定（PC）', () => {
    test.use({
        viewport: { width: 1280, height: 800 },
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36',
    });

    const baseUrl = 'http://wpdev.toshidayurika.com:7100';




    test('E2E-GA-01: トラッキングIDを入力し保存できる', async ({ page }) => {
        await login(page, baseUrl);
        await openCustomizer(page, baseUrl);

        await page.getByRole('button', { name: 'Google Analytics 設定' }).click();
        const trackingIdInput = page.getByLabel('Google Analytics トラッキングコード');

        await trackingIdInput.fill('');
        await trackingIdInput.fill('<script>UA-12345678-1</script>');

        const saveBtn = page.locator('#save');
        await saveBtn.click();
        await expect(saveBtn).toHaveAttribute('value', '公開済み');
        await expect(saveBtn).toBeDisabled();
    });

    test('E2E-GA-02: 保存したトラッキングIDが次回表示時に復元される', async ({ page }) => {
        await login(page, baseUrl);
        await openCustomizer(page, baseUrl);

        await page.getByRole('button', { name: 'Google Analytics 設定' }).click();
        const trackingIdInput = page.getByLabel('Google Analytics トラッキングコード');

        await expect(trackingIdInput).toHaveValue('<script>UA-12345678-1</script>');
    });

    test('E2E-GA-03: トラッキングIDがフロントエンドに出力される', async ({ page }) => {
        await page.goto(baseUrl, { waitUntil: 'networkidle' });
        const headContent = await page.locator('head').innerHTML();
        expect(headContent).toContain('<script>UA-12345678-1</script>');

    });

    test('E2E-GA-04: Twenty TwentyでもGAがheadに出力される', async ({ page }) => {
        const baseUrl = 'http://wpdev.toshidayurika.com:7100';
        const twentySlug = 'twentytwenty';
        const integlightSlug = 'integlight';


        await login(page, baseUrl);
        await activateTheme(page, baseUrl, twentySlug);

        await page.goto(baseUrl, { waitUntil: 'networkidle' });
        const headContent = await page.locator('head').innerHTML();

        expect(headContent).toContain('<script>UA-12345678-1</script>');
        await activateTheme(page, baseUrl, integlightSlug);

    });

    test('E2E-GA-05: Twenty Twentyでも保存したトラッキングIDが次回表示時に復元される', async ({ page }) => {
        await login(page, baseUrl);

        const twentySlug = 'twentytwenty';
        const integlightSlug = 'integlight';

        await activateTheme(page, baseUrl, twentySlug);
        await openCustomizer(page, baseUrl);

        await page.getByRole('button', { name: 'Google Analytics 設定' }).click();
        const trackingIdInput = page.getByLabel('Google Analytics トラッキングコード');

        await expect(trackingIdInput).toHaveValue('<script>UA-12345678-1</script>');
        await activateTheme(page, baseUrl, integlightSlug);
    });


});
