// tests/auth.setup.ts
import { test as setup } from '@playwright/test';
import * as path from 'path';

const authFile = path.join(__dirname, '..', 'playwright/.auth/user.json');

setup('authenticate', async ({ page }) => {
    await page.goto('https://wpdev.toshidayurika.com/wp-login.php');
    await page.getByLabel('ユーザー名またはメールアドレス').fill(process.env.WP_ADMIN_USER!);
    await page.getByLabel('パスワード', { exact: true }).fill(process.env.WP_ADMIN_PASSWORD!);
    await page.getByRole('button', { name: 'ログイン' }).click();
    await page.waitForURL('**/wp-admin/');
    await page.context().storageState({ path: authFile });
});