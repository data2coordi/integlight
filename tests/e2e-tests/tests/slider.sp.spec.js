import { test, expect } from '@playwright/test';

// すべてのテストにモバイルの設定を適用
test.use({
    viewport: { width: 375, height: 800 },
    userAgent:
        'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/15.0 Mobile/15E148 Safari/604.1',
});

// テスト本体（省略部分はあなたの元のコードと同じ）
test('E2E-04(モバイル): カスタマイザーで画像、テキストを選択...', async ({ page }) => {
    const baseUrl = 'http://wpdev.toshidayurika.com:7100';


    await page.goto(baseUrl);
    await expect(page.locator('.slider.fade-effect')).toBeVisible();

    // 以下、これまでのテストコードをそのまま貼り付け

    // …ログイン～カスタマイザー操作…
    await page.waitForTimeout(3000);

    await expect(
        page.locator('.slider.fade-effect .slide img[src*="Firefly-260521"]')
    ).toHaveCount(1);

    // wp_is_mobile() が true になっていることを前提にした挙動がここで確認できるはず
});

test('E2E-05(モバイル): カスタマイザーで画像、テキストを選択...', async ({ context, page }) => {


    await page.setViewportSize({ width: 375, height: 800 });

    const baseUrl = 'http://wpdev.toshidayurika.com:7100';

    // 1. 管理画面にログイン
    await page.goto(`${baseUrl}/wp-login.php`, { waitUntil: 'networkidle' });
    const adminUser = process.env.WP_ADMIN_USER;
    const adminPass = process.env.WP_ADMIN_PASSWORD;
    if (!adminUser || !adminPass) throw new Error('環境変数 WP_ADMIN_USER または WP_ADMIN_PASSWORD が定義されていません');
    await page.fill('#user_login', adminUser);
    await page.fill('#user_pass', adminPass);
    await page.click('#wp-submit');
    await page.waitForNavigation({ waitUntil: 'networkidle' });

    // 2. カスタマイザーを開く
    await page.goto(
        `${baseUrl}/wp-admin/customize.php?url=${encodeURIComponent(baseUrl)}`,
        { waitUntil: 'networkidle' }
    );
    // ← 追加：カスタマイザーコントロールが表示されていることを確認
    const customizePane = page.locator('.wp-full-overlay-main');

    await expect(customizePane).toBeVisible();


    // 3. スライダー設定を開く
    await page.getByRole('button', { name: 'トップヘッダー設定' }).click();
    await page.getByRole('button', { name: 'スライダー設定' }).click();


    // 4. スライダー方式「エフェクト: スライド」を選択
    const effectSelect = page.getByRole('combobox', { name: 'エフェクト' });
    await effectSelect.selectOption({ label: 'フェード' });
    await expect(effectSelect).toBeVisible();
    await page.waitForTimeout(500);


    const intervalInput = page.getByLabel('変更時間間隔（秒）');
    await intervalInput.fill('999999');
    await page.waitForTimeout(500);
    await intervalInput.fill('1');



    // 5.1 画像設定 - 1枚目の画像設定開始

    // スライダー画像を一度削除してから選択
    await page.getByRole('button', { name: '削除' }).nth(3).click();; // ← 追加
    await page.waitForTimeout(500); // ← 念のため待機

    // 1) 画像変更ボタンをクリック（1枚目）
    await page.getByRole('button', { name: '画像を選択' }).nth(0).click();;

    // 2) メディアライブラリモーダルの表示を待つ
    const mediaModal = page.locator('.attachments-browser');
    await mediaModal.waitFor({ state: 'visible', timeout: 10000 });

    // 3) 対象画像を取得し、可視化を待ってスクロール
    const imageFileNamePartial = 'Firefly-260521';
    const targetImage = page.locator(`img[src*="${imageFileNamePartial}"]`).first();
    await expect(targetImage).toBeVisible({ timeout: 15000 });
    await targetImage.scrollIntoViewIfNeeded();


    // 4) クリックして選択完了
    await targetImage.click({ force: true });
    await page.locator('.media-button-select').click();
    // 画像選択完了後、モーダルが閉じるのを待つ

    await page.locator('.media-modal').waitFor({ state: 'hidden', timeout: 10000 });


    const selectedSrc = await targetImage.getAttribute('src');
    // 任意：期待される部分文字列を含むかどうか
    expect(selectedSrc).toContain('Firefly-260521');









    // 5.2 メインテキスト設定 - 1枚目の画像設定開始
    const titleInput = page.getByLabel('スライダーテキスト（メイン）').nth(0); // 1枚目のタイトル
    const descriptionInput = page.getByLabel('スライダーテキスト（サブ）').nth(0); // 1枚目の説明

    await titleInput.fill('テストタイトル');
    await descriptionInput.fill('これはPlaywrightテストによって入力された説明文です。');

    // 入力の確認（任意）
    await expect(titleInput).toHaveValue('テストタイトル');
    await expect(descriptionInput).toHaveValue('これはPlaywrightテストによって入力された説明文です。');

    // 5.3 メインテキストの位置設定 - 1枚目の画像設定開始
    // テキスト位置：上（top）
    const topInput = page.getByLabel('スライダーテキスト位置（上）');
    await topInput.fill('100');
    await expect(topInput).toHaveValue('100');

    // テキスト位置：左（left）
    const leftInput = page.getByLabel('スライダーテキスト位置（左）');
    await leftInput.fill('150');
    await expect(leftInput).toHaveValue('150');













    // 6. 「公開」ボタンをクリック（日本語環境に対応）
    await page.locator('#save').click();
    await page.waitForTimeout(500);

    // 7. フロント画面に遷移してスライダーが表示されることを確認
    await page.goto(baseUrl, { waitUntil: 'networkidle' });
    await expect(page.locator('.slider.fade-effect')).toBeVisible();

    // ８.1 画像が１秒で変わることを確認
    // 表示中のスライド画像のsrcを取得（activeクラスがあるスライド内のimg）
    const getActiveImageSrc = async () => {
        const activeSlide = page.locator('.slider.fade-effect .slide.active img');
        return activeSlide.getAttribute('src');
    };

    const firstSrc = await getActiveImageSrc();
    expect(firstSrc).not.toBeNull();

    // 1秒待つ（画像切り替え待機）
    await page.waitForTimeout(1100);

    const secondSrc = await getActiveImageSrc();
    expect(secondSrc).not.toBeNull();

    // 画像が切り替わっていることを確認
    expect(secondSrc).not.toBe(firstSrc);


    // 8.2 セットした画像がスライダーに含まれていることを確認
    //await expect(page.locator(`.slider.fade-effect .slide img[src*="Firefly-260521"]`)).toBeVisible({ timeout: 10000 });
    await expect(page.locator('.slider.fade-effect .slide img[src*="Firefly-260521"]')).toHaveCount(1);


    //9.1上記でセットしたテキストが表示されていること
    // 9.1 スライダーテキストが正しく表示されていることを確認
    const mainText = page.locator('.slider .text-overlay h1'); // メインタイトル
    const subText = page.locator('.slider .text-overlay h2');   // サブテキスト（p要素の場合）

    await expect(mainText).toHaveText('テストタイトル');
    await expect(subText).toHaveText('これはPlaywrightテストによって入力された説明文です。');

    //9.2テキストの表示されている位置が正しいこと
    const overlay = page.locator('.slider .text-overlay');

    // getComputedStyleで top / left の位置を取得
    const position = await overlay.evaluate((el) => {
        const style = window.getComputedStyle(el);
        return {
            top: style.top,
            left: style.left,
        };
    });

    // 期待される位置との比較
    expect(position.top).toBe('200px');
    expect(position.left).toBe('20px');

});


